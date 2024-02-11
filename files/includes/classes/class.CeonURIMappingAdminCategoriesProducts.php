<?php

/**
 * Ceon URI Mapping Admin Shared Categories/Products Class.
 *
 * This file contains a class with shared methods necessary to handle the Ceon URI Mapping admin functionality for
 * categories or products.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: class.CeonURIMappingAdminCategoriesProducts.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the parent class if not already loaded
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');


// {{{ constants

// Other constants defined in page-type admin class files
define('CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_PATH_PART_WITH_NO_NAME', -1);

// }}}


// {{{ CeonURIMappingAdminCategoriesProducts

/**
 * Provides shared functionality for the Ceon URI Mapping admin functionality for categories and products.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2008 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
class CeonURIMappingAdminCategoriesProducts extends CeonURIMappingAdmin
{
	// {{{ Class Constructor
	
	/**
	 * Creates a new instance of the CeonURIMappingAdminCategoriesProducts class.
	 * 
	 * @access  public
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	// }}}
	
	
	// {{{ autogenCategoryOrProductURIMapping()
	
	/**
	 * Generates a URI mapping for a category or a product, for the specified language.
	 *
	 * @access  public
	 * @param   integer   $id              The ID of the category/product.
	 * @param   string    $type            Whether the ID corresponds to a category or a product.
	 * @param   integer   $parent_category_id   The ID of the parent category (used if the details in the database
	 *                                          could be out of date as new information is being submitted when the
	 *                                          URI is being generated).
	 * @param   string    $name                 The name of category/product (used if new information is being
	 *                                          submitted when the URI is being generated).
	 * @param   string    $language_code   The ISO 639 language code of the language.
	 * @param   integer   $language_id     The Zen Cart language ID for the language.
	 * @return  string    The auto-generated URI for the category/product and language.
	 */
	public function autogenCategoryOrProductURIMapping($id, $type, $parent_category_id, $name, $language_code,
		$language_id)
	{
		//global $db;
		
		// Get the complete path to this category/product, or the parent category if this is a new category/product
		if (is_null($name)) {
			$category_and_product_path_array = $this->getCategoryOrProductPath($id, $type, $language_id);
		} else {
			$category_and_product_path_array =
				$this->getCategoryOrProductPath($parent_category_id, 'category', $language_id);
		}
		
		$category_and_product_path_array = array_reverse($category_and_product_path_array);
		
		if (!is_null($name) || count($category_and_product_path_array) == 0) {
			// Must add the new category/product's name to the path array
			$category_and_product_path_array[] = $name;
		}
		
		// Must not generate URIs for any category or product which has no name.. would conflict with parent
		// category (manual mapping can be used if that behaviour is required).
		$num_categories_products = count($category_and_product_path_array);
		
		for ($i = 0; $i < $num_categories_products; $i++) {
			if (strlen($category_and_product_path_array[$i]) == 0 || $category_and_product_path_array[$i] == '/' ||
					$category_and_product_path_array[$i] == '\\') {
				if ($i == ($num_categories_products - 1)) {
					if ($type == 'product') {
						return CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_PRODUCT_WITH_NO_NAME;
					} else {
						return CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_WITH_NO_NAME;
					}
				}
				
				return CEON_URI_MAPPING_GENERATION_ATTEMPT_FOR_CATEGORY_PATH_PART_WITH_NO_NAME;
			}
		}
		
		for ($i = 0, $n = count($category_and_product_path_array); $i < $n; $i++) {
			$category_and_product_path_array[$i] = $this->_convertStringForURI(
				$category_and_product_path_array[$i], $language_code);
		}
		
		// Language code to be auto-included, then push to the beginning of the URI
		if ($this->_autoLanguageCodeAdd()) {
			array_unshift($category_and_product_path_array, $language_code);
		}
		
		// Implode the path array into a URI
		$uri = implode('/', $category_and_product_path_array);
		
		// Prepend the URI with the store's directory (if any), otherwise add a "root" slash
		if (strlen(DIR_WS_CATALOG) > 0) {
			$uri = DIR_WS_CATALOG . $uri;
		} else {
			$uri = '/' . $uri;
		}
		
		return $uri;
	}
	
	// }}}
	
	
	// {{{ getCategoryOrProductPath()
	
	/**
	 * Looks up the hierarchy of the parent categories for the given category/product.
	 *
	 * @access  public
	 * @param   integer   $id            The ID of the category/product.
	 * @param   string    $for           Either 'category' or 'product'.
	 * @param   integer   $language_id   The ID of the language of the category names to lookup.
	 * @param   array     $categories_array   Used internally as part of recursion process.
	 * @return  array     A hierarchical array of the parent categories (if any) for the given category/product,
	 *                    from the "leaf" product/category back to the "root/top" category.
	 */
	public function getCategoryOrProductPath($id, $for, $language_id, $categories_array = '')
	{
		global $db;
		
		if (!is_array($categories_array)) {
			$categories_array = array();
		}
		
		if ($id == 0) {
			return $categories_array;
		}
		
		if ($for == 'product') {
			$master_category = $db->Execute("
				SELECT
					pd.products_name,
					p.master_categories_id
				FROM
					" . TABLE_PRODUCTS . " p
				LEFT JOIN
					" . TABLE_PRODUCTS_DESCRIPTION . " pd
				ON
					pd.products_id = p.products_id
				WHERE
					p.products_id = '" . (int) $id . "'
				AND
					pd.language_id = '" . (int) $language_id . "';");
			
			if (!$master_category->EOF) {
				$categories_array[] = $master_category->fields['products_name'];
				
				if ($master_category->fields['master_categories_id'] == '0') {
					// Product uses root/top category
				} else {
					$category = $db->Execute("
						SELECT
							cd.categories_name,
							c.parent_id
						FROM
							" . TABLE_CATEGORIES . " c,
							" . TABLE_CATEGORIES_DESCRIPTION . " cd
						WHERE
							c.categories_id = '" .
								(int) $master_category->fields['master_categories_id'] . "'
						AND
							c.categories_id = cd.categories_id
						AND
							cd.language_id = '" . (int) $language_id . "'");
					
					$categories_array[] = $category->fields['categories_name'];
					
					if ((zen_not_null($category->fields['parent_id'])) &&
							($category->fields['parent_id'] != '0')) {
						$categories_array = $this->getCategoryOrProductPath($category->fields['parent_id'],
							'category', $language_id, $categories_array);
					}
				}
			}
		} else if ($for == 'category') {
			$category = $db->Execute("
				SELECT
					cd.categories_name,
					c.parent_id
				FROM
					" . TABLE_CATEGORIES . " c,
					" . TABLE_CATEGORIES_DESCRIPTION . " cd
				WHERE
					c.categories_id = '" . (int) $id . "'
				AND
					c.categories_id = cd.categories_id
				AND
					cd.language_id = '" . (int) $language_id . "'");
			
			$categories_array[] = $category->fields['categories_name'];
			
			if ((zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0')) {
				$categories_array = $this->getCategoryOrProductPath($category->fields['parent_id'], 'category',
					$language_id, $categories_array);
			}
		}
		
		return $categories_array;
	}
	
	// }}}
}

// }}}
