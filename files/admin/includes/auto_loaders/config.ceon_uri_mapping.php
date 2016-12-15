<?php
/**
 * Autoloader array for Ceon URI Mapping ADMIN functionality. Makes sure that Ceon URI Mapping is instantiated at the
 * right point of the Zen Cart initsystem.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      torvista
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://github.com/torvista/CEON-URI-Mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2016
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

//steve added to use an observer with html_output.php function zen_href_catalog_link

$autoLoadConfig[10][] = array('autoType' => 'class',
    'loadFile' => 'observers/class.CeonURIMappingLinkBuildAdmin.php',
    'classPath' => DIR_WS_CLASSES);

$autoLoadConfig[90][] = array('autoType' => 'classInstantiate',
    'className' => 'CeonURIMappingLinkBuildAdmin',
    'objectName' => 'ceon_uri_mapping_link_build_admin');

/*POSM example
$autoLoadConfig[200][] = array ('autoType' => 'init_script',
    'loadFile' => 'init_products_options_stock_admin.php');

$autoLoadConfig[200][] = array ('autoType' => 'class',
    'loadFile' => 'observers/class.products_options_stock_admin_observer.php',
    'classPath' => DIR_WS_CLASSES);
$autoLoadConfig[200][] = array ('autoType' => 'classInstantiate',
    'className' => 'products_options_stock_observer',
    'objectName' => 'posObserver');
*/