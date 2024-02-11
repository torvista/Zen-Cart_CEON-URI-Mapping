<?php

/**
 * Ceon URI Mapping Sessions cookie location Define.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: init_ceon_uri_mapping_sessions.php 1027 2019-07-22 for conor: Ceon Support added v5.0.1$
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

// Used to force the path for sessions to be at the root.
// Static URIs are relative to the site's root, so cookie should be set for the root
// This removes the need to override the file includes/init_includes/init_sessions.php and means
//  that the file includes/init_includes/overrides/init_sessions.php is not necessary.
// Option/configuration taken from includes/extra_datafiles/ceon_uri_mapping_sessions_define.php
if (defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED != 0) {
  define('CUSTOM_COOKIE_PATH', '/');
}
