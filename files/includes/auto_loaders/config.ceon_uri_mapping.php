<?php

/**
 * Autoloader array for Ceon URI Mapping functionality. Makes sure that Ceon URI Mapping is instantiated at the
 * right point of the Zen Cart initsystem.
 * 
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.ceon_uri_mapping.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
} 

$autoLoadConfig[0][] = array(
	'autoType' => 'class',
	'loadFile' => 'class.CeonURIMappingHandler.php'
	);

$autoLoadConfig[99][] = array(
	'autoType' => 'classInstantiate',
	'className' => 'CeonURIMappingHandler',
	'objectName' => 'ceon_uri_mapping'
	);
