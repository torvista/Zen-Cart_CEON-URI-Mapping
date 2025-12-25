<?php
/**
 * Autoloader array for Ceon URI Mapping canonical functionality. Makes sure that Ceon URI Mapping canonical integration is
 * instantiated at the right point of the Zen Cart initsystem.
 * The load point for this observer is to be before the includes/init_includes/init_canonical.php file.  File's load point is
 *  expected to be identified in includes/auto_loaders/config.canonical.php.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.ceon_canonical.php 1027 2019-04-24 20:31:10Z conor v5.0.0 Ceon Support$
 **/

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/class.ceon_canonical.php',
];
$autoLoadConfig[160][] = [
    'autoType' => 'classInstantiate',
    'className' => 'zcObserverCeonCanonical',
    'objectName' => 'ceon_canonical',
];
