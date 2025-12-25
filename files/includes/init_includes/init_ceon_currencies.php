<?php
/**
 * Initialise script to inject modification of the currency information for Ceon URI Mapping after includes/init_includes/init_currencies.php
 * has performed its operations, but before the next software may use that information.  Expected to load shortly after
 * includes/init_includes/init_currencies.php which has had a load point of 120 as identified in includes/auto_loaders/config.core.php.
 * This appears that it can be applied to all versions of Zen Cart.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2019 - 5.0.0
 **/

if (isset($ceon_uri_mapping)) {
    $ceon_uri_mapping->checkLanguageChangedCurrencyChangeRequired();
}
