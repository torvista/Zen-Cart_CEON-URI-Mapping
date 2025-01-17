<?php

/**************
 * Ceon URI Mapping URI Admin Remove Products Class.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingDeleteCategoriesObserver.php 2025-01-08 torvista
 */

require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php';

class ceonAdminRemoveProducts extends CeonURIMappingAdmin {
	
	/*
	 * This is the observer for the admin side of Ceon URI Mapping currently covering admin/includes/functions/general.php file to support when removing a product.
	 * zc 1.5.5: $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', array(), $product_id, $ptc);
	 */
	public function __construct() {
		global $zco_notifier;

		$attachNotifier = [];
		$attachNotifier[] = 'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT';

		$zco_notifier->attach($this, $attachNotifier); 
	}

/**
 *zc 1.5.5-1.5.6   $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', array(), $product_id, $ptc);
 */
	public function notifier_admin_zen_remove_product(&$callingClass, $notifier, $paramsArray, &$product_id, &$ptc): void
    {
		global $ceon_uri_mapping_product_pages, $ceon_uri_mapping_product_related_pages;
		
		$selections = [
			'main_page' => array_merge($ceon_uri_mapping_product_pages, $ceon_uri_mapping_product_related_pages),
			'associated_db_id' => (int) $product_id
        ];
		
		$this->deleteURIMappings($selections);
	}

/**
 *zc 1.5.5-1.5.6   $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', array(), $product_id, $ptc);
 */
	public function updateNotifierAdminZenRemoveProduct(&$callingClass, $notifier, $paramsArray, &$product_id, &$ptc): void
    {
		$this->notifier_admin_zen_remove_product($callingClass, $notifier, $paramsArray, $product_id, $ptc);
	}

	public function update(&$callingClass, $notifier, $p1, &$p2 = null): void//can use "update" or camelized notifier name. & required for &$link to modify it inside here
	{
		$this->notifier_admin_zen_remove_product($callingClass, $notifier, $p1, $p2, null);
	}
} // EOF Class
