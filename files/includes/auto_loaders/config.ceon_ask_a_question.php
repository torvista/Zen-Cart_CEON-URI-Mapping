<?php
/**
 * Autoloader array for Ceon URI Mapping ask a question functionality. Makes sure that Ceon URI Mapping's ask a question integration is
 * instantiated at the right point of the Zen Cart initsystem.
 * The load point for this observer is to be before the includes/modules/pages/ask_a_question/header_php.php file.  File's load point is
 *  expected to be identified in includes/auto_loaders/config.ceon_ask_a_question.php.
 * Added to account for the change in parameter naming in ZC versions before 1.5.7.
 * In ZC 1.5.7, the parameter for ask_a_question became pid=X instead of products_id=X.
 * This code is added to support either existing at the point of loading the page itself, although
 *   in Version 5.0.2, the parameter for generating the uri was changed to pid to match ZC 1.5.7
 *   and allow this sequence of files to be removed from a default ZC installation.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2020 Ceon
 * @copyright   Copyright 2003-2020 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.ceon_ask_a_question.php 1027 2020-08-02 20:31:10Z conor v5.0.0 Ceon Support$
 **/

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/class.ceon_ask_a_question.php',
];
$autoLoadConfig[200][] = [
    'autoType' => 'classInstantiate',
    'className' => 'zcObserverCeonAskAQuestion',
    'objectName' => 'ceon_ask_a_question',
];
