<?php
/**
 * This file is called by includes\classes\observers\class.CeonURIMappingJavaScriptLoader.php at the end of the body tag and loads most of the admin JavaScript components. It could go in the observer itself...
 *
 * @package admin
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ceon_uri_mapping_javascript.php 2025-01-08 torvista
 */

// displays the JavaScript necessary for
// admin/product.php&action=new_product
// admin/product.php&action=update_product
// admin/product.php&action=insert_product
if (defined('FILENAME_PRODUCT') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_PRODUCT, '.php') ? FILENAME_PRODUCT . '.php' : FILENAME_PRODUCT) && 
    isset($_GET['action']) && 
    ($_GET['action'] == 'new_product' || $_GET['action'] == 'update_product' || ($_GET['action'] == 'insert_product' && empty($_GET['pID'])))) {
	$ceon_class_name = 'form-group';
?>
	<script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingURI = document.createElement("div");
	ceonUriMappingURI.setAttribute("class", "<?= $ceon_class_name ?>");
	ceonUriMappingURI.innerHTML = <?php
	$languages = zen_get_languages();
	if (empty($ceon_uri_mapping_admin) || !is_object($ceon_uri_mapping_admin)) {
		if (!class_exists('CeonURIMappingAdminProductPages')) {
			require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
		}
		$ceon_uri_mapping_admin = empty($GLOBALS['ceon_uri_mapping_admin']) ? new CeonURIMappingAdminProductPages() : $GLOBALS['ceon_uri_mapping_admin'];
	}
	echo json_encode($ceon_uri_mapping_admin->collectInfoBuildURIMappingForm());
    ?>;
    
    let classList = document.getElementsByClassName("<?= $ceon_class_name ?>");
	let place = classList[classList.length - 1];
	if (!classList.length) {
		let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	place.parentElement.appendChild(ceonUriMappingURI);
};
	</script>
<?php } 

// displays the JavaScript necessary for
// admin/product.php&action=new_product_preview
if (defined('FILENAME_PRODUCT') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_PRODUCT, '.php') ? FILENAME_PRODUCT . '.php' : FILENAME_PRODUCT) && 
    isset($_GET['action']) && ($_GET['action'] == 'new_product_preview')) {
	$ceon_class_name = 'row';
?>
	<script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
window.onload = function(){
	let formList;
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute("class", "<?= $ceon_class_name ?>");
	ceonUriMappingGeneratedURI.innerHTML = <?php
	$languages = zen_get_languages();
	if (empty($ceon_uri_mapping_admin) || !is_object($ceon_uri_mapping_admin)) {
		if (!class_exists('CeonURIMappingAdminProductPages')) {
			require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
		}
		$ceon_uri_mapping_admin = empty($GLOBALS['ceon_uri_mapping_admin']) ? new CeonURIMappingAdminProductPages() : $GLOBALS['ceon_uri_mapping_admin'];
	}
    $ceonUriMappingPreview = '<p class="control-label">' . CEON_URI_MAPPING_TEXT_PRODUCT_URI . '</p>';
    for ($i = 0, $n = count($languages); $i < $n; $i++) {
		$ceonUriMappingPreview .= $ceon_uri_mapping_admin->productPreviewExportURIMappingInfo($languages[$i]);
	}
	echo json_encode($ceonUriMappingPreview); 
    ?>;
    
	let classList = document.getElementsByClassName("row");
	let place = classList[classList.length - 1];
	if (!classList.length) {
		formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);

	let ceonUriMappingHiddenURI = document.createElement("div");
	ceonUriMappingHiddenURI.innerHTML = <?= json_encode($ceon_uri_mapping_admin->productPreviewBuildHiddenFields()) ?>;
	classList = document.getElementsByClassName("row text-right");
	place = classList[classList.length - 1];
	if (!classList.length) {
		formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1]; //.lastChild;
	}
	place.appendChild(ceonUriMappingHiddenURI);
//  place.parentElement.appendChild(ceonUriMappingHiddenURI);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/manufacturers.php&action=edit
if (defined('FILENAME_MANUFACTURERS') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && 
    isset($_GET['action']) && $_GET['action'] == 'edit') {
	$ceon_class_name = 'row infoBoxContent';
?>
	<script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute("class", "<?= $ceon_class_name ?>");
	ceonUriMappingGeneratedURI.innerHTML = <?php
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminManufacturerPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminManufacturerPages();
    $GLOBALS['contents'] = []; 
    $ceon_uri_mapping_admin->addURIMappingFieldsToEditManufacturerFieldsFormArray((int) $_GET['mID']);
    $ceonUriMappingDiv = '';
    $contents = $GLOBALS['contents'];
    for ($i = 0; $i < count($contents); $i++) {
        $ceonUriMappingDiv .= $contents[$i]['text'];
    }
	echo json_encode($ceonUriMappingDiv); 
    ?>;
    
	let classList = document.getElementsByClassName("row infoBoxContent");
	let place = classList[classList.length - 1];
	if (!classList.length) {
		let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/manufacturers.php&action=new
if (defined('FILENAME_MANUFACTURERS') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && 
    isset($_GET['action']) && $_GET['action'] == 'new') {
	$ceon_class_name = 'row infoBoxContent';
?>
	<script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute("class", "<?= $ceon_class_name ?>");
	ceonUriMappingGeneratedURI.innerHTML = <?php
    $languages = zen_get_languages();
    require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminManufacturerPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminManufacturerPages();
    $GLOBALS['contents'] = [];
	$ceon_uri_mapping_admin->addURIMappingFieldsToAddManufacturerFieldsArray();
    $ceonUriMappingDiv = '';
    $contents = $GLOBALS['contents'];
    for ($i = 0; $i < count($contents); $i++) {
        $ceonUriMappingDiv .= $contents[$i]['text'];
    }
	echo json_encode($ceonUriMappingDiv);
	?>;
    
	let classList = document.getElementsByClassName("<?=$ceon_class_name; ?>");
	let place = classList[classList.length - 1];
	if (!classList.length) {
        let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/ezpages.php&action=new
if (defined('FILENAME_EZPAGES_ADMIN') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) && 
    isset($_GET['action']) && $_GET['action'] == 'new') {
?>
	<script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'form-group');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	$languages = zen_get_languages();
	if (empty($ceon_uri_mapping_admin) || !is_object($ceon_uri_mapping_admin)) {
		if (!class_exists('CeonURIMappingAdminEZPagePages')) {
			require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminEZPagePages.php');
		}
		$ceon_uri_mapping_admin = empty($GLOBALS['ceon_uri_mapping_admin']) ? new CeonURIMappingAdminEZPagePages() : $GLOBALS['ceon_uri_mapping_admin'];
	}
	echo json_encode($ceon_uri_mapping_admin->buildEZPageURIMappingFieldsForm()); 
    ?>;
	
	let classList = document.getElementsByClassName("form-group");
	let place = classList[classList.length - 1];
	if (!classList.length) {
		let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/product.php&action=copy_product
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && 
    isset($_GET['action']) && $_GET['action'] == 'copy_product') {
?>
	<script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row infoBoxContent duplicate-only hiddenField');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
	$GLOBALS['contents'] = [];
	$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int)$_GET['pID']);
	$ceonUriMappingCopyProduct = '';
	$contents = $GLOBALS['contents'];
	for ($i = 0; $i < count($contents); $i++) {
		$ceonUriMappingCopyProduct .= $contents[$i]['text'];
	}
	echo json_encode($ceonUriMappingCopyProduct); 
    ?>;
	
    let classList = document.getElementsByName("copy_as");
    for (let i = 0, n = classList.length; i < n; i++) {
		if (classList[i].value === "duplicate") {
            classList = document.getElementsByClassName('row infoBoxContent duplicate-only');
            // insert URI div after all other rows of Duplicate options
            classList[classList.length-1].insertAdjacentElement('afterend', ceonUriMappingGeneratedURI);
            break;
		}
	}
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/product.php&action=move_product
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && 
    isset($_GET['action']) && $_GET['action'] == 'move_product') {
?>
	<script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row infoBoxContent');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
	$GLOBALS['contents'] = [];
	$ceon_uri_mapping_admin->addURIMappingFieldsToProductMoveFieldsArray((int)$_GET['pID']);
	$ceonUriMappingMoveProduct = '';
	$contents = $GLOBALS['contents'];
	for ($i = 0; $i < count($contents); $i++) {
		$ceonUriMappingMoveProduct .= $contents[$i]['text'];
	}
	echo json_encode(/*utf8_encode*/($ceonUriMappingMoveProduct)); 
    ?>;
	
    let classList = document.getElementsByClassName("row infoBoxContent");
	let place = classList[classList.length - 1];
	if (!classList.length) {
		let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
		place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	//  place.appendChild(ceonUriMappingHiddenURI);
    //  place.parentElement.appendChild(ceonUriMappingHiddenURI);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/categories.php&action=new_category
if (defined('FILENAME_CATEGORIES') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORIES, '.php') ? FILENAME_CATEGORIES . '.php' : FILENAME_CATEGORIES) && 
    isset($_GET['action']) && $_GET['action'] == 'new_category') {
?>
	<script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
	$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryForm();
	$text_str = '';
	foreach ($GLOBALS['contents'] as $key => $value) {
		$text_str .= $value['text'];
	}
	echo json_encode($text_str); 
    ?>;

	let classList = document.getElementsByClassName("form-group");
	let place = classList[classList.length - 1];
	if (!classList.length) {
		let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
//  place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	place.parentElement.appendChild(ceonUriMappingGeneratedURI);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/categories.php&action=edit_category
if (defined('FILENAME_CATEGORIES') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORIES, '.php') ? FILENAME_CATEGORIES . '.php' : FILENAME_CATEGORIES) && 
    isset($_GET['action']) && $_GET['action'] == 'edit_category') {
?>
	<script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
	$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryForm(
		(int) $GLOBALS['cInfo']->categories_id,
		['label' => 'col-sm-2 control-label', 'input_field'=>'col-sm-9 col-md-6']
		);
	$text_str = '';
	foreach ($GLOBALS['contents'] as $key => $value) {
		$text_str .= $value['text'];
	}
	echo json_encode($text_str); 
    ?>;

	let classList = document.getElementsByClassName("form-group");
	let place = classList[0];
	if (!classList.length) {
		let formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
//  place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	place.parentElement.appendChild(ceonUriMappingGeneratedURI);
};
	</script>
<?php }

// displays the JavaScript necessary for
// admin/categories.php&action=move_category - Needs development in class structure.
if (false && 
    defined('FILENAME_CATEGORY_PRODUCT_LISTING') && 
    $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && 
    isset($_GET['action']) && $_GET['action'] == 'move_category') {
?>
	<script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');
	$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
		
	//@TODO: need to change the formatting of this through a different function.
	$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray(
		(int) $GLOBALS['cInfo']->categories_id);
	
	$text_str = '';
	foreach ($GLOBALS['contents'] as $key => $value) {
		$text_str .= $value['text'];
	}
	echo json_encode($text_str); 
    ?>;

	let classList = document.getElementsByClassName("form-group");
	let place = classList[classList.length - 1];
//  place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	place.parentElement.appendChild(ceonUriMappingGeneratedURI);
};
	</script>
<?php }
