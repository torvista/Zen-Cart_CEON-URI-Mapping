<?php

/**
 * Observer for Ceon URI Mapping link creation for admin-generated emails, BISN etc.
 * Watches html_output.php function zen_href_catalog_link
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      torvista
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://ceon.net
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2025-01-08 torvista
 */
class CeonURIMappingLinkBuildAdmin extends base
{
		public function __construct()
		{
				$this->attach($this, ['NOTIFY_SEFU_INTERCEPT_ADMCATHREF']);
		}

		public function notify_sefu_intercept_admcathref(&$callingClass, $notifier, $p1, &$link, $page, $parameters, $connection): void//can use "update" or camelized notifier name. & required for &$link to modify it inside here
		{
				if (!isset($link) && !isset($page) && !isset($parameters) && !isset($connection) && !isset($_SESSION['NotifySEFUInterceptAdmcathref'])) {
						trigger_error('System not updated to handle editable notifier parameters.  Need to properly update the operating system.  This message will not be repeated for this session.', E_USER_WARNING);
						$_SESSION['NotifySEFUInterceptAdmcathref'] = false;
				}

				if (!defined('CEON_URI_MAPPING_ENABLED') || CEON_URI_MAPPING_ENABLED != 1 || isset($_SESSION['NotifySEFUInterceptAdmcathref'])) {
					return;
				}

				static $ceon_uri_mapping_href_link_builder;

				if (!isset($ceon_uri_mapping_href_link_builder)) {
						require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingHREFLinkBuilder.php');

						$ceon_uri_mapping_href_link_builder = new CeonURIMappingHREFLinkBuilder();
				}

				if ($connection == 'NONSSL') {
						$link = defined('HTTP_CATALOG_SERVER') ? HTTP_CATALOG_SERVER : HTTP_SERVER;
				} elseif ($connection == 'SSL') {
						if (ENABLE_SSL_CATALOG == 'true') {
								$link = defined('HTTPS_CATALOG_SERVER') ? HTTPS_CATALOG_SERVER : (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER);
						} else {
								$link = defined('HTTP_CATALOG_SERVER') ? HTTP_CATALOG_SERVER : HTTP_SERVER;
						}
				}

				if ($ceon_uri_mapping_href_link_builder->buildHREFLink($link, $page, $parameters, $connection, false)) {
						$link = $ceon_uri_mapping_href_link_builder->getHREFLink();
				} else {
						$link = null;
				}
		}

		public function updateNotifySEFUInterceptAdmcathref(&$callingClass, $notifier, $p1, &$link, $page, $parameters, $connection): void//can use "update" or camelized notifier name. & required for &$link to modify it inside here
		{
				$this->notify_sefu_intercept_admcathref($callingClass, $notifier, $p1, $link, $page, $parameters, $connection);
		}

		public function update(&$callingClass, $notifier, $p1, &$link = null, $page = null, $parameters = null, $connection = null): void//can use "update" or camelized notifier name. & required for &$link to modify it inside here
		{
				if (!isset($link) && !isset($page) && !isset($parameters) && !isset($connection) && !isset($_SESSION['NotifySEFUInterceptAdmcathref'])) {
						trigger_error('System not updated to handle editable notifier parameters.  Need to properly update the operating system.  This message will not be repeated for this session.', E_USER_WARNING);
						$_SESSION['NotifySEFUInterceptAdmcathref'] = false;
				}
				
				if (!isset($_SESSION['NotifySEFUInterceptAdmcathref'])) {
					$this->notify_sefu_intercept_admcathref($callingClass, $notifier, $p1, $link, $page, $parameters, $connection);
				}
		}
}
