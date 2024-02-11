<?php

/** 
 * Ceon URI Mapping Manufacturer Admin Pages Language Defines.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_manufacturer_pages.php 1027 2012-07-17 20:31:10Z conor $
 */

define('CEON_URI_MAPPING_TEXT_MANUFACTURER_URI', 'URI Mapping:');

define('CEON_URI_MAPPING_TEXT_MANUFACTURER_URI_AUTOGEN', 'Tick this box to have the URI auto-generated for this manufacturer.');
define('CEON_URI_MAPPING_TEXT_MANUFACTURER_URIS_AUTOGEN', 'Tick this box to have the URIs auto-generated for this manufacturer.');

define('CEON_URI_MAPPING_TEXT_MANUFACTURER_MAPPING_ADDED', '%s URI Mapping added: %s');
define('CEON_URI_MAPPING_TEXT_MANUFACTURER_MAPPING_UPDATED', '%s URI Mapping updated: %s');
define('CEON_URI_MAPPING_TEXT_MANUFACTURER_MAPPING_MADE_HISTORICAL', '%s URI Mapping converted to historical mapping');

if (!defined('CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING')) {
	require_once('ceon_uri_mapping_admin_pages.php');
}

define('CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_MANUFACTURER_HAS_NO_NAME', '%s URI Mapping cannot be generated as the manufacturer has no name!');
