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
 * @version     $Id: ceon_uri_mapping_sessions_define.php 1027 2019-05-10 for conor: Ceon Support added v5.0.0$
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

// Used to force the path for sessions to be at the root.
// Though this has been moved to init_ceon_uri_mapping_sessions.php to support
//  loading if active and skipping if inactive.
// Static URIs are relative to the site's root, so cookie should be set for the root
// This removes the need to override the file includes/init_includes/init_sessions.php and means
//  that the file includes/init_includes/overrides/init_sessions.php is not necessary.
// Override removal is applicable to the file structure used by ZC 1.3.9c and more recent.
// This is used with consideration that it is the only such use of CUSTOM_COOKIE_PATH.
//   If another application defines CUSTOM_COOKIE_PATH to something else before the below define
//   then this define will be of no value.
//define('CUSTOM_COOKIE_PATH', '/');
