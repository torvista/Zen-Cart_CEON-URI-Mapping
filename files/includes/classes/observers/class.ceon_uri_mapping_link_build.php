<?php

/**torvista: changed from auto as not loaded soon enough for breadcrumbs
 * Observer for Ceon URI Mapping link creation. Watches html_output.php function zen_href_link
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      torvista
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        https://github.com/torvista/CEON-URI-Mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2024
 */
class CeonUriMappingLinkBuild extends base
{
		public function __construct()
		{
				$this->attach($this, array(
					'NOTIFY_SEFU_INTERCEPT',
					'NOTIFY_INIT_ADD_CRUMBS_GET_TERMS_LINK_PARAMETERS',
				));//OOP. Called from function zen_href_link (html_output.php)
		
		}
		
		/**
		 * Added in a Zen Cart version after 1.5.8a. Mitigates the need to override
		 *  includes/init_includes/init_add_crumbs.php
		 * Loaded in time to ensure in place before execution of that init file.
		 * Supports editing the $link_parameters parameter
		 * 
		 * @access  public
		 * @param   class  $callingClass    - editable (pass-by-reference)
		 * @param          $notifier        - non-editable
		 * @param   array  $next_get_term   - non-editable
		 * @param   string $link_parameters - editable (pass-by-reference)
		 * @return  void (early escape if no processing necessary otherwise edits $link_parameters)
		 * 
		 */
		public function notify_init_add_crumbs_get_terms_link_parameters(&$callingClass, $notifier, $next_get_term, &$link_parameters)
		{
				if (!defined('CEON_URI_MAPPING_ENABLED') || CEON_URI_MAPPING_ENABLED != 1) {
						return;
				}
				
				// Set the required parameters so that an attempt can be made to map the link to any static URI for the
				// filtered page
				if ($next_get_term['get_term_name'] === 'manufacturers_id') {
						return;
				}
				$link_parameters = 'typefilter=' . str_replace('_id', '', $next_get_term['get_term_name']) . '&' . $link_parameters;
		}
		
		public function notify_sefu_intercept(&$callingClass, $notifier, $p1, &$link, $page, $parameters, $connection, $add_session_id, $static, &$use_dir_ws_catalog)//& required for &$link to modify it inside here
		{
				if (!(defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED == 1 && $static == false)) {
					return;
				}
				
				static $ceon_uri_mapping_href_link_builder;
				
				if (!isset($ceon_uri_mapping_href_link_builder)) {
				
						require_once(DIR_WS_CLASSES . 'class.CeonURIMappingHREFLinkBuilder.php');
						
						$ceon_uri_mapping_href_link_builder = new CeonURIMappingHREFLinkBuilder();
				}
				
				$temp_link = HTTP_SERVER;
				
				if ($connection == 'SSL' && ENABLE_SSL == 'true') {
						$temp_link = HTTPS_SERVER;
				}
				
				if ($ceon_uri_mapping_href_link_builder->buildHREFLink($temp_link, $page, $parameters, $connection, $add_session_id)) {
						$link = $ceon_uri_mapping_href_link_builder->getHREFLink();
				}
		}
		
		public function updateNotifySefuIntercept(&$callingClass, $notifier, $p1, &$link, $page, $parameters, $connection, $add_session_id, $static, &$use_dir_ws_catalog)//& required for &$link to modify it inside here
		{
				$this->notify_sefu_intercept($callingClass, $notifier, $p1, $link, $page, $parameters, $connection, $add_session_id, $static, $use_dir_ws_catalog);
		}
		
		public function updateNotifyInitAddCrumbsGetTermsLinkParameters(&$callingClass, $notifier, $next_get_term, &$link_parameters) {
				$this->notify_init_add_crumbs_get_terms_link_parameters($callingClass, $notifier, $next_get_term, $link_parameters);
		}
		
		// Additional parameters are not provided a default value because this notifier is not expected for less than Zen Cart 1.5.3 and if it is used then it would need to address the additional parameters.
		public function update(&$callingClass, $notifier, $p1 = null, &$link = null, $page = null, $parameters = null, $connection = null, $add_session_id = null, $static = null, &$use_dir_ws_catalog = null)//& required for &$link to modify it inside here
		{
				if (!(defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED == 1 && $static == false)) {
					//trigger_error('closed', E_USER_WARNING);
					return;
				}
				
				if ($notifier == 'NOTIFY_SEFU_INTERCEPT') {
					$this->notify_sefu_intercept($callingClass, $notifier, $p1, $link, $page, $parameters, $connection, $add_session_id, $static, $use_dir_ws_catalog);
				}
				
				if ($notifier == 'NOTIFY_INIT_ADD_CRUMBS_GET_TERMS_LINK_PARAMETERS') {
					$link_parameters = $link;
					if (is_null($p1) && is_null($link_parameters)) {
						$p1 = $GLOBALS['get_terms']->fields;
						global $link_parameters;
					}
					
					$this->notify_init_add_crumbs_get_terms_link_parameters($callingClass, $notifier, $p1, $link_parameters);
					$link = $link_parameters;
				}
		}
}