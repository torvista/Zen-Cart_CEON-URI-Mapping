<?php

/**************
 * Ceon URI Mapping URI Admin Remove Category Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingDeleteCategoriesObserver.php 1027 2019-03-13 20:31:10Z conor $
 */

require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php';

class ceonAdminRemoveCategory extends CeonURIMappingAdmin {

	
	/*
	 * This is the observer for the admin side of Ceon URI Mapping currently covering admin/includes/functions/general.php file to support when removing a category.
	 */
	function __construct() {
		global $zco_notifier;
		
		$attachNotifier = array();
		$attachNotifier[] = 'NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY';

		$zco_notifier->attach($this, $attachNotifier); 
	}

/**
 *zc 1.5.5 - 
 *zc 1.5.6   $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY', array(), $category_id);
 */
	public function notifier_admin_zen_remove_category(&$callingClass, $notifier, $paramsArray, &$category_id) {
		
		$selections = array(
			'main_page' => FILENAME_DEFAULT,
			'associated_db_id' => (int) $category_id
			);
		
		$this->deleteURIMappings($selections);
	}

/**
 *zc 1.5.5 - 
 *zc 1.5.6   $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY', array(), $category_id);
 */
	public function updateNotifierAdminZenRemoveCategory(&$callingClass, $notifier, $paramsArray, &$category_id) {
		$this->notifier_admin_zen_remove_category($callingClass, $notifier, $paramsArray, $category_id);
	}

	public function update(&$callingClass, $notifier, $p1, &$p2 = null)//can use "update" or camelized notifier name. & required for &$link to modify it inside here
	{
		$this->notifier_admin_zen_remove_category($callingClass, $notifier, $p1, $p2);
	}
} // EOF Class
