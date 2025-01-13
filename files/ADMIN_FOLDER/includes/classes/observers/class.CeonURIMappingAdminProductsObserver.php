<?php

/**
 * Observer for Ceon URI Mapping admin/products.php code
 * Watches admin/products.php
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2020 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://ceon.net
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     class.CeonURIMappingAdminProductsObserver.php 2025-01-08 torvista
 */
class CeonURIMappingAdminProductsObserver extends base
{
		public function __construct()
		{
				$this->attach($this, ['NOTIFY_BEGIN_ADMIN_PRODUCTS']);
		}

		public function update(&$callingClass, $notifier, $action): void
		{
//steve, why was this done?
				if (!defined('CEON_URI_MAPPING_ENABLED') || CEON_URI_MAPPING_ENABLED === '0') {
						return;
				}
				if (!isset($action)) {
						return;
				}


				// new_product perform (acts on collect_info.php)
				//   Adds $ceon_uri_mapping_admin to global space
				if (($action == 'new_product' || $action == 'update_product' || ($action == 'insert_product' && empty($_GET['pID'])))) {

						// BEGIN CEON URI MAPPING 1 of 1
						require_once DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php';

						$GLOBALS['ceon_uri_mapping_admin'] = new CeonURIMappingAdminProductPages();

						$GLOBALS['ceon_uri_mapping_admin']->collectInfoHandler();

						// END CEON URI MAPPING 1 of 1

					//  Can't capture updated information until after it has been processed.  Best opportunity is to
					//    grab the information on redirect.
					//        if (defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED == 1 && isset($action) && $action == 'update_category') {
										// The categories_id and products_id are both known because this is an update of an existing product
					/*          if (isset($_POST['categories_id'])) {
												$categories_id = zen_db_prepare_input($_POST['categories_id']);
										}

										require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');

										$GLOBALS['ceon_uri_mapping_admin'] = new CeonURIMappingAdminCategoryPages();

										$GLOBALS['ceon_uri_mapping_admin']->insertUpdateHandler((int)$categories_id, (int)$GLOBALS['current_category_id']);
										*/
				}

				// new_product_preview perform
				//   Adds $ceon_uri_mapping_admin to global space

				if ($action == 'new_product_preview') {

                    require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');

						$GLOBALS['ceon_uri_mapping_admin'] = new CeonURIMappingAdminProductPages();

						if (zen_not_null($_POST)) {
								$GLOBALS['ceon_uri_mapping_admin']->productPreviewProcessSubmission((int)$GLOBALS['current_category_id']);

								// END CEON URI MAPPING 1 of 4
						} else {
								$GLOBALS['ceon_uri_mapping_admin']->productPreviewInitialLoad((int) $_GET['pID'],
									$GLOBALS['zc_products']->get_handler((int) ($_POST['product_type'] ?? (isset($_GET['pID']) ? zen_get_products_type($_GET['pID']) : 1))));

								// END CEON URI MAPPING 2 of 4
						}
				} // EOF of the action for categories file.

				// insert_product or update_product initialize
				// Detect that planning to insert or update a product and establish a sesion value for page change.
				if ($action == 'insert_product' || ($action == 'update_product'  && (empty($_POST['edit']) || $_POST['edit'] != 'edit'))) {

						unset($_SESSION['ceon_uri_mapping_' . $_GET['action']]);
						if ($action == 'update_product') {
								unset($_SESSION['ceon_uri_mapping_insert_product']);
						}
						if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
						//  $action = 'new_product';
						} elseif (($_POST['products_model'] ?? '') . (isset($_POST['products_url']) ? implode('', $_POST['products_url']) : '') . (isset($_POST['products_name']) ? implode('', $_POST['products_name']) : '') . (isset($_POST['products_description']) ? implode('', $_POST['products_description']) : '') != '') {
								$_SESSION['ceon_uri_mapping_' . $_GET['action']] = $_POST;
						}
				}

				// Identify that expectation is that the category is inserted or updated.
				if (($action == 'insert_category' || $action == 'update_category')) {
						// Set a session variable here to that of the current_category_id, because the final $categories_id is not known until the end of execution
						//   and redirection to the next page.
						$_SESSION['ceon_uri_mapping_' . $action] = [];
						$_SESSION['ceon_uri_mapping_' . $action]['current_category_id'] = $GLOBALS['current_category_id'];
						$_SESSION['ceon_uri_mapping_' . $action]['post'] = $_POST;
				}

				// Make class functions available to the global space for execution.
				if (($action == 'new_category' || $action == 'edit_category')) {
/*
				if ($action == 'new_category' || $action == 'edit_category') {
						// BEGIN CEON URI MAPPING 2 of 4
						require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');

						$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
						// END CEON URI MAPPING 2 of 4
*/
						// BEGIN CEON URI MAPPING 2 of 4
						require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');

						$GLOBALS['ceon_uri_mapping_admin'] = new CeonURIMappingAdminCategoryPages();
						// END CEON URI MAPPING 2 of 4
				}
		}
}
