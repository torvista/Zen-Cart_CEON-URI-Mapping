<?php
/**
 * Ceon URI Mapping Zen Cart Products Admin Functionality.
 *
 * This file contains a class which handles the functionality for product pages within the Zen Cart admin.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2020 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminProductPages.php 2025-01-07 torvista
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdminProducts.php');


// {{{ CeonURIMappingAdminProductPages

/**
 * Handles the functionality for product pages within the Zen Cart admin.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminProductPages extends CeonURIMappingAdminProducts
{
	// {{{ properties

	/**
	 * Maintains a copy of the current URI mappings entered/generated for the product.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_uri_mappings = [];

	/**
	 * Maintains a copy of any previous URI mappings for the product.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_prev_uri_mappings = [];

	/**
	 * Flag indicates whether auto-generation of URI mappings has been selected for the product.
	 *
	 * @var     bool
	 * @access  protected
	 */
	protected bool $_uri_mapping_autogen = false;

	/**
	 * Maintains a list of any URI mappings for the product which clash with existing mappings.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_clashing_mappings = [];

	/**
	 * Maintains a list of any URI mappings for the product for which auto-generation failed to produce a valid
	 * mapping.
	 *
	 * @var     array
	 * @access  protected
	 */
	protected array $_autogeneration_errors = [];

	// }}}


	// {{{ Class Constructor

	/**
	 * Creates a new instance of the CeonURIMappingAdminProductPages class.
	 *
	 * @access  public
	 */
	public function __construct()
	{
        // Load the language definition file for the current language
		@include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'ceon_uri_mapping_product_pages.php');

		if (!defined('CEON_URI_MAPPING_TEXT_PRODUCT_URI') && $_SESSION['language'] !== 'english') {
			// Fall back to english language file
			include_once(DIR_WS_LANGUAGES . 'english/' . 'ceon_uri_mapping_product_pages.php');
		}

		parent::__construct();
	}

	// }}}


	// {{{ collectInfoHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when the product admin page is being displayed.
	 *
	 * @access  public
	 * @return  void
     */
	public function collectInfoHandler(): void
    {
		global $db;

		// Get any current product URI mappings from the database
		if (isset($_GET['pID']) && empty($_POST)) {
			// Get the product's type handler
			$product_type_handler_query = '
				SELECT
					pt.type_handler
				FROM
					' . TABLE_PRODUCTS . ' p
				LEFT JOIN
					' . TABLE_PRODUCT_TYPES . ' pt
				ON
					pt.type_id = p.products_type
				WHERE
					p.products_id = ' . (int)$_GET['pID'];

			$product_type_handler_result = $db->Execute($product_type_handler_query);

			if (!$product_type_handler_result->EOF) {
				$product_type_handler = $product_type_handler_result->fields['type_handler'];

				$columns_to_retrieve = [
					'language_id',
					'uri'
                ];

				$selections = [
					'main_page' => $product_type_handler . '_info',
					'associated_db_id' => (int) $_GET['pID'],
					'current_uri' => 1
                ];

				$prev_uri_mappings_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections);

				while (!$prev_uri_mappings_result->EOF) {
					$this->_prev_uri_mappings[$prev_uri_mappings_result->fields['language_id']] =
						$prev_uri_mappings_result->fields['uri'];

					$prev_uri_mappings_result->MoveNext();
				}
			} else {
				// Product does not exist and therefore not expected to have previous mappings.
				$this->_prev_uri_mappings = [];
			}

			$this->_uri_mappings = $this->_prev_uri_mappings;

		} else {
			// Use the URI mappings passed in the POST variables
			$this->_prev_uri_mappings = $_POST['prev-uri-mappings'] ?? [];
			$this->_uri_mappings = $_POST['uri-mappings'] ?? [];

			$this->_uri_mapping_autogen = isset($_POST['uri-mapping-autogen']);
		}
	}

	// }}}


	// {{{ collectInfoBuildURIMappingFields()

	/**
	 * Builds the input fields for adding/editing URI mappings for products.
	 *
	 * @access  public
	 * @return  string    The source for the input fields.
	 */
	public function collectInfoBuildURIMappingFields(): string
    {
		global $languages;

		$num_uri_mappings = count($this->_uri_mappings);

		$num_languages = count($languages);

		$uri_mapping_input_fields = '<tr>
				<td colspan="2">' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>
				</tr>
				<tr>
				<td colspan="2">' . zen_draw_separator('pixel_black.gif', '100%', '2') . '</td>
				</tr>
				<tr>
				<td class="main" valign="top" style="padding-top: 0.5em;">' .
				CEON_URI_MAPPING_TEXT_PRODUCT_URI . '</td>
				<td class="main" style="padding-top: 0.5em; padding-bottom: 0.5em;">' . "\n";

		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			$uri_mapping_input_fields .= '<p>';

			if (!isset($this->_prev_uri_mappings[$languages[$i]['id']])) {
				$this->_prev_uri_mappings[$languages[$i]['id']] = '';
			}
			if (!isset($this->_uri_mappings[$languages[$i]['id']])) {
				$this->_uri_mappings[$languages[$i]['id']] = '';
			}

			$uri_mapping_input_fields .= zen_draw_hidden_field('prev-uri-mappings[' . $languages[$i]['id'] . ']',
				$this->_prev_uri_mappings[$languages[$i]['id']]);

			$uri_mapping_input_fields .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] .
				'/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .
				zen_draw_input_field('uri-mappings[' . $languages[$i]['id'] . ']',
				$this->_uri_mappings[$languages[$i]['id']], 'style="width:100%"');

			$uri_mapping_input_fields .= "</p>\n";
		}

		$uri_mapping_input_fields .= '<p>';

		if ($this->_autogenEnabled()) {
			if ($num_languages == 1) {
				$autogen_message = CEON_URI_MAPPING_TEXT_PRODUCT_URI_AUTOGEN;
			} else {
				$autogen_message = CEON_URI_MAPPING_TEXT_PRODUCT_URIS_AUTOGEN;
			}

			if ($num_uri_mappings == 0) {
				$autogen_selected = true;
			} else {
				$autogen_selected = false;

				if ($num_uri_mappings == 1) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING;
				} elseif ($num_uri_mappings == $num_languages) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ALL_EXISTING_MAPPINGS;
				} else {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_SOME_EXISTING_MAPPINGS;
				}
			}

			if (!$autogen_selected && $this->_uri_mapping_autogen) {
				$autogen_selected = true;
			}

			$uri_mapping_input_fields .= zen_draw_checkbox_field('uri-mapping-autogen', '1', $autogen_selected) .
				' ' . $autogen_message;
		} else {
			$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_URI_AUTOGEN_DISABLED;
		}

		$uri_mapping_input_fields .= '</p>';

		$uri_mapping_input_fields .= "\n\t\t</td>\n\t</tr>";

		$uri_mapping_input_fields .= '<tr>
				<td colspan="2">' . zen_draw_separator('pixel_black.gif', '100%', '2') . '</td>
				</tr>';

		return $uri_mapping_input_fields;
	}

	/// }}}


	// {{{ collectInfoBuildURIMappingForm()

	/**
	 * Builds the input fields for adding/editing URI mappings for products.
	 *
	 * @access  public
	 * @return  string    The source for the input fields.
	 */
	public function collectInfoBuildURIMappingForm(): string
    {
		global $languages;

		$num_uri_mappings = sizeof($this->_uri_mappings);

		$num_languages = sizeof($languages);

		$uri_mapping_input_fields = zen_draw_separator('pixel_trans.gif', '1', '10') . '
				' . zen_draw_separator('pixel_black.gif', '100%', '2') .
				'<p class="col-sm-3 control-label">' . CEON_URI_MAPPING_TEXT_PRODUCT_URI . '</p>' . '
				<div class="col-sm-9 col-md-6">' . "\n";

		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$uri_mapping_input_fields .= '<div class="input-group">' . "\n" . '<span class="input-group-addon">';

			if (!isset($this->_prev_uri_mappings[$languages[$i]['id']])) {
				$this->_prev_uri_mappings[$languages[$i]['id']] = '';
			}
			if (!isset($this->_uri_mappings[$languages[$i]['id']])) {
				$this->_uri_mappings[$languages[$i]['id']] = '';
			}

			$uri_mapping_input_fields .= zen_draw_hidden_field('prev-uri-mappings[' . $languages[$i]['id'] . ']',
				$this->_prev_uri_mappings[$languages[$i]['id']]);

			$uri_mapping_input_fields .= '' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] .
				'/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' .
				zen_draw_input_field('uri-mappings[' . $languages[$i]['id'] . ']',
				$this->_uri_mappings[$languages[$i]['id']], 'style="width:100%" class="form-control"');

			$uri_mapping_input_fields .= "</div>\n";
		}

		$uri_mapping_input_fields .= '<br><div class="input-group">';

		if ($this->_autogenEnabled()) {
			if ($num_languages == 1) {
				$autogen_message = CEON_URI_MAPPING_TEXT_PRODUCT_URI_AUTOGEN;
			} else {
				$autogen_message = CEON_URI_MAPPING_TEXT_PRODUCT_URIS_AUTOGEN;
			}

			if ($num_uri_mappings == 0) {
				$autogen_selected = true;
			} else {
				$autogen_selected = false;

				if ($num_uri_mappings == 1) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING;
				} elseif ($num_uri_mappings == $num_languages) {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ALL_EXISTING_MAPPINGS;
				} else {
					$autogen_message .= '<br>' . CEON_URI_MAPPING_TEXT_URI_AUTOGEN_SOME_EXISTING_MAPPINGS;
				}
			}

			if (!$autogen_selected && $this->_uri_mapping_autogen) {
				$autogen_selected = true;
			}

			$uri_mapping_input_fields .= '<label>' . zen_draw_checkbox_field('uri-mapping-autogen', '1', $autogen_selected) .
				' ' . $autogen_message;
		} else {
			$uri_mapping_input_fields .= CEON_URI_MAPPING_TEXT_URI_AUTOGEN_DISABLED;
		}

		$uri_mapping_input_fields .= '</div><br>';

		$uri_mapping_input_fields .= "\n\t\t</div>";

		$uri_mapping_input_fields .= '
				' . zen_draw_separator('pixel_black.gif', '100%', '2') . '
				';

		return $uri_mapping_input_fields;
	}

	/// }}}


	// {{{ productPreviewProcessSubmission()

	/**
	 * Handles the Ceon URI Mapping functionality when a product page has been submitted for preview.
	 *
	 * @access  public
	 * @param  int  $current_category_id   The ID of the original master category for the product.
	 * @return  void
	 */
	public function productPreviewProcessSubmission(int $current_category_id): void
    {
        $this->_prev_uri_mappings = $_POST['prev-uri-mappings'] ?? [];
		$this->_uri_mappings = $_POST['uri-mappings'] ?? [];

		$languages = zen_get_languages();

		// Are any supplied URIs to be overridden with generated URIs? If so, build a preview of these URIs to be
		// displayed
		$this->_uri_mapping_autogen = isset($_POST['uri-mapping-autogen']);

		if ($this->_uri_mapping_autogen) {
			// Is a linked product having its master category changed?
			if (isset($_POST['master_category']) && $_POST['master_category'] > 0) {
				$master_categories_id = (int) $_POST['master_category'];
			} else {
				// Find out where this product is being added/updated
				$master_categories_id = $current_category_id;
			}

			// Need to store names so they can be appended to the URI being generated for the product
			$names = $_POST['products_name'] ?? '';

			for ($i = 0, $n = count($languages); $i < $n; $i++) {
				$product_id = (isset($_GET['pID']) ? (int) $_GET['pID'] : null);

				$uri_mapping = $this->autogenProductURIMapping($product_id, $master_categories_id,
					$names[$languages[$i]['id']], $languages[$i]['code'], $languages[$i]['id'],
					$_POST['products_model']);

				if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME ||
						$uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_MODEL) {
					// Can't generate the URI because of missing "uniqueness" data
					if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME) {
						$message = CEON_URI_MAPPING_TEXT_PREVIEW_PRODUCT_ERROR_PRODUCT_HAS_NO_NAME;
					} else {
						$message = CEON_URI_MAPPING_TEXT_PREVIEW_PRODUCT_ERROR_PRODUCT_HAS_NO_MODEL;
					}

					$this->_autogeneration_errors[$languages[$i]['id']] = $message;

					// Set the "new" mapping back to the previous, so it won't be updated
					$this->_uri_mappings[$languages[$i]['id']] = $this->_prev_uri_mappings[$languages[$i]['id']];

				} elseif ($uri_mapping ==
						CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_PATH_PART_WITH_NO_NAME) {
					// Can't generate the URI because of invalid data
					$this->_autogeneration_errors[$languages[$i]['id']] =
						CEON_URI_MAPPING_TEXT_PREVIEW_PRODUCT_ERROR_CATEGORY_HAS_NO_NAME;

					// Set the "new" mapping back to the previous, so it won't be updated
					$this->_uri_mappings[$languages[$i]['id']] = $this->_prev_uri_mappings[$languages[$i]['id']];

				} else {
					// Auto-generated URI is fine to use
					$uri_mapping = $this->_cleanUpURIMapping($uri_mapping);

					$this->_uri_mappings[$languages[$i]['id']] = $uri_mapping;
				}
			}
		} else {
			for ($i = 0, $n = count($languages); $i < $n; $i++) {
				$this->_uri_mappings[$languages[$i]['id']] =
					$this->_cleanUpURIMapping($this->_uri_mappings[$languages[$i]['id']]);
			}
		}

		// Check that the mapping(s) just entered/generated doesn't/don't clash with any existing mapping(s), so
		// the user can be notified
		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			if ($this->_uri_mappings[$languages[$i]['id']] == $this->_prev_uri_mappings[$languages[$i]['id']]) {
				// Don't check for a clash with the previous mapping!
				continue;
			}

			$mapping_clashed = false;

			$columns_to_retrieve = [
				'main_page',
				'associated_db_id',
				'query_string_parameters'
            ];

			$selections = [
				'uri' => zen_db_prepare_input($this->_uri_mappings[$languages[$i]['id']]),
				'current_uri' => 1,
				'language_id' => $languages[$i]['id']
            ];

			$order_by = 'current_uri DESC';

			$existing_uri_mapping_result =
				$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

			// If the existing mapping is simply having some capitalisation changed, then a case-insensitive
			// comparison might result in a false positive for a mapping clash, so prevent that by checking the
			// mapping's settings don't match
			if (!$existing_uri_mapping_result->EOF &&
					!($existing_uri_mapping_result->fields['main_page'] ==
					(zen_get_handler_from_type(zen_get_products_type((int) $_GET['pID'])) . '_info') &&
					$existing_uri_mapping_result->fields['associated_db_id'] == $_GET['pID'] &&
					!isset($existing_uri_mapping_result->fields['query_string_parameters']))) {
				// This mapping clashes with an existing mapping
				$mapping_clashed = true;
			}

			if ($mapping_clashed) {
				// Take a copy of the mapping which clashed
				$this->_clashing_mappings[$languages[$i]['id']] = $this->_uri_mappings[$languages[$i]['id']];

				if ($this->_mappingClashAutoAppendInteger()) {
					// Attempt to find a unique variation of the mapping by appending an auto-incrementing integer
					$uniqueness_integer = 1;

					$base_uri = $this->_uri_mappings[$languages[$i]['id']];

					$unique_mapping_found = false;

					while (!$unique_mapping_found) {
						$uri = $base_uri . $uniqueness_integer;

						$columns_to_retrieve = [
							'main_page',
							'associated_db_id'
                        ];

						$selections = [
							'uri' => zen_db_prepare_input($uri),
							'current_uri' => 1,
							'language_id' => $languages[$i]['id']
                        ];

						$order_by = 'current_uri DESC';

						$existing_uri_mapping_result =
							$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

						if (!$existing_uri_mapping_result->EOF) {
							// Perform a sanity check to see if this matches the product's current
							// URI. If so, can take it that it was previously auto-appended
							if ($existing_uri_mapping_result->fields['main_page'] ==
									(zen_get_handler_from_type(zen_get_products_type(
									(int) $_GET['pID'])) . '_info') &&
									$existing_uri_mapping_result->fields['associated_db_id'] ==
									(int) $_GET['pID']) {
								// Match found so assuming that the URI hasn't changed
								unset($this->_clashing_mappings[$languages[$i]['id']]);

								$this->_uri_mappings[$languages[$i]['id']] = $uri;

								// Break out of this loop and advance to the next URI language
								continue 2;
							}
						} else {
							// This variation of the mapping doesn't clash with an existing
							// mapping, use it!
							$unique_mapping_found = true;
						}

						$uniqueness_integer++;
					}

					// A unique URI has been generated, store it for use later
					$this->_uri_mappings[$languages[$i]['id']] = $uri;

				} else {
					// No attempt should be made to create a unique variation of the mapping, instead the user must
					// be warned that the mapping was not created for this product

					// Set the "new" mapping back to the previous, so it won't be updated
					$this->_uri_mappings[$languages[$i]['id']] = $this->_prev_uri_mappings[$languages[$i]['id']];
				}
			}
		}
	}

	// }}}


	// {{{ productPreviewInitialLoad()

	/**
	 * Handles the Ceon URI Mapping functionality when a product page is loaded for preview.
	 *
	 * @access  public
	 * @param  int  $product_id     The product's ID.
	 * @param  string  $product_type   The product's type (not its type ID).
	 * @return  void
	 */
	public function productPreviewInitialLoad(int $product_id, string $product_type): void
    {
		$this->_prev_uri_mappings = [];
		$this->_uri_mappings = [];

		$this->_uri_mapping_autogen = false;

		// Get any current product URI mappings from the database
		$columns_to_retrieve = [
			'language_id',
			'uri'
        ];

		$selections = [
			'main_page' => zen_db_prepare_input($product_type . '_info'),
			'associated_db_id' => $product_id,
			'current_uri' => 1
        ];

		$uri_mappings_result = $this->getURIMappingsResultset($columns_to_retrieve, $selections);

		while (!$uri_mappings_result->EOF) {
			$this->_uri_mappings[$uri_mappings_result->fields['language_id']] =
				$uri_mappings_result->fields['uri'];

			$uri_mappings_result->MoveNext();
		}
	}

	// }}}


	// {{{ productPreviewOutputURIMappingInfo()

	/**
	 * Outputs the information about the product's URI mapping(s) on the product preview page.
	 * torvista: This function does not appear to be used anywhere...function productPreviewExportURIMappingInfo builds the table for the product preview page
	 * @access  public
	 * @param  array  $language_info   An array of Zen Cart language info.
	 * @return  void
     */
	public function productPreviewOutputURIMappingInfo(array $language_info): void
    {
		?>
			<tr>
				<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
			</tr>
			<tr>
			<td style="padding-top: 0.5em; padding-bottom: 0.5em; border-top: 1px solid #003d00; border-bottom: 1px solid #003d00;">
				<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td valign="top" style="width: 18em;">
				<?php
				if ($this->_uri_mapping_autogen) {
					echo CEON_URI_MAPPING_TEXT_PRODUCT_URI_AUTOGENERATED;
				} else {
					echo CEON_URI_MAPPING_TEXT_PRODUCT_URI_ENTERED;
				}
				?>
					</td>
					<td>
				<?php
				echo '<p style="float: left;">' . zen_image(DIR_WS_CATALOG_LANGUAGES .
					$language_info['directory'] . '/images/' . $language_info['image'], $language_info['name']) .
				'</p>';

				if ((isset($this->_uri_mappings[$language_info['id']]) &&
						strlen($this->_uri_mappings[$language_info['id']]) > 0) ||
						($this->_uri_mapping_autogen &&
							!isset($this->_autogeneration_errors[$language_info['id']]))) {
					if (isset($this->_clashing_mappings[$language_info['id']])) {
						// The mapping clashed with an existing mapping
						if ($this->_uri_mapping_autogen) {
							if ($this->_mappingClashAutoAppendInteger()) {
								// A new unique URI will have been generated
								echo '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_ORIGINALLY_GENERATED . '</p>';

								echo '<p style="margin-left: 50px;"><a href="' . HTTP_SERVER .
									$this->_uri_mappings[$language_info['id']] . '" target="_blank">' .
									$this->_clashing_mappings[$language_info['id']] . '</a></p>';

								echo '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_URI_GENERATED_AUTO_APPENDED . '</p>';

								echo '<p style="margin-left: 50px;">' .
									$this->_uri_mappings[$language_info['id']] . '</p>';

								echo '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_UPDATE_WILL_USE_AUTO_APPENDED_MAPPING;

							} else {
								// User is simply to be warned about the clash
								echo '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_GENERATED_WHICH_IS_CLASHING . '</p>';

								echo '<p style="margin-left: 50px;"><a href="' . HTTP_SERVER .
									$this->_uri_mappings[$language_info['id']] .
									'" target="_blank">' .
									$this->_clashing_mappings[$language_info['id']] . '</a></p>';
							}
						} else {
							if ($this->_mappingClashAutoAppendInteger()) {
								// A new unique URI will have been generated
								echo '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_ORIGINALLY_ENTERED . '</p>';

								echo '<p style="margin-left: 50px;"><a href="' . HTTP_SERVER .
									$this->_uri_mappings[$language_info['id']] .
									'" target="_blank">' .
									$this->_clashing_mappings[$language_info['id']] . '</a></p>';

								echo '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_URI_ENTERED_AUTO_APPENDED . '</p>';

								echo '<p style="margin-left: 50px;">' .
									$this->_uri_mappings[$language_info['id']] . '</p>';

								echo '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_UPDATE_WILL_USE_AUTO_APPENDED_MAPPING;

							} else {
								// User is simply to be warned about the clash
								echo '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_ENTERED_WHICH_IS_CLASHING . '</p>';

								echo '<p style="margin-left: 50px;">' .
									$this->_clashing_mappings[$language_info['id']] . '</p>';
							}
						}
					} else {
						echo '<p style="margin: 0 0 0 50px;">' .
							$this->_uri_mappings[$language_info['id']] . '</p>';
					}
				} elseif (isset($this->_autogeneration_errors[$language_info['id']])) {
					// The attempt to auto-generate a mapping failed
					echo '<p style="margin: 0 0 0 50px; color: #f00;">' .
						$this->_autogeneration_errors[$language_info['id']] . '</p>';
				} else {
					echo '<p style="margin: 0 0 0 50px;">' .
						CEON_URI_MAPPING_TEXT_PRODUCT_NO_URI_ENTERED . '</p>';
				}

				echo "</p>\n";
				?>
					</td>
				</tr>
				</table>
			</td>
			</tr>
	<?php
    }

	// }}}


	/**
	 * Exports the information about the product's URI mapping(s) on the product preview page.
	 *
	 * @access  public
	 * @param  array  $language_info   An array of Zen Cart language info.
	 * @return  string
	 */
	public function productPreviewExportURIMappingInfo(array $language_info): string
    {
		$contents = '
				<table>
				<tr>
					<td style="width: 18em; vertical-align: top;">
				';
				if ($this->_uri_mapping_autogen) {
					$contents .= CEON_URI_MAPPING_TEXT_PRODUCT_URI_AUTOGENERATED;
				} else {
					$contents .= CEON_URI_MAPPING_TEXT_PRODUCT_URI_ENTERED;
				}
				$contents .= '
					</td>
					<td>
				' .
				'<p style="float: left;">' . zen_image(DIR_WS_CATALOG_LANGUAGES .
					$language_info['directory'] . '/images/' . $language_info['image'], $language_info['name']) .
				'</p>';

				if ((isset($this->_uri_mappings[$language_info['id']]) &&
						strlen($this->_uri_mappings[$language_info['id']]) > 0) ||
						($this->_uri_mapping_autogen &&
							!isset($this->_autogeneration_errors[$language_info['id']]))) {
					if (isset($this->_clashing_mappings[$language_info['id']])) {
						// The mapping clashed with an existing mapping
						if ($this->_uri_mapping_autogen) {
							if ($this->_mappingClashAutoAppendInteger()) {
								// A new unique URI will have been generated
								$contents .= '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_ORIGINALLY_GENERATED . '</p>';

								$contents .= '<p style="margin-left: 50px;"><a href="' . HTTP_SERVER .
									$this->_uri_mappings[$language_info['id']] . '" target="_blank">' .
									$this->_clashing_mappings[$language_info['id']] . '</a></p>';

								$contents .= '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_URI_GENERATED_AUTO_APPENDED . '</p>';

								$contents .= '<p style="margin-left: 50px;">' .
									$this->_uri_mappings[$language_info['id']] . '</p>';

								$contents .= '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_UPDATE_WILL_USE_AUTO_APPENDED_MAPPING;

							} else {
								// User is simply to be warned about the clash
								$contents .= '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_GENERATED_WHICH_IS_CLASHING . '</p>';

								$contents .= '<p style="margin-left: 50px;"><a href="' . HTTP_SERVER .
									$this->_uri_mappings[$language_info['id']] .
									'" target="_blank">' .
									$this->_clashing_mappings[$language_info['id']] . '</a></p>';
							}
						} else {
							if ($this->_mappingClashAutoAppendInteger()) {
								// A new unique URI will have been generated
								$contents .= '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_ORIGINALLY_ENTERED . '</p>';

								$contents .= '<p style="margin-left: 50px;"><a href="' . HTTP_SERVER .
									$this->_uri_mappings[$language_info['id']] .
									'" target="_blank">' .
									$this->_clashing_mappings[$language_info['id']] . '</a></p>';

								$contents .= '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_URI_ENTERED_AUTO_APPENDED . '</p>';

								$contents .= '<p style="margin-left: 50px;">' .
									$this->_uri_mappings[$language_info['id']] . '</p>';

								$contents .= '<p style="margin-left: 50px;">' .
									CEON_URI_MAPPING_TEXT_UPDATE_WILL_USE_AUTO_APPENDED_MAPPING;

							} else {
								// User is simply to be warned about the clash
								$contents .= '<p style="margin: 0 0 0 50px; color: #f00;">' .
									CEON_URI_MAPPING_TEXT_URI_ENTERED_WHICH_IS_CLASHING . '</p>';

								$contents .= '<p style="margin-left: 50px;">' .
									$this->_clashing_mappings[$language_info['id']] . '</p>';
							}
						}
					} else {
						$contents .= '<p style="margin: 0 0 0 50px;">' .
							$this->_uri_mappings[$language_info['id']] . '</p>';
					}
				} elseif (isset($this->_autogeneration_errors[$language_info['id']])) {
					// The attempt to auto-generate a mapping failed
					$contents .= '<p style="margin: 0 0 0 50px; color: #f00;">' .
						$this->_autogeneration_errors[$language_info['id']] . '</p>';
				} else {
					$contents .= '<p style="margin: 0 0 0 50px;">' .
						CEON_URI_MAPPING_TEXT_PRODUCT_NO_URI_ENTERED . '</p>';
				}

				$contents .= "</p>\n";
				$contents .= '
					</td>
				</tr>
				</table>';
		return $contents;
	}

	// }}}


	// {{{ productPreviewBuildHiddenFields()

	/**
	 * Outputs the values for Ceon URI Mapping for posting to the insert/update page.
	 *
	 * @access  public
	 * @return  string
	 */
	public function productPreviewBuildHiddenFields(): string
    {
		global $languages;

		$hidden_fields = '';

		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			$hidden_fields .= zen_draw_hidden_field('prev-uri-mappings[' . $languages[$i]['id'] . ']',
				isset($this->_prev_uri_mappings[$languages[$i]['id']]) ? htmlspecialchars(stripslashes($this->_prev_uri_mappings[$languages[$i]['id']])) : '');

			$hidden_fields .= zen_draw_hidden_field('uri-mappings[' . $languages[$i]['id'] . ']',
				isset($this->_uri_mappings[$languages[$i]['id']]) ? htmlspecialchars(stripslashes($this->_uri_mappings[$languages[$i]['id']])) : '');
		}

		return $hidden_fields;
	}

	// }}}


	// {{{ updateProductHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when a product is being updated.
	 *
	 * @access  public
	 * @param  int  $product_id     The ID of the product.
	 * @param  string  $product_type   The product's type (not the type ID).
	 * @return  void
	 */
	public function updateProductHandler(int $product_id, string $product_type): void
    {
		global $languages, $messageStack;

		// Build the information for the pages that must have URIs managed
		$product_page_type = $product_type . '_info';

		$page_types = [
			'product_reviews',
			'product_reviews_info',
			'product_reviews_write',
			'tell_a_friend',
			'ask_a_question'
        ];

		$page_types_to_manage = [];

		foreach ($page_types as $page_type) {
			if ($this->autoManageProductRelatedPageURI($page_type)) {
				$page_types_to_manage[] = $page_type;
			}
		}

		for ($i = 0, $n = count($languages); $i < $n; $i++) {
			$prev_uri_mapping = isset($_POST['prev-uri-mappings'][$languages[$i]['id']]) ? trim($_POST['prev-uri-mappings'][$languages[$i]['id']]) : '';
			$uri_mapping = isset($_POST['uri-mappings'][$languages[$i]['id']]) ? trim($_POST['uri-mappings'][$languages[$i]['id']]) : '';

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

				$main_page = $product_page_type;

				$mapping_added = $this->addURIMapping($uri, $languages[$i]['id'], $main_page, null, $product_id);

				if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
					if ($insert_uri_mapping) {
						$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED,
							ucwords($languages[$i]['name']),
							'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');
					} else {
						$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_UPDATED,
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

				// Now add the URI mappings for the review pages/tell-a-friend page for this product
				$base_uri = $uri . '/';

				// Get the language code for the mapping's language
				$language_code = strtolower($languages[$i]['code']);

				foreach ($page_types_to_manage as $page_type) {
					// Mark any existing URI mapping for this product related page as no longer being the "primary"
					// mapping, leaving it in the database so old links aren't broken.
					$columns_to_retrieve = [
						'uri'
                    ];

					$selections = [
						'main_page' => $page_type,
						'associated_db_id' => $product_id,
						'language_id' => (int) $languages[$i]['id'],
						'current_uri' => 1,
                    ];

					$order_by = 'current_uri DESC';

					$current_uri_mapping_result =
						$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

					if (!$current_uri_mapping_result->EOF) {
						$this->makeURIMappingHistorical($current_uri_mapping_result->fields['uri'],
							$languages[$i]['id']);
					}

					$uri_part = $this->getProductRelatedPageURIPart($page_type, $language_code);

					if ($uri_part === false) {
						// Unexpected database problem encountered
						continue;
					}

					$uri_part = $this->_convertStringForURI($uri_part, $language_code);

					$uri = $base_uri . $uri_part;

					$main_page = constant('FILENAME_' . strtoupper($page_type));

					$mapping_added =
						$this->addURIMapping($uri, $languages[$i]['id'], $main_page, ($page_type == 'ask_a_question' ? 'pid=' . $product_id : null), $product_id);

					if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
						if ($insert_uri_mapping) {
							$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED,
								ucwords($languages[$i]['name']),
								'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');
						} else {
							$success_message =
								sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_UPDATED,
								ucwords($languages[$i]['name']),
								'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');
						}

						$messageStack->add_session($success_message, 'success');

					} else {
						if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_ERROR_MAPPING_EXISTS) {
							$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_ADD_MAPPING_EXISTS,
								ucwords($languages[$i]['name']), '<a href="' . HTTP_SERVER . $uri .
								'" target="_blank">' . $uri . '</a>');

						} elseif ($mapping_added ==CEON_URI_MAPPING_ADD_MAPPING_ERROR_DATA_ERROR) {
							$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_ADD_MAPPING_DATA,
								ucwords($languages[$i]['name']), $uri);
						} else {
							$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_ADD_MAPPING_DB,
								ucwords($languages[$i]['name']), $uri);
						}

						$messageStack->add_session($failure_message, 'error');
					}
				}
			} elseif ($prev_uri_mapping != '' && $uri_mapping == '') {
				// No URI mapping, consign existing mappings to the history, so old URI mappings aren't broken
				$this->makeURIMappingHistorical($prev_uri_mapping, $languages[$i]['id']);

				$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_MADE_HISTORICAL,
					ucwords($languages[$i]['name']));

				$messageStack->add_session($success_message, 'caution');

				foreach ($page_types_to_manage as $page_type) {
					$columns_to_retrieve = [
						'uri'
                    ];

					$selections = [
						'main_page' => $page_type,
						'associated_db_id' => $product_id,
						'language_id' => (int) $languages[$i]['id'],
						'current_uri' => 1,
                    ];

					$order_by = 'current_uri DESC';

					$current_uri_mapping_result =
						$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

					if (!$current_uri_mapping_result->EOF) {
						$prp_uri_mapping = $current_uri_mapping_result->fields['uri'];

						$this->makeURIMappingHistorical($prp_uri_mapping, $languages[$i]['id']);

						$success_message = sprintf(
							CEON_URI_MAPPING_TEXT_PRODUCT_RELATED_PAGE_MAPPING_MADE_HISTORICAL,
							ucwords($languages[$i]['name']), $prp_uri_mapping);

						$messageStack->add_session($success_message, 'caution');
					}
				}
			}
		}
	}

	// }}}


	// {{{ addURIMappingFieldsToProductCopyFieldsArray()

	/**
	 * Adds the fields necessary for the Ceon URI Mapping options to the list of product copy fields, accessing the
	 * list of product copy fields directly, through a global variable.
	 *
	 * @access  public
	 * @param  int  $product_id   The ID of the product being copied.
     * @return void
	 */
	public function addURIMappingFieldsToProductCopyFieldsArray(int $product_id): void
    {
		global $contents;

		$uri_mapping_input_fields = $this->buildProductCopyURIMappingFields($product_id);

		if ($uri_mapping_input_fields != false) {
			$contents[] = ['text' => $uri_mapping_input_fields];
		}
	}

	// }}}


	// {{{ buildProductCopyURIMappingFields()

	/**
	 * Builds the input fields for copying URI mappings, for a product that is being copied.
	 *
	 * @access  public
	 * @param  int  $product_id   The ID of the product being copied.
	 * @return  string|bool   The source for the input fields or false if no input fields necessary.
	 */
	public function buildProductCopyURIMappingFields(int $product_id): bool|string
    {
		global $languages, $ceon_uri_mapping_product_pages;

		if (!isset($languages) || !is_array($languages)) {
			$languages = zen_get_languages();
		}

		$num_languages = count($languages);

		// Does this product have any existing mappings?
		$product_has_mappings = false;

		$columns_to_retrieve = [
			'uri'
        ];

		$selections = [
			'main_page' => $ceon_uri_mapping_product_pages,
			'associated_db_id' => $product_id
        ];

		$existing_product_uri_mappings_result =
			$this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);

		if (!$existing_product_uri_mappings_result->EOF) {
			$product_has_mappings = true;
		}

		if ($product_has_mappings || $this->_autogenEnabled()) {
			$uri_mapping_input_fields = zen_draw_separator('pixel_black.gif', '0%', '2');

			$uri_mapping_input_fields .= '<h6>' . CEON_URI_MAPPING_TEXT_DUPLICATE_PRODUCT_URI_MAPPING . '</h6>';

			// Default to auto-generating URIs for product's copy
			$autogen_selected = $this->_autogenEnabled();

			$copy_selected = $product_has_mappings && !$this->_autogenEnabled();

			if ($this->_autogenEnabled()) {
				if ($num_languages == 1) {
					$autogen_message = CEON_URI_MAPPING_TEXT_COPY_AUTOGEN_URI;
				} else {
					$autogen_message = CEON_URI_MAPPING_TEXT_COPY_AUTOGEN_URIS;
				}

				$uri_mapping_input_fields .= zen_draw_radio_field('uri-mapping', 'autogen', $autogen_selected) .
					' ' . $autogen_message . '<br>';
			}

			if ($product_has_mappings) {
				if ($num_languages == 1) {
					$copy_message = CEON_URI_MAPPING_TEXT_COPY_EXISTING_MAPPING;
				} else {
					$copy_message = CEON_URI_MAPPING_TEXT_COPY_EXISTING_MAPPINGS;
				}

				$uri_mapping_input_fields .= zen_draw_radio_field('uri-mapping', 'copy', $copy_selected) . ' ' .
					$copy_message . '<br>';
			}

			if ($this->_autogenEnabled() && !$product_has_mappings) {
				if ($num_languages == 1) {
					$ignore_message = CEON_URI_MAPPING_TEXT_COPY_PRODUCT_DONT_AUTOGEN_URI;
				} else {
					$ignore_message = CEON_URI_MAPPING_TEXT_COPY_PRODUCT_DONT_AUTOGEN_URIS;
				}
			} elseif (!$this->_autogenEnabled() && $product_has_mappings) {
				if ($num_languages == 1) {
					$ignore_message = CEON_URI_MAPPING_TEXT_COPY_PRODUCT_DONT_COPY_URI;
				} else {
					$ignore_message = CEON_URI_MAPPING_TEXT_COPY_PRODUCT_DONT_COPY_URIS;
				}
			} else {
				if ($num_languages == 1) {
					$ignore_message = CEON_URI_MAPPING_TEXT_COPY_PRODUCT_DONT_AUTOGEN_COPY_URI;
				} else {
					$ignore_message = CEON_URI_MAPPING_TEXT_COPY_PRODUCT_DONT_AUTOGEN_COPY_URIS;
				}
			}

			$uri_mapping_input_fields .=
				zen_draw_radio_field('uri-mapping', 'ignore', false) . ' ' . $ignore_message;

			$uri_mapping_input_fields .= '</p>';

			return $uri_mapping_input_fields;
		}

		return false;
	}

	/// }}}


	// {{{ copyToConfirmHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when a product is being copied.
	 *
	 * @access  public
	 * @param  int  $product_id_from    The ID of the product being copied from.
	 * @param  int  $product_id_to      The ID of the product being top.
	 * @param  int  $product_type_id    The product's type ID.
	 * @param  string  $product_type       The product's type (not its type ID).
	 * @param  int  $dest_category_id   The ID of the category the product is being copied to.
	 * @return  void
	 */
	public function copyToConfirmHandler(
        int $product_id_from, int $product_id_to, int $product_type_id, string $product_type,
		int $dest_category_id): void
    {
		global /*$db,*/ $messageStack;
		
		// Generate new URI mapping for this new product?
		$this->_uri_mapping_autogen = $_POST['uri-mapping'] == 'autogen';

		// Copy existing URIs from product being copied?
		$uri_mapping_copy = $_POST['uri-mapping'] == 'copy';

		if ($this->_uri_mapping_autogen || $uri_mapping_copy) {
			if ($uri_mapping_copy) {
				// Look up and copy any URI mappings for the product being copied from
				$existing_product_uri_mappings = [];

				$columns_to_retrieve = [
					'language_id',
					'uri'
                ];

				$selections = [
					'main_page' => $product_type . '_info',
					'associated_db_id' => $product_id_from,
					'current_uri' => 1
                ];

				$existing_product_uri_mappings_result =
					$this->getURIMappingsResultset($columns_to_retrieve, $selections);

				while (!$existing_product_uri_mappings_result->EOF) {
					$existing_product_uri_mappings[$existing_product_uri_mappings_result->fields['language_id']] =
						$existing_product_uri_mappings_result->fields['uri'];

					$existing_product_uri_mappings_result->MoveNext();
				}
			}

			// Now insert the URI mappings for the new product into the database
			$languages = zen_get_languages();

			// Build the information for the pages that must have URIs managed
			$product_page_type = $product_type . '_info';

			$page_types = [
				'product_reviews',
				'product_reviews_info',
				'product_reviews_write',
				'tell_a_friend',
				'ask_a_question'
            ];

			$page_types_to_manage = [];

			foreach ($page_types as $page_type) {
				if ($this->autoManageProductRelatedPageURI($page_type)) {
					$page_types_to_manage[] = $page_type;
				}
			}

			for ($i = 0, $n = count($languages); $i < $n; $i++) {
				$uri_mapping = '';

				// Auto-generate the URI if requested
				if ($this->_uri_mapping_autogen) {
					$uri_mapping = $this->autogenProductURIMapping($product_id_to, null, null,
						$languages[$i]['code'], $languages[$i]['id']);

					if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME ||
							$uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_MODEL) {
						// Can't generate the URI because of missing "uniqueness" data

						// Build a link to the product's edit page so the user can fix the problem
						// immediately
						$product_type_admin_handler = $product_type . '.php';

						if (!file_exists(DIR_FS_ADMIN . $product_type_admin_handler) && (PROJECT_VERSION_MAJOR > '1' || version_compare(PROJECT_VERSION_MAJOR . PROJECT_VERSION_MINOR, '1.5.6', '>='))) {
							$product_type_admin_handler = 'product' . '.php';
						}

						$product_edit_link = zen_href_link($product_type_admin_handler, 'pID=' . $product_id_to .
							'&product_type=' . $product_type_id . '&cPath=' . $dest_category_id .
							'&action=new_product', 'NONSSL', true, true, false, false);

						if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME) {
							$message = CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_PRODUCT_HAS_NO_NAME;
						} else {
							$message = CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_PRODUCT_HAS_NO_MODEL;
						}

						$failure_message = sprintf($message, ucwords($languages[$i]['name']), $product_edit_link);

						$messageStack->add_session($failure_message, 'error');

						continue;

					} elseif ($uri_mapping ==
							CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_PATH_PART_WITH_NO_NAME) {
						// Can't generate the URI because of invalid data
						$failure_message = sprintf(
							CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_PRODUCT_CATEGORY_HAS_NO_NAME,
							ucwords($languages[$i]['name']));

						$messageStack->add_session($failure_message, 'error');

						continue;
					}
				} else {
					// Copy any URI mappings for the product be copied from, they'll be changed to be unique
					// shortly (in case admin user forgets to change the URI for the "new" product)
					if (isset($existing_product_uri_mappings[$languages[$i]['id']])) {
						$uri_mapping = $existing_product_uri_mappings[$languages[$i]['id']];
					}
				}

				if (strlen($uri_mapping) > 1) {
					// Make sure URI mapping is relative to root of site and does not have a
					// trailing slash or any illegal characters
					$uri_mapping = $this->_cleanUpURIMapping($uri_mapping);
				}

				if ($uri_mapping != '') {
					// URI mapping does not yet exist
					$uri = $uri_mapping;

					$main_page = $product_page_type;

					// Check that the mapping just generated doesn't clash with any existing mapping
					if (!$this->_uri_mapping_autogen) {
						// Mapping already exists for product being copied from!
						$mapping_clashed = true;
					} else {
						// Autogenerating URI so must check it is a unique URI
						$mapping_clashed = false;

						$columns_to_retrieve = [
							'uri'
                        ];

						$selections = [
							'uri' => zen_db_prepare_input($uri),
							'current_uri' => 1,
							'language_id' => (int) $languages[$i]['id']
                        ];

						$order_by = 'current_uri DESC';

						$existing_uri_mapping_result = $this->getURIMappingsResultset(
							$columns_to_retrieve, $selections, $order_by, 1);

						if (!$existing_uri_mapping_result->EOF) {
							// This mapping clashes with an existing mapping
							$mapping_clashed = true;
						}
					}

					if ($mapping_clashed) {
						if (!$this->_uri_mapping_autogen || $this->_mappingClashAutoAppendInteger()) {
							// Attempt to find a unique variation of the mapping by appending an auto-incrementing
							// integer
							$uniqueness_integer = 1;

							$base_uri = $uri;

							$unique_mapping_found = false;

							while (!$unique_mapping_found) {
								$uri = $base_uri . $uniqueness_integer;

								$columns_to_retrieve = [
									'main_page',
									'associated_db_id'
                                ];

								$selections = [
									'uri' => zen_db_prepare_input($uri),
									'current_uri' => 1,
									'language_id' => (int) $languages[$i]['id']
                                ];

								$order_by = 'current_uri DESC';

								$existing_uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve,
									$selections, $order_by, 1);

								if ($existing_uri_mapping_result->EOF) {
									// This variation of the mapping doesn't clash with an existing mapping, use
									// it!
									$unique_mapping_found = true;
								}

								$uniqueness_integer++;
							}
						} else {
							// No attempt should be made to create a unique variation of the mapping, instead the
							// user must be warned that the mapping was not created for this copied product

							// Build a link to the product's edit page so the user can fix the problem immediately
							$product_type_admin_handler = $product_type . '.php';

							if (!file_exists(DIR_FS_ADMIN . $product_type_admin_handler) && (PROJECT_VERSION_MAJOR > '1' || version_compare(PROJECT_VERSION_MAJOR . PROJECT_VERSION_MINOR, '1.5.6', '>='))) {
								$product_type_admin_handler = 'product' . '.php';
							}

							$product_edit_link = zen_href_link($product_type_admin_handler, 'pID=' .
								$product_id_to . '&product_type=' . $product_type_id . '&cPath=' .
								$dest_category_id . '&action=new_product', 'NONSSL', true, true, false, false);

							$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_COPY_PRODUCT_MAPPING_CLASHED,
								ucwords($languages[$i]['name']), '<a href="' . HTTP_SERVER . $uri .
								'" target="_blank">' . $uri . '</a>', ucwords($languages[$i]['name']),
								$product_edit_link);

							$messageStack->add_session($failure_message, 'error');

							continue;
						}
					}

					$mapping_added =
						$this->addURIMapping($uri, $languages[$i]['id'], $main_page, null, $product_id_to);

					if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
						if ($this->_uri_mapping_autogen && !$mapping_clashed) {
							$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED,
								ucwords($languages[$i]['name']), '<a href="' . HTTP_SERVER . $uri .
								'" target="_blank">' . $uri . '</a>');
						} else {
							// Build a link to the product's edit page so the user can change the auto-appended URI
							// immediately
							$product_type_admin_handler = $product_type . '.php';

							if (!file_exists(DIR_FS_ADMIN . $product_type_admin_handler) && (PROJECT_VERSION_MAJOR > '1' || version_compare(PROJECT_VERSION_MAJOR . PROJECT_VERSION_MINOR, '1.5.6', '>='))) {
								$product_type_admin_handler = 'product' . '.php';
							}

							$product_edit_link = zen_href_link($product_type_admin_handler, 'pID=' .
								$product_id_to . '&product_type=' . $product_type_id . '&cPath=' .
								$dest_category_id . '&action=new_product', 'NONSSL', true, true, false, false);

							// Message differs according to whether the auto-append setting was used
							// willingly or enforced
							if ($this->_mappingClashAutoAppendInteger()) {
								$message = CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED_WITH_AUTO_APPEND;
							} else {
								$message = CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED_ENFORCED_AUTO_APPEND;
							}

							$success_message = sprintf($message, ucwords($languages[$i]['name']),
								'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>',
								$product_edit_link);
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

					// Now add the URI mappings for the review pages/tell-a-friend page for this product
					$base_uri = $uri . '/';

					// Get the language code for the mapping's language
					$language_code = strtolower($languages[$i]['code']);

					foreach ($page_types_to_manage as $page_type) {
						$uri_part = $this->getProductRelatedPageURIPart($page_type, $language_code);

						if ($uri_part === false) {
							// Unexpected database problem encountered
							continue;
						}

						$uri_part = $this->_convertStringForURI($uri_part, $language_code);

						$uri = $base_uri . $uri_part;

						$main_page = constant('FILENAME_' . strtoupper($page_type));

						$mapping_added =
							$this->addURIMapping($uri, $languages[$i]['id'], $main_page, ($page_type == 'ask_a_question' ? 'pid=' . $product_id_to : null), $product_id_to);

						if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
							$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED,
								ucwords($languages[$i]['name']),
								'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');

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
					}
				}
			}
		}
	}

	// }}}


	// {{{ addURIMappingFieldsToProductMoveFieldsArray()

	/**
	 * Add the fields necessary for the Ceon URI Mapping options to the list of product move fields, accessing
	 * the list of product move fields directly, through a global variable.
	 *
	 * @access  public
	 * @param  int  $product_id   The ID of the product being moved.
	 * @return  void
	 */
	public function addURIMappingFieldsToProductMoveFieldsArray(int $product_id): void
    {
		global $contents;

		$uri_mapping_input_fields = $this->buildProductMoveURIMappingFields($product_id);

		if ($uri_mapping_input_fields != false) {
			$contents[] = ['text' => $uri_mapping_input_fields];
		}
	}

	// }}}


	// {{{ buildProductMoveURIFields()

	/**
	 * Builds the input fields for dealing with URI mappings for a product being moved.
	 *
	 * @access  public
	 * @param  int  $product_id   The ID of the product being moved.
	 * @return  string|bool   The source for the input fields or false if no input fields necessary.
	 */
	public function buildProductMoveURIMappingFields(int $product_id): bool|string
    {
		global /*$db,*/ $languages, $ceon_uri_mapping_product_pages;

		if (!isset($languages) || !is_array($languages)) {
			$languages = zen_get_languages();
		}

		$num_languages = count($languages);

		// Does this product have any existing mappings?
		$product_has_mappings = false;

		$columns_to_retrieve = [
			'uri'
        ];

		$selections = [
			'main_page' => $ceon_uri_mapping_product_pages,
			'associated_db_id' => $product_id
        ];

		$existing_product_uri_mappings_result =
			$this->getURIMappingsResultset($columns_to_retrieve, $selections, null, 1);

		if (!$existing_product_uri_mappings_result->EOF) {
			$product_has_mappings = true;
		}

		if ($product_has_mappings || $this->_autogenEnabled()) {
			$uri_mapping_input_fields = zen_draw_separator('pixel_black.gif', '100%', '2');

			$uri_mapping_input_fields .= '<p>' . CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_URI_MAPPING .
				'<br>';

			// Default to auto-generating URIs for product's new location
			$autogen_selected = $this->_autogenEnabled();
			$move_selected = $product_has_mappings && !$this->_autogenEnabled();

			if ($this->_autogenEnabled()) {
				if ($num_languages == 1) {
					$autogen_message = CEON_URI_MAPPING_TEXT_MOVE_AUTOGEN_URI;
				} else {
					$autogen_message = CEON_URI_MAPPING_TEXT_MOVE_AUTOGEN_URIS;
				}
				$uri_mapping_input_fields .= zen_draw_radio_field('uri-mapping', 'autogen', $autogen_selected) .
					' ' . $autogen_message . '<br>';
			}

			if ($product_has_mappings) {
				if ($num_languages == 1) {
					$move_message = CEON_URI_MAPPING_TEXT_MOVE_USE_EXISTING_MAPPING;
				} else {
					$move_message = CEON_URI_MAPPING_TEXT_MOVE_USE_EXISTING_MAPPINGS;
				}
				$uri_mapping_input_fields .= zen_draw_radio_field('uri-mapping', 'use-existing', $move_selected) .
					' ' . $move_message . '<br>';
			}

			if ($this->_autogenEnabled() && !$product_has_mappings) {
				if ($num_languages == 1) {
					$ignore_message = CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_DONT_AUTOGEN_URI;
				} else {
					$ignore_message = CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_DONT_AUTOGEN_URIS;
				}
			} elseif (!$this->_autogenEnabled() && $product_has_mappings) {
				if ($num_languages == 1) {
					$ignore_message = CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_DONT_MOVE_URI;
				} else {
					$ignore_message = CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_DONT_MOVE_URIS;
				}
			} else {
				if ($num_languages == 1) {
					$ignore_message = CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_DONT_AUTOGEN_MOVE_URI;
				} else {
					$ignore_message = CEON_URI_MAPPING_TEXT_MOVE_PRODUCT_DONT_AUTOGEN_MOVE_URIS;
				}
			}

			$uri_mapping_input_fields .=
				zen_draw_radio_field('uri-mapping', 'drop-existing', false) . ' ' . $ignore_message;

			$uri_mapping_input_fields .= '</p>';

			$uri_mapping_input_fields .= zen_draw_separator('pixel_black.gif', '100%', '2');

			return $uri_mapping_input_fields;
		}

		return false;
	}

	/// }}}


	// {{{ moveProductConfirmHandler()

	/**
	 * Handles the Ceon URI Mapping functionality when a product is being moved.
	 *
	 * @access  public
	 * @param  int  $product_id         The ID of the product being moved.
	 * @param  int  $product_type_id    The product's type ID.
	 * @param  string  $product_type    The product's type (not its type ID).
	 * @param  int  $dest_category_id   The ID of the category the product is being moved to.
	 * @return  void
	 */
	public function moveProductConfirmHandler(int $product_id, int $product_type_id, string $product_type, int $dest_category_id): void
    {
		global $messageStack;

		$languages = zen_get_languages();
		
		// Generate new URI mapping for this product?
		$this->_uri_mapping_autogen = $_POST['uri-mapping'] == 'autogen';

		// Drop existing URIs?
		$uri_mapping_drop_existing = $_POST['uri-mapping'] == 'drop-existing';

		if (!$this->_uri_mapping_autogen && !$uri_mapping_drop_existing) {
			// Nothing to be done!
			return;
		}

		// Build the information for the pages that must have URIs managed
		$product_page_type = $product_type . '_info';

		$page_types = [
			'product_reviews',
			'product_reviews_info',
			'product_reviews_write',
			'tell_a_friend',
			'ask_a_question'
        ];

		$page_types_to_manage = [];

		foreach ($page_types as $page_type) {
			if ($this->autoManageProductRelatedPageURI($page_type)) {
				$page_types_to_manage[] = $page_type;
			}
		}

		if ($this->_uri_mapping_autogen) {
			// Generate the new URI mappings for the product
			for ($i = 0, $n = count($languages); $i < $n; $i++) {
				// Mark any existing URI mapping for the product as no longer being the "primary" mapping, leaving
				// it in the database so old links aren't broken.
				$columns_to_retrieve = [
					'uri'
                ];

				$selections = [
					'main_page' => $product_page_type,
					'associated_db_id' => $product_id,
					'language_id' => (int) $languages[$i]['id'],
					'current_uri' => 1,
                ];

				$order_by = 'current_uri DESC';

				$current_uri_mapping_result =
					$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

				if (!$current_uri_mapping_result->EOF) {
					$this->makeURIMappingHistorical($current_uri_mapping_result->fields['uri'],
						$languages[$i]['id']);
				}

				//$uri_mapping = '';

				$uri_mapping = $this->autogenProductURIMapping($product_id, null, null, $languages[$i]['code'],
					$languages[$i]['id']);

				if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME ||
						$uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_MODEL) {
					// Can't generate the URI because of missing "uniqueness" data

					// Build a link to the product's edit page so the user can fix the problem immediately
					$product_type_admin_handler = $product_type . '.php';

					$product_edit_link = zen_href_link($product_type_admin_handler, 'pID=' . $product_id .
						'&product_type=' . $product_type_id . '&cPath=' . $dest_category_id .
						'&action=new_product', 'NONSSL', true, true, false, false);

					if ($uri_mapping == CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME) {
						$message = CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_PRODUCT_HAS_NO_NAME;
					} else {
						$message = CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_PRODUCT_HAS_NO_MODEL;
					}

					$failure_message = sprintf($message, ucwords($languages[$i]['name']),
						$product_edit_link);

					$messageStack->add_session($failure_message, 'error');

					continue;

				} elseif ($uri_mapping ==
						CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_PATH_PART_WITH_NO_NAME) {
					// Can't generate the URI because of invalid data
					$failure_message = sprintf(
						CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_PRODUCT_CATEGORY_HAS_NO_NAME,
						ucwords($languages[$i]['name']));

					$messageStack->add_session($failure_message, 'error');

					continue;
				}

				// Make sure URI mapping is relative to root of site and does not have a trailing slash or any
				// illegal characters
				$uri_mapping = $this->_cleanUpURIMapping($uri_mapping);

				if ($uri_mapping != '') {
					// URI mapping no longer exists for this newly moved product
					$uri = $uri_mapping;

					$main_page = $product_page_type;

					// Check that the mapping just generated doesn't clash with any existing mapping
					$mapping_clashed = false;

					$columns_to_retrieve = [
						'uri'
                    ];

					$selections = [
						'uri' => zen_db_prepare_input($uri),
						'current_uri' => 1,
						'language_id' => (int) $languages[$i]['id']
                    ];

					$order_by = 'current_uri DESC';

					$existing_uri_mapping_result =
						$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

					if (!$existing_uri_mapping_result->EOF) {
						// This mapping clashes with an existing mapping
						$mapping_clashed = true;
					}

					if ($mapping_clashed) {
						if ($this->_mappingClashAutoAppendInteger()) {
							// Attempt to find a unique variation of the mapping by appending an
							// auto-incrementing integer
							$uniqueness_integer = 1;

							$base_uri = $uri;

							$unique_mapping_found = false;

							while (!$unique_mapping_found) {
								$uri = $base_uri . $uniqueness_integer;

								$columns_to_retrieve = [
									'uri'
                                ];

								$selections = [
									'uri' => zen_db_prepare_input($uri),
									'current_uri' => 1,
									'language_id' => (int) $languages[$i]['id']
                                ];

								$order_by = 'current_uri DESC';

								$existing_uri_mapping_result = $this->getURIMappingsResultset($columns_to_retrieve,
									$selections, $order_by, 1);

								if ($existing_uri_mapping_result->EOF) {
									// This variation of the mapping doesn't clash with an existing
									// mapping, use it!
									$unique_mapping_found = true;
								}

								$uniqueness_integer++;
							}
						} else {
							// No attempt should be made to create a unique variation of the mapping, instead the
							// user must be warned that the mapping was not created for this moved product

							// Build a link to the product's edit page so the user can fix the problem immediately
							$product_type_admin_handler = $product_type . '.php';

							$product_edit_link = zen_href_link($product_type_admin_handler, 'pID=' . $product_id .
								'&product_type=' . $product_type_id . '&cPath=' . $dest_category_id .
								'&action=new_product', 'NONSSL', true, true, false, false);

							$failure_message = sprintf(CEON_URI_MAPPING_TEXT_ERROR_MOVE_PRODUCT_MAPPING_CLASHED,
								ucwords($languages[$i]['name']), '<a href="' . HTTP_SERVER . $uri .
								'" target="_blank">' . $uri . '</a>', ucwords($languages[$i]['name']),
								$product_edit_link);

							$messageStack->add_session($failure_message, 'error');

							continue;
						}
					}

					$mapping_added =
						$this->addURIMapping($uri, $languages[$i]['id'], $main_page, null, $product_id);

					if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
						if (!$mapping_clashed) {
							$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED,
								ucwords($languages[$i]['name']), '<a href="' . HTTP_SERVER . $uri .
								'" target="_blank">' . $uri . '</a>');
						} else {
							// Build a link to the product's edit page so the user can change the auto-appended URI
							// immediately
							$product_type_admin_handler = $product_type . '.php';

							$product_edit_link = zen_href_link($product_type_admin_handler, 'pID=' . $product_id .
								'&product_type=' . $product_type_id . '&cPath=' . $dest_category_id .
								'&action=new_product', 'NONSSL', true, true, false, false);

							$success_message = sprintf(
								CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED_WITH_AUTO_APPEND,
								ucwords($languages[$i]['name']),
								'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>',
								$product_edit_link);
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

					// Now add the URI mappings for the review pages/tell-a-friend page for this product
					$base_uri = $uri . '/';

					// Get the language code for the mapping's language
					$language_code = strtolower($languages[$i]['code']);

					foreach ($page_types_to_manage as $page_type) {
						// Mark any existing URI mapping for this product related page as no longer being the
						// "primary" mapping, leaving it in the database so old links aren't broken.
						$columns_to_retrieve = [
							'uri'
                        ];

						$selections = [
							'main_page' => $page_type,
							'associated_db_id' => $product_id,
							'language_id' => (int) $languages[$i]['id'],
							'current_uri' => 1,
                        ];

						$order_by = 'current_uri DESC';

						$current_uri_mapping_result =
							$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

						if (!$current_uri_mapping_result->EOF) {
							$this->makeURIMappingHistorical($current_uri_mapping_result->fields['uri'],
								$languages[$i]['id']);
						}

						$uri_part = $this->getProductRelatedPageURIPart($page_type, $language_code);

						if ($uri_part === false) {
							// Unexpected database problem encountered
							continue;
						}

						$uri_part = $this->_convertStringForURI($uri_part, $language_code);

						$uri = $base_uri . $uri_part;

						$main_page = constant('FILENAME_' . strtoupper($page_type));

						$mapping_added = $this->addURIMapping($uri, $languages[$i]['id'],
							$main_page, ($page_type == 'ask_a_question' ? 'pid=' . $product_id : null), $product_id);

						if ($mapping_added == CEON_URI_MAPPING_ADD_MAPPING_SUCCESS) {
							$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_ADDED,
								ucwords($languages[$i]['name']),
								'<a href="' . HTTP_SERVER . $uri . '" target="_blank">' . $uri . '</a>');

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
					}
				}
			}
		} elseif ($uri_mapping_drop_existing) {
			// Mark any existing URI mappings for this product and its related pages as no longer being the
			// "primary" mappings, leaving them in the database so old links aren't broken.
			for ($i = 0, $n = count($languages); $i < $n; $i++) {
				$columns_to_retrieve = [
					'uri'
                ];

				$selections = [
					'main_page' => $product_page_type,
					'associated_db_id' => $product_id,
					'language_id' => (int) $languages[$i]['id'],
					'current_uri' => 1,
                ];

				$order_by = 'current_uri DESC';

				$current_uri_mapping_result =
					$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

				if (!$current_uri_mapping_result->EOF) {
					$this->makeURIMappingHistorical($current_uri_mapping_result->fields['uri'],
						$languages[$i]['id']);

					$success_message = sprintf(CEON_URI_MAPPING_TEXT_PRODUCT_MAPPING_MADE_HISTORICAL,
						ucwords($languages[$i]['name']));

					$messageStack->add_session($success_message, 'caution');
				}

				foreach ($page_types_to_manage as $page_type) {
					$columns_to_retrieve = [
						'uri'
                    ];

					$selections = [
						'main_page' => $page_type,
						'associated_db_id' => $product_id,
						'language_id' => (int) $languages[$i]['id'],
						'current_uri' => 1,
                    ];

					$order_by = 'current_uri DESC';

					$current_uri_mapping_result =
						$this->getURIMappingsResultset($columns_to_retrieve, $selections, $order_by, 1);

					if (!$current_uri_mapping_result->EOF) {
						$prp_uri_mapping = $current_uri_mapping_result->fields['uri'];

						$this->makeURIMappingHistorical($prp_uri_mapping, $languages[$i]['id']);

						$success_message = sprintf(
							CEON_URI_MAPPING_TEXT_PRODUCT_RELATED_PAGE_MAPPING_MADE_HISTORICAL,
							ucwords($languages[$i]['name']), $prp_uri_mapping);

						$messageStack->add_session($success_message, 'caution');
					}
				}
			}
		}
	}

	// }}}
}

// }}}
