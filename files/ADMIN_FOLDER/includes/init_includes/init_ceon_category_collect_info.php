<?php
/**
 * @package Ceon URI Mapping
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @author Ceon Support
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * $Id: init_ceon_category_collect_info.php 2025-01-08 torvista
 */


// Code processed directly in/by the admin/categories.php file is handled by the
//   associated observer for the categories file.

// insert_category and/or update_category perform
// Take action when a redirect occurs and previously detected that going to insert or update a category
//   zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') &&
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (! str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) &&
    (isset($_SESSION['ceon_uri_mapping_insert_category']) || isset($_SESSION['ceon_uri_mapping_update_category']))) {
		$categories_id = isset($_GET['cID']) ? (int)$_GET['cID'] : 0;
		$current_category_id = (int)(!empty($_SESSION['ceon_uri_mapping_insert_category']) ? $_SESSION['ceon_uri_mapping_insert_category']['current_category_id'] : $_SESSION['ceon_uri_mapping_update_category']['current_category_id']);
//      zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $categories_id . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')));
// categories was the calling file redirecting indicating that need to store data.
//  1 of 3 of the categories file.

// action to be taken if the above redirect occurs and the conditions to get
//  to the categories file support taking action.
// notifier
//  $zco_notifier->notify('NOTIFY_BEGIN_ADMIN_CATEGORIES', $action);
		$_POST = !empty($_SESSION['ceon_uri_mapping_insert_category']) ? $_SESSION['ceon_uri_mapping_insert_category']['post'] : $_SESSION['ceon_uri_mapping_update_category']['post'];

		if (!isset($languages)) {
			$languages = zen_get_languages();
			$ceon_unset_languages = true;
		}

		unset($_SESSION['ceon_uri_mapping_insert_category']);
		unset($_SESSION['ceon_uri_mapping_update_category']);

		// BEGIN CEON URI MAPPING 1 of 3
		require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');

		$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();

		$ceon_uri_mapping_admin->insertUpdateHandler($categories_id, $current_category_id);
		// END CEON URI MAPPING 1 of 3

		if (isset($ceon_unset_languages) && $ceon_unset_languages) {
			unset($languages);
			unset($ceon_unset_languages);
		}

		zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, zen_get_all_get_params()));
}

// Move_category_confirm perform
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') &&
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (! str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) &&
    isset($_SESSION['ceon_uri_mapping_move_category_confirm'])) {
		$new_parent_id = $_GET['cPath'] ?? 0;
		$current_category_id = !empty($_SESSION['ceon_uri_mapping_move_category']) ? $_SESSION['ceon_uri_mapping_insert_category']['current_category_id'] : 0;
//      zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $categories_id . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')));
// categories was the calling file redirecting indicating that need to store data.
//  1 of 3 of the categories file.

// action to be taken if the above redirect occurs and the conditions to get
//  to the categories file support taking action.
// notifier
//  $zco_notifier->notify('NOTIFY_BEGIN_ADMIN_CATEGORIES', $action);
		$_POST = !empty($_SESSION['ceon_uri_mapping_move_category_confirm']) ? $_SESSION['ceon_uri_mapping_move_category_confirm'] : $_POST;

    if (!(isset($_POST['categories_id']) && ($_POST['categories_id'] != $_POST['move_to_category_id']))) {
			unset($_SESSION['ceon_uri_mapping_move_category']);
			zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, zen_get_all_get_params()));
		}

		if (!isset($languages)) {
			$languages = zen_get_languages();
			$ceon_unset_languages = true;
		}

		$categories_id = zen_db_prepare_input($_POST['categories_id']);
		$new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);

		unset($_SESSION['ceon_uri_mapping_move_category_confirm']);

		// BEGIN CEON URI MAPPING 1 of 3
		require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');

		$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();

//		$ceon_uri_mapping_admin->insertUpdateHandler($categories_id, $current_category_id);
		// END CEON URI MAPPING 1 of 3

		if (isset($ceon_unset_languages) && $ceon_unset_languages) {
			unset($languages);
			unset($ceon_unset_languages);
		}

		zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, zen_get_all_get_params()));
}

// move_category_confirm initialize
//		Because the operation will redirect at completion, want to identify if
//		an action had been identified as to be done so that can be triggered at the
//		next redirect.
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') &&
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (! str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) &&
    isset($_GET['action']) && $_GET['action'] == 'move_category_confirm') {
		unset($_SESSION['ceon_uri_mapping_move_category_confirm']);

		// Need to set session if the conditions to perform the operation are correct.
		if (isset($_POST['categories_id']) && ($_POST['categories_id'] != $_POST['move_to_category_id'])) {
			$ceon_categories_id = zen_db_prepare_input($_POST['categories_id']);
			$ceon_new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);

			$ceon_path = explode('_', zen_get_generated_category_path_ids($ceon_new_parent_id));

			if (!in_array($ceon_categories_id, $ceon_path)) {
				$_SESSION['ceon_uri_mapping_move_category_confirm'] = $_POST;
			}
		}
}
