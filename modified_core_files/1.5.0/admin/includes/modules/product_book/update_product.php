<?php //v4 dec 2007  line 78 jph, zc138 removes a few code lines
//beta2 adds copy names across on collate
//multiple LANG mods marked jph may 2007
// 2006-03-27 : moku

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
//defensive
if (!isset($_SESSION['language'])) {
  die('Language Session Missing');
}

//SPECIAL v3 use of misc_bool_4, at first language in collect_info
	$collate_values = false;
	if((int)$_POST['misc_bool_4'] == 1) $collate_values = true;
//get language array
	$languages_raw = zen_get_languages();
	$languages = array();
//put current page language  first
	$omit = '';
	for ($i=0, $n=sizeof($languages_raw); $i<$n; $i++) {
		if((int)$_SESSION['languages_id'] == (int)$languages_raw[$i]['id']) {
			$languages[] = $languages_raw[$i];	
			$omit = $i;
		}
	}
	//copy array
	for ($i=0, $n=sizeof($languages_raw); $i<$n; $i++) {
		if($omit != $i) $languages[] = $languages_raw[$i];
	}
//===============
if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
	$action = 'new_product';
} else {
	if (isset($_GET['pID'])) $products_id = zen_db_prepare_input($_GET['pID']);
	$products_date_available = zen_db_prepare_input($_POST['products_date_available']);

	$products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

	// Data-cleaning to prevent MySQL5 data-type mismatch errors:
	$tmp_value = zen_db_prepare_input($_POST['products_quantity']);
	$products_quantity = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
	$tmp_value = zen_db_prepare_input($_POST['products_price']);
	$products_price = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
	$tmp_value = zen_db_prepare_input($_POST['products_weight']);
	$products_weight = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
	$tmp_value = zen_db_prepare_input($_POST['manufacturers_id']);
	$manufacturers_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

	$sql_data_array = array(
							'products_quantity' => $products_quantity,
							'products_type' => zen_db_prepare_input($_GET['product_type']),
							'products_model' => zen_db_prepare_input($_POST['products_model']),
							'products_price' => $products_price,
							'products_date_available' => $products_date_available,
							'products_weight' => $products_weight,
							'products_status' => zen_db_prepare_input($_POST['products_status']),
							'products_virtual' => zen_db_prepare_input($_POST['products_virtual']),
							'products_tax_class_id' => zen_db_prepare_input($_POST['products_tax_class_id']),
							'manufacturers_id' => $manufacturers_id,
							'products_quantity_order_min' => zen_db_prepare_input($_POST['products_quantity_order_min']),
							'products_quantity_order_units' => zen_db_prepare_input($_POST['products_quantity_order_units']),
							'products_priced_by_attribute' => zen_db_prepare_input($_POST['products_priced_by_attribute']),
							'product_is_free' => zen_db_prepare_input($_POST['product_is_free']),
							'product_is_call' => zen_db_prepare_input($_POST['product_is_call']),
							'products_quantity_mixed' => zen_db_prepare_input($_POST['products_quantity_mixed']),
							'product_is_always_free_shipping' => zen_db_prepare_input($_POST['product_is_always_free_shipping']),
							'products_qty_box_status' => zen_db_prepare_input($_POST['products_qty_box_status']),
							'products_quantity_order_max' => zen_db_prepare_input($_POST['products_quantity_order_max']),
							'products_sort_order' => zen_db_prepare_input($_POST['products_sort_order']),
							'products_discount_type' => zen_db_prepare_input($_POST['products_discount_type']),
							'products_discount_type_from' => zen_db_prepare_input($_POST['products_discount_type_from']),
							'products_price_sorter' => zen_db_prepare_input($_POST['products_price_sorter'])
							);

// when set to none remove from database
//======================jph for v4, 138 code removes the if
//	if (isset($_POST['products_image']) && zen_not_null($_POST['products_image']) && (!is_numeric(strpos($_POST['products_image'],'none'))) ) {
		$sql_data_array['products_image'] = zen_db_prepare_input($_POST['products_image']);
		$new_image= 'true';
/*	} else {
		$sql_data_array['products_image'] = '';
		$new_image= 'false';
	}
	*/

	if ($action == 'insert_product') {
		$insert_sql_data = array( 'products_date_added' => 'now()',
									'master_categories_id' => (int)$current_category_id);

		$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

		zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
		$products_id = zen_db_insert_id();

		// reset products_price_sorter for searches etc.
		zen_update_products_price_sorter($products_id);

		$db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
						values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
//// INSERT PRODUCT-TYPE-SPECIFIC *INSERTS* HERE //////
//===================================NEW BOOK! Field entries values from the collect info page 

// Add all the new Authors MULTIPLE
//POST['book_authors_id1'] .. sizeof gives array size of authors for language for $i = 1
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_authors_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_authors_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_AUTHORS . " (products_id, book_authors_id, language_idta)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_genres_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_genres_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
			// Add all the new Genres  MULTIPLE array of values for each language
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_GENRES . " (products_id, book_genre_id, language_idg)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
	
//----------------- Add all the new Types, single selection per language but could process multiple
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_types_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_types_id' . $i];
		}
//beta2 correction, added lang field value
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_TYPES . " (products_id, book_type_id, language_idt)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}

//=============jph ADD the new dd fields as multiple selection per lang
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd1_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd1_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD1 . " (products_id, book_dd1_id, language_idd1)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd2_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd2_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD2 . " (products_id, book_dd2_id, language_idd2)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd3_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd3_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD3 . " (products_id, book_dd3_id, language_idd3)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd4_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd4_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);		
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD4 . " (products_id, book_dd4_id, language_idd4)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd5_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd5_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);		
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD5 . " (products_id, book_dd5_id, language_idd5)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//--------------------------
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd6_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd6_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);			
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD6 . " (products_id, book_dd6_id, language_idd6)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//=============================end jph ADD, below are one value whatever the language


		$tmp_value = zen_db_prepare_input($_POST['book_color_id']);
		$book_color_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
		$tmp_value = zen_db_prepare_input($_POST['book_condition_id']);
		$book_condition_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

		$sql_data_array = array('products_id' => $products_id,
								'book_color_id' => $book_color_id,
								'book_condition_id' => $book_condition_id,
								'subtitle' => zen_db_prepare_input($_POST['subtitle']),
								'pub_date' => zen_db_prepare_input($_POST['pub_date']),
								'size' => zen_db_prepare_input($_POST['size']),
								'pages' => zen_db_prepare_input($_POST['pages']),
								'misc_1' => zen_db_prepare_input($_POST['misc_1']),
								'misc_2' => zen_db_prepare_input($_POST['misc_2']),
								'misc_bool_1' => zen_db_prepare_input($_POST['misc_bool_1']),
								'misc_bool_2' => zen_db_prepare_input($_POST['misc_bool_2'])
					//add the jph fields
								 ,'misc_3' => zen_db_prepare_input($_POST['misc_3']),
  								'misc_4' => zen_db_prepare_input($_POST['misc_4']),
  								'misc_5' => zen_db_prepare_input($_POST['misc_5']),
  								'misc_6' => zen_db_prepare_input($_POST['misc_6']),
  								'misc_7' => zen_db_prepare_input($_POST['misc_7']),
  								'misc_8' => zen_db_prepare_input($_POST['misc_8']),
  								'misc_9' => zen_db_prepare_input($_POST['misc_9']),
  								'misc_10' => zen_db_prepare_input($_POST['misc_10']),
  								'misc_bool_3' => zen_db_prepare_input($_POST['misc_bool_3']),
  								'misc_bool_4' => zen_db_prepare_input($_POST['misc_bool_4']),
  								'misc_bool_5' => zen_db_prepare_input($_POST['misc_bool_5']),
  								'misc_bool_6' => zen_db_prepare_input($_POST['misc_bool_6']),
  								'misc_int_5_1'  => zen_db_prepare_input($_POST['misc_int_5_1']),
  								'misc_int_5_2'  => zen_db_prepare_input($_POST['misc_int_5_2']),
  								'misc_int_5_3'  => zen_db_prepare_input($_POST['misc_int_5_3']),     
  								'misc_int_11_1'  => zen_db_prepare_input($_POST['misc_int_11_1']),
  								'misc_int_11_2'  => zen_db_prepare_input($_POST['misc_int_11_2']),
  								'misc_int_11_3'  => zen_db_prepare_input($_POST['misc_int_11_3'])  
					//================
								);

		zen_db_perform(TABLE_PRODUCT_BOOK_EXTRA, $sql_data_array);

////    *END OF PRODUCT-TYPE-SPECIFIC INSERTS* ////////
///////////////////////////////////////////////////////

	} elseif ($action == 'update_product') {
		$update_sql_data = array( 'products_last_modified' => 'now()',
									'master_categories_id' => ($_POST['master_category'] > 0 ? zen_db_prepare_input($_POST['master_category']) : zen_db_prepare_input($_POST['master_categories_id'])));

		$sql_data_array = array_merge($sql_data_array, $update_sql_data);

		zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

		// reset products_price_sorter for searches etc.
		zen_update_products_price_sorter((int)$products_id);

///////////////////////////////////////////////////////
//// INSERT PRODUCT-TYPE-SPECIFIC *UPDATES* HERE //////

//===================================UPDATE existing BOOK! Field entries values from the collect info page 

// Remove all the previous Authors (ALL languages)
		$db->Execute("delete from " . TABLE_BOOKS_TO_AUTHORS . "
						where products_id = '" . (int)$products_id . "'");
//LANG MOD uses new lang id field............. Add all the new Authors and update
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
			//make all langage selections of this dropdown the selection of $i =0
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_authors_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_authors_id' . $i];
		}
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
			$tmp_value = zen_db_prepare_input($values_array[$i][$j]);
			$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
			$db->Execute("insert into " . TABLE_BOOKS_TO_AUTHORS . " (products_id, book_authors_id, language_idta)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//LANG MOD
// Remove all the previous Genres
		$db->Execute("delete from " . TABLE_BOOKS_TO_GENRES . "
						where products_id = '" . (int)$products_id . "'");
// Remove all the previous Types
		$db->Execute("delete from " . TABLE_BOOKS_TO_TYPES . "
						where products_id = '" . (int)$products_id . "'");

	$values_array = array();
	for ($k=0, $n=sizeof($languages); $k<$n; $k++) {
		$language_id = $languages[$k]['id'];
// Add all the new Genres values per language, I know itmay be multi dd value per lang
		if($collate_values) {
			if($k == 0) {
				$values_array[] = $_POST['book_genres_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_genres_id' . $k];
		}
		for ($i=0; $i<sizeof($values_array[$k]); $i++) {
			$tmp_value = zen_db_prepare_input($values_array[$k][$i]);
			$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
			$db->Execute("insert into " . TABLE_BOOKS_TO_GENRES . " (products_id, book_genre_id, language_idg)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//-------------------------------------------------
	$values_array = array();
	for ($k=0, $n=sizeof($languages); $k<$n; $k++) {
		$language_id = $languages[$k]['id'];
// Add all the new Types per language, its a single dd value per lang
		if($collate_values) {
			if($k == 0) {
				$values_array[] = $_POST['book_types_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_types_id' . $k];
		}
		//for ($i=0; $i<sizeof($_POST['book_types_id' . $k]); $i++) {
			$tmp_value = zen_db_prepare_input($values_array[$k]);
			$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
			$db->Execute("insert into " . TABLE_BOOKS_TO_TYPES . " (products_id, book_type_id, language_idt)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");

		//}
	}
//eof LANG MOD
//=============the 6 new dd fields are multiple dropdowns
// Remove all the previous dd1 and update: 'month' 
		$db->Execute("delete from " . TABLE_BOOKS_TO_DD1 . "
						where products_id = '" . (int)$products_id . "'");
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];		
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd1_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd1_id' . $i];
		}
				
			for ($j=0; $j<sizeof($values_array[$i]); $j++) {
				$tmp_value =  zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD1 . " (products_id, book_dd1_id, language_idd1)
							values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
			}
	}
//----------------------------------------
// Remove all the previous dd2 and update
		$db->Execute("delete from " . TABLE_BOOKS_TO_DD2 . "
						where products_id = '" . (int)$products_id . "'");
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];		
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd2_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd2_id' . $i];
		}
				
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
				$tmp_value =  zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD2 . " (products_id, book_dd2_id, language_idd2)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
// Remove all the previous dd3 and update
		$db->Execute("delete from " . TABLE_BOOKS_TO_DD3 . "
						where products_id = '" . (int)$products_id . "'");
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];		
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd3_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd3_id' . $i];
		}
				
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
				$tmp_value =  zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD3 . " (products_id, book_dd3_id, language_idd3)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
// Remove all the previous dd4 and update
		$db->Execute("delete from " . TABLE_BOOKS_TO_DD4 . "
						where products_id = '" . (int)$products_id . "'");
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];		
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd4_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd4_id' . $i];
		}
				
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
				$tmp_value =  zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD4 . " (products_id, book_dd4_id, language_idd4)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
		}
	}
//---------------------------------------
// Remove all the previous dd5 and update
		$db->Execute("delete from " . TABLE_BOOKS_TO_DD5 . "
						where products_id = '" . (int)$products_id . "'");
	
	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];		
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd5_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd5_id' . $i];
		}
				
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
				$tmp_value =  zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD5 . " (products_id, book_dd5_id, language_idd5)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
			}
	}
// Remove all the previous dd6 and update
		$db->Execute("delete from " . TABLE_BOOKS_TO_DD6 . "
						where products_id = '" . (int)$products_id . "'");

	$values_array = array();
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];		
		if($collate_values) {
			if($i == 0) {
				$values_array[] = $_POST['book_dd6_id0'];
			}else{
				$values_array[] = $values_array[0];
			}
		}else{
			$values_array[] = $_POST['book_dd6_id' . $i];
		}
				
		for ($j=0; $j<sizeof($values_array[$i]); $j++) {
				$tmp_value =  zen_db_prepare_input($values_array[$i][$j]);
				$new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
				$db->Execute("insert into " . TABLE_BOOKS_TO_DD6 . " (products_id, book_dd6_id, language_idd6)
								values ('" . (int)$products_id . "', '" . (int)$new_id . "', '" . $language_id . "')");
			}
	}
//=============================end jph ADD

//lang not copied to update code see above
// Remove all the previous Languages
		$db->Execute("delete from " . TABLE_BOOKS_TO_LANGUAGES . "
						where products_id = '" . (int)$products_id . "'");
// Add all the new Languages
        if (defined('TABLE_PRODUCTS_LANGUAGES')) {
          for ($i=0; $i<sizeof($_POST['book_languages_id']); $i++) {
              $tmp_value = zen_db_prepare_input($_POST['book_languages_id'][$i]);
              $new_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
              $db->Execute("insert into " . TABLE_BOOKS_TO_LANGUAGES . " (products_id, products_languages_id)
                              values ('" . (int)$products_id . "', '" . (int)$new_id . "')");
          }
        }
//single value for all languges.....
		$tmp_value = zen_db_prepare_input($_POST['book_color_id']);
		$book_color_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
		$tmp_value = zen_db_prepare_input($_POST['book_condition_id']);
		$book_condition_id = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

		$sql_data_array = array(
								'book_color_id' => $book_color_id,
								'book_condition_id' => $book_condition_id,
								'subtitle' => zen_db_prepare_input($_POST['subtitle']),
								'pub_date' => zen_db_prepare_input($_POST['pub_date']),
								'size' => zen_db_prepare_input($_POST['size']),
								'pages' => zen_db_prepare_input($_POST['pages']),
								'misc_1' => zen_db_prepare_input($_POST['misc_1']),
								'misc_2' => zen_db_prepare_input($_POST['misc_2']),
								'misc_bool_1' => zen_db_prepare_input($_POST['misc_bool_1']),
								'misc_bool_2' => zen_db_prepare_input($_POST['misc_bool_2'])
	//add the jph fields
								 ,'misc_3' => zen_db_prepare_input($_POST['misc_3']),
  								'misc_4' => zen_db_prepare_input($_POST['misc_4']),
  								'misc_5' => zen_db_prepare_input($_POST['misc_5']),
  								'misc_6' => zen_db_prepare_input($_POST['misc_6']),
  								'misc_7' => zen_db_prepare_input($_POST['misc_7']),
  								'misc_8' => zen_db_prepare_input($_POST['misc_8']),
  								'misc_9' => zen_db_prepare_input($_POST['misc_9']),
  								'misc_10' => zen_db_prepare_input($_POST['misc_10']),
  								'misc_bool_3' => zen_db_prepare_input($_POST['misc_bool_3']),
  								'misc_bool_4' => zen_db_prepare_input($_POST['misc_bool_4']),
  								'misc_bool_5' => zen_db_prepare_input($_POST['misc_bool_5']),
  								'misc_bool_6' => zen_db_prepare_input($_POST['misc_bool_6']),
  								'misc_int_5_1'  => zen_db_prepare_input($_POST['misc_int_5_1']),
  								'misc_int_5_2'  => zen_db_prepare_input($_POST['misc_int_5_2']),
  								'misc_int_5_3'  => zen_db_prepare_input($_POST['misc_int_5_3']),     
  								'misc_int_11_1'  => zen_db_prepare_input($_POST['misc_int_11_1']),
  								'misc_int_11_2'  => zen_db_prepare_input($_POST['misc_int_11_2']),
  								'misc_int_11_3'  => zen_db_prepare_input($_POST['misc_int_11_3'])  
					//================							
								
								);

		zen_db_perform(TABLE_PRODUCT_BOOK_EXTRA, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

////    *END OF PRODUCT-TYPE-SPECIFIC UPDATES* ////////
///////////////////////////////////////////////////////

	}

//name, description, url
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
//collate name, description values across languages, beta2 addition
		if($collate_values) {
			if($i == 0) {//copy
				$this_lang_products_name = $_POST['products_name'][$languages[0]['id']];
				$this_lang_products_desc = $_POST['products_description'][$languages[0]['id']];
			}
		}else{//normal
			$this_lang_products_name = $_POST['products_name'][$language_id];
			$this_lang_products_desc = $_POST['products_description'][$language_id];
		}
//======
		$sql_data_array = array('products_name' => zen_db_prepare_input($this_lang_products_name),
								'products_description' => zen_db_prepare_input($this_lang_products_desc),
								'products_url' => zen_db_prepare_input($_POST['products_url'][$language_id]));

		if ($action == 'insert_product') {
			$insert_sql_data = array('products_id' => $products_id,
										'language_id' => $language_id);

			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
		} elseif ($action == 'update_product') {
			zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
		}
	}

// add meta tags
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];

		$sql_data_array = array('metatags_title' => zen_db_prepare_input($_POST['metatags_title'][$language_id]),
								'metatags_keywords' => zen_db_prepare_input($_POST['metatags_keywords'][$language_id]),
								'metatags_description' => zen_db_prepare_input($_POST['metatags_description'][$language_id]));

		if ($action == 'insert_product_meta_tags') {

			$insert_sql_data = array('products_id' => $products_id,
										'language_id' => $language_id);

			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			zen_db_perform(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array);
		} elseif ($action == 'update_product_meta_tags') {
			zen_db_perform(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
		}
	}
    
    // BEGIN CEON URI MAPPING 1 of 1
    require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
    
    $ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
    
    $ceon_uri_mapping_admin->updateProductHandler($products_id, $zc_products->get_handler($product_type));
    
    // END CEON URI MAPPING 1 of 1


	// future image handler code
	define('IMAGE_MANAGER_HANDLER', 0);
	define('DIR_IMAGEMAGICK', '');
	if ($new_image == 'true' and IMAGE_MANAGER_HANDLER >= 1) {
		$src= DIR_FS_CATALOG . DIR_WS_IMAGES . zen_get_products_image((int)$products_id);
		$filename_small= $src;
		preg_match("/.*\/(.*)\.(\w*)$/", $src, $fname);
		list($oiwidth, $oiheight, $oitype) = getimagesize($src);

		define('DIR_IMAGEMAGICK', '');
		$small_width= SMALL_IMAGE_WIDTH;
		$small_height= SMALL_IMAGE_HEIGHT;
		$medium_width= MEDIUM_IMAGE_WIDTH;
		$medium_height= MEDIUM_IMAGE_HEIGHT;
		$large_width= LARGE_IMAGE_WIDTH;
		$large_height= LARGE_IMAGE_HEIGHT;

		$k = max($oiheight / $small_height, $oiwidth / $small_width); //use smallest size
		$small_width = round($oiwidth / $k);
		$small_height = round($oiheight / $k);

		$k = max($oiheight / $medium_height, $oiwidth / $medium_width); //use smallest size
		$medium_width = round($oiwidth / $k);
		$medium_height = round($oiheight / $k);

		$large_width= $oiwidth;
		$large_height= $oiheight;

		$products_image = zen_get_products_image((int)$products_id);
		$products_image_extension = substr($products_image, strrpos($products_image, '.'));
		$products_image_base = ereg_replace($products_image_extension, '', $products_image);

		$filename_medium = DIR_FS_CATALOG . DIR_WS_IMAGES . 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . '.' . $fname[2];
		$filename_large = DIR_FS_CATALOG . DIR_WS_IMAGES . 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . '.' . $fname[2];

// ImageMagick
		if (IMAGE_MANAGER_HANDLER == '1') {
			copy($src, $filename_large);
			copy($src, $filename_medium);
			exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $large_width . " " . $filename_large);
			exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $medium_width . " " . $filename_medium);
			exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $small_width . " " . $filename_small);
		}
	}
		zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
}
?>