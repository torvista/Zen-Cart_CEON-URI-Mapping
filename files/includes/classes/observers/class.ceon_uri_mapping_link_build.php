<?php

/**torvista: changed from auto as not loaded soon enough for breadcrumbs
 * Observer for Ceon URI Mapping link creation. Watches html_output.php function zen_href_link
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
class CeonUriMappingLinkBuild extends base
{
    function __construct()
    {
        $this->attach($this, array('NOTIFY_SEFU_INTERCEPT'));//OOP. Called from function zen_href_link (html_output.php)
        //global $zco_notifier;//if notifier is in procedural code
        //$zco_notifier->attach($this, array('NOTIFY_SEFU_INTERCEPT'));

    }

    function update(&$callingClass, $notifier, $p1, &$link, $page, $parameters, $connection, $add_session_id, $static, &$use_dir_ws_catalog)//& required for &$link to modify it inside here

    {
        if (defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED == 1 && $static == false) {

            if (!isset($ceon_uri_mapping_href_link_builder)) {
                static $ceon_uri_mapping_href_link_builder;

                require_once(DIR_WS_CLASSES . 'class.CeonURIMappingHREFLinkBuilder.php');

                $ceon_uri_mapping_href_link_builder = new CeonURIMappingHREFLinkBuilder();
            }
            if ($connection == 'NONSSL') {
                $link = HTTP_SERVER;
            } elseif ($connection == 'SSL') {
                if (ENABLE_SSL == 'true') {
                    $link = HTTPS_SERVER;
                } else {
                    $link = HTTP_SERVER;
                }
            }

            if ($ceon_uri_mapping_href_link_builder->buildHREFLink($link, $page, $parameters, $connection, $add_session_id)) {
                $link = $ceon_uri_mapping_href_link_builder->getHREFLink();
            } else {
                $link = null;
            }
        }
    }
}