<?php
//steve added a clause to the canonical generation
/**
 * Ceon URI Mapping URI Handler Base Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingHandlerBase.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingDBLookup.php');


// {{{ CeonURIMappingHandlerBase

/**
 * Base handler class which provides shared functionality for Ceon URI Mapping's URI handling functionality.
 * Subclasses must provide an implementation of the functionality necessary to match the current URI to a Zen Cart
 * page, and to configure the environment appropriately so that Zen Cart isn't even aware when a standard, dynamic
 * Zen Cart URI isn't being used.
 *
 * Subclasses can override the URI parsing functionality, e.g. for speed benefits with a particular server
 * configuration.
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
class CeonURIMappingHandlerBase extends CeonURIMappingDBLookup
{
	// {{{ properties
	
	/**
	 * Flag maintains the status of the all-important request_uri environment variable.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_request_uri_value_identified = null;
	
	/**
	 * The URI being used to access the current page.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_request_uri = null;
	
	/**
	 * The query parameters string for the current page.
	 *
	 * @var     string
	 * @access  protected
	 */
	var $_query_string = null;
	
	/**
	 * Flag to say whether the language is currently being changed by the user.
	 *
	 * @var     boolean
	 * @access  protected
	 */
	var $_language_changed = false;
	
	/**
	 * The original language ID this page was run with.
	 *
	 * @var     integer
	 * @access  protected
	 */
	var $_original_language_id = null;
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingBase class. Not intended to be used directly as this is an
	 * abstract class.
	 * 
	 * @access  public
	 */
	function __construct()
	{
		global $PHP_SELF, $messageStack;
		
		// The rewrite rule can sometimes interfere with the value of an important server variable.
		// Fix any problems
		if (!$this->_normaliseServerEnvironment()) {
			// Couldn't establish a reasonable value for PHP_SELF
		}
		
		if (!defined('CEON_URI_MAPPING_ENABLED') || CEON_URI_MAPPING_ENABLED == 0) {
			// Module either isn't installed or isn't enabled
			return;
		}
		
		// Ceon URI Mapping should only run when Zen Cart itself is responsible for runtime execution. If this code
		// is running because some other software has called the Zen Cart initsystem then don't attempt to map the
		// URI
		if ($PHP_SELF != (DIR_WS_CATALOG . 'index.php')) {
			return;
		}
		
		// If the check to make sure that a value exists for the request URI variable has not yet been run, run it
		// now
		if (is_null($this->_request_uri_value_identified)) {
			$this->_request_uri_value_identified = $this->_checkRequestURI();
		}
		
		if (!$this->_request_uri_value_identified) {
			// Essential information isn't available, can't proceed with attempt to map URIs, warn user!
			$messageStack->add(
				'Ceon URI Mapping cannot find a usable value for the REQUEST_URI server variable!', 'error');
			
			return;
		}
		
		$this->_parseURI();
		
		// Run the language initsystem code here as, for some strange reason, it runs late in the initsystem
		// process and the Ceon URI Mapping module must run before the sanitize initsystem script which normally
		// preceeds the language initsystem code.
		$this->_initLanguageSystem();
		
		$current_uri_is_index_page = $this->_checkForAndHandleIndexPage();
		
		if (!$current_uri_is_index_page) {
			$this->_handleURI();
		}
	}
	
	// }}}
	
	
	// {{{ _normaliseServerEnvironment()
	
	/**
	 * Makes sure that the PHP_SELF server environment variable has a reasonable value.
	 *
	 * @access  protected
	 * @return  boolean   True if PHP_SELF environment variable has a reasonable value, false otherwise.
	 */
	function _normaliseServerEnvironment()
	{
		global $PHP_SELF;
		
		if ($PHP_SELF == '/') {
			$PHP_SELF = '/index.php';
		} else if (strtolower($PHP_SELF) == strtolower(DIR_WS_CATALOG)) {
			$PHP_SELF = DIR_WS_CATALOG . 'index.php';
		} else if (empty($PHP_SELF)) {
			// Fall back to building the value from the current URI. Must ensure that the value of REQUEST_URI is
			// populated first. Result is stored to avoid repeating the check later
			$this->_request_uri_value_identified = $this->_checkRequestURI();
			
			if (!$this->_request_uri_value_identified) {
				return false;
			}
			
			$PHP_SELF = preg_replace('/(\?.*)?$/', '', $_SERVER['REQUEST_URI']);
			
		} else if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($PHP_SELF, $_SERVER['DOCUMENT_ROOT']) !== false) {
			// Server has incorrectly built the full path to the file for the value of PHP_SELF, must correct it by
			// removing the root path part
			$PHP_SELF = str_replace($_SERVER['DOCUMENT_ROOT'], '', $PHP_SELF);
		}
		
		if ($PHP_SELF != (DIR_WS_CATALOG . 'index.php')) {
			// Manually build the variable
			if (isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['DOCUMENT_ROOT'])) {
				$script_name_orig_len = strlen($_SERVER['SCRIPT_FILENAME']);
				
				$script_name = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']);
				
				// Convert any backslashes
				$script_name = str_replace('\\', '/', $script_name);
				
				if (strlen($script_name) < $script_name_orig_len) {
					// Script is in a subdirectory of the document root, assume this is the correct name for the
					// script
					$PHP_SELF = $script_name;
					
					// Make sure starting directory slash wasn't removed
					if (substr($PHP_SELF, 0, 1) != '/') {
						$PHP_SELF = '/' . $PHP_SELF;
					}
				}
			} else {
				return false;
			}
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _checkRequestURI()
	
	/**
	 * Tries to find the value of the request URI server environment variable. Attempts to build a value if
	 * necessary. Some code based on wordpress load script.
	 *
	 * @access  protected
	 * @return  boolean   True if request URI environment variable is found/built, false otherwise.
	 */
	function _checkRequestURI()
	{
		if (!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = '';
		}
		
		if (!isset($_SERVER['SERVER_SOFTWARE'])) {
			$_SERVER['SERVER_SOFTWARE'] = '';
		}
		
		if (empty($_SERVER['REQUEST_URI']) || (@php_sapi_name() != 'cgi-fcgi' &&
				strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false)) {
			// Necessary variable is missing or could possibly have an incorrect value
			
			if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
				// IIS URL Rewrite module is being used, use the original URI it provides
				$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
				
			} else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
				// IIS ISAPI_rewrite module is being used, use the original URI it provides
				$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
				
			} else {
				// Attempt to build the variable manually
				
				// Use ORIG_PATH_INFO if there is no PATH_INFO
				if (!isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO'])) {
					$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
				}
				
				// Some IIS + PHP configurations put the script-name in the path-info (No need to append it twice)
				if (isset($_SERVER['PATH_INFO'])) {
					if ($_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME']) {
						$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
					} else {
						$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
					}
				}
				
				// Append the query string if it exists and isn't null
				if (!empty($_SERVER['REQUEST_URI']) && !empty($_SERVER['QUERY_STRING'])) {
					$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
				}
			}
		}
		
		if (empty($_SERVER['REQUEST_URI'])) {
			// Couldn't find or build the REQUEST_URI variable's value
			return false;
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _parseURI()
	
	/**
	 * Parses the current URI into the main URI part and the query string parameters. Also removes any session ID
	 * found in the query string parameters, as it certainly won't be matched against in the database!
	 *
	 * @access  protected
	 * @return  none
	 */
	function _parseURI()
	{
		$this->_request_uri = $_SERVER['REQUEST_URI'];
		
		// Remove and store any query string
		$query_pos = strpos($this->_request_uri, '?');
		
		if ($query_pos !== false) {
			$this->_query_string =
				substr($this->_request_uri, $query_pos + 1, strlen($this->_request_uri) - $query_pos - 1);
			
			$this->_request_uri = substr($this->_request_uri, 0, $query_pos);
			
			if (isset($_GET[zen_session_name()])) {
				// Remove any session ID from the query string 
				$session_param_pattern = '/&?' . zen_session_name() . '=[^&]+/i';
				
				$this->_query_string = preg_replace($session_param_pattern, '', $this->_query_string);
			}
		}
	}
	
	// }}}
	
	
	// {{{ _initLanguageSystem()
	
	/**
	 * Works out the language currently being used and makes a note of any change in the language between this page
	 * load and the previous page load.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _initLanguageSystem()
	{
		if (isset($_SESSION['languages_id'])) {
			$this->_original_language_id = $_SESSION['languages_id'];
		}
		
		if (!isset($_SESSION['language']) || isset($_GET['language'])) {
			
			$lng = new language();
			
			if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
				// The user has selected a different language
				$lng->set_language($_GET['language']);
				
				$this->_language_changed = true;
				
				unset($_GET['language']);
				
			} else if (LANGUAGE_DEFAULT_SELECTOR == 'Browser') {
				$lng->get_browser_language();
			} else {
				$lng->set_language(DEFAULT_LANGUAGE);
			}
			
			$_SESSION['language'] =
				(zen_not_null($lng->language['directory']) ? $lng->language['directory'] : 'english');
			
			$_SESSION['languages_id'] = (zen_not_null($lng->language['id']) ? $lng->language['id'] : 1);
			
			$_SESSION['languages_code'] = (zen_not_null($lng->language['code']) ? $lng->language['code'] : 'en');
		}
		
		if ($this->_language_changed && $this->_original_language_id == $_SESSION['languages_id']) {
			// The language selected by the user is the one they were already using!
			$this->_language_changed = false;
		}
		
		if (is_null($this->_original_language_id)) {
			$this->_original_language_id = $_SESSION['languages_id'];
		}
	}
	
	// }}}
	
	
	// {{{ _checkForAndHandleIndexPage()
	
	/**
	 * Checks if the current URI is the store's index page. If so, makes sure that it uses the standard URI for the
	 * index page, redirecting to the standard URI if it doesn't. If this is the correct URI for the index page,
	 * the environment is configured appropriately.
	 *
	 * @access  protected
	 * @return  boolean   Whether or not the current URI is the index page.
	 */
	function _checkForAndHandleIndexPage()
	{
		$request_uri = strtolower($this->_request_uri);
		
		// Handle variations of the index page URI
		if ((isset($_GET['main_page']) &&
				(strlen($_GET['main_page']) == 0 || $_GET['main_page'] == FILENAME_DEFAULT) &&
				!isset($_GET['cPath']) && !isset($_GET['manufacturers_id']) && !isset($_GET['typefilter'])) ||
				(!isset($_GET['main_page']) && $request_uri == strtolower(DIR_WS_CATALOG) . 'index.php')) {
			// URI is DIR_WS_CATALOG/index.php?main_page=index, DIR_WS_CATALOG/index.php or
			// DIR_WS_CATALOG/index.php?main_page=
			
			// Redirect to DIR_WS_CATALOG
			$redirection_uri = DIR_WS_CATALOG;
			
			if (isset($_GET['main_page'])) {
				unset($_GET['main_page']);
			}
			
			$redirection_uri .= $this->_buildQueryString();
			
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: " . $redirection_uri);
			
			zen_exit();
			
		} else if (!isset($_GET['main_page']) && $request_uri == strtolower(DIR_WS_CATALOG) ||
				($request_uri . '/') == strtolower(DIR_WS_CATALOG)) {
			$_GET['main_page'] = FILENAME_DEFAULT;
			
			if (!isset($_GET['cPath']) && !isset($_GET['manufacturers_id']) && !isset($_GET['typefilter'])) {
				// Make this URI the canonical URI for the page
				global $ceon_uri_mapping_canonical_uri;
				
				$ceon_uri_mapping_canonical_uri = DIR_WS_CATALOG;
				
				// Remove any trailing slash(es) from the canonical URI, unless the URI is the root of the site
				//steve added $this_is_home_page or it strips closing slash from index (which breaks facebook Open Graph Debugger for the homepage). $this_is_home_page sometimes not set.

                while ( (isset($this_is_home_page) && $this_is_home_page) && strlen($ceon_uri_mapping_canonical_uri) > 1 &&
					   substr($ceon_uri_mapping_canonical_uri, -1) == '/') {
					$ceon_uri_mapping_canonical_uri =
						substr($ceon_uri_mapping_canonical_uri, 0, strlen($ceon_uri_mapping_canonical_uri) - 1);
				}
				
				$ceon_uri_mapping_canonical_uri = HTTP_SERVER . $ceon_uri_mapping_canonical_uri;
				
				// The index page is being used
				return true;
			}
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ _handleURI()
	
	/**
	 * Main dispatch method, determines what type of URI is being used and dispatches to the appropriate handler
	 * method.
	 *
	 * @access  protected
	 * @return  none
	 */
	function _handleURI()
	{
		if (!isset($_GET['main_page'])) {
			// Standard Zen Cart URI isn't being used, attempt to identify and map URI
			$this->_handleStaticURI();
			
		} else if (isset($_GET['main_page']) && isset($_SESSION['ceon_uri_mapping_redirected'])) {
			// Ceon URI Mapping redirected the customer here so there's no need for any further checks
			unset($_SESSION['ceon_uri_mapping_redirected']);
			
		} else if (isset($_GET['main_page']) && !isset($_GET['action']) && sizeof($_POST) == 0 &&
				!($_GET['main_page'] == FILENAME_DEFAULT && !isset($_GET['cPath']) &&
				!isset($_GET['manufacturers_id']) &&
				(!isset($_GET['typefilter']) || $_GET['typefilter'] == '') &&
				(isset($_GET['typefilter']) && (!isset($_GET[$_GET['typefilter'] . '_id']) ||
				$_GET[$_GET['typefilter'] . '_id'] == '')))) {
			// This is a standard Zen Cart dynamic URI - redirect it to its mapped URI if there is one (but not if
			// a form POST is taking place, as indicated by the action parameter being present and/or the POST
			// variable having values)
			$this->_handleZCDynamicURI();
		}
	}
	
	// }}}
	
	
	// {{{ _handleStaticURI()
	
	/**
	 * Attempts to match the current URI against a static URI in the Ceon URI Mapping database.
	 *
	 * @abstract
	 * @access  protected
	 * @return  none
	 */
	function _handleStaticURI() {}
	
	// }}}
	
	
	// {{{ _handleZCDynamicURI()
	
	/**
	 * Attempts to find and redirect to a static URI for the current, dynamic URI. If none is found, Zen Cart can
	 * proceed as normal.
	 *
	 * @abstract
	 * @access  protected
	 * @return  none
	 */
	function _handleZCDynamicURI() {}
	
	// }}}
	
	
	// {{{ _handleHistoricalURIWithNoCurrentMapping()
	
	/**
	 * Redirects to a standard Zen Cart dynamic URI, building the URI from the parameters passed.
	 *
	 * @abstract
	 * @access  protected
	 * @param   string    $main_page                 The name of the Zen Cart page for the URI.
	 * @param   integer   $associated_db_id          The associated database ID for the URI.
	 * @param   integer   $query_string_parameters   The query string parameters for the URI.
	 * @return  none
	 */
	function _handleHistoricalURIWithNoCurrentMapping($main_page, $associated_db_id, $query_string_parameters) {}
	
	// }}}
	
	
	// {{{ _handleUnmappedURI()
	
	/**
	 * Handles a Zen Cart dynamic URI which has no URI mapping.
	 *
	 * @abstract
	 * @access  protected
	 * @param   string    $main_page                 The name of the Zen Cart page for the URI.
	 * @param   integer   $associated_db_id          The associated database ID for the URI.
	 * @param   integer   $query_string_parameters   The query string parameters for the URI.
	 * @return  none
	 */
	function _handleUnmappedURI($main_page, $associated_db_id, $query_string_parameters) {}
	
	// }}}
	
	
	// {{{ _getCurrentURI()
	
	/**
	 * Attempts to find the current URI for the specified language in the Ceon URI Mapping database.
	 *
	 * @access  protected
	 * @param   string    $main_page                 The name of the Zen Cart page for the URI.
	 * @param   integer   $associated_db_id          The associated database ID for the URI.
	 * @param   integer   $query_string_parameters   The query string parameter string for the URI.
	 * @param   integer   $language_id               The ID of the language.
	 * @return  string|false   The current URI for the specified language.
	 */
	function _getCurrentURI($main_page, $associated_db_id, $query_string_parameters, $language_id)
	{
		global $db;
		
		$columns_to_retrieve = array(
			'uri'
			);
		
		if (!is_null($associated_db_id)) {
			$selections = array(
				'main_page' => zen_db_prepare_input($main_page),
				'associated_db_id' => (int) $associated_db_id,
				'language_id' => (int) $language_id,
				'current_uri' => 1
				);
				
		} else if (!is_null($query_string_parameters)) {
			$selections = array(
				'main_page' => zen_db_prepare_input($main_page),
				'query_string_parameters' => zen_db_prepare_input($query_string_parameters),
				'language_id' => (int) $language_id,
				'current_uri' => 1
				);
			
		} else {
			$selections = array(
				'main_page' => zen_db_prepare_input($main_page),
				'associated_db_id' => 'null',
				'query_string_parameters' => 'null',
				'language_id' => (int) $language_id,
				'current_uri' => 1
				);
		}
		
		$current_uri_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);
		
		if (!$current_uri_result->EOF) {
			return $current_uri_result->fields['uri'];
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ checkLanguageChangedCurrencyChangeRequired()
	
	/**
	 * Checks if the currency needs to be changed as the language has just been changed and the store is set to use
	 * a language's default currency. If so, attempts to switch to the language's default currency. This is a
	 * public method as it must be called after the languages system has run, which is not until well after Ceon
	 * URI Mapping's handler runs.
	 *
	 * @access  public
	 * @return  none
	 */
	function checkLanguageChangedCurrencyChangeRequired()
	{
		if (isset($_SESSION['ceon_uri_mapping_language_changed'])) {
			$this->_language_changed = $_SESSION['ceon_uri_mapping_language_changed'];
			
			unset($_SESSION['ceon_uri_mapping_language_changed']);
		}
		
		if ($this->_language_changed && USE_DEFAULT_LANGUAGE_CURRENCY == 'true' &&
				LANGUAGE_CURRENCY != $_SESSION['currency']) {
			$_SESSION['currency'] = zen_currency_exists(LANGUAGE_CURRENCY);
		}
	}
	
	// }}}
	
	
	// {{{ _buildQueryString()
	
	/**
	 * Builds the query string for the current query parameters. Can handle arrays and multi-dimensional arrays!
	 *
	 * @access  protected
	 * @return  string    A URL encoded query parameters string.
	 */
	function _buildQueryString()
	{
		$query_string = '';
		
		foreach ($_GET as $key => $value) {
			if (is_array($value)) {			
				$query_string .= $this->_buildArrayQueryParameter($key, $value);
			} else {
				$query_string .= '&' . urlencode($key) . '=' . urlencode($value);
			}
		}
		
		if ($query_string != '') {
			$query_string = '?' . substr($query_string, 1, strlen($query_string) - 1);
		}
		
		return $query_string;
	}
	
	// }}}
	
	
	// {{{ _buildArrayQueryParameter()
	
	/**
	 * Builds the URL encoded string for an array query parameter.
	 *
	 * @access  protected
	 * @param   string    $key     The key for the query string parameter.
	 * @param   string    $value   The value for the query string parameter.
	 * @return  string    A URL encoded string representation of the query parameter.
	 */
	function _buildArrayQueryParameter($key, $value)
	{
		$parameter_string = '';
		
		$key .= '[]';
		
		foreach ($value as $subvalue) {
			if (is_array($subvalue)) {
				$parameter_string .= $this->_buildArrayQueryParameter($key, $subvalue);
			} else {
				$parameter_string .= '&' . urlencode($key) . '=' . urlencode($subvalue);
			}
		}
		
		return $parameter_string;
	}
	
	// }}}
}

// }}}
