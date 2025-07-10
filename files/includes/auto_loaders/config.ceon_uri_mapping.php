<?php

/**
 * Autoloader array for Ceon URI Mapping functionality. Makes sure that Ceon URI Mapping is instantiated at the
 * right point of the Zen Cart initsystem.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.ceon_uri_mapping.php 2025-07-10 torvista
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'class.CeonURIMappingHandler.php',
];

$autoLoadConfig[94][] = [
    'autoType' => 'classInstantiate',
    'className' => 'CeonURIMappingHandler',
    'objectName' => 'ceon_uri_mapping',
];
//autoload to instantiate earlier than using auto.ceon_uri_mapping_link_build.php, for breadcrumbs
$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/class.ceon_uri_mapping_link_build.php',
];
$autoLoadConfig[99][] = [
    'autoType' => 'classInstantiate',
    'className' => 'CeonUriMappingLinkBuild',
    'objectName' => 'ceon_uri_mapping_link_build',
];

$autoLoadConfig[45][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_ceon_uri_mapping_sessions.php',
];
