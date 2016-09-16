<?php

/**
 * Ceon URI Mapping Database Table Name Defines.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_database_tables.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

define('TABLE_CEON_URI_MAPPINGS', DB_PREFIX . 'ceon_uri_mappings');
define('TABLE_CEON_URI_MAPPING_CONFIGS', DB_PREFIX . 'ceon_uri_mapping_configs');
define('TABLE_CEON_URI_MAPPING_PRODUCT_RELATED_PAGES_URI_PARTS', DB_PREFIX . 'ceon_uri_mapping_prp_uri_parts');
