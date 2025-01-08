<?php

/**
 * Ceon URI Mapping Admin Page Registration.
 *
 * Attempts to create a link to the Ceon URI Mapping Config Utility in the Zen Cart admin menu in Zen Cart 1.5+.
 * After running successfully once, this file deletes itself as it is never needed again!
 * 
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_admin_page_reg.php 2025-01-08 torvista
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

// This file should normally only need to be run once, but if the user hasn't installed the software properly, it
// may need to be run again. Flag tracks the situation
// set autodelete to false: confusing when testing locally before copying files to server and this file is missing
$can_autodelete = false;

if (function_exists('zen_register_admin_page')) {
	if (!zen_page_key_exists('ceon_uri_mapping_config')) {
		// Register the Ceon URI Mapping Config Utility with the Zen Cart admin
		
		// Quick sanity check in case the user hasn't uploaded a necessary file on which this depends
		$error_messages = [];
		
		if (!defined('FILENAME_CEON_URI_MAPPING_CONFIG')) {
			$error_messages[] = 'The Ceon URI Mapping filename define is missing. Please check that the file ' .
				DIR_WS_INCLUDES . 'extra_datafiles/' . 'ceon_uri_mapping_filenames.php has been uploaded.';
			
			$can_autodelete = false;
			
		}
		
    if (count($error_messages) > 0) {
			// Let the user know that there are problem(s) with the installation
			foreach ($error_messages as $error_message) {
				print '<p style="background: #fcc; border: 1px solid #f00; margin: 1em; padding: 0.4em;">' .
					'Error: ' . $error_message . "</p>\n";
			}
		} else {
			// The necessary file is in place so can register the admin page and have the menu item created
			zen_register_admin_page('ceon_uri_mapping_config', 'BOX_CEON_URI_MAPPING',
				'FILENAME_CEON_URI_MAPPING_CONFIG', '', 'modules', 'Y', 40);
		}
	}
}

if ($can_autodelete) {
	// Either the config utility file has been registered, or it doesn't need to be. Can stop the wasteful process
	// of having this script run again by having it delete itself
	unlink(DIR_WS_INCLUDES . 'functions/extra_functions/ceon_uri_mapping_admin_page_reg.php');
}
