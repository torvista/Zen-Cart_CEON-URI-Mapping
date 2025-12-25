<?php
/**
 * Autoloader array for Ceon URI Mapping currencies functionality. Makes sure that Ceon URI Mapping currencies integration is
 * instantiated at the right point of the Zen Cart initsystem.
 * The load point for this observer is to be after the includes/init_includes/init_currencies.php file but before the next
 * init_ related operation that might use currency information.  File's load point is expected to be identified
 * in includes/auto_loaders/config.core.php.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.ceon_currencies.php 1027 2019-04-24 20:31:10Z conor v5.0.0 Ceon Support$
 **/

/**
 * Breakpoint 123.
 *
 * require('includes/init_includes/init_ceon_currencies.php');
 *
 */
$autoLoadConfig[123][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_ceon_currencies.php'
];
