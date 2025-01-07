<?php
/**
 * This file is called by javascript_loader.php at the start of the body tag, just above the header menu, and loads most of the admin javascript components
 *
 * @package admin
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Ceon Support Fri Jan 05 13:32:43 2024 -0400 Modified in v1.5.8 $
 */

// displays the javascript necessary for
// admin/product.php&action=new_product and
// admin/product.php&action=update_product
if (defined('FILENAME_PRODUCT') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_PRODUCT, '.php') ? FILENAME_PRODUCT . '.php' : FILENAME_PRODUCT) && isset($_GET['action']) && ($_GET['action'] == 'new_product' || $_GET['action'] == 'update_product' || ($_GET['action'] == 'insert_product' && empty($_GET['pID'])))) {
	$ceon_class_name = 'form-group';
?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingURI = document.createElement("div");
	ceonUriMappingURI.setAttribute("class", "<?php echo $ceon_class_name; ?>");
	ceonUriMappingURI.innerHTML = <?php
	$languages = zen_get_languages();

	if (empty($ceon_uri_mapping_admin) || !is_object($ceon_uri_mapping_admin)) {
		if (!class_exists('CeonURIMappingAdminProductPages')) {
			require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
		}
		$ceon_uri_mapping_admin = empty($GLOBALS['ceon_uri_mapping_admin']) ? new CeonURIMappingAdminProductPages() : $GLOBALS['ceon_uri_mapping_admin'];
	}
	echo json_encode(/*utf8_encode*/($ceon_uri_mapping_admin->collectInfoBuildURIMappingForm())); ?>;
	var classList = document.getElementsByClassName("<?php echo $ceon_class_name; ?>");
	var place = classList[classList.length - 1];
	
	if (!classList.length) {
		var formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	place.parentElement.appendChild(ceonUriMappingURI);
};
	</script>
<?php } 

// displays the javascript necessary for
// admin/product.php&action=new_product_preview
if (defined('FILENAME_PRODUCT') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_PRODUCT, '.php') ? FILENAME_PRODUCT . '.php' : FILENAME_PRODUCT) && isset($_GET['action']) && ($_GET['action'] == 'new_product_preview')) {
	$ceon_class_name = 'row';
?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var formList;
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute("class", "<?php echo $ceon_class_name; ?>");
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
	
	echo json_encode(/*utf8_encode*/($ceonUriMappingPreview));
	 ?>;
	var classList = document.getElementsByClassName("row");
	var place = classList[classList.length - 1];
	
	if (!classList.length) {
		formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);

	var ceonUriMappingHiddenURI = document.createElement("div");
	ceonUriMappingHiddenURI.innerHTML = <?php
	echo json_encode(/*utf8_encode*/($ceon_uri_mapping_admin->productPreviewBuildHiddenFields()));
	 ?>;
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

// displays the javascript necessary for
// admin/manufacturers.php&action=edit
if (defined('FILENAME_MANUFACTURERS') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && isset($_GET['action']) && $_GET['action'] == 'edit') {
	$ceon_class_name = 'row infoBoxContent';
?>
	<script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute("class", "<?php echo $ceon_class_name; ?>");
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

// displays the javascript necessary for
// admin/manufacturers.php&action=new
if (defined('FILENAME_MANUFACTURERS') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_MANUFACTURERS, '.php') ? FILENAME_MANUFACTURERS . '.php' : FILENAME_MANUFACTURERS) && isset($_GET['action']) && $_GET['action'] == 'new') {
	$ceon_class_name = 'row infoBoxContent';
?>
	<script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
window.onload = function(){
	let ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute("class", "<?php echo $ceon_class_name; ?>");
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


// displays the javascript necessary for
// admin/ezpages.php&action=new
if (defined('FILENAME_EZPAGES_ADMIN') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) && isset($_GET['action']) && $_GET['action'] == 'new') {
?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'form-group');
	
	ceonUriMappingGeneratedURI.innerHTML = <?php

	$languages = zen_get_languages();

	if (empty($ceon_uri_mapping_admin) || !is_object($ceon_uri_mapping_admin)) {
		if (!class_exists('CeonURIMappingAdminEZPagePages')) {
			require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminEZPagePages.php');
		}
		$ceon_uri_mapping_admin = empty($GLOBALS['ceon_uri_mapping_admin']) ? new CeonURIMappingAdminEZPagePages() : $GLOBALS['ceon_uri_mapping_admin'];
	}

	echo json_encode(/*utf8_encode*/($ceon_uri_mapping_admin->buildEZPageURIMappingFieldsForm()));
	 ?>;
	
	var classList = document.getElementsByClassName("form-group");
	var place = classList[classList.length - 1];
	
	if (!classList.length) {
		var formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);

};
	</script>
<?php }


// displays the javascript necessary for
// admin/product.php&action=copy_product
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && isset($_GET['action']) && $_GET['action'] == 'copy_product') {

?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row infoBoxContent duplicate-only hiddenField');
	ceonUriMappingGeneratedURI.innerHTML = <?php

	// BEGIN CEON URI MAPPING 1 of 1
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();

	$GLOBALS['contents'] = [];

	$ceon_uri_mapping_admin->addURIMappingFieldsToProductCopyFieldsArray((int) $_GET['pID']);
	
	// END CEON URI MAPPING 1 of 1

	$ceonUriMappingCopyProduct = '';
	$contents = $GLOBALS['contents'];

	for ($i = 0; $i < count($contents); $i++) {
		$ceonUriMappingCopyProduct .= $contents[$i]['text'];
	}
	echo json_encode(/*utf8_encode*/($ceonUriMappingCopyProduct));
		?>;
	
	var classList = document.getElementsByName("copy_as");
	for (var i = 0, n = classList.length; i < n; i++) { 
		if (classList[i].value == "duplicate") {
		   var place = classList[i].parentElement.parentElement.parentElement.nextElementSibling;
		   break;
		}
	}
	if (!classList.length) {
		var formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
};
	</script>
<?php }


// displays the javascript necessary for
// admin/product.php&action=move_product
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && isset($_GET['action']) && $_GET['action'] == 'move_product') {

?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row infoBoxContent');
	ceonUriMappingGeneratedURI.innerHTML = <?php

	// BEGIN CEON URI MAPPING 1 of 1
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
	
	$GLOBALS['contents'] = [];

	$ceon_uri_mapping_admin->addURIMappingFieldsToProductMoveFieldsArray((int)$_GET['pID']);
	
	// END CEON URI MAPPING 1 of 1
	
	$ceonUriMappingMoveProduct = '';
	$contents = $GLOBALS['contents'];
	
	for ($i = 0; $i < count($contents); $i++) {
		$ceonUriMappingMoveProduct .= $contents[$i]['text'];
	}
	echo json_encode(/*utf8_encode*/($ceonUriMappingMoveProduct));
	 ?>;
	var classList = document.getElementsByClassName("row infoBoxContent");
	var place = classList[classList.length - 1];
	
	if (!classList.length) {
		var formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	
	place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	
//  place.appendChild(ceonUriMappingHiddenURI);
//  place.parentElement.appendChild(ceonUriMappingHiddenURI);
};
	</script>
<?php }

// displays the javascript necessary for
// admin/categories.php&action=new_category
if (defined('FILENAME_CATEGORIES') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORIES, '.php') ? FILENAME_CATEGORIES . '.php' : FILENAME_CATEGORIES) && isset($_GET['action']) && $_GET['action'] == 'new_category') {

?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	
	// BEGIN CEON URI MAPPING 2 of 3
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
	
	$ceon_uri_mapping_admin->addURIMappingFieldsToAddCategoryForm();
	
	// END CEON URI MAPPING 2 of 3
	
	$text_str = '';
	foreach ($GLOBALS['contents'] as $key => $value) {
		$text_str .= $value['text'];
	}
	
	echo json_encode(/*utf8_encode*/($text_str));
	// END CEON URI MAPPING 3 of 3
?>;

	var classList = document.getElementsByClassName("form-group");
	var place = classList[classList.length - 1];
	
	if (!classList.length) {
		var formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	
//  place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	place.parentElement.appendChild(ceonUriMappingGeneratedURI);

};
	</script>
<?php }

// displays the javascript necessary for
// admin/categories.php&action=edit_category
if (defined('FILENAME_CATEGORIES') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORIES, '.php') ? FILENAME_CATEGORIES . '.php' : FILENAME_CATEGORIES) && isset($_GET['action']) && $_GET['action'] == 'edit_category') {

?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row');
	ceonUriMappingGeneratedURI.innerHTML = <?php

	// BEGIN CEON URI MAPPING 3 of 3
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
	
	$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryForm(
		(int) $GLOBALS['cInfo']->categories_id,
		array('label' => 'col-sm-2 control-label', 'input_field'=>'col-sm-9 col-md-6')
		);
	
	$text_str = '';
	foreach ($GLOBALS['contents'] as $key => $value) {
		$text_str .= $value['text'];
	}
	
	echo json_encode(/*utf8_encode*/($text_str));
	// END CEON URI MAPPING 3 of 3
?>;

	var classList = document.getElementsByClassName("form-group");
	var place = classList[0];
	
	if (!classList.length) {
		var formList = document.forms;
		place = formList[formList.length - 1][formList[formList.length - 1].length - 1];
	}
	
//  place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	place.parentElement.appendChild(ceonUriMappingGeneratedURI);

};
	</script>
<?php }

// displays the javascript necessary for
// admin/categories.php&action=move_category - Needs development in class structure.
if (false && defined('FILENAME_CATEGORY_PRODUCT_LISTING') && $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!strstr(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) && isset($_GET['action']) && $_GET['action'] == 'move_category') {

?>
	<script title="ceon_uri_mapping_javascript(<?php echo __LINE__; ?>)">
window.onload = function(){
	var ceonUriMappingGeneratedURI = document.createElement("div");
	ceonUriMappingGeneratedURI.setAttribute('class', 'row');
	ceonUriMappingGeneratedURI.innerHTML = <?php
	
	// BEGIN CEON URI MAPPING 3 of 3
	require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');
	
	$ceon_uri_mapping_admin = new CeonURIMappingAdminCategoryPages();
	
	
	//@TODO: need to change the formatting of this through a different function.
	$ceon_uri_mapping_admin->addURIMappingFieldsToEditCategoryFieldsArray(
		(int) $GLOBALS['cInfo']->categories_id);
	
	$text_str = '';
	foreach ($GLOBALS['contents'] as $key => $value) {
		$text_str .= $value['text'];
	}
	
	echo json_encode(/*utf8_encode*/($text_str));
	// END CEON URI MAPPING 3 of 3
?>;

	var classList = document.getElementsByClassName("form-group");
	var place = classList[classList.length - 1];
	
//  place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
	place.parentElement.appendChild(ceonUriMappingGeneratedURI);

};
	</script>
<?php }
