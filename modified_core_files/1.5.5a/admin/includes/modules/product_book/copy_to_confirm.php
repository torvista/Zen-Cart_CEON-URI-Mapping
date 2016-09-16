<?php //jph mods
// 2006-03-27 : moku

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
	$products_id = zen_db_prepare_input($_POST['products_id']);
	$categories_id = zen_db_prepare_input($_POST['categories_id']);

// Copy attributes to duplicate product
	$products_id_from=$products_id;

	if ($_POST['copy_as'] == 'link') {
		if ($categories_id != $current_category_id) {
			$check = $db->Execute("select count(*) as total
									from " . TABLE_PRODUCTS_TO_CATEGORIES . "
									where products_id = '" . (int)$products_id . "'
									and categories_id = '" . (int)$categories_id . "'");
			if ($check->fields['total'] < '1') {
				$db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
								values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
								
			zen_record_admin_activity('Product ' . (int)$products_id . ' copied as link to category ' . (int)$categories_id . ' via admin console.', 'info');
              }
		} else {
			$messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
		}
	} elseif ($_POST['copy_as'] == 'duplicate') {
		$old_products_id = (int)$products_id;
		$product = $db->Execute("select products_type, products_quantity, products_model, products_image,
									products_price, products_virtual, products_date_available, products_weight,
									products_tax_class_id, manufacturers_id,
									products_quantity_order_min, products_quantity_order_units,
									products_priced_by_attribute, product_is_free, product_is_call,
									products_quantity_mixed, product_is_always_free_shipping,
									products_qty_box_status, products_quantity_order_max, products_sort_order,
									products_price_sorter, master_categories_id
								from " . TABLE_PRODUCTS . "
								where products_id = '" . (int)$products_id . "'");

		$db->Execute("insert into " . TABLE_PRODUCTS . "
							(products_type,
							products_quantity,
							products_model,
							products_image,
							products_price,
							products_virtual,
							products_date_added,
							products_date_available,
							products_weight,
							products_status,
							products_tax_class_id,
							manufacturers_id,
							products_quantity_order_min,
							products_quantity_order_units,
							products_priced_by_attribute,
							product_is_free,
							product_is_call,
							products_quantity_mixed,
							product_is_always_free_shipping,
							products_qty_box_status,
							products_quantity_order_max,
							products_sort_order,
							products_price_sorter,
							master_categories_id
							)
					values ('" . zen_db_input($product->fields['products_type']) . "',
							'" . zen_db_input($product->fields['products_quantity']) . "',
							'" . zen_db_input($product->fields['products_model']) . "',
							'" . zen_db_input($product->fields['products_image']) . "',
							'" . zen_db_input($product->fields['products_price']) . "',
							'" . zen_db_input($product->fields['products_virtual']) . "',
							now(),
							'" . zen_db_input($product->fields['products_date_available']) . "',
							'" . zen_db_input($product->fields['products_weight']) . "',
							'0',
							'" . (int)$product->fields['products_tax_class_id'] . "',
							'" . (int)$product->fields['manufacturers_id'] . "',
							'" . zen_db_input($product->fields['products_quantity_order_min']) . "',
							'" . zen_db_input($product->fields['products_quantity_order_units']) . "',
							'" . zen_db_input($product->fields['products_priced_by_attribute']) . "',
							'" . (int)$product->fields['product_is_free'] . "',
							'" . (int)$product->fields['product_is_call'] . "',
							'" . (int)$product->fields['products_quantity_mixed'] . "',
							'" . zen_db_input($product->fields['product_is_always_free_shipping']) . "',
							'" . zen_db_input($product->fields['products_qty_box_status']) . "',
							'" . zen_db_input($product->fields['products_quantity_order_max']) . "',
							'" . zen_db_input($product->fields['products_sort_order']) . "',
							'" . zen_db_input($product->fields['products_price_sorter']) . "',
							'" . zen_db_input($product->fields['master_categories_id']) . "'
							)
					");

		$dup_products_id = $db->Insert_ID();

		$book_extra = $db->Execute("select book_color_id, book_condition_id,
											subtitle, pub_date, size, pages,
											misc_1, misc_2, misc_bool_1, misc_bool_2" .
							//added fields jph
							  ",misc_3,
  								misc_4,
  								misc_5,
  								misc_6,
  								misc_7,
  								misc_8,
  								misc_9,
  								misc_10,
  								misc_bool_3,
  								misc_bool_4,
  								misc_bool_5,
  								misc_bool_6,
  								misc_int_5_1,
  								misc_int_5_2,
  								misc_int_5_3, 
  								misc_int_11_1,
  								misc_int_11_2,
  								misc_int_11_3" .
							//====================
									" from " . TABLE_PRODUCT_BOOK_EXTRA . "
									where products_id = '" . (int)$products_id . "'");

		$db->Execute("insert into " . TABLE_PRODUCT_BOOK_EXTRA . "
								(products_id,
								book_color_id,
								book_condition_id,
								subtitle,
								pub_date,
                                size,
								pages,
								misc_1,
								misc_2,
								misc_bool_1,
								misc_bool_2" . 
					//add the jph fields
							  ",misc_3,
  								misc_4,
  								misc_5,
  								misc_6,
  								misc_7,
  								misc_8,
  								misc_9,
  								misc_10,
  								misc_bool_3,
  								misc_bool_4,
  								misc_bool_5,
  								misc_bool_6,
  								misc_int_5_1,
  								misc_int_5_2,
  								misc_int_5_3, 
  								misc_int_11_1,
  								misc_int_11_2,
  								misc_int_11_3 " .
					//================
								")
						values ('" . (int)$dup_products_id . "',
								'" . zen_db_input($book_extra->fields['book_color_id']) . "',
								'" . zen_db_input($book_extra->fields['book_condition_id']) . "',
								'" . zen_db_input($book_extra->fields['subtitle']) . "',
								'" . zen_db_input($book_extra->fields['pub_date']) . "',
								'" . zen_db_input($book_extra->fields['size']) . "',
								'" . zen_db_input($book_extra->fields['pages']) . "',
								'" . zen_db_input($book_extra->fields['misc_1']) . "',
								'" . zen_db_input($book_extra->fields['misc_2']) . "',
								'" . zen_db_input($book_extra->fields['misc_bool_1']) . "',
								'" . zen_db_input($book_extra->fields['misc_bool_2']) . "'," . 
				//add the jph fields
							    "'" . zen_db_input($book_extra->fields['misc_3']) . "',
  								'" . zen_db_input($book_extra->fields['misc_4']) . "',
  								'" . zen_db_input($book_extra->fields['misc_5']) . "',
  								'" . zen_db_input($book_extra->fields['misc_6']) . "',
  								'" . zen_db_input($book_extra->fields['misc_7']) . "',
  								'" . zen_db_input($book_extra->fields['misc_8']) . "',
  								'" . zen_db_input($book_extra->fields['misc_9']) . "',
  								'" . zen_db_input($book_extra->fields['misc_10']) . "',
  								'" . zen_db_input($book_extra->fields['misc_bool_3']) . "',
  								'" . zen_db_input($book_extra->fields['misc_bool_4']) . "',
  								'" . zen_db_input($book_extra->fields['misc_bool_5']) . "',
  								'" . zen_db_input($book_extra->fields['misc_bool_6']) . "',
  								'" . zen_db_input($book_extra->fields['misc_int_5_1']) . "',
  								'" . zen_db_input($book_extra->fields['misc_int_5_2']) . "',
  								'" . zen_db_input($book_extra->fields['misc_int_5_3']) . "',
								'" . zen_db_input($book_extra->fields['misc_int_11_1']) . "',
							    '" . zen_db_input($book_extra->fields['misc_int_11_2']) . "',
  								'" . zen_db_input($book_extra->fields['misc_int_11_3']) . "'" .
					//================								
								")" 
					);

		$book_authors = $db->Execute("select a.book_authors_id
									from
										" . TABLE_BOOKS_TO_AUTHORS . " a,
										" . TABLE_BOOK_AUTHORS_INFO . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_authors_id = b.book_authors_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_authors->EOF) {
			$new_id = $book_authors->fields['book_authors_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_AUTHORS . " (products_id, book_authors_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_authors->MoveNext();
		}

		$book_genres = $db->Execute("select a.book_genre_id
									from
										" . TABLE_BOOKS_TO_GENRES . " a,
										" . TABLE_BOOK_GENRE_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_genre_id = b.book_genre_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_genres->EOF) {
			$new_id = $book_genres->fields['book_genre_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_GENRES . " (products_id, book_genre_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_genres->MoveNext();
		}

		$book_types = $db->Execute("select a.book_type_id
									from
										" . TABLE_BOOKS_TO_TYPES . " a,
										" . TABLE_BOOK_TYPE_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_type_id = b.book_type_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_types->EOF) {
			$new_id = $book_types->fields['book_type_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_TYPES . " (products_id, book_type_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_types->MoveNext();
		}

        if (defined('TABLE_PRODUCTS_LANGUAGES')) {
          $book_languages = $db->Execute("select products_languages_id
                                          from " . TABLE_BOOKS_TO_LANGUAGES . " a
                                          where products_id = '" . (int)$products_id . "'");
          while (!$book_languages->EOF) {
              $new_id = $book_languages->fields['products_languages_id'];
              $db->Execute("insert into " . TABLE_BOOKS_TO_LANGUAGES . " (products_id, products_languages_id)
                              values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
              $book_languages->MoveNext();
          }
        }
//========add jph extra dd's
//===================dd1

		$book_dd1 = $db->Execute("select a.book_dd1_id
									from
										" . TABLE_BOOKS_TO_DD1 . " a,
										" . TABLE_BOOK_DD1_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_dd1_id = b.book_dd1_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_dd1->EOF) {
			$new_id = $book_dd1->fields['book_dd1_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_DD1 . " (products_id, book_dd1_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_dd1->MoveNext();
		}
//===================== end dd1	
//===================dd2
		$book_dd2 = $db->Execute("select a.book_dd2_id
									from
										" . TABLE_BOOKS_TO_DD2 . " a,
										" . TABLE_BOOK_DD2_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_dd2_id = b.book_dd2_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_dd2->EOF) {
			$new_id = $book_dd2->fields['book_dd2_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_DD2 . " (products_id, book_dd2_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_dd2->MoveNext();
		}			
//===================== end dd2	
//===================dd3
		$book_dd3 = $db->Execute("select a.book_dd3_id
									from
										" . TABLE_BOOKS_TO_DD3 . " a,
										" . TABLE_BOOK_DD3_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_dd3_id = b.book_dd3_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_dd3->EOF) {
			$new_id = $book_dd3->fields['book_dd3_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_DD3 . " (products_id, book_dd3_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_dd3->MoveNext();
		}			
//===================== end dd3	
//===================dd4

		$book_dd4 = $db->Execute("select a.book_dd4_id
									from
										" . TABLE_BOOKS_TO_DD4 . " a,
										" . TABLE_BOOK_DD4_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_dd4_id = b.book_dd4_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_dd4->EOF) {
			$new_id = $book_dd4->fields['book_dd4_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_DD4 . " (products_id, book_dd4_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_dd4->MoveNext();
		}			
//===================== end dd4	
//===================dd5

		$book_dd5 = $db->Execute("select a.book_dd5_id
									from
										" . TABLE_BOOKS_TO_DD5 . " a,
										" . TABLE_BOOK_DD5_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_dd5_id = b.book_dd5_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_dd5->EOF) {
			$new_id = $book_dd5->fields['book_dd5_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_DD5 . " (products_id, book_dd5_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_dd5->MoveNext();
		}			
//===================== end dd5	
//===================dd6

		$book_dd6 = $db->Execute("select a.book_dd6_id
									from
										" . TABLE_BOOKS_TO_DD6 . " a,
										" . TABLE_BOOK_DD6_DESCRIPTION . " b
									where
										a.products_id = '" . (int)$products_id . "'
										and a.book_dd6_id = b.book_dd6_id
										and b.language_id = '" . (int)$_SESSION['languages_id'] . "'");
		while (!$book_dd6->EOF) {
			$new_id = $book_dd6->fields['book_dd6_id'];
			$db->Execute("insert into " . TABLE_BOOKS_TO_DD6 . " (products_id, book_dd6_id)
							values ('" . (int)$dup_products_id . "', '" . (int)$new_id . "')");
			$book_dd6->MoveNext();
		}			
//===================== end dd6	
//==================
		$description = $db->Execute("select language_id, products_name, products_description, products_url
									from " . TABLE_PRODUCTS_DESCRIPTION . "
									where products_id = '" . (int)$products_id . "'");
		while (!$description->EOF) {
			$db->Execute("insert into " . TABLE_PRODUCTS_DESCRIPTION . "
							(products_id,
							language_id,
							products_name,
							products_description,
							products_url,
							products_viewed)
						values ('" . (int)$dup_products_id . "',
								'" . (int)$description->fields['language_id'] . "',
								'" . zen_db_input($description->fields['products_name']) . "',
								'" . zen_db_input($description->fields['products_description']) . "',
								'" . zen_db_input($description->fields['products_url']) . "',
								'0')
						");
			$description->MoveNext();
		}

		$db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
					values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')
					");
		$products_id = $dup_products_id;
		$description->MoveNext();
// FIX HERE
/////////////////////////////////////////////////////////////////////////////////////////////
// Copy attributes to duplicate product
// moved above						$products_id_from=zen_db_input($products_id);
		$products_id_to= $dup_products_id;
		$products_id = $dup_products_id;

if ( $_POST['copy_attributes']=='copy_attributes_yes' and $_POST['copy_as'] == 'duplicate' ) {
	// $products_id_to= $copy_to_products_id;
	// $products_id_from = $pID;
//						$copy_attributes_delete_first='1';
//						$copy_attributes_duplicates_skipped='1';
//						$copy_attributes_duplicates_overwrite='0';

		if (DOWNLOAD_ENABLED == 'true') {
			$copy_attributes_include_downloads='1';
			$copy_attributes_include_filename='1';
		} else {
			$copy_attributes_include_downloads='0';
			$copy_attributes_include_filename='0';
		}

		zen_copy_products_attributes($products_id_from, $products_id_to);
}
// EOF: Attributes Copy on non-linked
/////////////////////////////////////////////////////////////////////

		// copy product discounts to duplicate
		zen_copy_discounts_to_product($old_products_id, (int)$dup_products_id);
            
            zen_record_admin_activity('Product ' . (int)$old_products_id . ' duplicated as product ' . (int)$dup_products_id . ' via admin console.', 'info');
			// BEGIN CEON URI MAPPING 1 of 1
            require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
            
            $ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
            
            $ceon_uri_mapping_admin->copyToConfirmHandler($products_id_from, $products_id_to,
              $product->fields['products_type'], $zc_products->get_handler($product->fields['products_type']),
              $categories_id);
            
            // END CEON URI MAPPING 1 of 1

	}

	// reset products_price_sorter for searches etc.
	zen_update_products_price_sorter($products_id);

}
zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
?>