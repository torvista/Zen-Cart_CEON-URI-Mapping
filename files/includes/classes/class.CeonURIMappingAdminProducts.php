<?php

/**
 * Ceon URI Mapping Product URI Mappings Admin Functionality.
 *
 * This file contains a class with the methods necessary to handle URI mappings for products.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminProducts.php 1054 2012-09-22 15:45:15Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoriesProducts.php');


// {{{ constants

define('CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME', -3);

// }}}


// {{{ CeonURIMappingAdminProducts

/**
 * Handles the URI mappings for products.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminProducts extends CeonURIMappingAdminCategoriesProducts
{
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingAdminProducts class.
	 * 
	 * @access  public
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	// }}}
	
	
	// {{{ autoManageProductRelatedPageURI()
	
	/**
	 * Checks whether auto-managing of the specified product related page URI is enabled or disabled.
	 *
	 * @access  public
	 * @param   string    $page_type   The type of the page.
	 * @return  boolean   Whether auto-managing of the page's URI is enabled or disabled.
	 */
	function autoManageProductRelatedPageURI($page_type)
	{
		global $db;
		
		if (!isset($automanage_enabled)) {
			static $automanage_enabled = array();
			
			// Only one config currently supported so ID is hard-coded in following SQL
			$automanage_enabled_sql = "
				SELECT
					manage_product_reviews_mappings,
					manage_product_reviews_info_mappings,
					manage_product_reviews_write_mappings,
					manage_tell_a_friend_mappings
				FROM
					" . TABLE_CEON_URI_MAPPING_CONFIGS . "
				WHERE
					id ='1';";
			
			$automanage_enabled_result = $db->Execute($automanage_enabled_sql);
			
			if (!$automanage_enabled_result->EOF) {
				$automanage_enabled = array(
					'product_reviews' =>
						$automanage_enabled_result->fields['manage_product_reviews_mappings'],
					'product_reviews_info' =>
						$automanage_enabled_result->fields['manage_product_reviews_info_mappings'],
					'product_reviews_write' =>
						$automanage_enabled_result->fields['manage_product_reviews_write_mappings'],
					'tell_a_friend' =>
						$automanage_enabled_result->fields['manage_tell_a_friend_mappings']
					);
			}
		}
		
		if ($automanage_enabled[$page_type] == 1) {
			return true;
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ getProductRelatedPageURIPart()
	
	/**
	 * Looks up the product related page URI part for the page type and language specified.
	 *
	 * @access  public
	 * @param   string    $page_type   The type of the page.
	 * @param   string    $language_code   The language code the URI part should be returned for.
	 * @return  string|false   The product related page's URI part or false if there is no URI part for the
	 *                         specified page type and language code.
	 */
	function getProductRelatedPageURIPart($page_type, $language_code)
	{
		global $db;
		
		if (!isset($uri_parts) || !isset($uri_parts[$language_code]) ||
				!isset($uri_parts[$language_code][$page_type])) {
			// URI part hasn't been cached for this page type for the specified language, cache all now
			static $uri_parts = array();
			
			// Only hold the information in memory for URI parts that are being auto-managed
			$page_types = array(
				'product_reviews',
				'product_reviews_info',
				'product_reviews_write',
				'tell_a_friend'
				);
			
			$page_types_sql_string = '';
			
			foreach ($page_types as $current_page_type) {
				if ($this->autoManageProductRelatedPageURI($current_page_type)) {
					if (strlen($page_types_sql_string) > 0) {
						$page_types_sql_string .= ' OR ';
					}
					
					$page_types_sql_string .= "page_type = '" . $current_page_type . "'\n";
				}
			}
			
			// Only one config currently supported so ID is hard-coded in following SQL
			$uri_parts_sql = "
				SELECT
					page_type,
					language_code,
					uri_part
				FROM
					" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "
				WHERE
					" . $page_types_sql_string . ";";
			
			$uri_parts_result = $db->Execute($uri_parts_sql);
			
			while (!$uri_parts_result->EOF) {
				if (!isset($uri_parts[$uri_parts_result->fields['language_code']])) {
					$uri_parts[$uri_parts_result->fields['language_code']] = array();
				}
				
				$uri_parts[$uri_parts_result->fields['language_code']][$uri_parts_result->fields['page_type']] =
					$uri_parts_result->fields['uri_part'];
				
				$uri_parts_result->MoveNext();
			}
		}
		
		if (isset($uri_parts[$language_code]) && isset($uri_parts[$language_code][$page_type]) &&
				strlen($uri_parts[$language_code][$page_type]) > 0) {
			
			return $uri_parts[$language_code][$page_type];
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ autogenProductURIMapping()
	
	/**
	 * Generates a URI mapping for a product, for the specified language.
	 *
	 * @access  public
	 * @param   integer   $id              The ID of the product.
	 * @param   integer   $parent_category_id   The ID of the parent category (used if the details in the database
	 *                                          could be out of date as new  information is being submitted when
	 *                                          the URI is being generated).
	 * @param   string    $name               The name of product (used if new information is being submitted when
	 *                                        the URI is being generated).
	 * @param   string    $language_code      The ISO 639 language code of the language.
	 * @param   integer   $language_id        The Zen Cart language ID for the language.
	 * @param   string    $model              The product's model code.
	 * @param   string    $mapping_template   The mapping template for this product.
	 * @return  string    The auto-generated URI for the product and language.
	 */
	function autogenProductURIMapping($id, $parent_category_id, $name, $language_code, $language_id, $model = null)
	{
		return $this->autogenCategoryOrProductURIMapping($id, 'product', $parent_category_id, $name,
			$language_code, $language_id);
	}
	
	// }}}
}

// }}}
