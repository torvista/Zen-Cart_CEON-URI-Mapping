<?php

/**
 * Ceon URI Mapping Manufacturer URI Mappings Admin Functionality.
 *
 * This file contains a class with the methods necessary to handle URI mappings for manufacturers.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminManufacturers.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');


// {{{ constants

define('CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_MANUFACTURER_WITH_NO_NAME', -5);

// }}}


// {{{ CeonURIMappingAdminManufacturers

/**
 * Handles the URI mappings for Manufacturers.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminManufacturers extends CeonURIMappingAdmin
{
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingAdminManufacturers class.
	 * 
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	// }}}
	
	
	// {{{ autogenManufacturerURIMapping()
	
	/**
	 * Generates a URI mapping for a manufacturer, for the specified language.
	 *
	 * @access  public
	 * @param   integer   $id              The ID of the manufacturer.
	 * @param   string    $name            The name of manufacturer (used if new information is
	 *                                     being submitted when the URI is being generated).
	 * @param   string    $language_code   The ISO 639 language code of the language.
	 * @param   integer   $language_id     The Zen Cart language ID for the language.
	 * @return  string    The auto-generated URI for the manufacturer and language.
	 */
	public function autogenManufacturerURIMapping($id, $name, $language_code, $language_id)
	{
		global $db;
		
		if (is_null($name)) {
			// Load name from database
			$manufacturer_name_result = $db->Execute("
				SELECT
					manufacturers_name
				FROM
					" . TABLE_MANUFACTURERS . "
				WHERE
					manufacturers_id = " . (int) $id . ";");
			
			$manufacturer_name = $manufacturer_name_result->fields['manufacturers_name'];
			
		} else {
			// Simply use name supplied
			$manufacturer_name = $name;
		}
		
		// Must not generate URIs for any manufacturer which has no name!
		if (strlen($manufacturer_name) == 0 || $manufacturer_name == '/' || $manufacturer_name == '\\') {
			return CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_MANUFACTURER_WITH_NO_NAME;
		}
		
		// Language code to be auto-included, then push to the beginning of the URI
		if ($this->_autoLanguageCodeAdd()) {
			$manufacturer_name = $language_code . '/' . $manufacturer_name;
		}
		
		$uri = $this->_convertStringForURI($manufacturer_name, $language_code);
		
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
