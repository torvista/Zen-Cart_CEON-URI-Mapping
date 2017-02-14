<?php

/** 
 * Ceon URI Mapping EZ-Page Admin Pages Language Defines.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_ezpage_pages.php 1027 2012-07-17 20:31:10Z conor $
 */

define('CEON_URI_MAPPING_TEXT_EZ_PAGE_URI', 'URI Mapping:');

define('CEON_URI_MAPPING_TEXT_EZ_PAGE_URI_AUTOGEN', 'Tick this box to have the URI auto-generated for this EZ-Page.');
define('CEON_URI_MAPPING_TEXT_EZ_PAGE_URIS_AUTOGEN', 'Tick this box to have the URIs auto-generated for this EZ-Page.');

define('CEON_URI_MAPPING_TEXT_EZ_PAGE_MAPPING_ADDED', '%s URI Mapping added: %s');
define('CEON_URI_MAPPING_TEXT_EZ_PAGE_MAPPING_UPDATED', '%s URI Mapping updated: %s');
define('CEON_URI_MAPPING_TEXT_EZ_PAGE_MAPPING_MADE_HISTORICAL', '%s URI Mapping converted to historical mapping');

if (!defined('CEON_URI_MAPPING_TEXT_URI_AUTOGEN_ONE_EXISTING_MAPPING')) {
	require_once('ceon_uri_mapping_admin_pages.php');
}

define('CEON_URI_MAPPING_TEXT_ERROR_AUTOGENERATION_EZ_PAGE_HAS_NO_NAME', '%s URI Mapping cannot be generated as the EZ-Page has no name!');

?>