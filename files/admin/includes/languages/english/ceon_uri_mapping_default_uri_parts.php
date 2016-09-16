<?php

/** 
 * Ceon URI Mapping Default Product Related Page URI Part Language Defines.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_default_uri_parts.php 1027 2012-07-17 20:31:10Z conor $
 */

/**
 * The language defines for the default URI part text to be used for the product related pages' URI
 * parts (the product reviews/tell-a-friend pages).
 *
 * These defines are only used when installing or updating the module, as fallbacks, if the store
 * hasn't already specified custom URI part text for a language the store uses.
 *
 * 1) If translating this for other languages: first, Thanks!
 *
 * Copy this file to admin/includes/LANGUAGE/ceon_uri_mapping_default_uri_parts.php
 *
 * 2) EACH LANGUAGE REQUIRES A UNIQUELY NAMED DEFINE.
 *
 * The last two letters in the define names should be the language's two letter code.
 *
 * 3) The entire define name must be uppercase.
 *
 * For example:
 *
 * DEFAULT_URI_PART_PRODUCT_REVIEWS_DE for German or DEFAULT_URI_PART_PRODUCT_REVIEWS_FR for French
 */
define('DEFAULT_URI_PART_PRODUCT_REVIEWS_EN', 'Reviews');
define('DEFAULT_URI_PART_REVIEWS_INFO_EN', 'Review');
define('DEFAULT_URI_PART_REVIEWS_WRITE_EN', 'Add a Review');
define('DEFAULT_URI_PART_TELL_A_FRIEND_EN', 'Tell a Friend');

?>