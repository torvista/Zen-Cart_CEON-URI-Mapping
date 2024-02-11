<?php

/**
 * Ceon URI Mapping Category URI Mappings Admin Functionality.
 *
 * This file contains a class with the methods necessary to handle URI mappings for categories.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminCategories.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdminCategoriesProducts.php');


// {{{ constants

define('CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_WITH_NO_NAME', -2);

// }}}


// {{{ CeonURIMappingAdminCategories

/**
 * Handles the URI mappings for categories.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2008 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminCategories extends CeonURIMappingAdminCategoriesProducts
{
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingAdminCategories class.
	 * 
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	// }}}
	
	
	// {{{ autogenCategoryURIMapping()
	
	/**
	 * Generates a URI for a category, for the specified language.
	 *
	 * @access  public
	 * @param   integer   $id              The ID of the category.
	 * @param   integer   $parent_category_id   The ID of the parent category (used if the details in the database
	 *                                          could be out of date as new information is being submitted when the
	 *                                          URI is being generated).
	 * @param   string    $name            The name of category (used if new information is being submitted when
	 *                                     the URI is being generated).
	 * @param   string    $language_code   The ISO 639 language code of the language.
	 * @param   integer   $language_id     The Zen Cart language ID for the language.
	 * @return  string    The auto-generated URI for the category and language.
	 */
	public function autogenCategoryURIMapping($id, $parent_category_id, $name, $language_code, $language_id)
	{
		return $this->autogenCategoryOrProductURIMapping($id, 'category', $parent_category_id, $name,
			$language_code, $language_id);
	}
	
	// }}}
}

// }}}
