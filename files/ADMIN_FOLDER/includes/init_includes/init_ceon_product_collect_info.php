<?php
/**
 * @package Ceon URI Mapping
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @author Ceon Support
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * $Id: init_ceon_product_collect_info.php xxxx 2019-01-02 20:31:10Z $
 */

// copy_product_confirm perform
// This is acted upon when forwarding out of the copy_to_confirm file.
//  zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
// THIS CODE DOES NOT NEED TO BE IN THE GLOBAL SPACE, BUT IT NEEDS TO ACT ACCORDINGLY.
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && (isset($_SESSION['ceon_uri_mapping_copy_product_confirm']) )) {
	
	if (!empty($CeonURIMappingAdminCopy_Observe->ceon_uri_mapping_copy_product_confirmed)) {
		unset($CeonURIMappingAdminCopy_Observe->ceon_uri_mapping_copy_product_confirmed);
		unset($_SESSION['ceon_uri_mapping_copy_product_confirm']);
		return;
	}
	$_POST = $_SESSION['ceon_uri_mapping_copy_product_confirm'];
	$products_id_from = (int)zen_db_prepare_input($_POST['products_id']);
	
	$categories_id = !empty($_GET['cPath']) ? (int)$_GET['cPath'] : 0;
	$products_id_to = !empty($_GET['pID']) ? (int)$_GET['pID'] : 0;
	
	$sql = "select products_type from " . TABLE_PRODUCTS . " where products_id=" . (int)$products_id_to;
	$product = $db->Execute($sql);
	
	unset($_SESSION['ceon_uri_mapping_copy_product_confirm']);
	
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
	
	$ceon_uri_mapping_admin->copyToConfirmHandler($products_id_from, $products_id_to,
		$product->fields['products_type'], $zc_products->get_handler($product->fields['products_type']),
		$categories_id);
	
	zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, zen_get_all_get_params()));
} // EOF of the action for category product listing file.

// copy_product_confirm initialize
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && isset($_GET['action']) && $_GET['action'] == 'copy_product_confirm') {

// This information is to be captured on the front end
	unset($_SESSION['ceon_uri_mapping_copy_product_confirm']);
	
	if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
// Copy attributes to duplicate product
//					$products_id_from=$products_id;
		if ($_POST['copy_as'] == 'duplicate') {
			// created a new product that now has products_id of $dup_products_id, so need
			// to find the new product_id that has information matching the old.
					// The $products_id on the uri is equal to $dup_products_id or $products_id_to
		//              $products_id_to= $dup_products_id;
			$_SESSION['ceon_uri_mapping_copy_product_confirm'] = $_POST;
		}
	}
}

// insert_product and/or update_product perform
// Take action when a redirect occurs and previously detected that going to insert or update a product
//   zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && (isset($_SESSION['ceon_uri_mapping_insert_product']) || isset($_SESSION['ceon_uri_mapping_update_product']))) {
		
	// BEGIN CEON URI MAPPING 1 of 1
	$products_id = (int)$_GET['pID'];
	$_POST = !empty($_SESSION['ceon_uri_mapping_insert_product']) ? $_SESSION['ceon_uri_mapping_insert_product'] : $_SESSION['ceon_uri_mapping_update_product'];
	$product_type = (int)$_POST['product_type'];
	
	if (!isset($languages)) {
		$languages = zen_get_languages();
		$ceon_unset_languages = true;
	}
	
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
	
	$ceon_uri_mapping_admin->updateProductHandler($products_id, $zc_products->get_handler($product_type));
	
	if (isset($ceon_unset_languages) && $ceon_unset_languages) {
		unset($languages);
		unset($ceon_unset_languages);
	}
	
	unset($_SESSION['ceon_uri_mapping_insert_product']);
	unset($_SESSION['ceon_uri_mapping_update_product']);
	
	zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, zen_get_all_get_params()));
}


// notifier
//  $zco_notifier->notify('NOTIFY_BEGIN_ADMIN_CATEGORIES', $action);

// move_product_confirm perform
// zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $new_parent_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && isset($_SESSION['ceon_uri_mapping_move_product_confirm'])) {

	$new_parent_id = $_GET['cPath'];
	$products_id = (int)$_GET['pID'];
	$_POST = $_SESSION['ceon_uri_mapping_move_product_confirm'];
	
	// BEGIN CEON URI MAPPING 1 of 1
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
	
	$ceon_uri_mapping_admin->moveProductConfirmHandler($products_id, zen_get_products_type($products_id), $zc_products->get_handler(zen_get_products_type($products_id)), $new_parent_id); //torvista https://github.com/torvista/CEON-URI-Mapping/pull/3/files#diff-0	
	
	// END CEON URI MAPPING 1 of 1
	unset($_SESSION['ceon_uri_mapping_move_product_confirm']);
	
	zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, zen_get_all_get_params()));
}

// move_product_confirm initialize
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && isset($_GET['action']) && $_GET['action'] == 'move_product_confirm') {
	
	unset($_SESSION['ceon_uri_mapping_move_product_confirm']);
	
	$products_id = zen_db_prepare_input($_POST['products_id']);
	$new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);
	
	$duplicate_check = $db->Execute("SELECT COUNT(*) AS total
																	FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
																	WHERE products_id = " . (int)$products_id . "
																	AND categories_id = " . (int)$new_parent_id);
	
	if ($duplicate_check->fields['total'] < 1) {
		$_SESSION['ceon_uri_mapping_move_product_confirm'] = $_POST;
	}
}

