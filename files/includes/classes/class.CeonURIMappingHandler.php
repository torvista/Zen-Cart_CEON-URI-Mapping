<?php

/**
 * Ceon URI Mapping URI Handler Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingHandler.php 1054 2024-01-05 15:45:15Z conor updated 5.1.1$
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingHandlerBase.php');


// {{{ CeonURIMappingHandler

/**
 * Standard version of Ceon URI Mapping URI Handler class which provides maximum compatibility and flexibility when
 * mapping the current URI to a Zen Cart page.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingHandler extends CeonURIMappingHandlerBase
{
	// {{{ Class Constructor

	/**
	 * Creates a new instance of the CeonURIMappingHandler class. Checks the server and module's configuration,
	 * then attempts to map the current URI to the appropriate Zen Cart page.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// }}}


	// {{{ _handleStaticURI()

	/**
	 * Attempts to match the current URI against a static URI in the Ceon URI Mapping database.
	 *
	 * @access  protected
	 * @return  void
	 * @author  Conor Kerr <zen-cart.uri-mapping@ceon.net>
	 */
	protected function _handleStaticURI(): void
    {
		global $db;

		// Make sure that request URI has no invalid characters in it
        //TODO IDE reports redundant escape character
		$uri_to_match = preg_replace('/[^a-zA-Z0-9_\-\.\/%]/', '', $this->_request_uri);

		// Remove any trailing slashes
		while (str_ends_with($uri_to_match, '/')) {
			$uri_to_match = substr($uri_to_match, 0, strlen($uri_to_match) -1);
		}
        //TODO unused variable?
		$match_criteria_sql = "um.uri = '" . zen_db_prepare_input($uri_to_match) . "'";

		$columns_to_retrieve = [
			'language_id',
			'main_page',
			'query_string_parameters',
			'associated_db_id',
			'alternate_uri',
			'current_uri',
			'redirection_type_code'
        ];

		$selections = [
			'uri' => zen_db_prepare_input($uri_to_match)
        ];

		$order_by = '
			current_uri DESC,
			language_id,
			date_added DESC';

		$match_uri_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by);

		if ($match_uri_result->EOF) {
			// URI couldn't be mapped, should the index page be shown or the 404 page?
			if (MISSING_PAGE_CHECK == 'On' || MISSING_PAGE_CHECK == 'true') {
				$_GET['main_page'] = 'index';
			} elseif (MISSING_PAGE_CHECK === 'Page Not Found') {
				header('HTTP/1.1 404 Not Found');
				$_GET['main_page'] = 'page_not_found';
			}

			return;
		}

		// Have matched the URI!

		// Use the current mapping that specifically maps the language ID originally being used when this URI was
		// accessed, otherwise simply use the most recent current mapping, falling back to the most recent mapping
		// for the original language ID and, as a last resort, the most recent mapping for any ID
		$current_and_original_language_mapping = null;
		$current_mappings = [];
		$original_language_mappings = [];
		$other_mappings = [];

		while (!$match_uri_result->EOF) {
			$current_mapping_info = $match_uri_result->fields;

			if ($current_mapping_info['current_uri'] == 1 &&
					$current_mapping_info['language_id'] == $this->_original_language_id) {
				// Optimal mapping found
				$current_and_original_language_mapping = $current_mapping_info;
				break;
			}

			if ($current_mapping_info['current_uri'] == 1) {
				$current_mappings[] = $current_mapping_info;
			} elseif ($current_mapping_info['language_id'] == $this->_original_language_id) {
				$original_language_mappings[] = $current_mapping_info;
			} else {
				$other_mappings[] = $current_mapping_info;
			}

			$match_uri_result->MoveNext();
		}

		// Use the details for the best possible match for the URI (criteria as above)
		if (!is_null($current_and_original_language_mapping)) {
			$mapping_info = $current_and_original_language_mapping;
		} elseif (sizeof($current_mappings) > 0) {
			$mapping_info = $current_mappings[0];
		} elseif (sizeof($original_language_mappings) > 0) {
			$mapping_info = $original_language_mappings[0];
		} else {
			$mapping_info = $other_mappings[0];
		}

		$language_id = (int)$mapping_info['language_id'];
		$main_page = $mapping_info['main_page'];
		$query_string_parameters = $mapping_info['query_string_parameters'];
		$associated_db_id = is_null($mapping_info['associated_db_id']) ? null : (int)$mapping_info['associated_db_id'];
		$alternate_uri = $mapping_info['alternate_uri'];
		$current_uri = $mapping_info['current_uri'];
		$redirection_type_code = $mapping_info['redirection_type_code'];

		if (!is_null($alternate_uri)) {
			$alternate_uri = trim($alternate_uri);

			if (strlen($alternate_uri) == 0) {
				$alternate_uri = null;
			}
		}

		if (!is_null($alternate_uri)) {
			// Alternative URI is a full URI to be redirected to

			// Assume that a form would never be posted to an alternative URI, so don't perform POST check
			switch ($redirection_type_code) {
				case 301:
					header('HTTP/1.1 301 Moved Permanently');
					break;
				case 302:
					header('HTTP/1.1 302 Found');
					break;
				case 303:
					header('HTTP/1.1 303 See Other');
					break;
				case 307:
					header('HTTP/1.1 307 Temporary Redirect');
					break;
			}

			header('Location: ' . $alternate_uri);

			zen_exit();

		} elseif (is_null($main_page)) {
			// No value found for main page, this is a corrupted URI mapping!
			header('HTTP/1.1 404 Not Found');

			$_GET['main_page'] = 'page_not_found';

			// Add an error message to the message stack session variable as the message stack
			// itself isn't available at this point in the initsystem
			if (!isset($_SESSION['messageToStack']) || !is_array($_SESSION['messageToStack'])) {
				$_SESSION['messageToStack'] = [];
			}

			$_SESSION['messageToStack'][] = [
				'class' => 'heading',
				'text' => 'Unable to map identified URI: ' . $this->_request_uri,
				'type' => 'error'
            ];

			return;
		}

		// Have matched a Zen Cart page to initialise

		// Is the URI a current URI or should an attempt be made to find the current URI to redirect to? Or has the
		// user just changed the language so an attempt should be made to find the current URI for the new
		// language?
		// Don't perform redirect checks if a form is being posted, as any redirect would break the post
		if (count($_POST) == 0 && ($current_uri != 1 || $this->_language_changed)) {
			// Attempt to find the current URI mapping for the user's chosen language
			$redirection_check_language_id = $_SESSION['languages_id'];

			$redirection_uri = $this->_getCurrentURI($main_page, $associated_db_id, $query_string_parameters, (int)$redirection_check_language_id);

			if ($redirection_uri === false) {
				// Didn't find a current URI mapping for the user's chosen language

				// If the URI mapping identified so far is a historical mapping which uses a different language,
				// try to find the current URI mapping for that language
				if ($current_uri != 1 && $language_id != $_SESSION['languages_id']) {
					$redirection_check_language_id = $language_id;

					$redirection_uri = $this->_getCurrentURI($main_page, $associated_db_id,	$query_string_parameters, (int)$redirection_check_language_id);

					if (!$redirection_uri === false) {
						// Didn't find a current URI mapping for the language the identified mapping is using

					} else {
						// If using a different language from the session (and the user hasn't just selected a new
						// language), must use the new language
						if ($language_id != $_SESSION['languages_id'] && !$this->_language_changed) {
							$language_info_query = "
								SELECT
									directory,
									code
								FROM
									" . TABLE_LANGUAGES . " 
								WHERE
									languages_id = '" . (int) $language_id . "';";

							$language_info_result = $db->Execute($language_info_query);

							$_SESSION['language'] = $language_info_result->fields['directory'];
							$_SESSION['languages_id'] = $language_id;
							$_SESSION['languages_code'] = $language_info_result->fields['code'];

							// Redirecting to a new URI, must record that the language is being changed so that the
							// correct currency can be used
							$_SESSION['ceon_uri_mapping_language_changed'] = true;
						}
					}
				}
			}

			if ($current_uri == 1 && $this->_language_changed) {
				// Must record that the language is being changed so that the correct currency can  be used
				$_SESSION['ceon_uri_mapping_language_changed'] = true;
			}

			// Don't redirect to another URI if the URI is the same but just for a different language
			if ($this->_language_changed && $redirection_uri == $uri_to_match) {
				$redirection_uri = false;
			}

			if ($redirection_uri !== false) {
				// Have found a current URI mapping for this page, force a redirect to it

				// Include any extra info in the GET variables
				if (!is_null($associated_db_id) || is_null($query_string_parameters)) {
					$query_string = $this->_buildQueryString();

				} elseif (!is_null($query_string_parameters)) {
					// Query string variables will be rebuilt after redirecting so no need to append them
					$query_string = '';
				}

				$redirection_uri .= $query_string;

				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $redirection_uri);

				zen_exit();

			} elseif (!$this->_language_changed) {
				// There is no current URI for this language, this is a URI from the historical database, being
				// used to prevent broken links
				$this->_handleHistoricalURIWithNoCurrentMapping($main_page, $associated_db_id,
					$query_string_parameters);
			}
		} elseif ($language_id != $_SESSION['languages_id']) {
			// The user hasn't just selected a new language, but the URI uses a different language from the
			// session, so must switch the session to use the new language
			$language_info_query = "
				SELECT
					directory,
					code
				FROM
					" . TABLE_LANGUAGES . " 
				WHERE
					languages_id = '" . (int) $language_id . "';";

			$language_info_result = $db->Execute($language_info_query);

			$_SESSION['language'] = $language_info_result->fields['directory'];
			$_SESSION['languages_id'] = $language_id;
			$_SESSION['languages_code'] = $language_info_result->fields['code'];

			$this->_language_changed = true;
		}

		// This URI is the current URI for this page. Set up the Zen Cart environment for the page
		$_GET['main_page'] = $main_page;

		if (!is_null($associated_db_id)) {
			// Page is a category, EZ-Page or a product info page, must initialise the appropriate ID
			if ($main_page === FILENAME_DEFAULT) {
				// This is a category page
				// Must build the full contextual link to the category, so any hierarchy is respected
				$categories = [];

				zen_get_parent_categories($categories, $associated_db_id);

				$categories = array_reverse($categories);

				$cPath = implode('_', $categories);

				if (zen_not_null($cPath)) {
					$cPath .= '_';
				}

				$cPath .= $associated_db_id;

				$_GET['cPath'] = $cPath;

			} elseif ($main_page === FILENAME_EZPAGES || $main_page === FILENAME_EZPAGES_POPUP) {
				// Have found an EZ-Page. Should it be displayed as a normal page or in a popup?
				if ($main_page == FILENAME_EZPAGES_POPUP || isset($_GET['ezpopup'])) {
					$_GET['main_page'] = FILENAME_EZPAGES_POPUP;
				} else {
					$_GET['main_page'] = FILENAME_EZPAGES;
				}

				$_GET['id'] = $associated_db_id;

			} elseif ($main_page == FILENAME_ASK_A_QUESTION) {
				// Have found an Ask A Question Page.
				if (isset($_GET['pid']) && str_contains($_GET['pid'], ':')) {
					// Query string parameter includes information about selected attributes (which is added when
					// linking from shopping cart to a product info page) so don't override the parameter's value

				} else {
					$_GET['pid'] = $associated_db_id;
				}
				// Rebuild the cPath variable for this page if it doesn't exist
				if (!isset($_GET['cPath'])) {
					$_GET['cPath'] = zen_get_product_path($_GET['pid']);
				}

			} else {
				// This is a product related page
				if (isset($_GET['products_id']) && str_contains($_GET['products_id'], ':')) {
					// Query string parameter includes information about selected attributes (which is added when
					// linking from shopping cart to a product info page) so don't override the parameter's value

				} else {
					$_GET['products_id'] = $associated_db_id;
				}

				// Rebuild the cPath variable for this page if it doesn't exist
				if (!isset($_GET['cPath'])) {
					$_GET['cPath'] = zen_get_product_path($_GET['products_id']);
				}
			}
		} elseif (!is_null($query_string_parameters)) {
			// Page is a Zen Cart page with preset parameters, must initialise the parameters
			$query_string_pairs = explode('&', $query_string_parameters);

			foreach ($query_string_pairs as $query_string_pair) {
				$parameter_parts = explode('=', $query_string_pair);

				// Parameter from database overrides any in query string
				if (count($parameter_parts) == 2) {
					$_GET[$parameter_parts[0]] = urldecode($parameter_parts[1]);
				}
			}
		}

		// Make this URI the canonical URI for the page
		global $ceon_uri_mapping_canonical_uri;

		$ceon_uri_mapping_canonical_uri = HTTP_SERVER . $uri_to_match;

		// A product review's page needs the ID included as part of the canonical URI
		if (defined('FILENAME_PRODUCT_REVIEWS_INFO') && $main_page == FILENAME_PRODUCT_REVIEWS_INFO &&
				isset($_GET['reviews_id'])) {
			$ceon_uri_mapping_canonical_uri .= '?reviews_id=' . (int) $_GET['reviews_id'];
		}
		if (isset($GLOBALS['zco_notifier'])) {
			$GLOBALS['zco_notifier']->notify('CEON_CLASS_HANDLER_HANDLE_STATIC_URI_END', compact('mapping_info', 'uri_to_match'));
		}
	}

	// }}}


	// {{{ _handleZCDynamicURI()

	/**
	 * Attempts to find and redirect to a static URI for the current, dynamic URI. If none is found, Zen Cart can
	 * proceed as normal.
	 *
	 * @access  protected
	 * @author  Conor Kerr <zen-cart.uri-mapping@ceon.net>
	 * @return  void
	 */
	protected function _handleZCDynamicURI(): void
    {
		global $ceon_uri_mapping_product_pages, $ceon_uri_mapping_product_related_pages;

		$associated_db_id = null;
		$query_string_to_match = null;

		if ($_GET['main_page'] == FILENAME_DEFAULT && (isset($_GET['cPath']) || isset($_GET['manufacturers_id']) ||
				(isset($_GET['typefilter']) && 	$_GET['typefilter'] != '' &&
				isset($_GET[$_GET['typefilter'] . '_id']) && $_GET[$_GET['typefilter'] . '_id'] != ''))) {
			if (isset($_GET['manufacturers_id'])) {
				// This is a manufacturer page - get the URI for the manufacturers page if there is one
				$query_string_to_match = 'manufacturers_id=' . (int) $_GET['manufacturers_id'];

			} elseif (isset($_GET['typefilter']) && $_GET['typefilter'] != '' &&
					isset($_GET[$_GET['typefilter'] . '_id']) && $_GET[$_GET['typefilter'] . '_id'] != '') {
				// This is a filtered page - get the URI for the filtered page if there is one
				$query_string_to_match = 'typefilter=' . $_GET['typefilter'] . '&' . $_GET['typefilter'] . '_id=' .
					$_GET[$_GET['typefilter'] . '_id'];

			} elseif (isset($_GET['cPath'])) {
				// This is a category page - get the URI for the category ID
				$category_parts = explode('_', $_GET['cPath']);
				$category_id = $category_parts[count($category_parts) - 1];

				$associated_db_id = $category_id;
			}
		} elseif (in_array($_GET['main_page'], $ceon_uri_mapping_product_pages) ||
				in_array($_GET['main_page'], $ceon_uri_mapping_product_related_pages)) {
			$associated_db_id = !empty($_GET['products_id']) ? $_GET['products_id'] : (!empty($_GET['pid']) ? $_GET['pid'] : 0);

		} elseif ($_GET['main_page'] == FILENAME_EZPAGES || $_GET['main_page'] == FILENAME_EZPAGES_POPUP) {
			$associated_db_id = !empty($_GET['id']) ? $_GET['id'] : 0;

		} elseif ((count($_GET) > 1 && !isset($_GET[zen_session_name()])) ||
				(isset($_GET[zen_session_name()]) && count($_GET) > 2)) {
			// Check if there's a query string to match against for this Zen Cart page
			$query_string_to_match = $this->_query_string;

			// Remove main_page variable from query string parameters to be matched
			$query_string_to_match = str_replace('main_page=' . $_GET['main_page'], '', $query_string_to_match);

			if (str_starts_with($query_string_to_match, '&')) {
				$query_string_to_match = substr($query_string_to_match, 1, strlen($query_string_to_match) - 1);
			}

			if (str_ends_with($query_string_to_match, '&')) {
				$query_string_to_match = substr($query_string_to_match, 0, strlen($query_string_to_match) - 1);
			}
		}

		// Attempt to find a current URI mapping for the user's chosen language
		$redirection_uri = $this->_getCurrentURI($_GET['main_page'], (int)$associated_db_id,
			$query_string_to_match, (int)$_SESSION['languages_id']);

		if ($redirection_uri !== false) {
			// Have found a URI mapping for this page, use this as the "current" URI for this page by forcing a
			// redirect to it

			// Perform a sanity check to make sure that the URI to be redirected to isn't simply the current URI
			if ($this->_request_uri != $redirection_uri) {
				// Fine to redirect

				// Remove unnecessary variables from query string
				if ($_GET['main_page'] === FILENAME_DEFAULT && isset($_GET['manufacturer_id'])) {
					// Remove the manufacturer ID from the URI as it will be regenerated
					// when the static URI is loaded
					unset($_GET['manufacturer_id']);

				} elseif ($_GET['main_page'] === FILENAME_DEFAULT && isset($_GET['typefilter']) &&
						$_GET['typefilter'] != '' && isset($_GET[$_GET['typefilter'] . '_id']) &&
						$_GET[$_GET['typefilter'] . '_id'] != '') {
					// Remove the filter info from the URI as it will be regenerated when the static URI is loaded
					unset($_GET['typefilter']);
					unset($_GET[$_GET['typefilter'] . '_id']);

				} elseif ($_GET['main_page'] === FILENAME_DEFAULT && isset($_GET['cPath'])) {
					// Remove the category ID from the URI as it will be regenerated when the static URI is loaded
					unset($_GET['cPath']);

				} elseif (in_array($_GET['main_page'], $ceon_uri_mapping_product_pages) ||
						in_array($_GET['main_page'], $ceon_uri_mapping_product_related_pages)) {
					// Remove the category ID from the URI if it is simply the product's master category as it will
					// be regenerated when the static URI is loaded
					if (isset($_GET['cPath']) && $_GET['cPath'] == zen_get_product_path($_GET['products_id'])) {
						unset($_GET['cPath']);
					}

					unset($_GET['products_id'], $_GET['pid']);

				} elseif ($_GET['main_page'] == FILENAME_EZPAGES ||
						$_GET['main_page'] == FILENAME_EZPAGES_POPUP) {
					unset($_GET['id']);
				}

				unset($_GET['main_page']);

				if (!is_null($query_string_to_match)) {
					// Remove the query string parameters which matched those in the string in the database as they
					// will be set automatically using the same parameter string associated with the mapped URI in
					// the database - this means they don't have to be displayed to the customer!
					// All other parameters should be passed or additional functionality on the mapped page would
					// not work correctly!
					$query_string_parameter_pairs = explode('&', $query_string_to_match);

					foreach ($query_string_parameter_pairs as $current_query_string_parameter_pair) {
						$key_and_value = explode('=', $current_query_string_parameter_pair);

						unset($_GET[$key_and_value[0]]);
					}
				}
			} else {
				// Don't redirect to the same address!
				$redirection_uri = false;
			}
		} elseif (is_null($associated_db_id) && !is_null($query_string_to_match) &&
				$_GET['main_page'] != FILENAME_DEFAULT) {
			// Could this possibly be a Zen Cart page without parameters?
			$redirection_uri = $this->_getCurrentURI($_GET['main_page'], null, null, (int)$_SESSION['languages_id']);

			if ($redirection_uri !== false) {
				// Perform a sanity check to make sure that the URI to be redirected to isn't simply the current
				// URI
				if ($this->_request_uri != $redirection_uri) {
					// Fine to redirect; remove unnecessary page identifier from query string
					unset($_GET['main_page']);
				} else {
					// Don't redirect to the same address!
					$redirection_uri = false;
				}
			}
		}

		if ($redirection_uri !== false) {
			$query_string = $this->_buildQueryString();

			$redirection_uri .= $query_string;

			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $redirection_uri);

			zen_exit();
		}

		$this->_handleUnmappedURI($_GET['main_page'], (int)$associated_db_id, $query_string_to_match);
	}

	// }}}


	// {{{ _handleHistoricalURIWithNoCurrentMapping()

	/**
	 * Redirects to a standard Zen Cart dynamic URI, building the URI from the parameters passed.
	 *
	 * @access  protected
	 * @param   string    $main_page                 The name of the Zen Cart page for the URI.
	 * @param  int  $associated_db_id          The associated database ID for the URI.
     * @param  string  $query_string_parameters   The query string parameters for the URI.
     * @return  void
	 * @author  Conor Kerr <zen-cart.uri-mapping@ceon.net>
	 */
	protected function _handleHistoricalURIWithNoCurrentMapping(string $main_page, int $associated_db_id, string $query_string_parameters): void
    {
		global $request_type;

		if (!is_null($associated_db_id)) {
			// Must supply the ID information necessary for the correct info to be loaded
			switch ($main_page) {
				case FILENAME_DEFAULT:
					if (!isset($_GET['cPath'])) {
						$_GET['cPath'] = $associated_db_id;
					}

					break;

				case FILENAME_EZPAGES:
				case FILENAME_EZPAGES_POPUP:
					$_GET['id'] = $associated_db_id;

					break;

				case FILENAME_ASK_A_QUESTION:
					$_GET['pid'] = $associated_db_id;


					break;

				default:
					// This is a product related page
					$_GET['products_id'] = $associated_db_id;

					break;
			}
		} elseif (!is_null($query_string_parameters)) {
			// Must make sure that all parameters for this URI are loaded
			$query_string_pairs = explode('&', $query_string_parameters);

			foreach ($query_string_pairs as $query_string_pair) {
				$parameter_parts = explode('=', $query_string_pair);

				// Parameter from database overrides any in query string as this is a historical URI mapping.
				if (count($parameter_parts) == 2) {
					$_GET[$parameter_parts[0]] = urldecode($parameter_parts[1]);
				}
			}
		}

		if ($request_type == 'SSL') {
			$redirection_uri = DIR_WS_HTTPS_CATALOG;
		} else {
			$redirection_uri = DIR_WS_CATALOG;
		}

		if (!str_starts_with($redirection_uri, '/')) {
			$redirection_uri = '/' . $redirection_uri;
		}

		$redirection_uri .= 'index.php';

		// Add the page to be loaded to the list of query parameters
		$_GET['main_page'] = $main_page;

		$query_string = $this->_buildQueryString();

		$redirection_uri .= $query_string;

		// Record that the source of redirect was this script, avoiding any unnecessary processing
		// later
		$_SESSION['ceon_uri_mapping_redirected'] = true;

		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirection_uri);

		zen_exit();
	}

	// }}}


	// {{{ _handleUnmappedURI()

	/**
	 * Handles a Zen Cart dynamic URI which has no URI mapping.
	 *
	 * @access  protected
	 * @param   string    $main_page                 The name of the Zen Cart page for the URI.
	 * @param  int|null  $associated_db_id          The associated database ID for the URI.
     * @param  null|string  $query_string_parameters   The query string parameters for the URI.
     * @return  void
	 * @author  Conor Kerr <zen-cart.uri-mapping@ceon.net>
	 */
	protected function _handleUnmappedURI(string $main_page, ?int $associated_db_id, ?string $query_string_parameters): void
    {
		if (is_null($associated_db_id) && !is_null($query_string_parameters) &&
				$_GET['main_page'] == FILENAME_DEFAULT) {
			// Build the canonical URI for a manufacturer page or a filtered page as the fallover Zen Cart
			// canonical code doesn't handle these pages
			if (isset($_GET['manufacturers_id'])) {
				global $ceon_uri_mapping_canonical_uri;

				$ceon_uri_mapping_canonical_uri = HTTP_SERVER . DIR_WS_CATALOG .
					'index.php?main_page=index&manufacturers_id=' . (int) $_GET['manufacturers_id'];

			} elseif (isset($_GET['typefilter']) && $_GET['typefilter'] != '' &&
					isset($_GET[$_GET['typefilter'] . '_id']) && $_GET[$_GET['typefilter'] . '_id'] != '') {
				global $ceon_uri_mapping_canonical_uri;

				$ceon_uri_mapping_canonical_uri = HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index' .
					'&typefilter=' . $_GET['typefilter'] . '&' . $_GET['typefilter'] . '_id=' .
					$_GET[$_GET['typefilter'] . '_id'];
			}
		}
	}

	// }}}
}

// }}}
