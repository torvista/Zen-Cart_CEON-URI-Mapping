<?php
/**
 * Observer for Ceon URI Mapping ask a question creation. Watches includes/modules/pages/ask_a_question/header_php.php
 * This observer must load before includes/modules/pages/ask_a_question/header_php.php is loaded (by includes/auto_loaders/config.ceon_ask_a_question.php)
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Ceon Support
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     2020 - 5.0.2
 **/

class zcObserverCeonAskAQuestion extends base
{
	public function __construct() {
		// Notifier was added in Zen Cart 1.5.6 accounting for the change of the parameter products_id to pid in the file includes/modules/pages/ask_a_question/header_php.php.
		$this->attach($this, array('NOTIFY_HEADER_START_ASK_A_QUESTION'));
	}
	
	/**
	 * Notifier information:
	 * in ZC 1.5.7 (and all sub-versions):
	 * $zco_notifier->notify('NOTIFY_HEADER_START_ASK_A_QUESTION');
	 **/
	public function notify_header_start_ask_a_question(&$cClass, $notifier) {
		if (!(defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED == '1')) {
			return;
		}
		if (isset($_GET['products_id']) && !isset($_GET['pid'])) {
			$_GET['pid'] = $_GET['products_id'];
		}
	}
	
	public function updateNotifyHeaderStartAskAQuestion(&$cClass, $notifier) {
		$this->notify_header_start_ask_a_question($cClass, $notifier);
	}

	
	/**
	 * This function is expected to load only if the camelcased notifier is not found or if installed in a version of Zen Cart that
	 *   does not support loading the camelcased update{Notifier} function, i.e. pre-Zen Cart 1.5.3.  Therefore the function is
	 *   written from the perspective of such previous version and that the camelcased function exists.
	 **/
	public function update(&$cClass, $notifier) {
		$this->notify_header_start_ask_a_question($cClass, $notifier);
	}
}
