<?php

/**
 * Ceon URI Mapping EZ-Page URI Mappings Admin Functionality.
 *
 * This file contains a class with the methods necessary to handle URI mappings for EZ-pages.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminEZPages.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');


// {{{ constants

define('CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_EZ_PAGE_WITH_NO_NAME', -6);

// }}}


// {{{ CeonURIMappingAdminEZPages

/**
 * Handles the URI mappings for EZ-pages.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminEZPages extends CeonURIMappingAdmin
{
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingAdminEZPages class.
	 * 
	 * @access  public
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	// }}}
	
	
	// {{{ autogenEZPageURIMapping()
	
	/**
	 * Generates a URI mapping for an EZ-Page, for the specified language.
	 *
	 * @access  public
	 * @param   integer   $id              The ID of the EZ-Page.
	 * @param   string    $name            The name of EZ-Page (used if new information is being
	 *                                     submitted when the URI is being generated).
	 * @param   string    $language_code   The ISO 639 language code of the language.
	 * @param   integer   $language_id     The Zen Cart language ID for the language.
	 * @return  string    The auto-generated URI for the EZ-Page and language.
	 */
	function autogenEZPageURIMapping($id, $name, $language_code, $language_id)
	{
		global $db;
		
		if (is_null($name)) {
			// Load name from database
			$ez_page_name_result = $db->Execute("
				SELECT
					pages_title
				FROM
					" . TABLE_EZPAGES . "
				WHERE
					pages_id = '" . (int) $id . "'
				AND
					languages_id = '" . (int) $language_id . "'");
			
			$ez_page_name = $ez_page_name_result->fields['pages_title'];
			
		} else {
			// Simply use name supplied
			$ez_page_name = $name;
		}
		
		// Must not generate URIs for any EZ-Page which has no name!
		if (strlen($ez_page_name) == 0 || $ez_page_name == '/' || $ez_page_name == '\\') {
			return CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_EZ_PAGE_WITH_NO_NAME;
		}
		
		$uri = $this->_convertStringForURI($ez_page_name, $language_code);
		
		// Prepend the URI with the store's directory (if any), otherwise add a "root" slash
		if (strlen(DIR_WS_CATALOG) > 0) {
			$uri = DIR_WS_CATALOG . $uri;
		} else {
			$uri = '/' . $uri;
		}
		
		return $uri;
	}
	
	// }}}
}

// }}}
