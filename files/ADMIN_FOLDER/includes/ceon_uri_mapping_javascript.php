<?php
declare(strict_types=1);
/**
 * This file is called by includes\classes\observers\class.CeonURIMappingJavaScriptLoader.php at the end of the body tag and loads most of the admin JavaScript components. It could go in the observer itself...
 *
 * @package admin
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ceon_uri_mapping_javascript.php 11 Aug 2026 zenexpert
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
        window.addEventListener('load', function() {
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

            // Scope the DOM search to the product form to avoid appending to external sideboxes
            let mainForm = document.forms['new_product'] || document.forms['update_product'] || document.forms['insert_product'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("<?= $ceon_class_name ?>");
                // Anchor to the last class inside the form, or fallback to the very end of the form itself
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            // Global fallback ONLY if mainForm wasn't found on the page at all
            if (!place) {
                let classList = document.getElementsByClassName("<?= $ceon_class_name ?>");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.appendChild(ceonUriMappingURI);
            }
        });
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
        window.addEventListener('load', function() {
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

            // Scope the DOM search to the product form to avoid external sideboxes
            let mainForm = document.forms['update_product'] || document.forms['insert_product'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("row");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("row");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
            }

            let ceonUriMappingHiddenURI = document.createElement("div");
            ceonUriMappingHiddenURI.innerHTML = <?= json_encode($ceon_uri_mapping_admin->productPreviewBuildHiddenFields()) ?>;

            let hiddenPlace;
            if (mainForm) {
                let classList = mainForm.getElementsByClassName("row text-right");
                // Safely fallback directly to the form instead of its last child
                hiddenPlace = classList.length > 0 ? classList[classList.length - 1] : mainForm;
            }

            if (!hiddenPlace) {
                let classList = document.getElementsByClassName("row text-right");
                if (classList.length > 0) {
                    hiddenPlace = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        // Fallback directly to the last form wrapper
                        hiddenPlace = formList[formList.length - 1];
                    }
                }
            }

            if (hiddenPlace) {
                hiddenPlace.appendChild(ceonUriMappingHiddenURI);
            }
        });
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
        window.addEventListener('load', function() {
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

            let mainForm = document.forms['manufacturers'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("row infoBoxContent");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("row infoBoxContent");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
            }
        });
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
        window.addEventListener('load', function() {
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

            let mainForm = document.forms['manufacturers'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("<?=$ceon_class_name; ?>");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("<?=$ceon_class_name; ?>");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
            }
        });
    </script>
<?php }

// displays the JavaScript necessary for
// admin/ezpages.php&action=new
if (defined('FILENAME_EZPAGES_ADMIN') &&
        $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_EZPAGES_ADMIN, '.php') ? FILENAME_EZPAGES_ADMIN . '.php' : FILENAME_EZPAGES_ADMIN) &&
        isset($_GET['action']) && $_GET['action'] == 'new') {
    ?>
    <script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
        window.addEventListener('load', function() {
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

            let mainForm = document.forms['new_page'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("form-group");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("form-group");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
            }
        });
    </script>
<?php }

// displays the JavaScript necessary for
// admin/product.php&action=copy_product
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') &&
        $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) &&
        isset($_GET['action']) && $_GET['action'] == 'copy_product') {
    ?>
    <script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
        window.addEventListener('load', function() {
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
                    if (classList.length > 0) {
                        classList[classList.length-1].insertAdjacentElement('afterend', ceonUriMappingGeneratedURI);
                    }
                    break;
                }
            }
        });
    </script>
<?php }

// displays the JavaScript necessary for
// admin/product.php&action=move_product
if (defined('FILENAME_CATEGORY_PRODUCT_LISTING') &&
        $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) &&
        isset($_GET['action']) && $_GET['action'] == 'move_product') {
    ?>
    <script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
        window.addEventListener('load', function() {
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

            let mainForm = document.forms['products'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("row infoBoxContent");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("row infoBoxContent");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.insertBefore(ceonUriMappingGeneratedURI, place);
            }
        });
    </script>
<?php }

// displays the JavaScript necessary for
// admin/categories.php&action=new_category
if (defined('FILENAME_CATEGORIES') &&
        $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORIES, '.php') ? FILENAME_CATEGORIES . '.php' : FILENAME_CATEGORIES) &&
        isset($_GET['action']) && $_GET['action'] == 'new_category') {
    ?>
    <script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
        window.addEventListener('load', function() {
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

            let mainForm = document.forms['categories'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("form-group");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("form-group");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.appendChild(ceonUriMappingGeneratedURI);
            }
        });
    </script>
<?php }

// displays the JavaScript necessary for
// admin/categories.php&action=edit_category
if (defined('FILENAME_CATEGORIES') &&
        $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORIES, '.php') ? FILENAME_CATEGORIES . '.php' : FILENAME_CATEGORIES) &&
        isset($_GET['action']) && $_GET['action'] == 'edit_category') {
    ?>
    <script title="ceon_uri_mapping_javascript(<?=__LINE__ ?>)">
        window.addEventListener('load', function() {
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

            // Target the 'categories' form specifically
            // Introduced for compatibility with plugin Modern Admin Dashboard
            let targetForm = document.forms['categories'];
            let place;

            if (targetForm) {
                let internalGroups = targetForm.getElementsByClassName("form-group");
                place = internalGroups.length > 0 ? internalGroups[internalGroups.length - 1] : targetForm.lastElementChild;
            }

            // Global Fallback
            if (!place) {
                let classList = document.getElementsByClassName("form-group");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            // Inject the Ceon Fields
            if (place && place.parentElement) {
                place.parentElement.appendChild(ceonUriMappingGeneratedURI);
            }
        });
    </script>
<?php }

// displays the JavaScript necessary for
// admin/categories.php&action=move_category
// Needs development in class structure.
// This is DISABLED: it was also disabled in original 5.1.1 code. i.e. never implemented!
if (false &&
        defined('FILENAME_CATEGORY_PRODUCT_LISTING') &&
        $_SERVER['SCRIPT_NAME'] == DIR_WS_ADMIN . (!str_contains(FILENAME_CATEGORY_PRODUCT_LISTING, '.php') ? FILENAME_CATEGORY_PRODUCT_LISTING . '.php' : FILENAME_CATEGORY_PRODUCT_LISTING) &&
        isset($_GET['action']) && $_GET['action'] == 'move_category') {
    ?>
    <script title="ceon_uri_mapping_javascript(<?= __LINE__ ?>)">
        window.addEventListener('load', function() {
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

            let mainForm = document.forms['categories'];
            let place;

            if (mainForm) {
                let classList = mainForm.getElementsByClassName("form-group");
                place = classList.length > 0 ? classList[classList.length - 1] : mainForm.lastElementChild;
            }

            if (!place) {
                let classList = document.getElementsByClassName("form-group");
                if (classList.length > 0) {
                    place = classList[classList.length - 1];
                } else {
                    let formList = document.forms;
                    if (formList.length > 0) {
                        let lastForm = formList[formList.length - 1];
                        place = lastForm[lastForm.length - 1];
                    }
                }
            }

            if (place && place.parentElement) {
                place.parentElement.appendChild(ceonUriMappingGeneratedURI);
            }
        });
    </script>
<?php }