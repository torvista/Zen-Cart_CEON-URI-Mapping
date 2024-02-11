<?php

/**
 * Ceon URI Mapping Product Page Definitions.
 *
 * Contains arrays with the list of the product pages and product related pages this store uses.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_product_pages.php 1027 2012-07-17 20:31:10Z conor $
 */

/**
 * If the store has any custom product page types, add their info page definitions to the list.
 */
if (empty($ceon_uri_mapping_product_pages)) $ceon_uri_mapping_product_pages = array();

$ceon_uri_mapping_product_pages = array_merge(array(
	FILENAME_DOCUMENT_GENERAL_INFO,
	FILENAME_DOCUMENT_PRODUCT_INFO,
	FILENAME_PRODUCT_INFO,
	FILENAME_PRODUCT_BOOK_INFO,
	FILENAME_PRODUCT_FREE_SHIPPING_INFO,
	FILENAME_PRODUCT_MUSIC_INFO
	), $ceon_uri_mapping_product_pages);

if (empty($ceon_uri_mapping_product_related_pages)) $ceon_uri_mapping_product_related_pages = array();
	
$ceon_uri_mapping_product_related_pages = array_merge(array(
	FILENAME_PRODUCT_REVIEWS,
	FILENAME_PRODUCT_REVIEWS_INFO,
	FILENAME_PRODUCT_REVIEWS_WRITE
	), $ceon_uri_mapping_product_related_pages);

if (defined('FILENAME_TELL_A_FRIEND')) {
	$ceon_uri_mapping_product_related_pages[] = FILENAME_TELL_A_FRIEND;
}
if (defined('FILENAME_ASK_A_QUESTION')) {
	$ceon_uri_mapping_product_related_pages[] = FILENAME_ASK_A_QUESTION;
}
