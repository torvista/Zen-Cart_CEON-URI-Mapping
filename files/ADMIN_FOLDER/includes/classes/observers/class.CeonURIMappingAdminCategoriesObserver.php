<?php

/**
 * Observer for Ceon URI Mapping admin/categories.php code
 * Watches admin/categories.php
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://ceon.net
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     class.CeonURIMappingAdminCategoriesObserver.php 2025-01-08 torvista
 */
class CeonURIMappingAdminCategoriesObserver extends base
{
    /**
     * ZC 1.5.6: $zco_notifier->notify('NOTIFY_BEGIN_ADMIN_CATEGORIES', $action);
     *
     */
    public function __construct()
    {
        $this->attach($this, ['NOTIFY_BEGIN_ADMIN_CATEGORIES']);
    }

    public function update(&$callingClass, $notifier, $action): void
    {
//  Can't capture updated information until after it has been processed. Best opportunity is to
//    grab the information on redirect.
        // Identify that expectation is that the category is inserted or updated.
        if (defined('CEON_URI_MAPPING_ENABLED') && isset($action) && ($action == 'insert_category' || $action == 'update_category')) {
            // Set a session variable here to that of the current_category_id, because the final $categories_id is not known until the end of execution
            //   and redirection to the next page.
            $_SESSION['ceon_uri_mapping_' . $action] = [];
            $_SESSION['ceon_uri_mapping_' . $action]['current_category_id'] = $GLOBALS['current_category_id'];
            $_SESSION['ceon_uri_mapping_' . $action]['post'] = $_POST;
        }

        // Make class functions available to the global space for execution.
        if (defined('CEON_URI_MAPPING_ENABLED') && isset($action) && ($action == 'new_category' || $action == 'edit_category')) {
            require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoryPages.php');

            $GLOBALS['ceon_uri_mapping_admin'] = new CeonURIMappingAdminCategoryPages();
        }
    }
}
