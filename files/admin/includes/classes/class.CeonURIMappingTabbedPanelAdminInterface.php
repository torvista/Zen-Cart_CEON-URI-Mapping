<?php

/**steve don't check for updates
 * Ceon Tabbed Panel Admin Interface Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingTabbedPanelAdminInterface.php 1054 2012-09-22 15:45:15Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');


// {{{ CeonURIMappingTabbedPanelAdminInterface

/**
 * Provides shared functionality to allow the building of a set of tabbed panels as the basis of an admin interface
 * for a module within the main Zen Cart admin.
 *
 * @package     ceon_uri_mapping
 * @abstract
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingTabbedPanelAdminInterface extends CeonURIMappingAdmin
{
	// {{{ properties
	
	/**
	 * The Ceon base model code for the module this admin interface is being built for.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_ceon_base_model_code = null;
	
	/**
	 * The Ceon model edition code of the module this admin interface is being built for.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_ceon_model_edition_code = null;
	
	/**
	 * The version of the module this admin interface is being built for.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_version = null;
	
	/**
	 * The localised edition title of the module this admin interface is being built for.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_edition_title = null;
	
	/**
	 * The version of the module that is currently installed.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_installed_version = null;
	
	/**
	 * The start year of the copyright range for the module.
	 *
	 * @var     integer
	 * @access  protected
	 */
	var $_copyright_start_year = null;
	
	/**
	 * The URI part for the module's web address.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_web_address_uri_part = null;
	
	/**
	 * Whether or not automatic version checking is enabled.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_automatic_version_checking = null;
	
	/**
	 * The list of panels that this instance is to use.
	 *
	 * @var     array
	 * @access  protected
	 */
	var $_panels = array();
	
	/**
	 * The HTML output built by this instance.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_output = null;
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the class.
	 * 
	 * @param   boolean   Whether or not the autogeneration configuration should be loaded when
	 *                    instantiating the class.
	 * @access  public
	 */
	function __construct($load_config = true)
	{
		parent::__construct($load_config);
		
		// Load the language definition file for the current language
		@include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' .
			'ceon_uri_mapping_tabbed_panel_admin_interface.php');
		
		if (!defined('TEXT_ERROR_VERSION_CHECK_PROBLEM') && $_SESSION['language'] != 'english') {
			// Fall back to english language file
			@include_once(DIR_WS_LANGUAGES . 'english/' . 'ceon_uri_mapping_tabbed_panel_admin_interface.php');
		}
	}
	
	// }}}
	
	
	// {{{ _lookUpInstalledVersion()
	
	/**
	 * Looks up the currently installed version.
	 *
	 * @access  protected
	 * @abstract
	 * @return  boolean   True if the version number check completed without failure, false otherwise. The module
	 *                    not being installed yet is not counted as a failure.
	 */
	function _lookUpInstalledVersion() {}
	
	// }}}
	
	
	// {{{ getEditionCode()
	
	/**
	 * Simply returns the Ceon model edition code for the software.
	 *
	 * @access  public
	 * @return  string    The Ceon model edition code for this software.
	 */
	function getEditionCode()
	{
		return $this->_ceon_model_edition_code;
	}
	
	// }}}
	
	
	// {{{ getVersion()
	
	/**
	 * Simply returns the version of the software.
	 *
	 * @access  public
	 * @return  string    The version of this software.
	 */
	function getVersion()
	{
		return $this->_version;
	}
	
	// }}}
	
	
	// {{{ getOutput()
	
	/**
	 * Simply returns the output built by this instance.
	 *
	 * @access  public
	 * @return  string    The HTML output for the configuration utility's output.
	 */
	function getOutput()
	{
		return $this->_output;
	}
	
	// }}}
	
	
	// {{{ _addPanel()
	
	/**
	 * Adds a panel to the list of panels that are part of this tabbed panel admin interface.
	 *
	 * @access  protected
	 * @param   string    $id        The ID of the panel.
	 * @param   string    $title     The title of the panel, to be used as the tab's text.
	 * @param   string    $link      If the tab is to link to another page, the link.
	 * @param   string    $content   If the panel's content is to be displayed directly within the tabbed panel
	 *                               admin interface, the content that makes up the panel.
	 * @return  none
	 */
	function _addPanel($id, $title, $link, $content = null)
	{
		$this->_panels[] = array(
			'id' => $id,
			'title' => $title,
			'link' => $link,
			'content' => $content
			);
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
		return null;
	}
	
	// }}}
	
	
	// {{{ _buildTabbedPanelMenu()
	
	/**
	 * Builds the menu for the various panels, adding it to the output content. Implements a bar of tabs which can
	 * be used to show/hide the various panels or navigate between the various panels' pages. 
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildTabbedPanelMenu()
	{
		$selected_panel_id = $this->_getSelectedPanelID();
		
		// Have the panel selected and displayed automatically
		if (!is_null($selected_panel_id)) {
			$this->_output .= '<style type="text/css">' . "\n";
			$this->_output .= 'fieldset#' . $selected_panel_id . ' { display: block; }' . "\n";
			$this->_output .= '</style>' . "\n";
		}
		
		$this->_output .= <<<TABBED_PANELS_MENU_JS
<script type="text/javascript">
var current_panel_id = '$selected_panel_id';

function CeonShowPanel(id) {
	selected_class = 'CeonPanelTabSelected';
	
	// Hide previous panel
	prev_panel_el = document.getElementById(current_panel_id)
	prev_panel_el.style.display = 'none';
	
	new_panel_el = document.getElementById(id);
	new_panel_el.style.display = 'block';
	
	prev_panel_tab_el = document.getElementById(current_panel_id + '-tab');
	new_panel_tab_el = document.getElementById(id + '-tab');
	
	if (new_panel_tab_el.className.indexOf(selected_class) == -1) {
		new_panel_tab_el.className = new_panel_tab_el.className + " " + selected_class;
	}
	
	prev_panel_tab_el.className = prev_panel_tab_el.className.replace(selected_class, "");
	
	current_panel_id = id;
}
</script>
<noscript>
<style type="text/css">
fieldset.CeonPanel { display: block; }
</style>
</noscript>
TABBED_PANELS_MENU_JS;
		
		
		$this->_output .= '<ul id="ceon-panels-menu">' . "\n";
		
		foreach ($this->_panels as $panel) {
			if ($panel['id'] == $selected_panel_id) {
				$class = 'CeonPanelTabSelected';
			} else {
				$class = '';
			}
			
			$this->_output .= '<li id="' . $panel['id'] . '-tab" class="' . $class . '">';
			
			if (is_null($panel['link'])) {
				$this->_output .= '<a href="javascript:CeonShowPanel(' . "'" . $panel['id'] . "'" . ');">';
			} else {
				$this->_output .= '<a href="' . htmlspecialchars($panel['link']) . '">';
			}
			
			$this->_output .= str_replace(' ', '&nbsp;', htmlentities($panel['title'])) . '</a>';
			
			$this->_output .= '</li>';
		}
		
		$this->_output .= '</ul>' . "\n";
	}
	
	// }}}
	
	
	// {{{ _buildPanels()
	
	/**
	 * Outputs any panels that have content.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildPanels()
	{
		$this->_output .= '<div id="ceon-panels-wrapper">' . "\n";
		
		foreach ($this->_panels as $panel) {
			if (!is_null($panel['content'])) {
				$this->_output .= $panel['content'];
			}
		}
		
		$this->_output .= '</div>' . "\n";
	}
	
	// }}}
	
	
	// {{{ _buildConfigSettingDescRow()
	
	/**
	 * Builds a HTML table row with a label and description.
	 *
	 * @access  protected
	 * @param   string    $id            The ID of the form element (if any) the label is for.
	 * @param   string    $label         The text for the label.
	 * @param   string    $description   The description.
	 * @return  string    The HTML source for the row.
	 */
	function _buildConfigSettingDescRow($id, $label, $description)
	{
		$table_row = '		<tr>
			<td rowspan="2" class="CeonFormItemLabel"><label>' . $label . '</label></td>' . "\n";
		
		$table_row .= '			<td class="CeonFormItemDesc">' . "\n";
		
		$table_row .= $description . "\n";
		
		$table_row .= '			</td>
		</tr>' . "\n";
		
		return $table_row;
	}
	
	// }}}
	
	
	// {{{ _buildConfigPanel()
	
	/**
	 * Builds a HTML panel using a fieldset and an enclosed table or content.
	 *
	 * @access  protected
	 * @param   string    $id           The ID for the panel.
	 * @param   string    $title        The title for the panel.
	 * @param   string    $table_rows   The content of the panel, as HTML rows.
	 * @param   string    $content      The content of the panel, as HTML.
	 * @return  string    The HTML source for the panel.
	 */
	function _buildConfigPanel($id, $title, $table_rows, $content = null)
	{
		$panel = '<fieldset id="' . $id . '" class="CeonPanel">
	<legend>';
		
		$panel .= $title;
		
		$panel .= '</legend>' . "\n";
		
		if (is_null($content)) {
			// The panel's content is built using table rows
			$panel .= '<table border="0" width="98%" cellpadding="0" cellspacing="0">' . "\n";
			
			$panel .= $table_rows;
			
			$panel .= '	</table>';
		} else {
			$panel .= $content;
		}
		
		$panel .= '</fieldset>' . "\n";
		
		return $panel;
	}
	
	// }}}
	
	
	// {{{ _buildSubPanel()
	
	/**
	 * Builds a HTML subpanel using a fieldset and HTML content.
	 *
	 * @access  protected
	 * @param   string    $title     The title for the subpanel.
	 * @param   string    $content   The content for the panel, as HTML.
	 * @return  string    The HTML source for the subpanel.
	 */
	function _buildSubPanel($title, $content)
	{
		$panel = '				<fieldset>
					<legend>';
		
		$panel .= $title;
		
		$panel .= '</legend>' . "\n";
		
		$panel .= $content;
		
		$panel .= '				</fieldset>' . "\n";
		
		return $panel;
	}
	
	// }}}
	
	
	// {{{ _buildSubmitAndCancelButtons()
	
	/**
	 * Builds the HTML for the submit and cancel buttons. Adds it to the output content.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildSubmitAndCancelButtons()
	{
		$buttons = '				<div class="SpacerSmall"></div>' . "\n";
		
		$buttons .= zen_image_submit('button_save.gif', IMAGE_SAVE, 'name="save" value="save" id="save"');
		
		$buttons .= '&nbsp;<a href="' . zen_href_link(FILENAME_CEON_URI_MAPPING_CONFIG, '', 'NONSSL') .'">' .
			zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . "\n";
		
		$this->_output .= $buttons;
	}
	
	// }}}
	
	
	// {{{ _buildFooter()
	
	/**
	 * Builds the HTML for the module copyright and version information. Adds it to the output content.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _buildFooter()
	{
		$footer = '				<div class="SpacerSmall"></div>' . "\n" . '<div id="footer">';
		
		$footer .= '<p><a href="http://ceon.net/software/business/zen-cart" target="_blank"><img src="';
		
		$footer .= DIR_WS_IMAGES;
		
		// Build the copyright year range
		$copyright_year_range = $this->_copyright_start_year;
		
		$end_year = (date('Y') > 2012 ? date('Y') : 2012);
		
		$copyright_year_range .= '-' . $end_year;
		
		$footer .= 'ceon-button-logo.png" alt="Ceon" id="ceon-button-logo" /></a>' .
			'Module &copy; Copyright ' . $copyright_year_range .
			' <a href="http://ceon.net/software/business/zen-cart" target="_blank">Ceon</a>' . "</p>\n";
		
		$footer .= '<p id="version-info">' . TEXT_FILES_VERSION . ': ' . $this->_version .
			(!is_null($this->_edition_title) ? ' ' . $this->_edition_title : '') . "\n";
		
		$footer .= '<br />' . TEXT_INSTALLED_VERSION . ': ' .
			(is_null($this->_installed_version) ? TEXT_NOT_INSTALLED : $this->_installed_version .
			(!is_null($this->_edition_title) ? ' ' . $this->_edition_title : ''));
		
		$footer .= "</p>\n";
		
//		$footer .= $this->_getVersionCheckerOutput();//steve don't check for updates
		
		$footer .= "</div>\n";
		
		$this->_output .= $footer;
	}
	
	// }}}
	
	
	// {{{ _getVersionCheckerOutput()
	
	/**
	 * If automatic version checking is enabled, connects to Ceon's version checker server to check if the current
	 * version is out of date. If the current version is out of date, information about the latest version and a
	 * link to download the update are returned. If an error is encountered, it is returned for display.
	 *
	 * If automatic checking is disabled, a link to check the version manually is returned.
	 * 
	 * @access  protected
	 * @return  string    The information about the latest version, an error message, or a link to manually check
	 *                    the version.
	 */
	function _getVersionCheckerOutput()
	{
		$output = '';
		
		$output_manual_link = false;
		
		if (isset($_GET['reset-version-checker-response']) &&
				isset($_SESSION[$this->_ceon_base_model_code . '_vc_response'])) {
			unset($_SESSION[$this->_ceon_base_model_code . '_vc_response']);
		}
		
		if (extension_loaded('curl') && (is_null($this->_automatic_version_checking) ||
				$this->_automatic_version_checking)) {
			// If a response has been recorded for this session, don't try again in this session
			if (isset($_SESSION[$this->_ceon_base_model_code . '_vc_response'])) {
				$version_checker_response = $_SESSION[$this->_ceon_base_model_code . '_vc_response'];
			} else {
				// Post the information needed for the version check to the Ceon version checker server
				
				// Build the data required for the version check
				$uri = 'https://version-checker.ceon.net/';
				
				$data = 'base_model_code=' . $this->_ceon_base_model_code;
				
				if (!is_null($this->_ceon_model_edition_code)) {
					$data .= '&model_edition_code=' . $this->_ceon_model_edition_code;
				}
				
				$data .= '&version=' . $this->_version;
				
				$data .= '&language_code=' . $_SESSION['languages_code'];
				
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $uri);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, '5');
				curl_setopt($ch, CURLOPT_REFERER, HTTP_SERVER);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
				$version_checker_response = curl_exec($ch);
				
				curl_close($ch);
			}
			
			// Parse information returned from version check and display appropriate output to user
			if (strlen($version_checker_response) == 0) {
				$output .= '<p class="Error"><strong>' . TEXT_ERROR_VERSION_CHECK_NO_RESPONSE . '</strong></p>';
				
				$output_manual_link = true;
				
			} else if ($version_checker_response == '1') {
				// Version is up to date
				$_SESSION[$this->_ceon_base_model_code . '_vc_response'] = $version_checker_response;
				
				$output .= '<p>';
				
				if (!is_null($this->_installed_version)) {
					$output .= TEXT_MOST_UP_TO_DATE_VERSION_INSTALLED;
				} else {
					$output .= TEXT_MOST_UP_TO_DATE_VERSION_FILES_PRESENT;
				}
				
				$output .= '</p>';
				
			} else if (substr($version_checker_response, 0, 1) == '-') {
				// Error occurred looking up version number
				$_SESSION[$this->_ceon_base_model_code . '_vc_response'] = $version_checker_response;
				
				$output .= '<p class="Error"><strong>' . TEXT_ERROR_VERSION_CHECK_PROBLEM . '</strong></p>';
				
				$output .= '<p style="margin-top: 0.4em;">' . substr($version_checker_response, 3) . '</p>';
				
				$output_manual_link = true;
				
			} else if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+[^,]*,/', $version_checker_response)) {
				// Version is out of date, display latest version info and download link
				$_SESSION[$this->_ceon_base_model_code . '_vc_response'] = $version_checker_response;
				
				$latest_version_info = explode(',', $version_checker_response);
				
				$output .=
					'<p class="Error">' . '<strong style="color: red">' . TEXT_OUT_OF_DATE . '</strong>' . '</p>';
				
				$output .= '<p style="margin-top: 0.4em">' . TEXT_LATEST_VERSION_IS . ' <strong>' .
					$latest_version_info[0] .
					(!is_null($this->_edition_title) ? ' ' . $this->_edition_title : '') . '</strong></p>';
				
				if (isset($latest_version_info[2])) {
					// Display any additional message provided by the version checker server
					$output .= '<p><strong>' . $latest_version_info[2] . '</strong></p>';
				}
				
				$output .= '<p>' . '<a href="' . $latest_version_info[1] . '" target="_blank">' .
					TEXT_CLICK_HERE_TO_DOWNLOAD_LATEST_VERSION . '</a></p>';
			} else {
				$output .= '<p class="Error"><strong>' . TEXT_ERROR_VERSION_CHECK_PROBLEM_PARSING .
					'</strong></p>';
				
				// Output the response received as a HTML comment so that the error can be possibly be identified
				$output .= "<!--\n" . $version_checker_response . "\n-->\n";
				
				$output_manual_link = true;
			}
		} else {
			$output_manual_link = true;
		}
		
		if ($output_manual_link) {
			// Can't autosubmit the version check form, user must use manual link
			$output .= '<p><a href="' . 'http://ceon.net/software/business/zen-cart/' .
				$this->_web_address_uri_part . '/version-checker/' . $this->_version . '" target="_blank">' .
				TEXT_CHECK_FOR_UPDATES . '</a></p>';
		}
		
		return $output;
	}
	
	// }}}
}

// }}}
