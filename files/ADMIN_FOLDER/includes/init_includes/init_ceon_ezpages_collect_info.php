<?php
/**
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://ceon.net
 * $Id: init_ceon_ezpages_collect_info.php  2025-01-08 torvista
 */


if (defined('FILENAME_EZPAGES_ADMIN') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) && isset($_GET['action']) && $_GET['action'] == 'deleteconfirm') {
			$pages_id = (int)zen_db_prepare_input($_POST['ezID']);

			// BEGIN CEON URI MAPPING 2 of 4
			require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminEZPagePages.php');

			$ceon_uri_mapping_admin = new CeonURIMappingAdminEZPagePages();

			$ceon_uri_mapping_admin->deleteConfirmHandler($pages_id);

			// END CEON URI MAPPING 2 of 4

} // EOF of the action for categories file.

if (defined('FILENAME_EZPAGES_ADMIN') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) && (isset($_SESSION['ceon_uri_mapping_ezpages_insert']) || isset($_SESSION['ceon_uri_mapping_ezpages_update']))) {
		$_POST = !empty($_SESSION['ceon_uri_mapping_ezpages_insert']) ? $_SESSION['ceon_uri_mapping_ezpages_insert'] : $_SESSION['ceon_uri_mapping_ezpages_update'];

		unset($_SESSION['ceon_uri_mapping_ezpages_insert']);
		unset($_SESSION['ceon_uri_mapping_ezpages_update']);

		$pages_id = isset($_GET['ezID']) ? (int)$_GET['ezID'] : 0;
		$pages_title_array = zen_db_prepare_input($_POST['pages_title']); //todo review this wrt the subsequent call to insertUpdateHandler with second parameter $pages_title_array

		// BEGIN CEON URI MAPPING 1 of 4
		require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminEZPagePages.php');

		$ceon_uri_mapping_admin = new CeonURIMappingAdminEZPagePages();

		$ceon_uri_mapping_admin->insertUpdateHandler($pages_id, $pages_title_array, ((!empty($pages_title_array) && is_array($pages_title_array)) ? $pages_title_array : null));

		// END CEON URI MAPPING 1 of 4

		// Really should just redirect to the same uri that currently have...  Will reduce the amount of work in the future.
		zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, zen_get_all_get_params()));
}




if (defined('FILENAME_EZPAGES_ADMIN') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) && isset($_GET['action']) && ($_GET['action'] == 'insert' || $_GET['action'] == 'update')) {

			unset($_SESSION['ceon_uri_mapping_ezpages_insert']);
			unset($_SESSION['ceon_uri_mapping_ezpages_update']);

			$languages = zen_get_languages();

			if (isset($_POST['pages_id'])) {
				$pages_id = zen_db_prepare_input($_POST['pages_id']);
			}

			$page_open_new_window = (int)$_POST['page_open_new_window'];
			$status_visible = (int)$_POST['status_visible'];




			$alt_url = zen_db_prepare_input($_POST['alt_url']);

			$alt_url_external = zen_db_prepare_input($_POST['alt_url_external']);



			$pages_header_sort_order = (int)$_POST['header_sort_order'];
			$pages_sidebox_sort_order = (int)$_POST['sidebox_sort_order'];
			$pages_footer_sort_order = (int)$_POST['footer_sort_order'];
			$pages_toc_sort_order = (int)$_POST['toc_sort_order'];

			$toc_chapter = (int)$_POST['toc_chapter'];

			$status_header = ($pages_header_sort_order == 0 ? 0 : (int)$_POST['status_header']);
			$status_sidebox = ($pages_sidebox_sort_order == 0 ? 0 : (int)$_POST['status_sidebox']);
			$status_footer = ($pages_footer_sort_order == 0 ? 0 : (int)$_POST['status_footer']);
			$status_toc = ($pages_toc_sort_order == 0 ? 0 : (int)$_POST['status_toc']);

			$page_error = false;
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				if (empty($_POST['pages_title'][$languages[$i]['id']])) {
					$page_error = true;
				}
			}

			$zv_link_method_cnt = 0;
			if ($alt_url != '') {
				$zv_link_method_cnt++;
			}
			if ($alt_url_external != '') {
				$zv_link_method_cnt++;
			}

			$pages_html_text_count = 0;
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				if (!empty($pages_html_text[$languages[$i]['id']]) && strlen(trim($pages_html_text[$languages[$i]['id']])) > 6) {
					$pages_html_text_count = $i + 1;
				}
			}
			if ($pages_html_text_count > 0) {
				$zv_link_method_cnt++;
			}
			if ($zv_link_method_cnt > 1) {
				$page_error = true;
			}

			if ($page_error == false) {
				// SET SESSION VARIABLE AWAITING REDIRECT. WHERE
//        zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ezID=' . $pages_id));

				$_SESSION['ceon_uri_mapping_ezpages_' . $_GET['action']] = $_POST;
			}

}

if (defined('FILENAME_EZPAGES_ADMIN') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) && isset($_GET['action']) && $_GET['action'] == 'new') {

	$languages = zen_get_languages();

		// BEGIN CEON URI MAPPING 3 of 4
		require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminEZPagePages.php');

		$ceon_uri_mapping_admin = new CeonURIMappingAdminEZPagePages();

		$ceon_uri_mapping_admin->configureEnvironment();

		// END CEON URI MAPPING 3 of 4
}
