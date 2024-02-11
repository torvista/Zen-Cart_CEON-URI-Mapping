<?php
/**
 * Autoloader array for Ceon URI Mapping ADMIN functionality. Makes sure that Ceon URI Mapping is instantiated at the
 * right point of the Zen Cart initsystem.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://github.com/torvista/CEON-URI-Mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2019 added in Version 5.0.1 2019 August 18
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// added to use an observer with the footer.php notifier supporting Zen Cart 1.5.7 and above.

$autoLoadConfig[10][] = array('autoType' => 'class',
    'loadFile' => 'observers/class.CeonURIMappingJavaScriptLoader.php',
    'classPath' => DIR_WS_CLASSES);

$autoLoadConfig[180][] = array('autoType' => 'classInstantiate',
    'className' => 'zcObserverClassCeonURIMappingJavaScriptLoader',
    'objectName' => 'zcObserverClassCeonURIMappingJavaScriptLoader');
