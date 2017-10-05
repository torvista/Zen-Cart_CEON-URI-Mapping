<?php

/**steve merged html_output.php ZC155 changes
 * Ceon URI Mapping URI HREF Link Builder Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingHREFLinkBuilder.php 1054 2012-09-22 15:45:15Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingDBLookup.php');


// {{{ CeonURIMappingHREFLinkBuilder

/**
 * Provides functionality to build a static URI for a Zen Cart page, for use in a HTML link's href
 * parameter.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingHREFLinkBuilder extends CeonURIMappingDBLookup
{
	// {{{ properties
	
	/**
	 * The link built by this instance.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_href_link = '';
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingHREFLinkBuilder class.
	 * 
	 * @access  public
	 */
	function __construct()
	{
		
	}
	
	// }}}
	
	
	// {{{ buildHREFLink()
	
	/**
	 * Builds a static URI-based link for a Zen Cart page, if a mapping exists for the page. The link is built in a
	 * format that can be directly included in a HTML link's href attribute.
	 *
	 * @access  public
	 * @param   string    $base_link    The base string for the link, as built by the standard Zen Cart 
	 *                                  zen_href_link() function.
	 * @param   string    $main_page    The Zen Cart page type the link is to.
	 * @param   string    $parameters   The query string parameters which are to be used to identify the specific
	 *                                  Zen Cart page and to build any additional parameters for that page.
	 * @param   string    $connection   The connection type the link is to use.
	 * @param   boolean   $add_session_id   Whether or not to add the session ID to the link.
	 * @return  boolean   Whether or not a static URI was successfully built for the Zen Cart page.
	 */
	function buildHREFLink($base_link, $main_page, $parameters, $connection, $add_session_id)
	{
		global $request_type, $session_started, $http_domain, $https_domain, $ceon_uri_mapping_product_pages,
			$ceon_uri_mapping_product_related_pages;
		
		// Reset the link for this instance
		$this->_href_link = '';
		
		// Variable holds the main part of the link being built
		$link = '';
		
		if (preg_match('/.*index.php\?main_page=([^&]+)/i', $main_page, $matches)) {
			// Link isn't to a page's filename but to a specific URI (e.g. an EZ-Page alt url)
			// Extract the page ID and any parameters from the string
			$main_page = $matches[1];
			
			$temp = str_replace($matches[0], '', $page);
			
			$parameters = $parameters . $temp;
		}
		
		if ($main_page == FILENAME_DEFAULT && $parameters != '' &&
				strpos(strtolower($parameters), 'manufacturers_id=') !== false) {
			// This link is to a manufacturer's page
			
			// Get the manufacturer ID
			$pattern = '/[&\?]?(manufacturers_id=([0-9]+))/i';
			
			if (preg_match($pattern, $parameters, $matches)) {
				$manufacturer_query_pair = $matches[1];
				$manufacturer_id = $matches[2];
				
				$manufacturer_parameter = 'manufacturers_id=' . $manufacturer_id;
				
				$columns_to_retrieve = array(
					'uri'
					);
				
				$selections = array(
					'main_page' => $main_page,
					'query_string_parameters' => $manufacturer_parameter,
					'language_id' => (int) $_SESSION['languages_id'],
					'current_uri' => 1
					);
				
				$uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
				
				if (!$uri_mapping_result->EOF) {
					$link = $uri_mapping_result->fields['uri'];
					
					// Remove the manufacturer ID from the URI as it will be regenerated when the static URI is
					// loaded
					$parameters = str_replace($manufacturer_query_pair . '&', '', $parameters);
					$parameters = str_replace($manufacturer_query_pair, '', $parameters);
				}
			}
		} else if ($main_page == FILENAME_DEFAULT && $parameters != '' &&
				strpos(strtolower($parameters), 'typefilter=') !== false) {
			// This link is to a category page
			
			// Get the filter information
			$pattern = '/[&\?]?(typefilter=([^&]+))/i';
			
			if (preg_match($pattern, $parameters, $matches)) {
				$typefilter_name_query_pair = $matches[1];
				$typefilter_name = $matches[2];
				
				// Attempt to get ID for filter's property
				$pattern = '/[&\?]?(' . $typefilter_name . '_id=([^&]+))/i';
				
				if (preg_match($pattern, $parameters, $matches)) {
					$typefilter_id_query_pair = $matches[1];
					$typefilter_id = $matches[2];
					
					$typefilter_parameter = 'typefilter=' . $typefilter_name . '&' . $typefilter_name . '_id=' .
						$typefilter_id;
					
					$columns_to_retrieve = array(
						'uri'
						);
					
					$selections = array(
						'main_page' => $main_page,
						'query_string_parameters' => $typefilter_parameter,
						'language_id' => (int) $_SESSION['languages_id'],
						'current_uri' => 1
						);
					
					$uri_mapping_result =
						$this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
					
					if (!$uri_mapping_result->EOF) {
						$link = $uri_mapping_result->fields['uri'];
						
						// Remove the typefilter info from the URI as it will be regenerated when the static URI is
						// loaded
						$parameters = str_replace($typefilter_id_query_pair . '&', '', $parameters);
						$parameters = str_replace($typefilter_id_query_pair, '', $parameters);
						
						$parameters = str_replace($typefilter_name_query_pair . '&', '', $parameters);
						$parameters = str_replace($typefilter_name_query_pair, '', $parameters);
					}
				}
			}
		} else if ($main_page == FILENAME_DEFAULT && $parameters != '' &&
				strpos(strtolower($parameters), 'cpath=') !== false) {
			// This link is to a category page
			
			// Get the category ID
			$pattern = '/[&\?]?(cPath=([0-9\_]+))/i';
			
			if (preg_match($pattern, $parameters, $matches)) {
				$category_query_pair = $matches[1];
				$category_parts = explode('_', $matches[2]);
				
				// Category ID to be linked to is the last part of the specification
				$category_id = $category_parts[sizeof($category_parts) - 1];
				
				$columns_to_retrieve = array(
					'uri'
					);
				
				$selections = array(
					'main_page' => $main_page,
					'associated_db_id' => (int) $category_id,
					'language_id' => (int) $_SESSION['languages_id'],
					'current_uri' => 1
					);
				
				$uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
				
				if (!$uri_mapping_result->EOF) {
					$link = $uri_mapping_result->fields['uri'];
					
					// Remove the category ID from the URI as it will be regenerated when the static URI is loaded
					$parameters = str_replace($category_query_pair . '&', '', $parameters);
					$parameters = str_replace($category_query_pair, '', $parameters);
				}
			}
		} else if ($main_page == FILENAME_DEFAULT && strpos(strtolower($parameters), 'typefilter=') === false) {
			// Link is to the index page.. no need to look up mapping in database, simply use the default index
			// page static URI
			$link = DIR_WS_CATALOG;
			
		} else if (in_array($main_page, $ceon_uri_mapping_product_pages) ||
				in_array($main_page, $ceon_uri_mapping_product_related_pages)) {
			// This link is to a product info page or one of the other pages related to the product
			
			// Get the product ID
			$pattern = '/[&\?]?(products_id=([0-9]+)[^&]*)/i';
			
			if (preg_match($pattern, $parameters, $matches)) {
				$product_query_pair = $matches[1];
				$product_id = $matches[2];
				
				$columns_to_retrieve = array(
					'uri'
					);
				
				$selections = array(
					'main_page' => $main_page,
					'associated_db_id' => (int) $product_id,
					'language_id' => (int) $_SESSION['languages_id'],
					'current_uri' => 1
					);
				
				$uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
				
				if (!$uri_mapping_result->EOF) {
					$link = $uri_mapping_result->fields['uri'];
					
					if (strpos($product_query_pair, ':') === false) {
						// Remove product ID from query string unless it includes information about selected
						// attributes (which is added when linking from shopping cart to product info page)
						$parameters = str_replace($product_query_pair . '&', '', $parameters);
						$parameters = str_replace($product_query_pair, '', $parameters);
					}
					
					// Don't add the cPath to the URI if it matches the product's full cPath. All  other variations
					// must still be added to retain context on the site
					$cpath_being_linked_to = null;
					
					$pattern = '/cPath\=([0-9\_]+)/';
					
					if (preg_match($pattern, $parameters, $matches)) {
						$cpath_being_linked_to = $matches[1];
					}
					
					if ($cpath_being_linked_to == zen_get_product_path($product_id)) {
						$parameters = str_replace('cPath=' . $cpath_being_linked_to . '&', '', $parameters);
						$parameters = str_replace('cPath=' . $cpath_being_linked_to, '', $parameters);
					}
				}
			}
		} else if ($main_page == FILENAME_EZPAGES || $main_page == FILENAME_EZPAGES_POPUP) {
			// This link is to an EZ-Pages page
			
			// Get the page ID
			$pattern = '/[&\?]?(id=([0-9]+))/i';
			
			if (preg_match($pattern, $parameters, $matches)) {
				$ez_page_id_query_pair = $matches[1];
				$ez_page_id = $matches[2];
				
				$columns_to_retrieve = array(
					'uri'
					);
				
				$selections = array(
					'main_page' => $main_page,
					'associated_db_id' => (int) $ez_page_id,
					'language_id' => (int) $_SESSION['languages_id'],
					'current_uri' => 1
					);
				
				$uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
				
				if (!$uri_mapping_result->EOF) {
					$link = $uri_mapping_result->fields['uri'];
					
					$parameters = str_replace($ez_page_id_query_pair . '&', '', $parameters);
					$parameters = str_replace($ez_page_id_query_pair, '', $parameters);
				}
			}
		} else {
			// This link is some other Zen Cart page like popup coupon help
			
			// Attempt to match a page with the exact same parameters first
			$columns_to_retrieve = array(
				'uri'
				);
			
			$selections = array(
				'main_page' => $main_page,
				'associated_db_id' => 'null',
				'query_string_parameters' => $parameters,
				'language_id' => (int) $_SESSION['languages_id'],
				'current_uri' => 1
				);
			
			$uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
			
			if (!$uri_mapping_result->EOF) {
				$link = $uri_mapping_result->fields['uri'];
				
				// Don't add any parameters as they will be initialised later if URI being mapped to is loaded
				$parameters = '';
				
			} else {
				 // Attempt to match a page with no parameters
				$columns_to_retrieve = array(
					'uri'
					);
				
				$selections = array(
					'main_page' => $main_page,
					'associated_db_id' => 'null',
					'query_string_parameters' => 'null',
					'language_id' => (int) $_SESSION['languages_id'],
					'current_uri' => 1
					);
				
				$uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
				
				if (!$uri_mapping_result->EOF) {
					$link = $uri_mapping_result->fields['uri'];
				}
			}
		}
		
		if ($link != '') {
			$link = $base_link . $link;
			
			if (strlen($parameters) > 0) {
				// Must add the parameters to the link
				$link .= '?';
				
				while (substr($parameters, 0, 1) == '?' || substr($parameters, 0, 1) == '&') {
					$parameters = substr($parameters, 1, strlen($parameters) - 1);
				}
				
				$link .= zen_output_string($parameters);
				
				$separator = '&';
			} else {
				$separator = '?';
			}
			
			// Perform standard Zen Cart functionality for links
			
//steve bof modified to match html_output.php zc155			
// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (defined('SID') && zen_not_null(constant('SID'))) {
        $sid = constant('SID');
//      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL_ADMIN == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if ($http_domain != $https_domain) {
          $sid = zen_session_name() . '=' . zen_session_id();
        }
      }
    }

// clean up the link before processing
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('&amp;', '/', $link);
      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);

      $separator = '?';
    }

    if (isset($sid)) {
      $link .= $separator . zen_output_string($sid);
    }

// clean up the link after processing
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

    $link = preg_replace('/&/', '&amp;', $link);
//steve eof from html_output.php
			// Have got the link to be used, store it in this instance
			$this->_href_link = $link;
		}
		
		return (strlen($this->_href_link) > 0);
	}
	
	// }}}
	
	
	// {{{ getHREFLink()
	
	/**
	 * Simply returns the HREF link value built by this instance.
	 *
	 * @access  public
	 * @return  string    The HREF link built by this instance.
	 */
	function getHREFLink()
	{
		return $this->_href_link;
	}
	
	// }}}
}

// }}}
