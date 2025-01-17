<?php

/**
 * Ceon URI Mapping Zen Cart Categories Admin Functionality.
 *
 * This file contains a class which handles the functionality for category pages within the Zen Cart admin.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminCategoryPages.php 2025-01-08 torvista
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategories.php');


// {{{ CeonURIMappingAdminCategoryPages

/**
 * Handles the functionality for category pages within the Zen Cart admin.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminCategoryPages extends CeonURIMappingAdminCategories
{
	// {{{ Class Constructor

	/**
	 * Creates a new instance of the CeonURIMappingAdminCategoryPages class.
	 *
	 * @access  public
	 */
	public function __construct()
	{
		// Load the language definition file for the current language
		@include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'ceon_uri_mapping_category_pages.php');

		if (!defined('CEON_URI_MAPPING_TEXT_CATEGORY_URI') && $_SESSION['language'] != 'english') {
			// Fall back to english language file
			include_once(DIR_WS_LANGUAGES . 'english/' . 'ceon_uri_mapping_category_pages.php');
		}

		parent::__construct();
	}

	// }}}


	// {{{ insertUpdateHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when a category is being inserted or updated.
	 *
	 * @access  public
	 * @param  int  $category_id   The ID of the category.
	 * @param  int  $current_category_id   The ID of the category's parent category.
	 * @return  void
	 */
	public function insertUpdateHandler(int $category_id, int $current_category_id): void
    {
		global $languages, $messageStack;

		$uri_mapping_autogen = isset($_POST['uri-mapping-autogen']);

		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			$prev_uri_mapping = trim($_POST['prev-uri-mappings'][$languages[$i]['id']]);

			// Auto-generate the URI if requested
			if ($uri_mapping_autogen) {
				$uri_mapping = $this->autogenCategoryURIMapping($category_id, $current_category_id,
					$_POST['categories_name'][$languages[$i]['id']], $languages[$i]['code'], $languages[$i]['id']);

				if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_WITH_NO_NAME ||
						$uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_PATH_PART_WITH_NO_NAME) {
					// Can't generate the URI because of missing "uniqueness" data or invalid data
					if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_WITH_NO_NAME) {
						$message = CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_CATEGORY_HAS_NO_NAME;
					} else {
						$message = CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_A_PARENT_CATEGORY_HAS_NO_NAME;
					}

					$failure_message = sprintf($message, ucwords($languages[$i]['name']));

					$messageStack->add_session($failure_message, 'error');

					continue;
				}
			} else {
				$uri_mapping = $_POST['uri-mappings'][$languages[$i]['id']];
			}

			if (strlen($uri_mapping) > 1) {
				// Make sure URI mapping is relative to root of site and does not have a trailing slash or any
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

			if ($insert_uri_mapping || $update_uri_mapping) {
				if ($update_uri_mapping) {
					// Consign previous mapping to the history, so old URI mapping isn't broken
					$this->makeURIMappingHistorical($prev_uri_mapping, $languages[$i]['id']);
				}

				// Add the new URI mapping
				$uri = $uri_mapping;

				$main_page = FILENAME_DEFAULT;

				$mapping_added = $this->addURIMapping($uri, $languages[$i]['id'], $main_page, null, $category_id);

				if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
					if ($insert_uri_mapping) {
						$success_message = sprintf(CEON_URI_MAPPING_TEXT_CATEGORY_MAPPING_ADDED,
							ucwords($languages[$i]['name']),
							'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');
					} else {
						$success_message = sprintf(CEON_URI_MAPPING_TEXT_CATEGORY_MAPPING_UPDATED,
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

				$success_message = sprintf(CEON_URI_MAPPING_TEXT_CATEGORY_MAPPING_MADE_HISTORICAL,
					ucwords($languages[$i]['name']));

				$messageStack->add_session($success_message, 'caution');
			}
		}
	}

	// }}}


	// {{{ addURIMappingFieldsToAddCategoryFieldsArray()

	/** Appears to be unused
	 * Adds the fields necessary for the Ceon URI Mapping options to the list of add category fields, accessing the
	 * list of add category fields directly, through a global variable.
	 *
	 * @access  public
	 * @return  void
	 */
	public function addURIMappingFieldsToAddCategoryFieldsArray(): void
	{
		global $contents;

		// New category doesn't have any previous URI mappings
		$prev_uri_mappings = [];

		$uri_mapping_input_fields = $this->buildCategoryURIMappingFields($prev_uri_mappings);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}

	// }}}


///	// {{{ addURIMappingFieldsToAddCategoryForm()

	/**
	 * Adds the fields necessary for the Ceon URI Mapping options to the screen, accessing the
	 * list of add category fields directly, through a global variable.
	 *
	 * @access  public
	 * @return  void
	 */
	public function addURIMappingFieldsToAddCategoryForm():void
	{
		global $contents;

		// New category doesn't have any previous URI mappings
		$prev_uri_mappings = [];

		$uri_mapping_input_fields = $this->buildCategoryURIMappingFormFields($prev_uri_mappings);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}

//	// }}}


	// {{{ addURIMappingFieldsToEditCategoryFieldsArray()

	/**
	 * Adds the fields necessary for the Ceon URI Mapping options to the list of edit category fields, accessing
	 * the list of edit category fields directly, through a global variable.
	 *
	 * @access  public
	 * @param  int  $category_id   The ID of the category.
	 * @return  void
	 */
	public function addURIMappingFieldsToEditCategoryFieldsArray(int $category_id): void
	{
		global $contents;

		// Get any current category mappings from the database, up to one for each language
		$prev_uri_mappings = [];

		$columns_to_retrieve = [
			'language_id',
			'uri'
        ];

		$selections = [
			'main_page' => FILENAME_DEFAULT,
			'associated_db_id' => $category_id,
			'current_uri' => 1
        ];

		$prev_uri_mappings_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections);

		while (!$prev_uri_mappings_result->EOF) {
			$prev_uri_mappings[$prev_uri_mappings_result->fields['language_id']] =
				$prev_uri_mappings_result->fields['uri'];

			$prev_uri_mappings_result->MoveNext();
		}

		$uri_mapping_input_fields = $this->buildCategoryURIMappingFields($prev_uri_mappings);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}

	// }}}


	// {{{ addURIMappingFieldsToEditCategoryForm()

    /**
     * Adds the fields necessary for the Ceon URI Mapping options to the list of edit category fields, accessing
     * the list of edit category fields directly, through a global variable.
     *
     * @access  public
     * @param  int  $category_id  The ID of the category.
     * @param  string[]  $dividerClass
     * @return  void
     */
	public function addURIMappingFieldsToEditCategoryForm(int $category_id, array $dividerClass = ['label' => 'col-sm-3 control-label', 'input_field'=>'col-sm-9 col-md-6']): void
    {
		global $contents;

		// Get any current category mappings from the database, up to one for each language
		$prev_uri_mappings = [];

		$columns_to_retrieve = [
			'language_id',
			'uri'
        ];

		$selections = [
			'main_page' => FILENAME_DEFAULT,
			'associated_db_id' => $category_id,
			'current_uri' => 1
        ];

		$prev_uri_mappings_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections);

		while (!$prev_uri_mappings_result->EOF) {
			$prev_uri_mappings[$prev_uri_mappings_result->fields['language_id']] =
				$prev_uri_mappings_result->fields['uri'];

			$prev_uri_mappings_result->MoveNext();
		}

		$uri_mapping_input_fields = $this->buildCategoryURIMappingFormFields($prev_uri_mappings, $dividerClass);

		$contents[] = ['text' => $uri_mapping_input_fields];
	}


	// {{{ buildCategoryURIMappingFields()

	/**
	 * Builds the input fields for adding/editing URI mappings for categories.
	 *
	 * @access  public
	 * @param  array  $prev_uri_mappings   An array of the current values for the URI mappings, if any.
	 * @return  string    The source for the input fields.
	 */
	public function buildCategoryURIMappingFields(array $prev_uri_mappings): string
    {
		global $languages;

		$num_prev_uri_mappings = count($prev_uri_mappings);

		$num_languages = count($languages);

		$uri_mapping_input_fields = zen_draw_separator('pixel_black.gif', '100%', '2');

		$uri_mapping_input_fields .= '<table border="0" cellspacing="0" cellpadding="0">' . "\n\t";
		$uri_mapping_input_fields .= '<tr>' . "\n\t\t" .
			'<td rowspan="2" class="main" valign="top" style="width: 10em; padding-top: 0.5em;">';

		$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_CATEGORY_URI . '</td>' . "\n\t\t" .
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
				$autogen_message = CEON_URI_MAPPING_TEXT_CATEGORY_URI_AUTOGEN;
			} else {
				$autogen_message = CEON_URI_MAPPING_TEXT_CATEGORY_URIS_AUTOGEN;
			}

			if ($num_prev_uri_mappings == 0) {
				$autogen_selected = true;
			} else {
				$autogen_selected = false;

				if ($num_prev_uri_mappings == 1) {
					$autogen_message .= '<br />' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING;
				} elseif ($num_prev_uri_mappings == $num_languages) {
					$autogen_message .= '<br />' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ALL_EXISTING_MAPPINGS;
				} else {
					$autogen_message .= '<br />' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_SOME_EXISTING_MAPPINGS;
				}
			}

			$uri_mapping_input_fields .= zen_draw_checkbox_field('uri-mapping-autogen', '1', $autogen_selected) .
				' ' . $autogen_message;
		} else {
			$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_URI_AUTOGEN_DISABLED;
		}

		$uri_mapping_input_fields .= '</p>';

		$uri_mapping_input_fields .= "\n\t\t</td>\n\t</tr>\n</table>\n";

		$uri_mapping_input_fields .= zen_draw_separator('pixel_black.gif', '100%', '2');

		return $uri_mapping_input_fields;
	}

	/// }}}


	// {{{ buildCategoryURIMappingFormFields()

	/**
	 * Builds the input fields for adding/editing URI mappings for categories.
	 *
	 * @access  public
	 * @param  array  $prev_uri_mappings   An array of the current values for the URI mappings, if any.
	 * @return  string    The source for the input fields.
	 */
	public function buildCategoryURIMappingFormFields(array $prev_uri_mappings, $dividerClass = ['label' => 'col-sm-3 control-label', 'input_field'=>'col-sm-9 col-md-6']): string
    {
		global $languages;

		$num_prev_uri_mappings = sizeof($prev_uri_mappings);

		$num_languages = sizeof($languages);

		$uri_mapping_input_fields = zen_draw_separator('pixel_black.gif', '100%', '2');

		$uri_mapping_input_fields .= "\n\t\t" . '<div class="form-group">' . "\n\t\t\t";

        $uri_mapping_input_fields .= '<p class="' . $dividerClass['label'] . '">' . CEON_URI_MAPPING_TEXT_CATEGORY_URI . '</p>' . "\n\t\t\t";

		$uri_mapping_input_fields .= '<div class="' . $dividerClass['input_field'] . '">' . "\n\t\t\t\t";

		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$uri_mapping_input_fields .= '<div class="input-group">' . "\n\t\t\t\t\t";

			if (!isset($prev_uri_mappings[$languages[$i]['id']])) {
				$prev_uri_mappings[$languages[$i]['id']] = '';
			}

			$uri_mapping_input_fields .= zen_draw_hidden_field('prev-uri-mappings[' . $languages[$i]['id'] . ']',
				$prev_uri_mappings[$languages[$i]['id']]);

			$uri_mapping_input_fields .= "\n\t\t\t\t\t";
			$uri_mapping_input_fields .= '<div class="input-group">' . "\n\t\t\t\t\t\t";
			$uri_mapping_input_fields .= '<span class="input-group-addon">' . "\n\t\t\t\t\t\t\t";

			$uri_mapping_input_fields .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] .
				'/images/' . $languages[$i]['image'], $languages[$i]['name']); // . '&nbsp;';

			$uri_mapping_input_fields .= "\n\t\t\t\t\t\t" . '</span>' . "\n\t\t\t\t\t\t";
			$uri_mapping_input_fields .= zen_draw_input_field('uri-mappings[' . $languages[$i]['id'] . ']',
				$prev_uri_mappings[$languages[$i]['id']], 'class="form-control"' . ' size="60"');

			$uri_mapping_input_fields .= "\n\t\t\t\t\t" . '</div>' . "\n\t\t\t\t";
			$uri_mapping_input_fields .= '</div>' . "\n\t\t\t\t";
		}

		$uri_mapping_input_fields .= '<p>' . "\n\t\t\t\t\t";

		if ($this->_autogenEnabled()) {
			if ($num_languages == 1) {
				$autogen_message = CEON_URI_MAPPING_TEXT_CATEGORY_URI_AUTOGEN;
			} else {
				$autogen_message = CEON_URI_MAPPING_TEXT_CATEGORY_URIS_AUTOGEN;
			}

			if ($num_prev_uri_mappings == 0) {
				$autogen_selected = true;
			} else {
				$autogen_selected = false;
//steve override to always update
$autogen_selected = true;

				if ($num_prev_uri_mappings == 1) {
					$autogen_message .= '<br />' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING;
				} elseif ($num_prev_uri_mappings == $num_languages) {
					$autogen_message .= '<br />' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ALL_EXISTING_MAPPINGS;
				} else {
					$autogen_message .= '<br />' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_SOME_EXISTING_MAPPINGS;
				}
			}

			$uri_mapping_input_fields .= zen_draw_checkbox_field('uri-mapping-autogen', '1', $autogen_selected) .
				' ' . $autogen_message;
		} else {
			$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_URI_AUTOGEN_DISABLED;
		}

		$uri_mapping_input_fields .= "\n\t\t\t\t";

		$uri_mapping_input_fields .= '</p>';

		$uri_mapping_input_fields .= "\n\t\t\t</div>\n\t\t</div>\n\t";

		$uri_mapping_input_fields .= zen_draw_separator('pixel_black.gif', '100%', '2');

		$uri_mapping_input_fields .= "\n";

		return $uri_mapping_input_fields;
	}

	/// }}}

}

// }}}
