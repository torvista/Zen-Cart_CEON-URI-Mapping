<?php

/**
 * Ceon URI Mapping Install/Upgrade Class - Installs/configures the module, upgrades from an earlier version or
 * finds and fixes and configuration/database issues.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingInstallOrUpgrade.php 1028 2012-07-18 07:20:05Z conor $
 */


// {{{ CeonURIMappingInstallOrUpgrade

/**
 * Installs or upgrades Ceon URI Mapping. If a previous installation/upgrade attempt failed before it completed,
 * the class can be run again as it will attempt to find and fix any configuration/database issues.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingInstallOrUpgrade
{
	// {{{ properties
	
	/**
	 * The version of the module.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_version = null;
	
	/**
	 * The version of the module which is currently installed.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_installed_version = null;
	
	/**
	 * Tracks if any updates were performed.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_actions_performed = false;
	
	/**
	 * Tracks if the URI mappings table has just been created.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_uri_mappings_table_created = false;
	
	/**
	 * Tracks if the configs table has just been created.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_uri_mapping_configs_table_created = false;
	
	/**
	 * Tracks if the product related pages URI parts table has just been created.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_uri_mapping_prp_uri_parts_table_created = false;
	
	/**
	 * Maintains a list of any errors encountered in an installation or upgrade.
	 *
	 * @var     array
	 * @access  public
	 */
	var $error_messages = array();
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the class. Installs/upgrades the database and adds or updates configuration
	 * options.
	 * 
	 * @access  public
	 * @param   string    $version   The version of the module.
	 * @param   string    $installed_version   The currently installed version of the module.
	 */
	function CeonURIMappingInstallOrUpgrade($version, $installed_version)
	{
		global $db;
		
		$this->_version = $version;
		
		$this->_installed_version = $installed_version;
		
		$this->_checkCreateDatabases();
		
		if (sizeof($this->error_messages) > 0) {
			// Can't progress without working databases
			return;
		}
		
		// Make sure the product related pages URI parts table is fully populated as it may be needed to upgrade
		// from any previous version
		$this->_checkProductRelatedPagesURIPartsExistForLanguages();
		
		$database_up_to_date = $this->_ensureDatabaseIsUpToDate();
		
		$this->_checkForDefaultConfig();
		
		if (((int) substr($this->_installed_version, 0, 1)) < 4) {
			$this->_changeDefaultRemoveWordsSetting();
		}
		
		$this->_checkZenCartConfigGroupAndOption();
		
		if (!$this->_uri_mappings_table_created &&
				substr($this->_installed_version, 0, 1) == '2' ||
				substr($this->_installed_version, 0, 3) == '3.0' ||
				substr($this->_installed_version, 0, 3) == '3.2' || $this->_installed_version == '3.4.0') {
			// From 3.4.1 onwards the index page is always mapped to the main directory - i.e. the value of
			// DIR_WS_CATALOG (e.g. / or /shop). Make any mappings for the index page historical
			$this->_makeIndexPageMappingsHistorical();
		}
		
		if (((int) substr($this->_installed_version, 0, 1)) < 4) {
			$this->_stripTrailingSlashesFromMappings();
		}
		
		// If updates were performed and all were successful, update version number 
		if (sizeof($this->error_messages) == 0 && $this->_installed_version != $this->_version) {
			// Only one config currently supported so the ID is hard-coded in the following SQL
			$update_db_version_number_sql = "
				UPDATE
					" . TABLE_CEON_URI_MAPPING_CONFIGS . "
				SET
					version = '" . $this->_version . "'
				WHERE
					id = '1';";
			
			$update_db_version_number_result = $db->Execute($update_db_version_number_sql);
			
			// Reset the version check status as it may change when the software is upgraded
			if (isset($_SESSION[$this->_ceon_base_model_code . '_vc_response'])) {
				unset($_SESSION[$this->_ceon_base_model_code . '_vc_response']);
			}
		}
	}
	
	// }}}
	
	
	// {{{ actionPerformed()
	
	/**
	 * Simply returns whether or not any action was performed during the running of this instance.
	 *
	 * @access  public
	 * @return  boolean   Whether or not any action was performed.
	 */
	function actionPerformed()
	{
		return $this->_actions_performed;		
	}
	
	// }}}
	
	
	// {{{ _checkCreateDatabases()
	
	/**
	 * Makes sure that the database tables exist. Creates any that don't.
	 *
	 * @access  protected
	 * @return  boolean   False if a problem occurred when creating a database table, true otherwise.
	 */
	function _checkCreateDatabases()
	{
		global $db, $messageStack;
		
		// Add the URI Mappings table if it doesn't exist
		$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPINGS . '";';
		$table_exists_result = $db->Execute($table_exists_query);
		
		if ($table_exists_result->EOF) {
			if (!$this->_DBUserHasPrivilege('CREATE')) {
				$this->error_messages[] = 'Database table could not be created! The database user may not have' .
					' CREATE TABLE privileges?!';
				
				return false;
			}
			
			$create_uri_mappings_table_sql = "
				CREATE TABLE
					`" . TABLE_CEON_URI_MAPPINGS . "`
					(
					`uri` TEXT NOT NULL,
					`language_id` INT(11) UNSIGNED DEFAULT NULL,
					`current_uri` INT(1) UNSIGNED DEFAULT '0',
					`main_page` VARCHAR(45) NULL,
					`query_string_parameters` VARCHAR(255) DEFAULT NULL,
					`associated_db_id` INT(11) UNSIGNED DEFAULT NULL,
					`alternate_uri` VARCHAR(255) DEFAULT NULL,
					`redirection_type_code` VARCHAR(3) DEFAULT '301',
					`date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					INDEX `assoc_db_id_idx` (`language_id`, `current_uri`, `main_page`, `associated_db_id`)
					);";
			
			$create_uri_mappings_table_result = $db->Execute($create_uri_mappings_table_sql);
			
			// Check the table was created
			$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPINGS . '";';
			$table_exists_result = $db->Execute($table_exists_query);
			
			if ($table_exists_result->EOF) {
				$this->error_messages[] = 'Database table could not be created! The database user may not have' .
					' CREATE TABLE privileges?!';
				
				return false;
			}
			
			$this->_actions_performed = true;
			
			$this->_uri_mappings_table_created = true;
			
			$messageStack->add('Mappings database table successfully created.', 'success');
		}
		
		
		// Add the configuration table if it doesn't exist
		$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPING_CONFIGS . '";';
		$table_exists_result = $db->Execute($table_exists_query);
		
		if ($table_exists_result->EOF) {
			if (!$this->_DBUserHasPrivilege('CREATE')) {
				$this->error_messages[] = 'Database table could not be created! The database user may not have' .
					' CREATE TABLE privileges?!';
				
				return false;
			}
			
			$create_uri_mapping_configs_table_sql = "
				CREATE TABLE IF NOT EXISTS
					`" . TABLE_CEON_URI_MAPPING_CONFIGS . "`
					(
					`id` INT UNSIGNED NOT NULL,
					`version` VARCHAR(14) NOT NULL,
					`autogen_new` INT(1) UNSIGNED NOT NULL,
					`whitespace_replacement` INT(1) UNSIGNED NOT NULL,
					`capitalisation` INT(1) UNSIGNED NOT NULL,
					`remove_words` TEXT DEFAULT NULL,
					`char_str_replacements` TEXT DEFAULT NULL,
					`mapping_clash_action` VARCHAR(11) DEFAULT 'warn',
					`manage_product_reviews_mappings` INT(1) UNSIGNED DEFAULT 1,
					`manage_product_reviews_info_mappings` INT(1) UNSIGNED DEFAULT 1,
					`manage_product_reviews_write_mappings` INT(1) UNSIGNED DEFAULT 1,
					`manage_tell_a_friend_mappings` INT(1) UNSIGNED DEFAULT 1,
					`automatic_version_checking` INT(1) UNSIGNED DEFAULT 1,
					PRIMARY KEY (`id`)
					);";
			
			$create_uri_mapping_configs_table_result = $db->Execute($create_uri_mapping_configs_table_sql);
			
			// Check the table was created
			$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPING_CONFIGS . '";';
			
			$table_exists_result = $db->Execute($table_exists_query);
			
			if ($table_exists_result->EOF) {
				$this->error_messages[] = 'Database table could not be created! The database' .
					' user may not have CREATE TABLE privileges?!';
				
				return false;
				
			}
			
			$this->_actions_performed = true;
			
			$this->_uri_mapping_configs_table_created = true;
			
			$messageStack->add('Configuration database table successfully created.', 'success');
		}
		
		
		// Add the product related pages URI parts table if it doesn't exist
		$table_exists_query = 'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . '";';
		$table_exists_result = $db->Execute($table_exists_query);
		
		if ($table_exists_result->EOF) {
			if (!$this->_DBUserHasPrivilege('CREATE')) {
				$this->error_messages[] = 'Database table could not be created! The database user may not have' .
					' CREATE TABLE privileges?!';
				
				return false;
			}
			
			$create_uri_mapping_prp_uri_parts_table_sql = "
				CREATE TABLE IF NOT EXISTS
					`" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "`
					(
					`page_type` VARCHAR(21) NOT NULL,
					`language_code` CHAR(2) NOT NULL,
					`uri_part` VARCHAR(50) NOT NULL,
					PRIMARY KEY (`page_type`, `language_code`)
					);";
			
			$create_uri_mapping_prp_uri_parts_table_result = 
				$db->Execute($create_uri_mapping_prp_uri_parts_table_sql);
			
			// Check the table was created
			$table_exists_query =
				'SHOW TABLES LIKE "' . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . '";';
			$table_exists_result = $db->Execute($table_exists_query);
			
			if ($table_exists_result->EOF) {
				$this->error_messages[] = 'Database table could not be created! The database user may not have' .
					' CREATE TABLE privileges?!';
				
				return false;
			}
			
			$this->_actions_performed = true;
			
			$this->_uri_mapping_prp_uri_parts_table_created = true;
			
			$messageStack->add('Product related pages URI parts database table successfully created.', 'success');
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _checkProductRelatedPagesURIPartsExistForLanguages()
	
	/**
	 * Makes sure that every language has values for all the product related pages URI parts.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _checkProductRelatedPagesURIPartsExistForLanguages()
	{
		global $db;
		
		$languages = zen_get_languages();
		
		// Check if URI parts are missing for any language
		$page_types = array(
			'product_reviews',
			'product_reviews_info',
			'product_reviews_write',
			'tell_a_friend'
			);
		
		// Variable holds the list of URIs parts for each language as currently specified in the database
		$current_uri_parts = array();
		
		$current_uri_parts_sql = "
			SELECT
				page_type,
				language_code,
				uri_part
			FROM
				" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "
			WHERE
				1 = 1;";
		
		$current_uri_parts_result = $db->Execute($current_uri_parts_sql);
		
		while (!$current_uri_parts_result->EOF) {
			if (!isset($current_uri_parts[$current_uri_parts_result->fields['language_code']])) {
				$current_uri_parts[$current_uri_parts_result->fields['language_code']] = array();
			}
			
			$current_uri_parts[$current_uri_parts_result->fields['language_code']]
				[$current_uri_parts_result->fields['page_type']] = $current_uri_parts_result->fields['uri_part'];
			
			$current_uri_parts_result->MoveNext();
		}
		
		$uri_parts_missing = false;
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			if (!isset($current_uri_parts[$language_code])) {
				$uri_parts_missing = true;
				
				break;
			}
			
			foreach ($page_types as $page_type) {
				if (!isset($current_uri_parts[$language_code][$page_type]) ||
						(isset($current_uri_parts[$language_code][$page_type]) &&
						(is_null($current_uri_parts[$language_code][$page_type]) ||
						strlen($current_uri_parts[$language_code][$page_type]) == 0))) {
					$uri_parts_missing = true;
					
					break 2;
				}
			}
		}
		
		if (!$uri_parts_missing) {
			// Everything is in order, nothing else to do
			return;
		}
		
		// Reaching this point means that at least one URI part is missing, must populate the
		// missing URI part(s)
		
		
		// Variable holds the list of URIs parts for each language as defined in the language 
		// definition files for each language
		$default_uri_parts = array();
		
		// Attempt to get the specific defines for each store the language uses
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$language_name = $languages[$i]['name'];
			
			$language_code_lower = strtolower($languages[$i]['code']);
			$language_code_upper = strtoupper($languages[$i]['code']);
			
			if (file_exists(DIR_WS_LANGUAGES . $language_name . 'ceon_uri_mapping_default_uri_parts.php')) {
				include_once (DIR_WS_LANGUAGES . $language_name . 'ceon_uri_mapping_default_uri_parts.php');
				
				$default_uri_parts[$language_code_lower] = array();
				
				foreach ($page_types as $page_type) {
					if (defined('DEFAULT_URI_PART_' . strtoupper($page_type) . $language_code_upper)) {
						$default_uri_parts[$language_code_lower][$page_type] =
							constant('DEFAULT_URI_PART_' . strtoupper($page_type) . $language_code_upper);
					}
				}
			}
		}
		
		// Variable holds the defines to be used for languages that don't have any custom defines
		// If the store's default language is missing any defines, defines for English are used. If English defines
		// aren't found, English is used, hard-coded here
		$default_language_code = strtolower(DEFAULT_LANGUAGE);
		
		$fallback_defines = array(
			'product_reviews' =>
				isset($default_uri_parts[$default_language_code]['product_reviews']) ?
				$default_uri_parts[$default_language_code]['product_reviews'] :
				isset($default_uri_parts['en']['product_reviews']) ?
				$default_uri_parts['en']['product_reviews'] : 'Reviews',
			'product_reviews_info' =>
				isset($default_uri_parts[$default_language_code]['product_reviews_info']) ?
				$default_uri_parts[$default_language_code]['product_reviews_info'] :
				isset($default_uri_parts['en']['product_reviews_info']) ?
				$default_uri_parts['en']['product_reviews_info'] : 'Review',
			'product_reviews_write' =>
				isset($default_uri_parts[$default_language_code]['product_reviews_write']) ?
				$default_uri_parts[$default_language_code]['product_reviews_write'] :
				isset($default_uri_parts['en']['product_reviews_write']) ?
				$default_uri_parts['en']['product_reviews_write'] : 'Write a Review',
			'tell_a_friend' => isset($default_uri_parts[$default_language_code]['tell_a_friend']) ?
				$default_uri_parts[$default_language_code]['tell_a_friend'] :
				isset($default_uri_parts['en']['tell_a_friend']) ?
				$default_uri_parts['en']['tell_a_friend'] : 'Tell a Friend'
			);
		
		// Populate any missing URI parts for any language
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			if (!isset($default_uri_parts[$language_code])) {
				$default_uri_parts[$language_code] = array();
			}
			
			foreach ($page_types as $page_type) {
				if (!isset($default_uri_parts[$language_code][$page_type])) {
					$default_uri_parts[$language_code][$page_type] = $fallback_defines[$page_type];
				}
			}
		}
		
		// Add the missing URI parts to the database
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			// Are there no URI Parts for this language? Or are any empty?
			if (!isset($current_uri_parts[$language_code])) {
				// URI Parts are missing for this language, so add default values
				foreach ($page_types as $page_type) {
					$add_uri_part_sql = "
						INSERT INTO
							" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "
							(
							page_type,
							language_code,
							uri_part
							)
						VALUES
							(
							'" . zen_db_input($page_type) . "',
							'" . zen_db_input($language_code) . "',
							'" . zen_db_input($default_uri_parts[$language_code][$page_type]) . "'
							);";
					
					$add_uri_part_result = $db->Execute($add_uri_part_sql);
				}
			} else {
				foreach ($page_types as $page_type) {
					if (is_null($current_uri_parts[$language_code][$page_type]) ||
							strlen($current_uri_parts[$language_code][$page_type]) == 0) {
						// URI Part has no value for this language, use the default URI part identified earlier
						$update_uri_part_sql = "
							UPDATE
								" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "
							SET
								uri_part = '" . zen_db_input(
									$default_uri_parts[$language_code][$page_type]) . "'
							WHERE
								page_type = '" . zen_db_input($page_type) . "'
							AND
								language_code = '" . zen_db_input($language_code) . "';";
						
						$update_uri_part_result = $db->Execute($update_uri_part_sql);
					}
				}
			}
		}
		
		$this->_actions_performed = true;
	}
	
	// }}}
	
	
	// {{{ _ensureDatabaseIsUpToDate()
	
	/**
	 * Makes sure that the database tables are up to date.
	 *
	 * @access  protected
	 * @return  boolean   False if a problem occurred when updating a database table, true otherwise.
	 */
	function _ensureDatabaseIsUpToDate()
	{
		global $db;
		
		// If a problem occurs trying to update a table, take note but continue to try and update any other table,
		// so things are as good as can be in a bad situation
		$problem_updating_table = false;
		
		if (!$this->_uri_mappings_table_created) {
			if (!$this->_checkUpdateURIMappingsTable()) {
				$problem_updating_table = true;
			}
		}
		
		if (!$this->_uri_mapping_configs_table_created) {
			if (!$this->_checkUpdateConfigsTable()) {
				$problem_updating_table = true;
			}
		}
		
		if ($problem_updating_table) {
			return false;
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _checkUpdateURIMappingsTable()
	
	/**
	 * Adds any columns which are missing from the main URI mappings table and migrates the URI mappings from
	 * really old versions.
	 *
	 * @access  protected
	 * @return  boolean   Whether or not the database table is up to date.
	 */
	function _checkUpdateURIMappingsTable()
	{
		global $db, $messageStack;
		
		// Get the list of columns in the database table
		$columns = array();
		
		$columns_query = 'SHOW COLUMNS FROM ' . TABLE_CEON_URI_MAPPINGS . ';';
		$columns_result = $db->Execute($columns_query);
		
		while (!$columns_result->EOF) {
			$columns[] = $columns_result->fields['Field'];
			$columns_result->MoveNext();
		}
		
		if (!in_array('current_uri', $columns)) {
			// Updating old version 2 database table
			if (!$this->_DBUserHasPrivilege('ALTER')) {
				$this->error_messages[] = 'Database table can not be updated! The database user' .
					' does not have ALTER TABLE privileges!';
				
				return false;
			}
			
			// Add the new current uri column to the uri mappings table
			$add_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD
					`current_uri` INT(1) UNSIGNED DEFAULT '0'
				AFTER
					`language_id`;";
			
			$add_column_result = $db->Execute($add_column_sql);
			
			
			// Verify that the column was added
			$columns_query = 'SHOW COLUMNS FROM ' . TABLE_CEON_URI_MAPPINGS . ';';
			$columns_result = $db->Execute($columns_query);
			
			$columns = array();
			
			while (!$columns_result->EOF) {
				$columns[] = $columns_result->fields['Field'];
				$columns_result->MoveNext();
			}
			
			if (!in_array('current_uri', $columns)) {
				// Unable to add column to table! The database user may not actually have ALTER TABLE privileges
				$this->error_messages[] = 'Unable to add column to table! The database user may' .
					' not have ALTER TABLE privileges';
				
				return false;
			}
			
			$this->_actions_performed = true;
			
			// Assume all other ALTER operations will work fine if the first one did
			
			// Add the new main page column to the uri mappings table
			$add_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD
					`main_page` VARCHAR(45) NULL
				AFTER
					`current_uri`;";
			
			$add_column_result = $db->Execute($add_column_sql);
			
			// Add the new associated DB ID column to the uri mappings table
			$add_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD
					`associated_db_id` INT(11) UNSIGNED DEFAULT NULL
				AFTER
					`main_page`;";
			
			$add_column_result = $db->Execute($add_column_sql);
			
			// Add the new redirection type code column to the uri mappings table
			$add_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD
					`redirection_type_code` VARCHAR(3) DEFAULT '301'
				AFTER
					`alternate_uri`;";
			
			$add_column_result = $db->Execute($add_column_sql);
			
			// Add the new date added column to the uri mappings table
			$add_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD
					`date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
				AFTER
					`redirection_type_code`;";
			
			$add_column_result = $db->Execute($add_column_sql);
			
			// URI mappings format has changed and URIs will need to be updated
			$this->_migrateVersion2URIMappings();
			
			// Remove the old columns
			$drop_columns_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				DROP 
					`category_id`,
				DROP 
					`product_id`,
				DROP 
					`page_id`;";
			
			$drop_columns_result = $db->Execute($drop_columns_sql);
			
			$version_2_table_updated = true;
		}
		
		if (!in_array('query_string_parameters', $columns)) {
			// Version 2.x/3.0.x table needs updating
			if (!$this->_DBUserHasPrivilege('ALTER')) {
				$this->error_messages[] = 'Database table could not be created! The database user may not have' .
					' does not have ALTER TABLE privileges!';
				
				return false;
			}
			
			// Add the new query string column to the uri mappings table
			$add_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD
					`query_string_parameters` VARCHAR(255) DEFAULT NULL
				AFTER
					`main_page`;";
			
			$add_column_result = $db->Execute($add_column_sql);
			
			
			// Verify that the column was added
			$columns_query = 'SHOW COLUMNS FROM ' . TABLE_CEON_URI_MAPPINGS . ';';
			$columns_result = $db->Execute($columns_query);
			
			$columns = array();
			
			while (!$columns_result->EOF) {
				$columns[] = $columns_result->fields['Field'];
				$columns_result->MoveNext();
			}
			
			if (!in_array('query_string_parameters', $columns)) {
				// Unable to add column to table! The database user may not actually have ALTER TABLE privileges
				$this->error_messages[] = 'Unable to add column to table! The database user may' .
					' not have ALTER TABLE privileges';
				
				return false;
			}
			
			$this->_migrateFromOldAlternateURIMappingFormat();
			
			$this->_actions_performed = true;
			
			$version_2_table_updated = true;
			$version_3_table_updated = true;
		}
		
		// Add the index if it doesn't exist
		$indexes_exist_query = 'SHOW INDEXES FROM ' . TABLE_CEON_URI_MAPPINGS . ';';
		$indexes_exist_result = $db->Execute($indexes_exist_query);
		
		$indexes = array();
		
		while (!$indexes_exist_result->EOF) {
			$indexes[] = $indexes_exist_result->fields['Column_name'];
			$indexes_exist_result->MoveNext();
		}
		
		if (!in_array('language_id', $indexes)) {
			// Add an index to speed up database lookups for categories, products, manufacturers and EZ-Pages.
			// Index format suggested by Christian Pinder.
			$add_index_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPINGS . "
				ADD INDEX `assoc_db_id_idx`
					(
					`language_id`,
					`current_uri`,
					`main_page`,
					`associated_db_id`
					);";
			
			$add_index_result = $db->Execute($add_index_sql);
			
			$this->_actions_performed = true;
			
			$version_2_table_updated = true;
			$version_3_table_updated = true;
		}
		
		if (isset($version_3_updated)) {
			$messageStack->add('Mappings database table successfully updated from old version 3.x format.',
				'success');
		} else if (isset($version_2_updated)) {
			$messageStack->add('Mappings database table successfully updated from old version 2.x format.',
				'success');
		}
	}
	
	// }}}
	
	
	// {{{ _migrateVersion2URIMappings()
	
	/**
	 * Migrates the URI mappings from the old version 2 format to the current format.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _migrateVersion2URIMappings()
	{
		global $db, $languages;
		
		$uri_mappings_query = "
			SELECT
				um.*,
				pt.type_handler
			FROM
				" . TABLE_CEON_URI_MAPPINGS . " um
			LEFT JOIN
				" . TABLE_PRODUCTS . " p
			ON
				p.products_id = um.product_id
			LEFT JOIN
				" . TABLE_PRODUCT_TYPES . " pt
			ON
				pt.type_id = p.products_type
			WHERE
				1 = 1;";
		
		$uri_mappings_result = $db->Execute($uri_mappings_query);
		
		while (!$uri_mappings_result->EOF) {
			$main_page = null;
			
			if (!is_null($uri_mappings_result->fields['category_id'])) {
				$main_page = FILENAME_DEFAULT;
				$associated_db_id = $uri_mappings_result->fields['category_id'];
				
				$selection_query_part = "category_id = '" . $uri_mappings_result->fields['category_id'] . "'";
				
			} else if (!is_null($uri_mappings_result->fields['product_id'])) {
				$main_page = $uri_mappings_result->fields['type_handler'] . '_info';
				$associated_db_id = $uri_mappings_result->fields['product_id'];
				
				$selection_query_part = "product_id = '" . $uri_mappings_result->fields['product_id'] . "'";
				
			} else if (!is_null($uri_mappings_result->fields['page_id'])) {
				$main_page = FILENAME_EZPAGES;
				$associated_db_id = $uri_mappings_result->fields['page_id'];
				
				$selection_query_part = "page_id = '" . $uri_mappings_result->fields['page_id'] . "'";
			}
			
			if (!is_null($main_page)) {
				$update_record_format_query = "
					UPDATE
						" . TABLE_CEON_URI_MAPPINGS . "
					SET
						current_uri = '1',
						main_page = '" . zen_db_input($main_page) . "',
						associated_db_id = '" . zen_db_input($associated_db_id) . "'
					WHERE
						" . $selection_query_part . ";";
				
				$update_record_format_result = $db->Execute($update_record_format_query);
			}
			
			if (!is_null($uri_mappings_result->fields['product_id'])) {
				// Must add the default URI mappings for the other product-related pages
				$uri_mapping = $uri_mappings_result->fields['uri'];
				
				if (substr($uri_mapping, -1) != '/') {
					$uri_mapping .= '/';
				}
				
				$sql_data_array = array(
					'language_id' => $uri_mappings_result->fields['language_id'],
					'current_uri' => 1,
					'associated_db_id' => $associated_db_id,
					'alternate_uri' => 'null',
					'redirection_type_code' => '301',
					'date_added' => $uri_mappings_result->fields['date_added']
					);
				
				require_once(DIR_WS_FUNCTIONS . 'ceon_uri_mapping_products.php');
				
				$page_types = array(
					'product_reviews',
					'product_reviews_info',
					'product_reviews_write',
					'tell_a_friend'
					);
				
				// Get the language code for the mapping's language
				for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
					if ($languages[$i]['id'] == $uri_mappings_result->fields['language_id']) {
						$language_code = strtolower($languages[$i]['code']);
						break;
					}
				}
				
				foreach ($page_types as $page_type) {
					if (ceon_uri_mappingAutoManageProductRelatedPageURI($page_type)) {
						$uri_part = ceon_uri_mappingGetProductRelatedPageURIPart($page_type, $language_code);
						
						if ($uri_part == false) {
							// Unexpected database problem encountered
							continue;
						}
						
						$uri_part = ceon_uri_mappingConvertStringForURI($uri_part, $language_code);
						
						$sql_data_array['uri'] = zen_db_prepare_input($uri_mapping . $uri_part);
						
						$sql_data_array['main_page'] =
							zen_db_prepare_input(constant('FILENAME_' . strtoupper($page_type)));
					}
					
					zen_db_perform(TABLE_CEON_URI_MAPPINGS, $sql_data_array);
				}
			}
			
			$uri_mappings_result->MoveNext();
		}
		
		// Update the date fields to give them a default date
		$update_date_added_query = "
			UPDATE
				" . TABLE_CEON_URI_MAPPINGS . "
			SET
				date_added = '" . date('Y-m-d H:i:s', time()) . "'
			WHERE
				1 = 1;";
		
		$update_date_added_result = $db->Execute($update_date_added_query);
		
		// Set the alternate_uri column's values to null where appropriate
		$update_alternate_uri_query = "
			UPDATE
				" . TABLE_CEON_URI_MAPPINGS . "
			SET
				alternate_uri = NULL
			WHERE
				alternate_uri = 'null';";
		
		$update_alternate_uri_result = $db->Execute($update_alternate_uri_query);
	}
	
	// }}}
	
	
	// {{{ _migrateFromOldAlternateURIMappingFormat()
	
	/**
	 * Modifies mappings which used the old alternate URI format, updating them to use the newly added
	 * query_string_parameter format.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _migrateFromOldAlternateURIMappingFormat()
	{
		global $db;
		
		$uri_mappings_query = "
			SELECT
				um.*
			FROM
				" . TABLE_CEON_URI_MAPPINGS . " um
			WHERE
				alternate_uri LIKE '%index.php?main_page=%';";
		
		$uri_mappings_result = $db->Execute($uri_mappings_query);
		
		while (!$uri_mappings_result->EOF) {
			// Have matched an alternative URI which is a Zen Cart page with preset parameters
			// Extract the values for the main page variable and the query string parameter variable
			$pattern = '/index\.php\?main_page\=([^\&]+)\&?(.*)/';
			
			if (preg_match($pattern, $uri_mappings_result->fields['alternate_uri'], $matches)) {
				// Alternative URI is a Zen Cart page, get any parameters to be set
				$main_page = $matches[1];
				$parameter_string = $matches[2];
				
				if (strlen($parameter_string) == 0) {
					$parameter_string = null;
				}
				
				$update_record_format_query = "
					UPDATE
						" . TABLE_CEON_URI_MAPPINGS . "
					SET
						main_page = '" . zen_db_input($main_page) . "',
						query_string_parameters = " . (is_null($parameter_string) ? 'null' :
							"'" . zen_db_input($parameter_string) . "'") . ",
						alternate_uri = NULL
					WHERE
						alternate_uri = '" . $uri_mappings_result->fields['alternate_uri'] . "'
					AND
						language_id = '" . $uri_mappings_result->fields['language_id'] . "'
					AND
						current_uri = '" . $uri_mappings_result->fields['current_uri'] . "'
					AND
						date_added = '" . $uri_mappings_result->fields['date_added'] . "';";
				
				$update_record_format_result = $db->Execute($update_record_format_query);
			}
			
			$uri_mappings_result->MoveNext();
		}
	}
	
	// }}}
	
	
	// {{{ _checkUpdateConfigsTable()
	
	/**
	 * Adds any columns which are missing from the configs table and removes an unneeded column.
	 *
	 * @access  protected
	 * @return  boolean   Whether or not the database table is up to date.
	 */
	function _checkUpdateConfigsTable()
	{
		global $db, $messageStack;
		
		// Get the list of columns in the database table
		$columns = array();
		
		$columns_query = 'SHOW COLUMNS FROM ' . TABLE_CEON_URI_MAPPING_CONFIGS . ';';
		$columns_result = $db->Execute($columns_query);
		
		while (!$columns_result->EOF) {
			$columns[] = $columns_result->fields['Field'];
			$columns_result->MoveNext();
		}
		
		if (in_array('mapping_clash_action', $columns)) {
			// Can assume other columns are present
			return true;
		}
		
		if (!$this->_DBUserHasPrivilege('ALTER')) {
			$this->error_messages[] = 'Database table can not be updated! The database user' .
				' does not have ALTER TABLE privileges!';
			
			return false;
		}
		
		$add_column_sql = "
			ALTER TABLE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			ADD
				`mapping_clash_action` VARCHAR(11) DEFAULT 'warn'
			AFTER
				`char_str_replacements`;";
		
		$add_column_result = $db->Execute($add_column_sql);
		
		// Verify that the column was added
		$columns_query = 'SHOW COLUMNS FROM ' . TABLE_CEON_URI_MAPPING_CONFIGS . ';';
		$columns_result = $db->Execute($columns_query);
		
		$columns = array();
		
		while (!$columns_result->EOF) {
			$columns[] = $columns_result->fields['Field'];
			$columns_result->MoveNext();
		}
		
		if (!in_array('mapping_clash_action', $columns)) {
			// Unable to add column to table! The database user may not actually have ALTER TABLE privileges
			$this->error_messages[] = 'Unable to add column to table! The database user may not have ALTER TABLE' .
				' privileges';
			
			return false;
		}
		
		$this->_actions_performed = true;
		
		// Assume all other ALTER operations will work fine if the first one did
		
		$add_column_sql = "
			ALTER TABLE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			ADD
				`manage_product_reviews_mappings` INT(1) UNSIGNED DEFAULT 1
			AFTER
				`mapping_clash_action`;";
		
		$add_column_result = $db->Execute($add_column_sql);
		
		$add_column_sql = "
			ALTER TABLE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			ADD
				`manage_product_reviews_info_mappings` INT(1) UNSIGNED DEFAULT 1
			AFTER
				`manage_product_reviews_mappings`;";
		
		$add_column_result = $db->Execute($add_column_sql);
		
		$add_column_sql = "
			ALTER TABLE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			ADD
				`manage_product_reviews_write_mappings` INT(1) UNSIGNED DEFAULT 1
			AFTER
				`manage_product_reviews_info_mappings`;";
		
		$add_column_result = $db->Execute($add_column_sql);
		
		
		$add_column_sql = "
			ALTER TABLE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			ADD
				`manage_tell_a_friend_mappings` INT(1) UNSIGNED DEFAULT 1
			AFTER
				`manage_product_reviews_write_mappings`;";
		
		$add_column_result = $db->Execute($add_column_sql);
		
		
		$add_column_sql = "
			ALTER TABLE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			ADD
				`automatic_version_checking` INT(1) UNSIGNED DEFAULT 1
			AFTER
				`manage_tell_a_friend_mappings`;";
		
		$add_column_result = $db->Execute($add_column_sql);
		
		
		// Remove old excluded files column
		if (in_array('excluded_files', $columns)) {
			$remove_column_sql = "
				ALTER TABLE
					" . TABLE_CEON_URI_MAPPING_CONFIGS . "
				DROP
					`excluded_files`;";
			
			$remove_column_result = $db->Execute($remove_column_sql);
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _checkForDefaultConfig()
	
	/**
	 * Makes sure that the default configuration exists. If not, it is created.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _checkForDefaultConfig()
	{
		global $db;
		
		$check_config_exists_sql = "
			SELECT
				id
			FROM
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			WHERE
				1 = 1;";
		
		$check_config_exists_result = $db->Execute($check_config_exists_sql);
		
		if (!$check_config_exists_result->EOF) {
			// Config already exists
			return;
		}
		
		$create_default_config_sql = "
			INSERT INTO
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
				(
				id,
				version,
				autogen_new,
				whitespace_replacement,
				capitalisation,
				remove_words,
				char_str_replacements,
				mapping_clash_action,
				manage_product_reviews_mappings,
				manage_product_reviews_info_mappings,
				manage_product_reviews_write_mappings,
				manage_tell_a_friend_mappings,
				automatic_version_checking
				)
			VALUES
				(
				'1',
				'" . $this->_version . "',
				'1',
				'" . CEON_URI_MAPPING_SINGLE_DASH . "',
				'" . CEON_URI_MAPPING_CAPITALISATION_LOWERCASE . "',
				'-',
				'$=>USD',
				'warn',
				'1',
				'1',
				'1',
				'0',
				'1'
				);";
		
		$create_default_config_result = $db->Execute($create_default_config_sql);
		
		$this->_actions_performed = true;
	}
	
	// }}}
	
	
	// {{{ _changeDefaultRemoveWordsSetting()
	
	/**
	 * Makes a small adjustment to the default configuration. The words "a" and "an" are no longer removed by
	 * default.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _changeDefaultRemoveWordsSetting()
	{
		global $db;
		
		$get_remove_words_value_sql = "
			SELECT
				remove_words
			FROM
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			WHERE
				id ='1';";
		
		$get_remove_words_value_result = $db->Execute($get_remove_words_value_sql);
		
		if ($get_remove_words_value_result->EOF) {
			// Error, config not found
			return;
		}
		
		$remove_words = $get_remove_words_value_result->fields['remove_words'];
		
		$orig_remove_words = explode(',', $remove_words);
		
		$new_remove_words = array();
		
		for ($i = 0; $i < sizeof($remove_words); $i++) {
			$orig_remove_words[$i] = trim($orig_remove_words[$i]);
			
			if ($orig_remove_words[$i] != 'a' && $orig_remove_words[$i] != 'an') {
				$new_remove_words[] = $orig_remove_words[$i];
			}
		}
		
		$new_remove_words = implode(',', $new_remove_words);
		
		$adjust_remove_words_setting_sql = "
			UPDATE
				" . TABLE_CEON_URI_MAPPING_CONFIGS . "
			SET
				remove_words = '" . zen_db_input($new_remove_words) . "'
			WHERE
				id = '1';";
		
		$adjust_remove_words_setting_result = $db->Execute($adjust_remove_words_setting_sql);
		
		$this->_actions_performed = true;
	}
	
	// }}}
	
	
	// {{{ _checkZenCartConfigGroupAndOption()
	
	/**
	 * Makes sure that the configuration group and option are present in the Zen Cart configuration table. If
	 * either is missing, it is created.
	 *
	 * @access  protected
	 * @return  boolean   Whether or not the configuration group and option are present and valid.
	 */
	function _checkZenCartConfigGroupAndOption()
	{
		global $db, $messageStack;
		
		$check_config_group_exists_sql = "
			SELECT
				configuration_group_id
			FROM
				" . TABLE_CONFIGURATION_GROUP . "
			WHERE
				configuration_group_title = 'Ceon URI Mapping (SEO)';";
		
		$check_config_group_exists_result = $db->Execute($check_config_group_exists_sql);
		
		if (!$check_config_group_exists_result->EOF) {
			$configuration_group_id = $check_config_group_exists_result->fields['configuration_group_id'];
		} else {
			$add_config_group_options_sql = "
				INSERT INTO
					" . TABLE_CONFIGURATION_GROUP . "
					(
					configuration_group_title,
					configuration_group_description,
					sort_order,
					visible
					)
				VALUES
					(
					'Ceon URI Mapping (SEO)',
					'Set Ceon URI Mapping (SEO) Options',
					'1',
					'1'
					);";
			
			$add_config_group_options_result = $db->Execute($add_config_group_options_sql);
			
			$this->_actions_performed = true;
			
			$configuration_group_id_sql = "
				SELECT
					configuration_group_id  
				FROM
					" . TABLE_CONFIGURATION_GROUP . "
				WHERE
					configuration_group_title = 'Ceon URI Mapping (SEO)';";
			
			$configuration_group_id_result = $db->Execute($configuration_group_id_sql);
			
			if (!$configuration_group_id_result->EOF) {
				$configuration_group_id = $configuration_group_id_result->fields['configuration_group_id'];
			} else {
				// Problem getting ID!
				$this->error_messages[] = 'Couldn\'t get the ID of the configuration group!';
				
				return false;
			}
			
			$set_group_sort_order_sql = "
				UPDATE
					" . TABLE_CONFIGURATION_GROUP . "
				SET
					sort_order = '" . $configuration_group_id . "'
				WHERE
					configuration_group_id = '" . $configuration_group_id . "';";
			
			$set_group_sort_order_result = $db->Execute($set_group_sort_order_sql);
			
			$messageStack->add('Configuration group created.', 'success');
		}
		
		$check_config_option_exists_sql = "
			SELECT
				configuration_group_id
			FROM
				" . TABLE_CONFIGURATION . "
			WHERE
				configuration_key = 'CEON_URI_MAPPING_ENABLED';";
		
		$check_config_option_exists_result = $db->Execute($check_config_option_exists_sql);
		
		if (!$check_config_option_exists_result->EOF) {
			// Make sure the option is assigned to the correct group
			if ($check_config_option_exists_result->fields['configuration_group_id'] != $configuration_group_id) {
				
				$set_group_id_sql = "
					UPDATE
						" . TABLE_CONFIGURATION . "
					SET
						configuration_group_id = '" . $configuration_group_id . "'
					WHERE
						configuration_key = 'CEON_URI_MAPPING_ENABLED';";
				
				$set_group_id_result = $db->Execute($set_group_id_sql);
				
				$messageStack->add('Configuration option assigned to correct group.', 'success');
				
				$this->_actions_performed = true;
			}
		} else {
			$add_config_option_sql = "
				INSERT INTO
					" . TABLE_CONFIGURATION . "
					(
					`configuration_title`,
					`configuration_key`,
					`configuration_value`,
					`configuration_description`,
					`configuration_group_id`,
					`sort_order`,
					`set_function`,
					`date_added`
					)
				VALUES
					(
					'Enable/Disable URI Mapping',
					'CEON_URI_MAPPING_ENABLED',
					'1',
					'If enabled, any Categories/Products/Manufacturers/EZ-Pages/other pages which have a static URI Mapping specified for them in the database will use those static URIs instead of the standard Zen Cart dynamically-built URIs.<br /><br />0 = off <br />1 = on',
					'" . $configuration_group_id . "',
					'1',
					'zen_cfg_select_option(array(''0'', ''1''), ',
					NOW()
					);";
			
			$add_config_option_result = $db->Execute($add_config_option_sql);
			
			$messageStack->add('Configuration group option added.', 'success');
			
			$this->_actions_performed = true;
		}
		
		// Make sure configuration group can be displayed in admin menu
		if (function_exists('zen_register_admin_page')) {
			if (!zen_page_key_exists('ceon_uri_mapping_config_group')) {
				// Add the link to the Ceon URI Mapping Zen Cart configuration options to the admin menu
				zen_register_admin_page('ceon_uri_mapping_config_group', 'BOX_CEON_URI_MAPPING_CONFIG_GROUP',
					'FILENAME_CONFIGURATION', 'gID=' . $configuration_group_id, 'configuration', 'Y',
					$configuration_group_id);
				
				$messageStack->add('Configuration group added to admin menu.', 'success');
			}
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _makeIndexPageMappingsHistorical()
	
	/**
	 * Makes any mappings for the index page historical.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _makeIndexPageMappingsHistorical()
	{
		global $db;
		
		$make_index_page_mapping_historical_query = "
			UPDATE
				" . TABLE_CEON_URI_MAPPINGS . "
			SET
				current_uri = '0'
			WHERE
				main_page = 'index'
			AND
				current_uri = '1'
			AND
				query_string_parameters IS NULL
			AND
				associated_db_id IS NULL;";
		
		$make_index_page_mapping_historical_result = $db->Execute($make_index_page_mapping_historical_query);
	}
	
	// }}}
	
	
	// {{{ _stripTrailingSlashesFromMappings()
	
	/**
	 * Strips any trailing slashes from existing URI mappings, to comply with the new format.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _stripTrailingSlashesFromMappings()
	{
		global $db;
		
		set_time_limit(0);
		
		$uris_with_trailing_slashes_sql = "
			SELECT
				uri,
				language_id,
				current_uri
			FROM
				" . TABLE_CEON_URI_MAPPINGS . "
			WHERE
				uri LIKE '%/';";
		
		$uris_with_trailing_slashes_result = $db->Execute($uris_with_trailing_slashes_sql);
		
		while (!$uris_with_trailing_slashes_result->EOF) {
			
			$uri = $uris_with_trailing_slashes_result->fields['uri'];
			
			while (substr($uri, -1) == '/') {
				$uri = substr($uri, 0, strlen($uri) - 1);
			}
			
			// Don't bother updating this mapping if it was the result of someone having manually added a URI
			// mapping for the root of the site!
			if (strlen($uri) > 1) {
				$strip_trailing_slashes_sql = "
					UPDATE
						" . TABLE_CEON_URI_MAPPINGS . "
					SET
						uri = '" . zen_db_input($uri) . "'
					WHERE
						uri = '" . zen_db_input($uris_with_trailing_slashes_result->fields['uri']) . "'
					AND
						language_id =  '" .
							zen_db_input($uris_with_trailing_slashes_result->fields['language_id']) . "'
					AND
						current_uri = '" .
							zen_db_input($uris_with_trailing_slashes_result->fields['current_uri']) . "';";
				
				$strip_trailing_slashes_result = $db->Execute($strip_trailing_slashes_sql);
				
				$this->_actions_performed = true;
			}
			
			$uris_with_trailing_slashes_result->MoveNext();
		}
	}
	
	// }}}
	
	
	// {{{ _DBUserHasPrivilege()
	
	/**
	 * Checks if the current database user has a particular privilege type for the store's database.
	 *
	 * @access  protected
	 * @param   string    $privilege_type   The type of privilege to be checked for.
	 * @return  boolean   Whether or not the current database user has the specified privilege.
	 */
	function _DBUserHasPrivilege($privilege_type)
	{
		global $db;
		
		if (isset($_GET['override-db-privileges-check'])) {
			// Unfortunately some servers have been found to not return the correct list of privileges! Make things
			// easier for the user by allowing an override flag to be used
			return true;
		}
		
		$privilege_type = strtoupper($privilege_type);
		
		$db_user_has_privilege = false;
		
		$check_user_privileges_sql = "SHOW GRANTS;";
		
		$check_user_privileges_result = $db->Execute($check_user_privileges_sql);
		
		while (!$check_user_privileges_result->EOF) {
			// Check each GRANT for the current user to see if it covers the current database and gives the
			// specified privilege
			foreach ($check_user_privileges_result->fields as $current_grant_string) {
				if (preg_match('/GRANT (.*) ON (.*) TO /i', $current_grant_string, $matches)) {
					// Get the privilege string
					$privilege_string = strtoupper($matches[1]);
					
					// Extract the database name for this grant string by removing all the characters/strings it
					// could be surrounded with or followed by
					$database_name = $matches[2];
					
					$database_name = str_replace('`', '', $database_name);
					$database_name = str_replace("'", '', $database_name);
					$database_name = str_replace('.*', '', $database_name);
					$database_name = preg_replace('/@.*$/', '', $database_name);
					$database_name = str_replace('\_', '_', $database_name);
					
					if (($database_name == '*' || $database_name == DB_DATABASE) &&
							(strpos($privilege_string, 'ALL PRIVILEGES') !== false ||
							strpos($privilege_string, $privilege_type) !== false)) {
						// This grant gives the specified privilege for the Zen Cart database, no need to examine
						// any others
						$db_user_has_privilege = true;
						
						break 2;
					}
				}
			}
			
			$check_user_privileges_result->MoveNext();
		}
		
		return $db_user_has_privilege;
	}
	
	// }}}
}

// }}}
