<?php

/**
 * Ceon URI Mapping Zen Cart Manufacturers Admin Functionality.
 *
 * This file contains a class which handles the functionality for manufacturer pages within the Zen Cart admin.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminManufacturerPages.php 2025-01-08 torvista
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdminManufacturers.php');


// {{{ CeonURIMappingAdminManufacturerPages

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
class CeonURIMappingAdminManufacturerPages extends CeonURIMappingAdminManufacturers
{
	// {{{ properties

	/**
	 * Maintains a copy of the current URI mappings entered/generated for the Manufacturer.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_uri_mappings = [];

	/**
	 * Maintains a copy of any previous URI mappings for the Manufacturer.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_prev_uri_mappings = [];

	/**
	 * Flag indicates whether auto-generation of URI mappings has been selected for the Manufacturer.
	 *
	 * @var     bool
	 * @access  protected
	 */
	protected bool $_uri_mapping_autogen = false;

	/**
	 * Maintains a list of any URI mappings for the Manufacturer which clash with existing mappings.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_clashing_mappings = [];

	// }}}


	// {{{ Class Constructor

	/**
	 * Creates a new instance of the CeonURIMappingAdminManufacturerPages class.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		// Load the language definition file for the current language
		@include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'ceon_uri_mapping_manufacturer_pages.php');

		if (!defined('CEON_URI_MAPPING_TEXT_MANUFACTURER_URI') && $_SESSION['language'] != 'english') {
			// Fall back to english language file
			include_once(DIR_WS_LANGUAGES . 'english/' . 'ceon_uri_mapping_manufacturer_pages.php');
		}

		parent::__construct();
	}

	// }}}


	// {{{ insertSaveHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when a manufacturer is being inserted or updated.
	 *
	 * @access  public
	 * @param  int  $manufacturer_id   The ID of the manufacturer.
	 * @param  string  $manufacturer_name   The name of the manufacturer.
	 * @return  void
	 */
	public function insertSaveHandler(int $manufacturer_id, string $manufacturer_name): void
    {
		global $messageStack;

		$uri_mapping_autogen = isset($_POST['uri-mapping-autogen']);

		$languages = zen_get_languages();

		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			$prev_uri_mapping = trim($_POST['prev-uri-mappings'][$languages[$i]['id']]);

			// Auto-generate the URI if requested
			if ($uri_mapping_autogen) {
				$uri_mapping = $this->autogenManufacturerURIMapping($manufacturer_id, $manufacturer_name,
					$languages[$i]['code'], $languages[$i]['id']);

				if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_MANUFACTURER_WITH_NO_NAME) {
					// Can't generate the URI because of missing "uniqueness" data
					$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_MANUFACTURER_HAS_NO_NAME,
						ucwords($languages[$i]['name']));

					$messageStack->add_session($failure_message, 'error');

					continue;
				}
			} else {
				$uri_mapping = $_POST['uri-mappings'][$languages[$i]['id']];
			}

			if (strlen($uri_mapping) > 1) {
				// Make sure URI mapping is relative to root of site and does not have a trailing slash or any ~
				// illegal characters
				$uri_mapping = $this->_cleanUpURIMapping($uri_mapping);
			}

			$insert_uri_mapping = false;
			$update_uri_mapping = false;

			if ($uri_mapping != '') {
				// Check if the URI mapping is being updated or does not yet exist
				if ($prev_uri_mapping == '') {
					$insert_uri_mapping = true;
				} elseif ($prev_uri_mapping != $uri_mapping) {
					$update_uri_mapping = true;
				}
			}

			$query_string_parameters = 'manufacturers_id=' . $manufacturer_id;

			if ($insert_uri_mapping || $update_uri_mapping) {
				if ($update_uri_mapping) {
					// Consign previous mapping to the history, so old URI mapping isn't broken
					$this->makeURIMappingHistorical($prev_uri_mapping, $languages[$i]['id']);
				}

				// Add the new URI mapping
				$uri = $uri_mapping;

				$main_page = FILENAME_DEFAULT;

				$mapping_added =
					$this->addURIMapping($uri, $languages[$i]['id'], $main_page, $query_string_parameters);

				if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
					if ($insert_uri_mapping) {
						$success_message = sprintf(CEON_URI_MAPPING_TEXT_MANUFACTURER_MAPPING_ADDED,
							ucwords($languages[$i]['name']),
							'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');
					} else {
						$success_message = sprintf(CEON_URI_MAPPING_TEXT_MANUFACTURER_MAPPING_UPDATED,
							ucwords($languages[$i]['name']),
							'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');
					}

					$messageStack->add_session($success_message, 'success');

				} else {
					if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_ERROR_MAPPING_EXISTS) {
						$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_ADD_MAPPING_EXISTS,
							ucwords($languages[$i]['name']),
							'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');

					} elseif ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_ERROR_DATA_ERROR) {
						$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_ADD_MAPPING_DATA,
							ucwords($languages[$i]['name']), $uri);
					} else {
						$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_ADD_MAPPING_DB,
							ucwords($languages[$i]['name']), $uri);
					}

					$messageStack->add_session($failure_message, 'error');
				}
			} elseif ($prev_uri_mapping != '' && $uri_mapping == '') {
				// No URI mapping, consign existing mapping to the history, so old URI mapping isn't broken
				$this->makeURIMappingHistorical($prev_uri_mapping, $languages[$i]['id']);

				$success_message = sprintf(CEON_URI_MAPPING_TEXT_MANUFACTURER_MAPPING_MADE_HISTORICAL,
					ucwords($languages[$i]['name']));

				$messageStack->add_session($success_message, 'caution');
			}
		}
	}

	// }}}


	// {{{ deleteConfirmHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when a Manufacturer is being deleted.
	 *
	 * @access  public
	 * @param  int  $manufacturer_id   The ID of the manufacturer.
	 * @return  void
	 */
	public function deleteConfirmHandler(int $manufacturer_id): void
    {
		$query_string_parameters = 'manufacturers_id=' . $manufacturer_id;

		$selections = [
			'main_page' => FILENAME_DEFAULT,
			'query_string_parameters' => $query_string_parameters
        ];

		$this->deleteURIMappings($selections);
	}

	// }}}


	// {{{ addURIMappingFieldsToAddManufacturerFieldsArray()

	/**
	 * Adds the fields necessary for the Ceon URI Mapping options to the list of add manufacturer fields, accessing
	 * the list of add manufacturer fields directly, through a global variable.
	 *
	 * @access  public
	 * @return  void
	 */
	public function addURIMappingFieldsToAddManufacturerFieldsArray(): void
    {
		global $contents;

		// New manufacturer doesn't have any previous URI mappings
		$prev_uri_mappings = [];

		$uri_mapping_input_fields = $this->buildManufacturerURIMappingFormFields($prev_uri_mappings);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}

	// }}}


	// {{{ addURIMappingFieldsToEditManufacturerFieldsArray()

	/** Appears Unused
	 * Adds the fields necessary for the Ceon URI Mapping options to the list of edit manufacturer fields,
	 * accessing the list of edit manufacturer fields directly, through a global variable.
	 *
	 * @access  public
	 * @param  int  $manufacturer_id   The ID of the manufacturer.
	 * @return  void
	 */
	public function addURIMappingFieldsToEditManufacturerFieldsArray(int $manufacturer_id): void
    {
		global $contents;

		// Get any current manufacturer mappings from the database, up to one for each language
		$prev_uri_mappings = [];

		$columns_to_retrieve = [
			'language_id',
			'uri'
        ];

		$query_string_parameters = 'manufacturers_id=' . $manufacturer_id;

		$selections = [
			'main_page' => FILENAME_DEFAULT,
			'query_string_parameters' => $query_string_parameters,
			'current_uri' => 1
        ];

		$prev_uri_mappings_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections);

		while (!$prev_uri_mappings_result->EOF) {
			$prev_uri_mappings[$prev_uri_mappings_result->fields['language_id']] =
				$prev_uri_mappings_result->fields['uri'];

			$prev_uri_mappings_result->MoveNext();
		}

		$uri_mapping_input_fields = $this->buildManufacturerURIMappingFields($prev_uri_mappings);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}

	// }}}


	// {{{ addURIMappingFieldsToEditManufacturerFieldsFormArray()

	/**
	 * Adds the fields necessary for the Ceon URI Mapping options to the list of edit manufacturer fields,
	 * accessing the list of edit manufacturer fields directly, through a global variable.
	 *
	 * @access  public
	 * @param  int  $manufacturer_id   The ID of the manufacturer.
	 * @return  void
	 */
	public function addURIMappingFieldsToEditManufacturerFieldsFormArray(int $manufacturer_id): void
    {
		global $contents;

		// Get any current manufacturer mappings from the database, up to one for each language
		$prev_uri_mappings = [];

		$columns_to_retrieve = [
			'language_id',
			'uri'
        ];

		$query_string_parameters = 'manufacturers_id=' . $manufacturer_id;

		$selections = [
			'main_page' => FILENAME_DEFAULT,
			'query_string_parameters' => $query_string_parameters,
			'current_uri' => 1
        ];

		$prev_uri_mappings_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections);

		while (!$prev_uri_mappings_result->EOF) {
			$prev_uri_mappings[$prev_uri_mappings_result->fields['language_id']] =
				$prev_uri_mappings_result->fields['uri'];

			$prev_uri_mappings_result->MoveNext();
		}

		$uri_mapping_input_fields = $this->buildManufacturerURIMappingFormFields($prev_uri_mappings);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}

	// }}}


	// {{{ buildManufacturerURIMappingFields()

	/**
	 * Builds the input fields for adding/editing URI mappings for manufacturers.
	 *
	 * @access  public
	 * @param  array  $prev_uri_mappings   An array of the current values for the URI mappings, if any.
	 * @return  string   The source for the input fields.
	 */
	public function buildManufacturerURIMappingFields(array $prev_uri_mappings): string
    {
		global $languages;

		$num_prev_uri_mappings = count($prev_uri_mappings);

		$num_languages = count($languages);

		$uri_mapping_input_fields = zen_draw_separator('pixel_black.gif', '100%', '2');

		$uri_mapping_input_fields .= '<table border="0" cellspacing="0" cellpadding="0">' . "\n\t";
		$uri_mapping_input_fields .= '<tr>' . "\n\t\t" .
			'<td rowspan="2" class="main" valign="top" style="width: 10em; padding-top: 0.5em;">';

		$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_MANUFACTURER_URI . '</td>' . "\n\t\t" .
			'<td class="main" style="padding-top: 0.5em; padding-bottom: 0;">' . "\n";

		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			$uri_mapping_input_fields .= '<p>';

			if (!isset($prev_uri_mappings[$languages[$i]['id']])) {
				$prev_uri_mappings[$languages[$i]['id']] = '';
			}

			$uri_mapping_input_fields .= zen_draw_hidden_field('prev-uri-mappings[' . $languages[$i]['id'] . ']',
				$prev_uri_mappings[$languages[$i]['id']]);

			$uri_mapping_input_fields .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] .
				'/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('uri-mappings[' . $languages[$i]['id'] . ']',
				$prev_uri_mappings[$languages[$i]['id']], 'size="60"');

			$uri_mapping_input_fields .= "</p>\n";
		}

		$uri_mapping_input_fields .= '</td>' . "\n\t</tr>\n\t<tr>\n\t\t" .
			'<td class="main" style="padding-top: 1em; padding-bottom: 0.5em;">' . "\n";

		$uri_mapping_input_fields .= '<p>';

		if ($this->_autogenEnabled()) {
			if ($num_languages == 1) {
				$autogen_message = CEON_URI_MAPPING_TEXT_MANUFACTURER_URI_AUTOGEN;
			} else {
				$autogen_message = CEON_URI_MAPPING_TEXT_MANUFACTURER_URIS_AUTOGEN;
			}

			if ($num_prev_uri_mappings == 0) {
				$autogen_selected = true;
			} else {
				$autogen_selected = false;

				if ($num_prev_uri_mappings == 1) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING;
				} elseif ($num_prev_uri_mappings == $num_languages) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ALL_EXISTING_MAPPINGS;
				} else {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_SOME_EXISTING_MAPPINGS;
				}
			}

			$uri_mapping_input_fields .=
				zen_draw_checkbox_field('uri-mapping-autogen', '1', $autogen_selected) . ' ' . $autogen_message;
		} else {
			$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_URI_AUTOGEN_DISABLED;
		}

		$uri_mapping_input_fields .= '</p>';

		$uri_mapping_input_fields .= "\n\t\t</td>\n\t</tr>\n</table>\n";

		$uri_mapping_input_fields .= zen_draw_separator('pixel_black.gif', '100%', '2');

		return $uri_mapping_input_fields;
	}

	/// }}}


	// {{{ buildManufacturerURIMappingFormFields()

	/**
	 * Builds the input fields for adding/editing URI mappings for manufacturers.
	 *
	 * @access  public
	 * @param  array  $prev_uri_mappings   An array of the current values for the URI mappings, if any.
	 * @return  string   The source for the input fields.
	 */
	public function buildManufacturerURIMappingFormFields(array $prev_uri_mappings): string
    {
		global $languages;

		$num_prev_uri_mappings = count($prev_uri_mappings);

		$num_languages = count($languages);

		$uri_mapping_input_fields = '<br>';

		$uri_mapping_input_fields .= zen_draw_separator('pixel_black.gif', '100%', '2');

/*		$uri_mapping_input_fields .= '<table border="0" cellspacing="0" cellpadding="0">' . "\n\t";
		$uri_mapping_input_fields .= '<tr>' . "\n\t\t" .
			'<td rowspan="2" class="main" valign="top" style="width: 10em; padding-top: 0.5em;">';*/

		$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_MANUFACTURER_URI /*. '</td>' . "\n\t\t" .
			'<td class="main" style="padding-top: 0.5em; padding-bottom: 0;">'*/ . "\n";

		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$uri_mapping_input_fields .= '<p>';

			if (!isset($prev_uri_mappings[$languages[$i]['id']])) {
				$prev_uri_mappings[$languages[$i]['id']] = '';
			}

			$uri_mapping_input_fields .= zen_draw_hidden_field('prev-uri-mappings[' . $languages[$i]['id'] . ']',
				$prev_uri_mappings[$languages[$i]['id']]);

			$uri_mapping_input_fields .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] .
				'/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('uri-mappings[' . $languages[$i]['id'] . ']',
				$prev_uri_mappings[$languages[$i]['id']], 'class="form-control" size="60"');

			$uri_mapping_input_fields .= "</p>\n";
		}

		$uri_mapping_input_fields .= /*'</td>' . "\n\t</tr>\n\t<tr>\n\t\t" .
			'<td class="main" style="padding-top: 1em; padding-bottom: 0.5em;">' .*/ "\n";

		$uri_mapping_input_fields .= '<p>';

		if ($this->_autogenEnabled()) {
			if ($num_languages == 1) {
				$autogen_message = CEON_URI_MAPPING_TEXT_MANUFACTURER_URI_AUTOGEN;
			} else {
				$autogen_message = CEON_URI_MAPPING_TEXT_MANUFACTURER_URIS_AUTOGEN;
			}

			if ($num_prev_uri_mappings == 0) {
				$autogen_selected = true;
			} else {
				$autogen_selected = false;

				if ($num_prev_uri_mappings == 1) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING;
				} elseif ($num_prev_uri_mappings == $num_languages) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ALL_EXISTING_MAPPINGS;
				} else {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_SOME_EXISTING_MAPPINGS;
				}
			}

			$uri_mapping_input_fields .=
				zen_draw_checkbox_field('uri-mapping-autogen', '1', $autogen_selected) . ' ' . $autogen_message;
		} else {
			$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_URI_AUTOGEN_DISABLED;
		}

		$uri_mapping_input_fields .= '</p>';

//		$uri_mapping_input_fields .= "\n\t\t</td>\n\t</tr>\n</table>\n";

		$uri_mapping_input_fields .= zen_draw_separator('pixel_black.gif', '100%', '2');

		return $uri_mapping_input_fields;
	}

	/// }}}
}

// }}}
