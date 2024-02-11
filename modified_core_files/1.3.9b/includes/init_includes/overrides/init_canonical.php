<?php
/**
 * canonical link handling
 *
 * @package initSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_canonical.php 15763 2010-03-31 20:05:22Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// BEGIN CEON URI MAPPING 1 of 2
if (isset($ceon_uri_mapping_canonical_uri)) {
  // Use the canonical URI generated by Ceon URI Mapping
  $canonicalLink = $ceon_uri_mapping_canonical_uri;
} else {
  // Only use Zen Cart's canonical URI functionality if Ceon URI Mapping Canonical functionality not in use
// END CEON URI MAPPING 1 of 2
// CANONICAL HANDLING:
/**
 * for products linked to multiple categories:
 */
if (strstr($current_page, '_info') && isset($_GET['products_id'])) {
  $canonicalLink = zen_href_link($current_page, 'cPath=' . zen_get_generated_category_path_rev(zen_get_products_category_id($_GET['products_id'])) . '&products_id=' . $_GET['products_id']);
}
/**
 * for product listings:
 */
if ($current_page == 'index' && isset($_GET['cPath'])) {
  $excludeParams = array('zenid', 'action', 'main_page', 'currency', 'typefilter');
  $excludeParams[] = 'language';
  $excludeParams[] = 'disp_order';
  $excludeParams[] = 'page';
  $excludeParams[] = 'sort';
  $excludeParams[] = 'alpha_filter_id';
  $excludeParams[] = 'filter_id';
  $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams));
}
// BEGIN CEON URI MAPPING 2 of 2
}
// END CEON URI MAPPING 2 of 2