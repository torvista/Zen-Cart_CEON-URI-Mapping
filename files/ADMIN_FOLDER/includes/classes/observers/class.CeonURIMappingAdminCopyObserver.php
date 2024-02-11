<?php

/**
 * Observer for Ceon URI Mapping admin/categories.php code
 * Watches admin/categories.php
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2020 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://ceon.net
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2020
 */
class CeonURIMappingAdminCopyObserver extends base
{
		
		var $ceon_uri_mapping_copy_product_confirmed;
		
		function __construct()
		{
				$attachNotifier = array();
				$attachNotifier[] = 'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE';
				
				$this->attach($this, $attachNotifier);
				
				$this->ceon_uri_mapping_copy_product_confirmed = false;
		}
		
		function notify_modules_copy_to_confirm_duplicate(&$callingClass, $notifier, $product_data)
		{
			
				if (isset($_POST['products_id'], $_POST['categories_id'], $_POST['copy_as']) && $_POST['copy_as'] == 'duplicate') {
			// Copy attributes to duplicate product
			//					$products_id_from=$products_id;
					// created a new product that now has products_id of $dup_products_id, so need
					// to find the new product_id that has information matching the old.
							// The $products_id on the uri is equal to $dup_products_id or $products_id_to
				//							$products_id_to= $dup_products_id;
						global $zc_products;
						
						$products_id_from = $product_data['products_id'];
						$products_id_to = $product_data['dup_products_id'];
						$categories_id = (int)$_POST['categories_id'];
//						$products_id_from = (int)zen_db_prepare_input($_POST['products_id']);
//						$categories_id = !empty($_GET['cPath']) ? (int)$_GET['cPath'] : 0;
//						$products_id_to = !empty($_GET['pID']) ? (int)$_GET['pID'] : 0;
						
						$sql = "SELECT products_type FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$products_id_to;
						$product = $GLOBALS['db']->Execute($sql);
						
						$this->ceon_uri_mapping_copy_product_confirmed = true;
						unset($_SESSION['ceon_uri_mapping_copy_product_confirm']);
						
						if ($product->RecordCount() == 0) {
							return;
						}
						
						require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
						
						$ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
						
						$ceon_uri_mapping_admin->copyToConfirmHandler($products_id_from, $products_id_to,
							$product->fields['products_type'], $zc_products->get_handler($product->fields['products_type']),
							$categories_id);
				}
		}
		
		function updateNotifyModulesCopyToConfirmDuplicate(&$callingClass, $notifier, $product_data)
		{
				return $this->notify_modules_copy_to_confirm_duplicate($callingClass, $notifier, $product_data);
		}
		
		function update(&$callingClass, $notifier, $action)
		{
		}
}