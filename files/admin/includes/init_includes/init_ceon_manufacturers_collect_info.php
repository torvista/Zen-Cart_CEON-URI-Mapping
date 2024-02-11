<?php
/**
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://ceon.net
 * $Id: init_ceon_manufacturers_collect_info.php xxxx 2016-11-14 20:31:10Z Ceon Support $
 */


if (defined('FILENAME_MANUFACTURERS') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && isset($_GET['action']) && $_GET['action'] == 'deleteconfirm') {

	if (!function_exists('zen_admin_demo') || !zen_admin_demo()) {
		$manufacturers_id = zen_db_prepare_input($_POST['mID']);
		
		// BEGIN CEON URI MAPPING 2 of 4
		require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminManufacturerPages.php');
		
		$ceon_uri_mapping_admin = new CeonURIMappingAdminManufacturerPages();
		
		$ceon_uri_mapping_admin->deleteConfirmHandler((int) $manufacturers_id);
		
		// END CEON URI MAPPING 2 of 4      
		// don't redirect as this delete is to occur before the redirect of the manufacturers.php page.
	}
} // EOF of the action for categories file.

// $_SESSION['ceon_uri_mapping_manufacturers_insert'] || $_SESSION['ceon_uri_mapping_manufacturers_save']
//   Either is set identifying that such a request was made.
if (defined('FILENAME_MANUFACTURERS') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && (isset($_SESSION['ceon_uri_mapping_manufacturers_insert']) || isset($_SESSION['ceon_uri_mapping_manufacturers_save']))) {
	$_POST = !empty($_SESSION['ceon_uri_mapping_manufacturers_insert']) ? $_SESSION['ceon_uri_mapping_manufacturers_insert'] : $_SESSION['ceon_uri_mapping_manufacturers_save'];
	
	unset($_SESSION['ceon_uri_mapping_manufacturers_insert']);
	unset($_SESSION['ceon_uri_mapping_manufacturers_save']);
	
	$manufacturers_id = isset($_GET['mID']) ? (int)$_GET['mID'] : 0;
	$manufacturers_name = zen_db_prepare_input($_POST['manufacturers_name']);
	
	// BEGIN CEON URI MAPPING 1 of 4
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminManufacturerPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminManufacturerPages();
	
	$ceon_uri_mapping_admin->insertSaveHandler((int) $manufacturers_id, $manufacturers_name);
	
	// END CEON URI MAPPING 1 of 4
	
	// Really should just redirect to the same uri that currently have...  Will reduce the amount of work in the future.
	zen_redirect(zen_href_link(FILENAME_MANUFACTURERS, zen_get_all_get_params()));
}

if (defined('FILENAME_MANUFACTURERS') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && isset($_GET['action']) && ($_GET['action'] == 'insert' || $_GET['action'] == 'save')) {
	
	unset($_SESSION['ceon_uri_mapping_manufacturers_insert']);
	unset($_SESSION['ceon_uri_mapping_manufacturers_save']);
	
	// SET SESSION VARIABLE AWAITING REDIRECT. WHERE
	//	zen_redirect(zen_href_link(FILENAME_MANUFACTURERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $manufacturers_id));
	$_SESSION['ceon_uri_mapping_manufacturers_' . $_GET['action']] = $_POST;
}

