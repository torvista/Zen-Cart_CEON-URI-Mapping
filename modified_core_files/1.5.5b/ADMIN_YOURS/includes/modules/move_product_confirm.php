<?php
/** 2017/12/08 bugfix https://github.com/torvista/CEON-URI-Mapping/pull/3/files#diff-0
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Jun 30 2014 Modified in v1.5.4 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

        $products_id = zen_db_prepare_input($_POST['products_id']);
        $new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);

        $duplicate_check = $db->Execute("select count(*) as total
                                        from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                        where products_id = '" . (int)$products_id . "'
                                        and categories_id = '" . (int)$new_parent_id . "'");

        if ($duplicate_check->fields['total'] < 1) {
          $db->Execute("update " . TABLE_PRODUCTS_TO_CATEGORIES . "
                        set categories_id = '" . (int)$new_parent_id . "'
                        where products_id = '" . (int)$products_id . "'
                        and categories_id = '" . (int)$current_category_id . "'");

          // reset master_categories_id if moved from original master category
          $check_master = $db->Execute("select products_id, master_categories_id from " . TABLE_PRODUCTS . " where products_id='" .  (int)$products_id . "'");
          if ($check_master->fields['master_categories_id'] == (int)$current_category_id) {
            $db->Execute("update " . TABLE_PRODUCTS . "
                          set master_categories_id='" . (int)$new_parent_id . "'
                          where products_id = '" . (int)$products_id . "'");
          }

          // BEGIN CEON URI MAPPING 1 of 1
          require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
          
          $ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
          
          $ceon_uri_mapping_admin->moveProductConfirmHandler($products_id, zen_get_products_type($products_id), $zc_products->get_handler(zen_get_products_type($products_id)), $new_parent_id); //torvista https://github.com/torvista/CEON-URI-Mapping/pull/3/files#diff-0	

          
          // END CEON URI MAPPING 1 of 1
          // reset products_price_sorter for searches etc.
          zen_update_products_price_sorter((int)$products_id);
          zen_record_admin_activity('Moved product ' . (int)$products_id . ' from category ' . (int)$current_category_id . ' to category ' . (int)$new_parent_id, 'notice');

        } else {
          $messageStack->add_session(ERROR_CANNOT_MOVE_PRODUCT_TO_CATEGORY_SELF, 'error');
        }

        zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
