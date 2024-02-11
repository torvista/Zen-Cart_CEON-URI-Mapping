<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Thu Dec 6 14:42:02 2018 -0500 New in v1.5.6 $
 */

class zcObserverClassCeonURIMappingJavaScriptLoader extends base
{
    public function __construct() {
        $observeThis = array();
        
        $observeThis[] = 'NOTIFY_ADMIN_FOOTER_END';
        
        $this->attach($this, $observeThis);
    }

    public function updateNotifyAdminFooterEnd(&$callingClass, $notifier) {
        if (file_exists(DIR_WS_INCLUDES . 'ceon_uri_mapping_javascript.php')) {
          require DIR_WS_INCLUDES . 'ceon_uri_mapping_javascript.php';
        }
    }
}