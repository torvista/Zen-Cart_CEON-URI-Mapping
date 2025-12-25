<?php

/**
 * Ceon URI Mapping Version Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingVersion.php 1054 2025-01-08 torvista
 */

/**
 * Load in the Ceon Tabbed Panel Admin Interface class so it can be extended
 */
require_once(DIR_WS_CLASSES . 'class.CeonURIMappingTabbedPanelAdminInterface.php');


// {{{ CeonURIMappingVersion

/**
 * Specifies the essential version properties for the module and implements the installed version's version number
 * (if any). Allows the extending class to access the version information for the module easily.
 *
 * @package     ceon_uri_mapping
 * @abstract
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingVersion extends CeonURIMappingTabbedPanelAdminInterface
{
	// {{{ Class Constructor

	/**
	 * Creates a new instance of the class.
	 *
	 * @param  bool  $load_config  Whether the autogeneration configuration should be loaded when instantiating the class.
	 * @access  public
	 */
	public function __construct($load_config = true)
	{
		parent::__construct($load_config);

		// Load the language definition file for the current language
		@include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' .
			'ceon_uri_mapping_config.php');

		if (!defined('TEXT_EDITION_TITLE') && $_SESSION['language'] != 'english') {
			// Fall back to english language file
			include_once(DIR_WS_LANGUAGES . 'english/' . 'ceon_uri_mapping_config.php');
		}

		// Set up the basic version settings for this module
		$this->_ceon_base_model_code = 'S-ZC-UM';

		$this->_version = '5.1.1';

		$this->_copyright_start_year = 2008;

		$this->_web_address_uri_part = 'uri-mapping';
	}

	// }}}


	// {{{ _lookUpInstalledVersion()

	/**
	 * Looks up the currently installed version and stores it in this instance's property.
	 *
	 * @access  protected
	 * @return  bool   True if the version number look-up completed without failure, false otherwise. The module
	 *                    not being installed yet is not counted as a failure.
	 */
	protected function _lookUpInstalledVersion(): bool
    {
		global $db;

		$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPING_CONFIGS . '";';
		$table_exists_result = $db->Execute($table_exists_query);

		if (!$table_exists_result->EOF) {
			// Database table exists, get version info

			// Only one config currently supported so its ID is hard-coded in the following SQL
			$installed_version_sql = '
				SELECT
					version
				FROM
					' . TABLE_CEON_URI_MAPPING_CONFIGS . '
				WHERE
					id = 1';

			$installed_version_result = $db->Execute($installed_version_sql);

			if (!$installed_version_result->EOF) {
				$this->_installed_version = $installed_version_result->fields['version'];
			} else {
                return false;
            }
		}
		return true;
	}

	// }}}
}

// }}}
