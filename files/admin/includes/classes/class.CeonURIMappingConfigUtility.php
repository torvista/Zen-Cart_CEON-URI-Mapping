<?php

/**
 * Ceon URI Mapping Config Utility Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingConfigUtility.php 1054 2012-09-22 15:45:15Z conor $
 */

/**
 * Load in the Ceon URI Mapping Version class so it can be extended
 */
require_once(DIR_WS_CLASSES . 'class.CeonURIMappingVersion.php');


// {{{ CeonURIMappingConfigUtility

/**
 * Installs/upgrades the module, handles the configuration utility of the module, including building its interface
 * output, performs configuration checks and builds the configuration check interface output.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingConfigUtility extends CeonURIMappingVersion
{
	// {{{ properties
	
	/**
	 * Maintains a list of any errors encountered in the current configuration, or an installation or upgrade.
	 *
	 * @var     array
	 * @access  protected
	 */
	var $_error_messages = array();
	
	/**
	 * Whether or not auto-generation is enabled.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_autogen_new = null;
	
	/**
	 * The whitespace replacement setting for the store.
	 *
	 * @var     integer
	 * @access  protected
	 */
	var $_whitespace_replacement = null;
	
	/**
	 * The capitalisation setting for the store.
	 *
	 * @var     integer
	 * @access  protected
	 */
	var $_capitalisation = null;
	
	/**
	 * The list of words to be removed from auto-generated URIs.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_remove_words = null;
	
	/**
	 * The list of character/string replacements to be applied when auto-generating URIs.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_char_str_replacements = null;
	
	/**
	 * The action to be taken if a URI mapping being auto-generated clashes with an existing mapping.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_mapping_clash_action = null;
	
	/**
	 * Whether or not product reviews pages should have their URIs auto-managed.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_manage_product_reviews_mappings = null;
	
	/**
	 * Whether or not products' review info pages should have their URIs auto-managed.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_manage_product_reviews_info_mappings = null;
	
	/**
	 * Whether or not products' write a review pages should have their URIs auto-managed.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_manage_product_reviews_write_mappings = null;
	
	/**
	 * Whether or not product tell a friend pages should have their URIs auto-managed.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_manage_tell_a_friend_mappings = null;
	
	/**
	 * The list of URI part's text for products' reviews pages, for the various languages the store uses.
	 *
	 * @var     array
	 * @access  protected
	 */
	var $_product_reviews_pages_uri_parts = array();
	
	/**
	 * The list of URI part's text for product review info pages, for the various languages the
	 * store uses.
	 *
	 * @var     array
	 * @access  protected
	 */
	var $_product_reviews_info_pages_uri_parts = array();
	
	/**
	 * The list of URI part's text for product write a review pages, for the various languages the store uses.
	 *
	 * @var     array
	 * @access  protected
	 */
	var $_product_reviews_write_pages_uri_parts = array();
	
	/**
	 * The list of URI part's text for product tell a friend pages, for the various languages the store uses.
	 *
	 * @var     array
	 * @access  protected
	 */
	var $_product_tell_a_friend_pages_uri_parts = array();
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the class. Handles the display and processing of the configuration utility. Also
	 * instigates the installation/upgrade functionality if necessary.
	 * 
	 * @access  public
	 */
	function __construct()
	{
		// Set the flag so that the configuration isn't autoladed by the admin superclass - it may not exist or yet
		// or it may be out of date!
		parent::__construct(false);
		
		if (!$this->_lookUpInstalledVersion()) {
			// Error occurred when attempting to get the version number
		}
		
		// If the configuration options are being submitted then the configuration has previously been loaded and
		// tested. If not, it must be loaded and checked now
		if (!isset($_POST['autogen-new'])) {
			if (!$this->_checkInstalledAndUpToDate()) {
				$this->_buildVersionErrorOutput();
				
				$this->_buildFooter();
				
				return;
			}
			
			$this->_loadConfig();
			
		} else {
			$this->_processConfigFormSubmission();
		}
		
		$this->_buildConfigUtilityInterface();
	}
	
	// }}}
	
	
	// {{{ _checkInstalledAndUpToDate()
	
	/**
	 * Checks that the module is installed and that the database and configuration are up to date.
	 *
	 * @access  protected
	 * @return  boolean   True if the module is installed and the database and configuration are up to date, false
	 *                    otherwise.
	 */
	function _checkInstalledAndUpToDate()
	{
		global $messageStack;
		
		if ((is_null($this->_installed_version) || $this->_installed_version != $this->_version) ||
				isset($_GET['check-config'])) {
			// Main variable holds list of installation/upgrade errors (if any)
			$version_errors = array();
			
			// Instantiate and run the installation/upgrade class
			require_once('class.CeonURIMappingInstallOrUpgrade.php');
			
			$install_or_upgrade = new CeonURIMappingInstallOrUpgrade($this->_version, $this->_installed_version);
			
			if (sizeof($install_or_upgrade->error_messages) > 0) {
				$this->_error_messages = $install_or_upgrade->error_messages;
				
				return false;
				
			} else {
				// Version has been brought up to date
				if (is_null($this->_installed_version)) {
					$messageStack->add(sprintf(SUCCESS_MODULE_INSTALLED, $this->_version), 'success');
				} else {
					if ($install_or_upgrade->actionPerformed() || $this->_installed_version != $this->_version) {
						$messageStack->add(sprintf(SUCCESS_DATABASE_AND_CONFIG_UPDATED, $this->_version),
							'success');
					} else {
						// Things can be assumed to have been up to date (e.g. user has run a manual config check
						// when everything is already fine)
						$messageStack->add(sprintf(SUCCESS_DATABASE_AND_CONFIG_UP_TO_DATE, $this->_version),
							'success');
					}
				}
				
				$this->_installed_version = $this->_version;
			}
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _buildVersionErrorOutput()
	
	/**
	 * Builds the HTML for the error messages to be displayed to the user when a problem occurs in the installation
	 * or upgrade process.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildVersionErrorOutput()
	{
		$num_errors = sizeof($this->_error_messages);
		
		foreach ($this->_error_messages as $version_error) {
			$this->_output .=  '<p class="ErrorIntro">' . $version_error . '</p>' . "\n";
		}
	}
	
	// }}}
	
	
	// {{{ _loadConfig()
	
	/**
	 * Loads the configuration for the module from the database.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _loadConfig()
	{
		global $db;
		
		// Only one config currently supported so its ID is hard-coded in the following SQL
		$load_config_sql = "
			SELECT
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
			FROM
				" . TABLE_CEON_URI_MAPPING_CONFIGS . " 
			WHERE
				id = '1';";
		
		$load_config_result = $db->Execute($load_config_sql);
		
		if ($load_config_result->EOF) {
			
		} else {
			$this->_installed_version = $load_config_result->fields['version'];
			
			$this->_autogen_new = $load_config_result->fields['autogen_new'];
			$this->_whitespace_replacement = $load_config_result->fields['whitespace_replacement'];
			$this->_capitalisation = $load_config_result->fields['capitalisation'];
			$this->_remove_words = $load_config_result->fields['remove_words'];
			$this->_char_str_replacements = $load_config_result->fields['char_str_replacements'];
			$this->_mapping_clash_action = $load_config_result->fields['mapping_clash_action'];
			
			$this->_manage_product_reviews_mappings =
				($load_config_result->fields['manage_product_reviews_mappings'] == 1 ? true : false);
			
			$this->_manage_product_reviews_info_mappings =
				($load_config_result->fields['manage_product_reviews_info_mappings'] == 1 ? true : false);
			
			$this->_manage_product_reviews_write_mappings =
				($load_config_result->fields['manage_product_reviews_write_mappings'] == 1 ? true : false);
			
			$this->_manage_tell_a_friend_mappings =
				($load_config_result->fields['manage_tell_a_friend_mappings'] == 1 ? true : false);
			
			$this->_automatic_version_checking =
				($load_config_result->fields['automatic_version_checking'] == 1 ? true : false);
			
			$uri_parts_sql = "
				SELECT
					page_type,
					language_code,
					uri_part
				FROM
					" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "
				WHERE
					1 = 1;";
			
			$uri_parts_result = $db->Execute($uri_parts_sql);
			
			while (!$uri_parts_result->EOF) {
				switch ($uri_parts_result->fields['page_type']) {
					case 'product_reviews':
						$this->_product_reviews_pages_uri_parts
							[$uri_parts_result->fields['language_code']] =
							$uri_parts_result->fields['uri_part'];
						break;
					case 'product_reviews_info':
						$this->_product_reviews_info_pages_uri_parts
							[$uri_parts_result->fields['language_code']] =
							$uri_parts_result->fields['uri_part'];
						break;
					case 'product_reviews_write':
						$this->_product_reviews_write_pages_uri_parts
							[$uri_parts_result->fields['language_code']] =
							$uri_parts_result->fields['uri_part'];
						break;
					case 'tell_a_friend':
						$this->_tell_a_friend_pages_uri_parts
							[$uri_parts_result->fields['language_code']] =
							$uri_parts_result->fields['uri_part'];
						break;
				}
				
				$uri_parts_result->MoveNext();
			}
		}
	}
	
	// }}}
	
	
	// {{{ _processConfigFormSubmission()
	
	/**
	 * Processes the submission of configuration values through the configuration utility. Any mistakes/errors are
	 * added to the list of error messages, for display later.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _processConfigFormSubmission()
	{
		global $db, $languages, $num_languages, $ceon_uri_mapping_demo, $messageStack;
		
		$this->_autogen_new = $_POST['autogen-new'];
		$this->_whitespace_replacement = $_POST['whitespace-replacement'];
		$this->_capitalisation = $_POST['capitalisation'];
		$this->_remove_words = trim($_POST['remove-words']);
		$this->_char_str_replacements = trim($_POST['char-str-replacements']);
		$this->_mapping_clash_action = trim($_POST['mapping-clash-action']);
		
		$this->_manage_product_reviews_mappings =
			(isset($_POST['manage-product-reviews-mappings']) ? true : false);
		
		$this->_manage_product_reviews_info_mappings =
			(isset($_POST['manage-product-reviews-info-mappings']) ? true : false);
		
		$this->_manage_product_reviews_write_mappings =
			(isset($_POST['manage-product-reviews-write-mappings']) ? true : false);
		
		$this->_manage_tell_a_friend_mappings = (isset($_POST['manage-tell-a-friend-mappings']) ? true : false);
		
		for ($i = 0; $i < $num_languages; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			$uri_part = trim(preg_replace('|[^a-zA-Z0-9\.\-_\/ ]|', '',
				$_POST['product-reviews-pages-uri-parts'][$language_code]));
			
			// Remove any slashes at the start of the URI part
			while (substr($uri_part, 0, 1) == '/') {
				$uri_part = substr($uri_part, 1, strlen($uri_part) -1);
			}
			
			// Remove any trailing slashes
			while (substr($uri_part, -1) == '/') {
				$uri_part = substr($uri_part, 0, strlen($uri_part) -1);
			}
			
			$this->_product_reviews_pages_uri_parts[$language_code] = $uri_part;
			
			if ($this->_manage_product_reviews_mappings && strlen($uri_part) == 0) {
				// URI Part is required
				$this->_error_messages['product-reviews-pages-uri-parts-' . $language_code] =
					TEXT_ERROR_URI_PART_MUST_BE_ENTERED;
			}
			
			
			$uri_part = trim(preg_replace('|[^a-zA-Z0-9\.\-_\/ ]|', '',
				$_POST['product-reviews-info-pages-uri-parts'][$language_code]));
			
			// Remove any slashes at the start of the URI part
			while (substr($uri_part, 0, 1) == '/') {
				$uri_part = substr($uri_part, 1, strlen($uri_part) -1);
			}
			
			// Remove any trailing slashes
			while (substr($uri_part, -1) == '/') {
				$uri_part = substr($uri_part, 0, strlen($uri_part) -1);
			}
			
			$this->_product_reviews_info_pages_uri_parts[$language_code] = $uri_part;
			
			if ($this->_manage_product_reviews_info_mappings && strlen($uri_part) == 0) {
				// URI Part is required
				$this->_error_messages['product-reviews-info-pages-uri-parts-' . $language_code] =
					TEXT_ERROR_URI_PART_MUST_BE_ENTERED;
			}
			
			
			$uri_part = trim(preg_replace('|[^a-zA-Z0-9\.\-_\/ ]|', '',
				$_POST['product-reviews-write-pages-uri-parts'][$language_code]));
			
			// Remove any slashes at the start of the URI part
			while (substr($uri_part, 0, 1) == '/') {
				$uri_part = substr($uri_part, 1, strlen($uri_part) -1);
			}
			
			// Remove any trailing slashes
			while (substr($uri_part, -1) == '/') {
				$uri_part = substr($uri_part, 0, strlen($uri_part) -1);
			}
			
			$this->_product_reviews_write_pages_uri_parts[$language_code] = $uri_part;
			
			if ($this->_manage_product_reviews_write_mappings && strlen($uri_part) == 0) {
				// URI Part is required
				$this->_error_messages['product-reviews-write-pages-uri-parts-' . $language_code] =
					TEXT_ERROR_URI_PART_MUST_BE_ENTERED;
			}
			
			
			$uri_part = trim(preg_replace('|[^a-zA-Z0-9\.\-_\/ ]|', '',
				$_POST['tell-a-friend-pages-uri-parts'][$language_code]));
			
			// Remove any slashes at the start of the URI part
			while (substr($uri_part, 0, 1) == '/') {
				$uri_part = substr($uri_part, 1, strlen($uri_part) -1);
			}
			
			// Remove any trailing slashes
			while (substr($uri_part, -1) == '/') {
				$uri_part = substr($uri_part, 0, strlen($uri_part) -1);
			}
			
			$this->_tell_a_friend_pages_uri_parts[$language_code] = $uri_part;
			
			if ($this->_manage_tell_a_friend_mappings && strlen($uri_part) == 0) {
				// URI Part is required
				$this->_error_messages['tell-a-friend-pages-uri-parts-' . $language_code] =
					TEXT_ERROR_URI_PART_MUST_BE_ENTERED;
			}
		}
		
		$this->_automatic_version_checking = $_POST['automatic-version-checking'];
		
		// Save the configuration
		if (!$ceon_uri_mapping_demo && sizeof($this->_error_messages) == 0) {
			
			$save_config_data_array = array(
				'autogen_new' => $this->_autogen_new,
				'whitespace_replacement' => $this->_whitespace_replacement,
				'capitalisation' => $this->_capitalisation,
				'remove_words' => ((strlen($this->_remove_words) == 0) ? 'null' : $this->_remove_words),
				'char_str_replacements' =>
					((strlen($this->_char_str_replacements) == 0) ? 'null' : $this->_char_str_replacements),
				'mapping_clash_action' => $this->_mapping_clash_action,
				'manage_product_reviews_mappings' => ($this->_manage_product_reviews_mappings ? 1 : 0),
				'manage_product_reviews_info_mappings' => ($this->_manage_product_reviews_info_mappings ? 1 : 0),
				'manage_product_reviews_write_mappings' => ($this->_manage_product_reviews_write_mappings ? 1 : 0),
				'manage_tell_a_friend_mappings' => ($this->_manage_tell_a_friend_mappings ? 1 : 0),
				'automatic_version_checking' => $this->_automatic_version_checking
				);
			
			// Only one config currently supported so ID is hard-coded
			$selection_sql = "id = '1'";
			
			$save_config_result = zen_db_perform(TABLE_CEON_URI_MAPPING_CONFIGS,
				$save_config_data_array, 'update', $selection_sql);
			
			$remove_current_prp_uri_part_records = "
				DELETE FROM
					" . TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS . "
				WHERE
					1 = 1;";
			
			$db->Execute($remove_current_prp_uri_part_records);
			
			for ($i = 0; $i < $num_languages; $i++) {
				$language_code = strtolower($languages[$i]['code']);
				
				$uri_parts_data_array = array(
					'page_type' => 'product_reviews',
					'language_code' => $language_code,
					'uri_part' => $this->_product_reviews_pages_uri_parts[$language_code]
					);
				
				zen_db_perform(TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS, $uri_parts_data_array);
				
				$uri_parts_data_array = array(
					'page_type' => 'product_reviews_info',
					'language_code' => $language_code,
					'uri_part' => $this->_product_reviews_info_pages_uri_parts[$language_code]
					);
				
				zen_db_perform(TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS, $uri_parts_data_array);
				
				$uri_parts_data_array = array(
					'page_type' => 'product_reviews_write',
					'language_code' => $language_code,
					'uri_part' => $this->_product_reviews_write_pages_uri_parts[$language_code]
					);
				
				zen_db_perform(TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS, $uri_parts_data_array);
				
				$uri_parts_data_array = array(
					'page_type' => 'tell_a_friend',
					'language_code' => $language_code,
					'uri_part' => $this->_tell_a_friend_pages_uri_parts[$language_code]
					);
				
				zen_db_perform(TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS, $uri_parts_data_array);
				
			}
			
			if ($save_config_result) {
				if ($ceon_uri_mapping_demo) {
					$messageStack->add(SUCCESS_CONFIGURATION_SAVED_DEMO, 'success');
				} else {
					$messageStack->add(SUCCESS_CONFIGURATION_SAVED, 'success');
				}
			}
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _buildConfigUtilityInterface()
	
	/**
	 * Builds the interface for the configuration utility.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildConfigUtilityInterface()
	{
		// Build the panels this utility uses, adding each to the list of panels
		$this->_buildAutogenerationConfigPanel();
		
		$this->_buildAutoManagedProductURIsConfigPanel();
		
		$this->_buildVersionCheckingConfigPanel();
		
		$this->_buildInstallationCheckPanel();
		
		
		// Build the actual output for the utility
		
		// Display a count of the number of errors (if any) at the top of the page
		$num_errors = sizeof($this->_error_messages);
		
		if ($num_errors > 0) {
			if ($num_errors == 1) {
				$this->_output .= '<p class="ErrorIntro">' . TEXT_ERROR_IN_CONFIG . '</p>';
			} else if ($num_errors > 0 ) {
				$this->_output .= sprintf('<p class="ErrorIntro">' . TEXT_ERRORS_IN_CONFIG, $num_errors) . '</p>';
			}
		}
		
		$this->_buildTabbedPanelMenu();
		
		$this->_buildPanels();
		
		$this->_buildSubmitAndCancelButtons();
		
		$this->_buildFooter();
	}
	
	// }}}
	
	
	// {{{ _getSelectedPanelID()
	
	/**
	 * Gets the ID of the panel which should be selected by default when the tabbed panel admin interface is built.
	 *
	 * @access  protected
	 * @return  string    The ID of the selected panel.
	 */
	function _getSelectedPanelID()
	{
		// Currently only the auto-managed product page URIs tab can have errors, so if any error messages are
		// being displayed, it will be for that panel, so show it. Otherwise default to showing the auto-generation
		// panel
		if (sizeof($this->_error_messages) > 0) {
			$selected_panel_id = 'auto-managed-product-uris-panel';
		} else {
			$selected_panel_id = 'autogen-panel';
		}
		
		return $selected_panel_id;
	}
	
	// }}}
	
	
	// {{{ _buildAutogenerationConfigPanel()
	
	/**
	 * Builds the configuration panel used to display the settings for the autogeneration functionality. Adds the
	 * panel to the list of panels.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildAutogenerationConfigPanel()
	{
		// Variable holds the table rows being built for the options for this panel
		$option_output_rows = '';
		
		
		// Autogeneration enabled/disabled setting
		$option_output_rows .= $this->_buildConfigSettingDescRow('autogen-new', TEXT_LABEL_AUTOGEN_ENABLED_NEW,
			TEXT_CONFIG_DESC_AUTOGEN_ENABLED_NEW);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= '<p>' . zen_draw_radio_field('autogen-new', '1',
			($this->_autogen_new == 1 ? true : false), '', 'id="autogen-new-yes"') .
			'<label class="attribsRadioButton" for="autogen-new-yes">' . TEXT_YES . '</label>' . "\n";
		
		$option_output_rows .= '<br />' . zen_draw_radio_field('autogen-new', '0',
			($this->_autogen_new == 1 ? false : true), '', 'id="autogen-new-no"') .
			'<label class="attribsRadioButton" for="autogen-new-no">' . TEXT_NO . '</label>' . "</p>\n";
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Whitespace replacement setting
		
		/**
		 * Variable holds values for the whitespace replacement options
		 */
		$whitespace_replacement_options = array(
			array(
				'id' => CEON_URI_MAPPING_SINGLE_UNDERSCORE,
				'text' => TEXT_WHITESPACE_REPLACEMENT_SINGLE_UNDERSCORE
				),
			array(
				'id' => CEON_URI_MAPPING_SINGLE_DASH,
				'text' => TEXT_WHITESPACE_REPLACEMENT_SINGLE_DASH
				),
			array(
				'id' => CEON_URI_MAPPING_SINGLE_FULL_STOP,
				'text' => TEXT_WHITESPACE_REPLACEMENT_SINGLE_FULL_STOP
				),
			array(
				'id' => CEON_URI_MAPPING_REMOVE,
				'text' => TEXT_WHITESPACE_REPLACEMENT_REMOVE
				)
			);
		
		$option_output_rows .= $this->_buildConfigSettingDescRow('whitespace-replacement',
			TEXT_LABEL_WHITESPACE_REPLACEMENT, TEXT_CONFIG_DESC_WHITESPACE_REPLACEMENT);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= zen_draw_pull_down_menu('whitespace-replacement',
			$whitespace_replacement_options, $this->_whitespace_replacement, '') . "\n";
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Capitalisation setting
		$option_output_rows .= $this->_buildConfigSettingDescRow('capitalisation',
			TEXT_LABEL_CAPITALISATION, TEXT_CONFIG_DESC_CAPITALISATION);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= '<p>' . zen_draw_radio_field('capitalisation',
			CEON_URI_MAPPING_CAPITALISATION_LOWERCASE,
			($this->_capitalisation == CEON_URI_MAPPING_CAPITALISATION_LOWERCASE ? true : false),
			'', 'id="capitalisation-lowercase"') .
			'<label class="attribsRadioButton" for="capitalisation-lowercase">' .
			TEXT_CAPITALISATION_LOWERCASE . '</label>' . "\n";
		
		$option_output_rows .= '<br />' . zen_draw_radio_field('capitalisation',
			CEON_URI_MAPPING_CAPITALISATION_AS_IS,
			($this->_capitalisation == CEON_URI_MAPPING_CAPITALISATION_AS_IS ? true : false), '',
			'id="capitalisation-as-is"') .
			'<label class="attribsRadioButton" for="capitalisation-as-is">' .
			TEXT_CAPITALISATION_AS_IS . '</label>' . "\n";
		
		$option_output_rows .= '<br />' . zen_draw_radio_field('capitalisation',
			CEON_URI_MAPPING_CAPITALISATION_UCFIRST,
			($this->_capitalisation == CEON_URI_MAPPING_CAPITALISATION_UCFIRST ? true : false), '',
			'id="capitalisation-ucfirst"') .
			'<label class="attribsRadioButton" for="capitalisation-ucfirst">' .
			TEXT_CAPITALISATION_UCFIRST . '</label>' . "</p>\n";
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Remove words setting
		$option_output_rows .= $this->_buildConfigSettingDescRow('remove-words',
			TEXT_LABEL_REMOVE_WORDS, TEXT_CONFIG_DESC_REMOVE_WORDS);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= zen_draw_textarea_field('remove-words', 'virtual', 50, 6,
			htmlentities($this->_remove_words, ENT_COMPAT, CHARSET));
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Character/string replacements setting
		$option_output_rows .= $this->_buildConfigSettingDescRow('char-str-replacements',
			TEXT_LABEL_CHAR_STR_REPLACEMENTS, TEXT_CONFIG_DESC_CHAR_STR_REPLACEMENTS);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= zen_draw_textarea_field('char-str-replacements', 'virtual', 50, 2,
			$this->_char_str_replacements);
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Clashing mapping action setting
		$option_output_rows .= $this->_buildConfigSettingDescRow('mapping-clash-action',
			TEXT_LABEL_MAPPING_CLASH_ACTION, TEXT_CONFIG_DESC_MAPPING_CLASH_ACTION);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= '<p>' . zen_draw_radio_field('mapping-clash-action',
			'warn', ($this->_mapping_clash_action == 'warn' ? true : false),
			'', 'id="mapping-clash-action-warn"') .
			'<label class="attribsRadioButton" for="mapping-clash-action-warn">' .
			TEXT_MAPPING_CLASH_ACTION_WARN . '</label>' . "\n";
		
		$option_output_rows .= '<br />' . zen_draw_radio_field('mapping-clash-action',
			'auto-append', ($this->_mapping_clash_action == 'auto-append' ? true : false), '',
			'id="mapping-clash-action-auto-append"') .
			'<label class="attribsRadioButton" for="mapping-clash-action-auto-append">' .
			TEXT_MAPPING_CLASH_ACTION_AUTO_APPEND . '</label>' . "\n";
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Now build the containing panel and add the rows to it
		$this->_addPanel('autogen-panel', TEXT_AUTOGEN_CONFIG, null, 
			$this->_buildConfigPanel('autogen-panel', TEXT_AUTOGEN_CONFIG, $option_output_rows));
	}
	
	// }}}
	
	
	// {{{ _buildAutoManagedProductURIsConfigPanel()
	
	/**
	 * Builds the configuration panel used to display the settings for the auto-managed product URIs functionality.
	 * Adds the panel to the list of panels.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildAutoManagedProductURIsConfigPanel()
	{
		global $languages, $num_languages;
		
		// Variable holds the table rows being built for the options for this panel
		$option_output_rows = '';
		
		
		// Auto-managed Product Related Page URI Mappings enabled/disabled settings
		$option_output_rows .= $this->_buildConfigSettingDescRow('auto-managed-product-uris',
			TEXT_LABEL_AUTO_MANAGED_PRODUCT_URIS, TEXT_CONFIG_DESC_AUTO_MANAGED_PRODUCT_URIS);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= '<p style="margin-top: 1.5em;">' .
			TEXT_INSTRUCTIONS_AUTO_MANAGED_PRODUCT_URIS . "</p>\n";
		
		$option_output_rows .= '<p>' . zen_draw_checkbox_field('manage-product-reviews-mappings',
			$this->_manage_product_reviews_mappings, $this->_manage_product_reviews_mappings, '',
			'id="manage-product-reviews-mappings"');
		
		$option_output_rows .= ' <label for="manage-product-reviews-mappings">';
		$option_output_rows .= TEXT_LABEL_AUTO_MANAGED_URI_REVIEWS . "</label></p>\n";
		
		$option_output_rows .= '<p>' . zen_draw_checkbox_field(
			'manage-product-reviews-info-mappings', $this->_manage_product_reviews_info_mappings,
			$this->_manage_product_reviews_info_mappings, '',
			'id="manage-product-reviews-info-mappings"');
		
		$option_output_rows .= ' <label for="manage-product-reviews-info-mappings">';
		$option_output_rows .= TEXT_LABEL_AUTO_MANAGED_URI_REVIEW_INFO . "</label></p>\n";
		
		$option_output_rows .= '<p>' . zen_draw_checkbox_field(
			'manage-product-reviews-write-mappings', $this->_manage_product_reviews_write_mappings,
			$this->_manage_product_reviews_write_mappings, '',
			'id="manage-product-reviews-write-mappings"');
		
		$option_output_rows .= ' <label for="manage-product-reviews-write-mappings">';
		$option_output_rows .= TEXT_LABEL_AUTO_MANAGED_URI_WRITE_A_REVIEW . "</label></p>\n";
		
		$option_output_rows .= '<p>' . zen_draw_checkbox_field('manage-tell-a-friend-mappings',
			$this->_manage_tell_a_friend_mappings, $this->_manage_tell_a_friend_mappings, '',
			'id="manage-tell-a-friend-mappings"');
		
		$option_output_rows .= ' <label for="manage-tell-a-friend-mappings">';
		$option_output_rows .= TEXT_LABEL_AUTO_MANAGED_URI_TELL_A_FRIEND . "</label></p>\n";
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Product Related Pages URI Parts textfields
		$option_output_rows .= $this->_buildConfigSettingDescRow(
			'auto-managed-product-uris-uri-parts', TEXT_LABEL_AUTO_MANAGED_PRODUCT_URIS_URI_PARTS,
			TEXT_CONFIG_DESC_AUTO_MANAGED_PRODUCT_URIS_URI_PARTS);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		
		// Product reviews pages URI part textfields are displayed in a subpanel. Build its content here
		$subpanel = '';
		
		for ($i = 0; $i < $num_languages; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			$subpanel .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' .
				$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('product-reviews-pages-uri-parts[' . $language_code . ']',
				$this->_product_reviews_pages_uri_parts[$language_code], 'size="20" class="Textfield"') . "\n";
			
			if (isset($this->_error_messages['product-reviews-pages-uri-parts-' . $language_code])) {
				$subpanel .= '<br /><span class="FormError">' . TEXT_ERROR_URI_PART_MUST_BE_ENTERED . "</span>\n";
			}
			
			if ($i < $num_languages - 1) {
				$subpanel .= '<br />';
			}
		}
		
		$option_output_rows .= $this->_buildSubPanel(TEXT_LABEL_AUTO_MANAGED_URI_REVIEWS, $subpanel);
		
		
		// Product review info pages URI part textfields are displayed in a subpanel. Build its content here
		$subpanel = '';
		
		for ($i = 0; $i < $num_languages; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			$subpanel .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' .
				$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('product-reviews-info-pages-uri-parts[' . $language_code . ']',
				$this->_product_reviews_info_pages_uri_parts[$language_code], 'size="20" class="Textfield"') .
				"\n";
			
			if (isset($this->_error_messages['product-reviews-info-pages-uri-parts-' . $languages[$i]['id']])) {
				$subpanel .= '<br /><span class="FormError">' . TEXT_ERROR_URI_PART_MUST_BE_ENTERED . "</span>\n";
			}
			
			if ($i < $num_languages - 1) {
				$subpanel .= '<br />';
			}
		}
		
		$option_output_rows .= $this->_buildSubPanel(TEXT_LABEL_AUTO_MANAGED_URI_REVIEW_INFO, $subpanel);
		
		
		// Product write a review pages URI part textfields are displayed in a subpanel. Build its content here
		$subpanel = '';
		
		for ($i = 0; $i < $num_languages; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			$subpanel .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' .
				$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('product-reviews-write-pages-uri-parts[' . $language_code . ']',
				$this->_product_reviews_write_pages_uri_parts[$language_code], 'size="20" class="Textfield"') .
				"\n";
			
			if (isset($this->_error_messages['product-reviews-write-pages-uri-parts-' . $languages[$i]['id']])) {
				$subpanel .= '<br /><span class="FormError">' . TEXT_ERROR_URI_PART_MUST_BE_ENTERED . "</span>\n";
			}
			
			if ($i < $num_languages - 1) {
				$subpanel .= '<br />';
			}
		}
		
		$option_output_rows .= $this->_buildSubPanel(TEXT_LABEL_AUTO_MANAGED_URI_WRITE_A_REVIEW, $subpanel);
		
		
		// Tell a friend pages URI part textfields are displayed in a subpanel. Build its content here
		$subpanel = '';
		
		for ($i = 0; $i < $num_languages; $i++) {
			$language_code = strtolower($languages[$i]['code']);
			
			$subpanel .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' .
				$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('tell-a-friend-pages-uri-parts[' . $language_code . ']',
				$this->_tell_a_friend_pages_uri_parts[$language_code], 'size="20" class="Textfield"') . "\n";
			
			if (isset($this->_error_messages['tell-a-friend-pages-uri-parts-' . $language_code])) {
				$subpanel .= '<br /><span class="FormError">' . TEXT_ERROR_URI_PART_MUST_BE_ENTERED . "</span>\n";
			}
			
			if ($i < $num_languages - 1) {
				$subpanel .= '<br />';
			}
		}
		
		$option_output_rows .= $this->_buildSubPanel(TEXT_LABEL_AUTO_MANAGED_URI_TELL_A_FRIEND, $subpanel);
		
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		
		// Now build the containing panel and add the rows to it
		$this->_addPanel('auto-managed-product-uris-panel', TEXT_AUTO_MANAGED_PRODUCT_URIS, null,
			$this->_buildConfigPanel('auto-managed-product-uris-panel', TEXT_AUTO_MANAGED_PRODUCT_URIS,
			$option_output_rows));
	}
	
	// }}}
	
	
	// {{{ _buildVersionCheckingConfigPanel()
	
	/**
	 * Builds the configuration panel used to display the setting for the version checking functionality. Adds the
	 * panel to the list of panels.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildVersionCheckingConfigPanel()
	{
		// Variable holds the table rows being built for the options for this panel
		$option_output_rows = '';
		
/*		
		// Autogeneration enabled/disabled setting
		$option_output_rows .= $this->_buildConfigSettingDescRow('automatic-version-checking',
			TEXT_LABEL_AUTOMATIC_VERSION_CHECKING, TEXT_CONFIG_DESC_AUTOMATIC_VERSION_CHECKING);
		
		$option_output_rows .= '		<tr>
			<td class="CeonFormItemField">' . "\n";
		
		$option_output_rows .= '<p>' . zen_draw_radio_field('automatic-version-checking', '1',
			($this->_automatic_version_checking == 1 ? true : false), '',
			'id="automatic-version-checking-yes"') .
			'<label class="attribsRadioButton" for="automatic-version-checking-yes">' .
			TEXT_AUTOMATIC_VERSION_CHECKING . '</label>' . "\n";
		
		$option_output_rows .= '<br />' . zen_draw_radio_field('automatic-version-checking', '0',
			($this->_automatic_version_checking == 1 ? false : true), '',
			'id="automatic-version-checking-no"') .
			'<label class="attribsRadioButton" for="automatic-version-checking-no">' .
			TEXT_MANUAL_VERSION_CHECKING . '</label>' . "</p>\n";
		
		$option_output_rows .= '			</td>
		</tr>' . "\n";
		
		// Now build the containing panel and add the rows to it
		$this->_addPanel('version-checking-panel', TEXT_VERSION_CHECKING, null,
			$this->_buildConfigPanel('version-checking-panel', TEXT_VERSION_CHECKING, $option_output_rows));
	*/
	}
	
	// }}}
	
	
	// {{{ _buildInstallationCheckPanel()
	
	/**
	 * Builds a HTML panel which simply links to the installation check page and to the URI to let a user run the
	 * config check. Adds the panel to the list of panels.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildInstallationCheckPanel()
	{
		$panel = '<fieldset id="installation-check-panel" class="CeonPanel">
	<legend>';
		
		$panel .= TEXT_INSTALLATION_CHECK;
		
		$panel .= '</legend>' . "\n";
		
		$panel .= '<h2>' . TEXT_INSTALLATION_CHECK . '</h2>';
		
		$panel .= '<p>' . TEXT_INSTALLATION_DESC . '</p>';
		
		$panel .= '<p><a href="' . zen_href_link(FILENAME_CEON_URI_MAPPING_INSTALLATION_CHECK, '', 'NONSSL') .
			'" target="_blank">' . TEXT_LINK_TO_INSTALLATION_CHECK . '</a></p>' . "\n";
		
		
		$panel .= '<h2 class="DoubleSpaceAbove">' . TEXT_CONFIG_CHECK . '</h2>';
		
		$panel .= '<p>' . TEXT_CONFIG_DESC . '</p>';
		
		$panel .= '<p><a href="' . zen_href_link(FILENAME_CEON_URI_MAPPING_CONFIG, 'check-config=1', 'NONSSL') .
			'" target="_blank">' . TEXT_LINK_TO_CONFIG_CHECK . '</a></p>' . "\n";
		
		$panel .= '</fieldset>' . "\n";
		
		$this->_addPanel('installation-check-panel', TEXT_INSTALLATION_CHECK, null, $panel);
	}
	
	// }}}
}

// }}}
