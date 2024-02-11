<?php //v4 update to zc 138a code lines ~917, jph dec 2007, 
//V4 changes: re-do page lang switching code and modify FCK code section jan 2008
//V4 CSS has price area GREY, V3 was YELLOW
//multiple LANG extensive mods often marked jph may 2007
//136/7 belated code update for fckeditor apr 2007 jph
//for v136 added l 793 manual image  jph mod nov 2006
// 2006-05-12 : moku
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
//chunk from categories.php header for languages dropdown
//defensive
if (!isset($_SESSION['language'])) die('Language Session Missing');
//=================v4 mod add local takeover of page blocks language order settable by dd jph
 $current_language_folder = '';
  //language blocks order control by local dd request 
 if($_GET['first_language'] <> ''){
 	$current_language_folder = $_GET['first_language'];
	$_SESSION['first_language'] = $_GET['first_language'];  
 }else//order control by local session
 	if(isset($_SESSION['first_language']) && $_SESSION['first_language'] <> '') {
 	$current_language_folder = $_SESSION['first_language'];  
 }else{  //order control was at dd on categories listing
 	$current_language_folder = $_SESSION['language'];
	$_SESSION['first_language'] = $_SESSION['language'];
 }
 
/*
  echo "<br />sess languge = " . $_SESSION['language'];
  echo "<br />sess first_language = " . $_SESSION['first_language'];
  echo "<br />$ current_language_folder =" . $current_language_folder;
  */
//==============================
// Create page Language Dropdown
  $languages = zen_get_languages();
  if (sizeof($languages) > 1) {
    $languages_array = array();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		//for dd
		$test_directory= DIR_WS_LANGUAGES . $languages[$i]['directory'];
      	$test_file= DIR_WS_LANGUAGES . $languages[$i]['directory'] . '.php';
      	if ( file_exists($test_file) and file_exists($test_directory) ) {
        	$languages_array[] = array('id' => $languages[$i]['directory'],
                                 'text' => $languages[$i]['name']);
			if ($languages[$i]['directory'] == $current_language_folder) {
				//local labels language instead of categories page language session 
		  		$languages_id_selected = $languages[$i]['id'];
		  		$languages_name_selected = $languages[$i]['name'];
				//for fckeditor only
		  		$languages_code_selected = $languages[$i]['code'];
				//no use for htmlarea, but useful for my modded tintmce				
				$_SESSION['languages_code'] = $languages[$i]['code'];
			}
			if($languages[$i]['directory'] == $_SESSION['language']){ 
				//get proper labels language name for info display
				$labels_language = $languages[$i]['name'];
			}
        } 
    }
	
    $hide_languages= false;
  } else {
	//single language mode
	$languages_id_selected = $languages[0]['id'];
    $hide_languages= true;
  } 
//defensive
	if ((int)$languages_id_selected < 1) die('Error: No Page language I.D value');
//get language array
	$languages_raw = zen_get_languages();
	$languages = array();
//put current page language  first in book blocks
	$omit = '';
	for ($i=0, $n=sizeof($languages_raw); $i<$n; $i++) {
		if((int)$languages_id_selected  == (int)$languages_raw[$i]['id']) {
			$languages[] = $languages_raw[$i];	
			$omit = $i;
		}
	}
	//copy remainder of array
	for ($i=0, $n=sizeof($languages_raw); $i<$n; $i++) {
		if($omit != $i) {
			$languages[] = $languages_raw[$i];
		}
	}
			
	//for ($i=0, $n=sizeof($languages); $i<$n; $i++) { echo '<br />Name = ' . $languages[$i]['name']; }

//========================================= $languages[$i]['id'] drives the page loops
//=============jph get admin settings for jph fields, only display ones theoretically shown in a cart book display
  $fg_show_product_book_info_misc_1 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_1');
  $fg_show_product_book_info_misc_2 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_2');


  $fg_show_product_book_info_misc_3 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_3');
  $fg_show_product_book_info_misc_4 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_4');
  $fg_show_product_book_info_misc_5 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_5');
  $fg_show_product_book_info_misc_6 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_6');
  $fg_show_product_book_info_misc_7 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_7');
  $fg_show_product_book_info_misc_8 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_8');
  $fg_show_product_book_info_misc_9 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_9');
  $fg_show_product_book_info_misc_10 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_10');

 $fg_show_product_book_info_misc_bool_1 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_bool_1');
 $fg_show_product_book_info_misc_bool_2 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_bool_2');

  $fg_show_product_book_info_misc_bool_3 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_bool_3');
  $fg_show_product_book_info_misc_bool_4 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_bool_4');
  $fg_show_product_book_info_misc_bool_5 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_bool_5');
  $fg_show_product_book_info_misc_bool_6 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_bool_6');
  $fg_show_product_book_info_misc_int_5_1 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_int_5_1');
  $fg_show_product_book_info_misc_int_5_2 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_int_5_2');
  $fg_show_product_book_info_misc_int_5_3 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_int_5_3');  
  $fg_show_product_book_info_misc_int_11_1 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_int_11_1');
  $fg_show_product_book_info_misc_int_11_2 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_int_11_2');
  $fg_show_product_book_info_misc_int_11_3 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_int_11_3');

  $fg_show_product_book_info_misc_dd1 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_dd1');
  $fg_show_product_book_info_misc_dd2 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_dd2');
  $fg_show_product_book_info_misc_dd3 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_dd3');
  $fg_show_product_book_info_misc_dd4 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_dd4');
  $fg_show_product_book_info_misc_dd5 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_dd5');
  $fg_show_product_book_info_misc_dd6 = zen_get_show_product_switch_by_th_id($_GET['product_type'], 'misc_dd6');
//================================================================
		$parameters = array('products_name' => '',
							'products_description' => '',
							'products_url' => '',
							'products_id' => '',
							'products_quantity' => '',
							'products_model' => '',
							'products_image' => '',
							'products_price' => '',
							'products_virtual' => DEFAULT_PRODUCT_BOOK_PRODUCTS_VIRTUAL,
							'products_weight' => '',
							'products_date_added' => '',
							'products_last_modified' => '',
							'products_date_available' => '',
							'products_status' => '',
							'products_tax_class_id' => DEFAULT_PRODUCT_BOOK_TAX_CLASS_ID,
							'manufacturers_id' => '',
							'products_quantity_order_min' => '',
							'products_quantity_order_units' => '',
							'products_priced_by_attribute' => '',
							'product_is_free' => '',
							'product_is_call' => '',
							'products_quantity_mixed' => '',
							'product_is_always_free_shipping' => DEFAULT_PRODUCT_BOOK_PRODUCTS_IS_ALWAYS_FREE_SHIPPING,
							'products_qty_box_status' => PRODUCTS_QTY_BOX_STATUS,
							'products_quantity_order_max' => '0',
							'products_sort_order' => '0',
							'products_discount_type' => '0',
							'products_discount_type_from' => '0',
							'products_price_sorter' => '0',
							'master_categories_id' => ''
							);

		$pInfo = new objectInfo($parameters);
//beta 2 added (int)$_GET['pID'] > 0... 
	if (isset($_GET['pID']) && (int)$_GET['pID'] > 0 && empty($_POST)) {
			$product = $db->Execute("select
										pe.book_color_id,
										pe.book_condition_id,
										pe.subtitle, pe.pub_date, pe.size, pe.pages,
										pe.misc_1, pe.misc_2,
										pe.misc_bool_1, pe.misc_bool_2," .
			//================added ALL extra jph fields
										"pe.misc_3,pe.misc_4,pe.misc_5,pe.misc_6,pe.misc_7,pe.misc_8,
										pe.misc_9,pe.misc_10,pe.book_dd1_id,pe.book_dd2_id,
										pe.book_dd3_id,pe.book_dd4_id,pe.book_dd5_id,pe.book_dd6_id,
										pe.misc_bool_3,pe.misc_bool_4,pe.misc_bool_5,pe.misc_bool_6,
										pe.misc_int_5_1,pe.misc_int_5_2,pe.misc_int_5_3,
										pe.misc_int_11_1,pe.misc_int_11_2,pe.misc_int_11_3," .       
			//======================
										"pd.products_name, pd.products_description, pd.products_url,
										p.products_id, p.products_quantity, p.products_model,
										p.products_image, p.products_price, p.products_virtual, p.products_weight,
										p.products_date_added, p.products_last_modified,
										date_format(p.products_date_available, '%Y-%m-%d') as products_date_available,
										p.products_status, p.products_tax_class_id, p.manufacturers_id,
										p.products_quantity_order_min, p.products_quantity_order_units,
										p.products_priced_by_attribute, p.product_is_free, p.product_is_call,
										p.products_quantity_mixed, p.product_is_always_free_shipping,
										p.products_qty_box_status, p.products_quantity_order_max,
										p.products_sort_order, p.products_discount_type,
										p.products_discount_type_from, p.products_price_sorter,
										p.master_categories_id
									from
										" . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd,
										" . TABLE_PRODUCT_BOOK_EXTRA . " pe
									where
										p.products_id = '" . (int)$_GET['pID'] . "'
										and p.products_id = pd.products_id and p.products_id = pe.products_id
										and pd.language_id = '" . (int)$languages_id_selected  . "'");

			$pInfo->objectInfo($product->fields);
//============================LANG MOD get db values per language,  altered sql
//THESE ARE THE EXISTING VALUES FOR THIS BOOK, IN DB!
	//$get_man_urls_value = array();
			$book_authors_default_array = array();
			$book_genres_default_array = array();
			$book_types_default = array();
			$book_dd1_default_array = array();
			$book_dd2_default_array = array();
			$book_dd3_default_array = array();
			$book_dd4_default_array = array();
			$book_dd5_default_array = array();
			$book_dd6_default_array = array();


	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];
			$book_authors = $db->Execute("select
											a.book_authors_id, book_authors_name, book_authors_nickname
										from
											" . TABLE_BOOKS_TO_AUTHORS . " a,
											" . TABLE_BOOK_AUTHORS_INFO . " b,
											" . TABLE_BOOK_AUTHORS . " c
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_authors_id = b.book_authors_id
											and a.book_authors_id = c.book_authors_id
											and a.language_idta = '" . $language_id . "'
											and b.language_id = '" . $language_id . "'
											and b.language_id = c.language_ida
										order by book_authors_name");
			while (!$book_authors->EOF) {
				//multiple values may be in each sub array
				$book_authors_default_array[$i][] = $book_authors->fields['book_authors_id'];
				$book_authors->MoveNext();
			}


			$book_genres = $db->Execute("select
											a.book_genre_id as book_genre_id, book_genre_name
										from
											" . TABLE_BOOKS_TO_GENRES . " a,
											" . TABLE_BOOK_GENRE_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_genre_id = b.book_genre_id
											and b.language_id = '" . $language_id . "'
											and b.language_id = a.language_idg
										order by book_genre_name");
			while (!$book_genres->EOF) {
				//multiple values may be in each sub array
				$book_genres_default_array[$i][] = $book_genres->fields['book_genre_id'];
				$book_genres->MoveNext();
			}

			$book_types = $db->Execute("select
											a.book_type_id as book_type_id, book_type_name
										from
											" . TABLE_BOOKS_TO_TYPES . " a,
											" . TABLE_BOOK_TYPE_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_type_id = b.book_type_id
											and b.language_id = '" . $language_id . "'
											and b.language_id = a.language_idt
										order by book_type_name");
			while (!$book_types->EOF) {
				//single value in each sub array
				$book_types_default[] = $book_types->fields['book_type_id'];
				$book_types->MoveNext();
			}

//============add the six mutiple dd's  jph
//============================= dd1 used as single only ever [0]
			$book_dd1 = $db->Execute("select
											a.book_dd1_id, book_dd1_name
										from
											" . TABLE_BOOKS_TO_DD1 . " a,
											" . TABLE_BOOK_DD1_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_dd1_id = b.book_dd1_id
											and b.language_id = '" . $language_id . "'
								and b.language_id = a.language_idd1
										order by book_dd1_name");
			while (!$book_dd1->EOF) {
				//the same single value month is in each array [$i][0]
				$book_dd1_default_array[$i][] = $book_dd1->fields['book_dd1_id'];
				$book_dd1->MoveNext();
			}
//============================= dd2

			$book_dd2 = $db->Execute("select
											a.book_dd2_id, book_dd2_name
										from
											" . TABLE_BOOKS_TO_DD2 . " a,
											" . TABLE_BOOK_DD2_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_dd2_id = b.book_dd2_id
											and b.language_id = '" . $language_id . "'
								and b.language_id = a.language_idd2
										order by book_dd2_name");
			while (!$book_dd2->EOF) {
				//multiple values may be in each sub array
				$book_dd2_default_array[$i][] = $book_dd2->fields['book_dd2_id'];
				$book_dd2->MoveNext();
			}
//============================= dd3
			$book_dd3 = $db->Execute("select
											a.book_dd3_id, book_dd3_name
										from
											" . TABLE_BOOKS_TO_DD3 . " a,
											" . TABLE_BOOK_DD3_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_dd3_id = b.book_dd3_id
											and b.language_id = '" . $language_id . "'
								and b.language_id = a.language_idd3
										order by book_dd3_name");
			while (!$book_dd3->EOF) {
				$book_dd3_default_array[$i][] = $book_dd3->fields['book_dd3_id'];
				$book_dd3->MoveNext();
			}			
//============================= dd4
			$book_dd4 = $db->Execute("select
											a.book_dd4_id, book_dd4_name
										from
											" . TABLE_BOOKS_TO_DD4 . " a,
											" . TABLE_BOOK_DD4_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_dd4_id = b.book_dd4_id
											and b.language_id = '" . $language_id . "'
								and b.language_id = a.language_idd4
										order by book_dd4_name");
			while (!$book_dd4->EOF) {
				//multiple values may be in each sub array
				$book_dd4_default_array[$i][] = $book_dd4->fields['book_dd4_id'];
				$book_dd4->MoveNext();
			}
//============================= dd5
			$book_dd5 = $db->Execute("select
											a.book_dd5_id, book_dd5_name
										from
											" . TABLE_BOOKS_TO_DD5 . " a,
											" . TABLE_BOOK_DD5_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_dd5_id = b.book_dd5_id
											and b.language_id = '" . $language_id . "'
									and b.language_id = a.language_idd5
									order by book_dd5_name");
			while (!$book_dd5->EOF) {
				//multiple values may be in each sub array
				$book_dd5_default_array[$i][] = $book_dd5->fields['book_dd5_id'];
				$book_dd5->MoveNext();
			}
//============================= dd6
			$book_dd6 = $db->Execute("select
											a.book_dd6_id, book_dd6_name
										from
											" . TABLE_BOOKS_TO_DD6 . " a,
											" . TABLE_BOOK_DD6_DESCRIPTION . " b
										where
											a.products_id = '" . (int)$_GET['pID'] . "'
											and a.book_dd6_id = b.book_dd6_id
											and b.language_id = '" . $language_id . "'
									and b.language_id = a.language_idd6
										order by book_dd6_name");
			while (!$book_dd6->EOF) {
				//multiple values may be in each sub array
				$book_dd6_default_array[$i][] = $book_dd6->fields['book_dd6_id'];

				$book_dd6->MoveNext();
			}
//manuf url
			$get_man_urls_sql = "select mi.manufacturers_url 
								from 
								" . TABLE_MANUFACTURERS . " m,
								" . TABLE_MANUFACTURERS_INFO . " mi 
								where
								mi.manufacturers_id = m.manufacturers_id
								and mi.manufacturers_id = '" . $pInfo->manufacturers_id . "'
								and mi.languages_id = '" . $language_id . "' limit 1";							
			$get_man_urls_array = $db->Execute($get_man_urls_sql);
			$get_man_urls_value[] = $get_man_urls_array->fields['manufacturers_url']; 
//**************======================			
		}
		//======================eof LANG mod cycle

		} elseif (zen_not_null($_POST)) {
			$pInfo->objectInfo($_POST);
			$products_name = $_POST['products_name'];
			$products_description = $_POST['products_description'];
			$products_url = $_POST['products_url'];
		}

// Book authors LANG MOD
	//$languages = zen_get_languages();
	$book_authors_array = array();

	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		//$book_authors_array[$i][] = array('id' => '', 'text' => TEXT_NONE);
		$book_authors = $db->Execute("select a.book_authors_id, book_authors_name, book_authors_nickname
									from " . TABLE_BOOK_AUTHORS . " a, " . TABLE_BOOK_AUTHORS_INFO . " b
									where a.book_authors_id = b.book_authors_id
									and b.language_id = '" . $languages[$i]['id'] . "'
									and b.language_id = a.language_ida
									order by book_authors_name");
		while (!$book_authors->EOF) {
            $book_authors_show_name = $book_authors->fields['book_authors_name'];
            (($book_authors->fields['book_authors_nickname']) ? $book_authors_show_name = $book_authors_show_name . ' (' . $book_authors->fields['book_authors_nickname'] . ')' : '');
			$book_authors_array[$i][] = array('id' => $book_authors->fields['book_authors_id'],
								    'text' => $book_authors_show_name);

		$book_authors->MoveNext();
		}
	}

// Book genre
//LANG MOD may 2007
	//$languages = zen_get_languages();
	$book_genre_array = array();
	$book_type_array = array();
	$book_condition_array = array();	
	$book_color_array = array();	

	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$book_genre = $db->Execute("select g.book_genre_id, book_genre_name
									from " . TABLE_BOOK_GENRE . " g, " . TABLE_BOOK_GENRE_DESCRIPTION . " gd
									where g.book_genre_id = gd.book_genre_id
									and gd.language_id = '" . $languages[$i]['id'] . "'
									order by book_genre_name");

		//$book_genre_array[$i][] = array('id' => '','text' => TEXT_NONE);
		while (!$book_genre->EOF) {		//multiple selection	
			$book_genre_array[$i][] = array('id' => $book_genre->fields['book_genre_id'],
										'text' => $book_genre->fields['book_genre_name']);
			$book_genre->MoveNext();
		}

// Book type
		$book_type_array[$i][] = array('id' => '', 'text' => TEXT_NONE);
	
		$book_type = $db->Execute("select t.book_type_id, book_type_name
									from " . TABLE_BOOK_TYPE . " t, " . TABLE_BOOK_TYPE_DESCRIPTION . " td
									where t.book_type_id = td.book_type_id
									and td.language_id = '" . $languages[$i]['id'] . "'
									order by book_type_name");
		//$book_type_array[$i][] = array('id' => '','text' => TEXT_NONE);
		while (!$book_type->EOF) {//element integer is just sequential matches language order
			$book_type_array[$i][] = array('id' => $book_type->fields['book_type_id'],
										'text' => $book_type->fields['book_type_name']);
			$book_type->MoveNext();
		}
// Book condition

		$book_condition = $db->Execute("select c.book_condition_id, book_condition_name
                                        from " . TABLE_BOOK_CONDITION . " c, " . TABLE_BOOK_CONDITION_DESCRIPTION . " cd
                                        where c.book_condition_id = cd.book_condition_id
                                        and cd.language_id = '" . $languages[$i]['id'] . "'
                                        order by book_condition_name");
		$book_condition_array[$i][] = array('id' => '',
										'text' => TEXT_NONE);
		while (!$book_condition->EOF) {
			$book_condition_array[$i][] = array('id' => $book_condition->fields['book_condition_id'],
                                            'text' => $book_condition->fields['book_condition_name']);
			//get this languages condition text name
			if($book_condition->fields['book_condition_id'] == $pInfo->book_condition_id) {
				$book_condition_text = $book_condition->fields['book_condition_name'];
			}
			$book_condition->MoveNext();
		}

	}
//eof lang mod
// Book color single non-lang value (FORMAT)
		$book_color_array = array(array('id' => '', 'text' => TEXT_NONE));
		$book_color = $db->Execute("select c.book_color_id, book_color_name
									from " . TABLE_BOOK_COLOR . " c, " . TABLE_BOOK_COLOR_DESCRIPTION . " cd
									where c.book_color_id = cd.book_color_id
									and cd.language_id = '" . (int)$languages_id_selected  . "'
									order by book_color_name");
		while (!$book_color->EOF) {
			$book_color_array[] = array('id' => $book_color->fields['book_color_id'],
										'text' => $book_color->fields['book_color_name']);
			$book_color->MoveNext();
		}

//LANG MOD may 2007
//THESE ARE THE VARIOUS DROPDOWN LIST VALUES, IN DB!
		$book_dd1_array = array();
		$book_dd2_array = array();
		$book_dd3_array = array();
		$book_dd4_array = array();
		$book_dd5_array = array();
		$book_dd6_array = array();

		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];

//============add the six multiple dd's  jph
//============================= dd1 as months
			$book_dd1_array[$i][] = array('id' => '',
										'text' => TEXT_NONE);
			$book_dd1 = $db->Execute("select
											a.book_dd1_id, book_dd1_name
										from
											" . TABLE_BOOK_DD1 . " a,
											" . TABLE_BOOK_DD1_DESCRIPTION . " b
										where a.book_dd1_id = b.book_dd1_id
											and b.language_id = '" . $language_id . "'
										order by book_dd1_id");
			while (!$book_dd1->EOF) {
				//multiple
				$book_dd1_array[$i][] = array('id' => $book_dd1->fields['book_dd1_id'],
										'text' => $book_dd1->fields['book_dd1_name']);
				$book_dd1->MoveNext();
			}
			
//============================= dd2
		//$book_dd2_array = array(array('id' => '', 'text' => TEXT_NONE));
			$book_dd2 = $db->Execute("select
											a.book_dd2_id, book_dd2_name
										from
											" . TABLE_BOOK_DD2 . " a,
											" . TABLE_BOOK_DD2_DESCRIPTION . " b
										where a.book_dd2_id = b.book_dd2_id
											and b.language_id = '" . $language_id . "'
										order by book_dd2_name");
			while (!$book_dd2->EOF) {
//echo "<br />lang= " . $i . " , book_dd2_name = " . $book_dd2->fields['book_dd2_name'];
				$book_dd2_array[$i][] = array('id' => $book_dd2->fields['book_dd2_id'],
										'text' => $book_dd2->fields['book_dd2_name']);
				$book_dd2->MoveNext();
			}
//============================= dd3
		//$book_dd3_array = array(array('id' => '', 'text' => TEXT_NONE));
			$book_dd3 = $db->Execute("select
											a.book_dd3_id, book_dd3_name
										from
											" . TABLE_BOOK_DD3 . " a,
											" . TABLE_BOOK_DD3_DESCRIPTION . " b
										where a.book_dd3_id = b.book_dd3_id
											and b.language_id = '" . $language_id . "'
									order by book_dd3_name");
			while (!$book_dd3->EOF) {
				$book_dd3_array[$i][] = array('id' => $book_dd3->fields['book_dd3_id'],
										'text' => $book_dd3->fields['book_dd3_name']);
				$book_dd3->MoveNext();
			}			
//============================= dd4
		//$book_dd4_array = array(array('id' => '', 'text' => TEXT_NONE));
			$book_dd4 = $db->Execute("select
											a.book_dd4_id, book_dd4_name
										from
											" . TABLE_BOOK_DD4 . " a,
											" . TABLE_BOOK_DD4_DESCRIPTION . " b
										where a.book_dd4_id = b.book_dd4_id
											and b.language_id = '" . $language_id . "'
									order by book_dd4_name");
			while (!$book_dd4->EOF) {
				$book_dd4_array[$i][] = array('id' => $book_dd4->fields['book_dd4_id'],
										'text' => $book_dd4->fields['book_dd4_name']);
				$book_dd4->MoveNext();
			}
//============================= dd5
		//$book_dd5_array = array(array('id' => '', 'text' => TEXT_NONE));
			$book_dd5 = $db->Execute("select
											a.book_dd5_id, book_dd5_name
										from
											" . TABLE_BOOK_DD5 . " a,
											" . TABLE_BOOK_DD5_DESCRIPTION . " b
										where a.book_dd5_id = b.book_dd5_id
											and b.language_id = '" . $language_id . "'
										order by book_dd5_name");
			while (!$book_dd5->EOF) {
				$book_dd5_array[$i][] = array('id' => $book_dd5->fields['book_dd5_id'],
										'text' => $book_dd5->fields['book_dd5_name']);
				$book_dd5->MoveNext();
			}
//============================= dd6
		//$book_dd6_array = array(array('id' => '', 'text' => TEXT_NONE));
			$book_dd6 = $db->Execute("select
											a.book_dd6_id, book_dd6_name
										from
											" . TABLE_BOOK_DD6 . " a,
											" . TABLE_BOOK_DD6_DESCRIPTION . " b
										where a.book_dd6_id = b.book_dd6_id
											and b.language_id = '" . $language_id . "'
										order by book_dd6_name");
			while (!$book_dd6->EOF) {
				$book_dd6_array[$i][] = array('id' => $book_dd6->fields['book_dd6_id'],
										'text' => $book_dd6->fields['book_dd6_name']);
				$book_dd6->MoveNext();
			}
//======================			
		}//END lang cycle
//=======================
    
    // BEGIN CEON URI MAPPING 1 of 2
    require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
    
    $ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
    
    $ceon_uri_mapping_admin->collectInfoHandler();
    
    // END CEON URI MAPPING 1 of 2

// Publisher
		$manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
		$manufacturers = $db->Execute("select manufacturers_id, manufacturers_name
									from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
		while (!$manufacturers->EOF) {
			$manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'],
										'text' => $manufacturers->fields['manufacturers_name']);
			$manufacturers->MoveNext();
		}

		$tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
		$tax_class = $db->Execute("select tax_class_id, tax_class_title
									from " . TABLE_TAX_CLASS . " order by tax_class_title");
		while (!$tax_class->EOF) {
			$tax_class_array[] = array('id' => $tax_class->fields['tax_class_id'],
										'text' => $tax_class->fields['tax_class_title']);
			$tax_class->MoveNext();
		}

		//$languages = zen_get_languages();

		if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
		switch ($pInfo->products_status) {
			case '0': $in_status = false; $out_status = true; break;
			case '1':
			default: $in_status = true; $out_status = false;
				break;
		}
// set to out of stock if categories_status is off and new product or existing products_status is off
		if (zen_get_categories_status($current_category_id) == '0' and $pInfo->products_status != '1') {
			$pInfo->products_status = 0;
			$in_status = false;
			$out_status = true;
		}

// Virtual Products
		if (!isset($pInfo->products_virtual)) $pInfo->products_virtual = PRODUCTS_VIRTUAL_DEFAULT;
		switch ($pInfo->products_virtual) {
			case '0': $is_virtual = false; $not_virtual = true; break;
			case '1': $is_virtual = true; $not_virtual = false; break;
			default: $is_virtual = false; $not_virtual = true;
		}
// Always Free Shipping
		if (!isset($pInfo->product_is_always_free_shipping)) $pInfo->product_is_always_free_shipping = DEFAULT_PRODUCT_BOOK_PRODUCTS_IS_ALWAYS_FREE_SHIPPING;
		switch ($pInfo->product_is_always_free_shipping) {
			case '0': $is_product_is_always_free_shipping = false; $not_product_is_always_free_shipping = true; $special_product_is_always_free_shipping = false; break;
			case '1': $is_product_is_always_free_shipping = true; $not_product_is_always_free_shipping = false; $special_product_is_always_free_shipping = false; break;
			case '2': $is_product_is_always_free_shipping = false; $not_product_is_always_free_shipping = false; $special_product_is_always_free_shipping = true; break;
			default: $is_product_is_always_free_shipping = false; $not_product_is_always_free_shipping = true;$special_product_is_always_free_shipping = false; break;
		}
// products_qty_box_status shows
		if (!isset($pInfo->products_qty_box_status)) $pInfo->products_qty_box_status = PRODUCTS_QTY_BOX_STATUS;
		switch ($pInfo->products_qty_box_status) {
			case '0': $is_products_qty_box_status = false; $not_products_qty_box_status = true; break;
			case '1': $is_products_qty_box_status = true; $not_products_qty_box_status = false; break;
			default: $is_products_qty_box_status = true; $not_products_qty_box_status = false;
		}
// Product is Priced by Attributes
		if (!isset($pInfo->products_priced_by_attribute)) $pInfo->products_priced_by_attribute = '0';
		switch ($pInfo->products_priced_by_attribute) {
			case '0': $is_products_priced_by_attribute = false; $not_products_priced_by_attribute = true; break;
			case '1': $is_products_priced_by_attribute = true; $not_products_priced_by_attribute = false; break;
			default: $is_products_priced_by_attribute = false; $not_products_priced_by_attribute = true;
		}
// Product is Free
		if (!isset($pInfo->product_is_free)) $pInfo->product_is_free = '0';
		switch ($pInfo->product_is_free) {
			case '0': $in_product_is_free = false; $out_product_is_free = true; break;
			case '1': $in_product_is_free = true; $out_product_is_free = false; break;
			default: $in_product_is_free = false; $out_product_is_free = true;
		}
// Product is Call for price
		if (!isset($pInfo->product_is_call)) $pInfo->product_is_call = '0';
		switch ($pInfo->product_is_call) {
			case '0': $in_product_is_call = false; $out_product_is_call = true; break;
			case '1': $in_product_is_call = true; $out_product_is_call = false; break;
			default: $in_product_is_call = false; $out_product_is_call = true;
		}
// Products can be purchased with mixed attributes retail
		if (!isset($pInfo->products_quantity_mixed)) $pInfo->products_quantity_mixed = '0';
		switch ($pInfo->products_quantity_mixed) {
			case '0': $in_products_quantity_mixed = false; $out_products_quantity_mixed = true; break;
			case '1': $in_products_quantity_mixed = true; $out_products_quantity_mixed = false; break;
			default: $in_products_quantity_mixed = true; $out_products_quantity_mixed = false;
		}

// set image overwrite
	$on_overwrite = true;
	$off_overwrite = false;
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript"><!--
	var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "new_product", "products_date_available","btnDate1","<?php echo $pInfo->products_date_available; ?>",scBTNMODE_CUSTOMBLUE);
//--></script>
<script language="javascript"><!--
	var pubDate = new ctlSpiffyCalendarBox("pubDate", "new_product", "pub_date","btnDate2","<?php echo $pInfo->pub_date; ?>",scBTNMODE_CUSTOMBLUE);
//--></script>
<script language="javascript"><!--
var tax_rates = new Array();
<?php
		for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
			if ($tax_class_array[$i]['id'] > 0) {
				echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . zen_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
			}
		}
?>

function doRound(x, places) {
	return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
	var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
	var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

	if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
		return tax_rates[parameterVal];
	} else {
		return 0;
	}
}

function updateGross() {
	var taxRate = getTaxRate();
	var grossValue = document.forms["new_product"].products_price.value;

	if (taxRate > 0) {
		grossValue = grossValue * ((taxRate / 100) + 1);
	}

	document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
	var taxRate = getTaxRate();
	var netValue = document.forms["new_product"].products_price_gross.value;

	if (taxRate > 0) {
		netValue = netValue / ((taxRate / 100) + 1);
	}

	document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//--></script>
		

      <?php
//page language stolen from categories .php
      if (!$hide_languages) {
		?>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
  			<tr class="headerBar" height="23" width="100%">
			<td class="headerBarContent" align="left">
            <!-- bof Menu bar #3. -->
		<?php echo '<div style="float:left;">' . DEFINE_LANGUAGE . '&nbsp;&nbsp;' . zen_draw_form('languages', basename($PHP_SELF), '', 'get');
        echo  (sizeof($languages) > 1 ? zen_draw_pull_down_menu('first_language', $languages_array, $current_language_folder, 'onChange="this.form.submit();"') : '');
        echo zen_hide_session_id() . 
            zen_draw_hidden_field('cID', $cPath) .
            zen_draw_hidden_field('cPath', $cPath) .
            zen_draw_hidden_field('pID', $_GET['pID']) .
            zen_draw_hidden_field('page', $_GET['page']) .
            zen_draw_hidden_field('action', $_GET['action']) .
			zen_draw_hidden_field('product_type', $_GET['product_type']) ;
		?>
        </form>
		</div>
		<div style="float:right">
		<?php //=============added extra info for v4 jph
		echo '<font style="color:gray">Block 1= </font><font style="color:white">' . $languages_name_selected . "; " . $languages_code_selected . " </font>" .
		'<font style="color:gray">Labels language = </font><font style="color:white">' . $labels_language . "; </font> ";
		if('' <> $_SESSION['html_editor_preference_status'] ) {
			echo '<font style="color:gray">Editor = </font><font style="color:white">' . 
					$_SESSION['html_editor_preference_status'] . ' </font>';
		}else{
			echo '<font style="color:red">Text Editor type not defined!</font>';
		}
		//============================================================
		?>		
		&nbsp;</div>
		<!--eof  Menu bar #3. -->
    		</td>
  			</tr>
		</table>
		<?php 
      	}
    	?>

<?php

//	echo $type_admin_handler;
echo zen_draw_form('new_product', $type_admin_handler , 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"'); ?>

		<table border="0" width="100%" cellspacing="0" cellpadding="2">
			<tr>
				<td>
				<table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<td class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, zen_output_generated_category_path($current_category_id)); ?></td>
						<td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
						<td class="main" align="right">
						<?php echo zen_draw_hidden_field('products_date_added', 
						(zen_not_null($pInfo->products_date_added) ? 
						$pInfo->products_date_added : date('Y-m-d'))) . 
						zen_image_submit('button_preview.gif', IMAGE_PREVIEW) . 
						'&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . 
						(isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . 
						(isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . 
						zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?>
						</td>
					</tr>
				</table>
				</td>
			</tr>
	
			<tr>
				<td>				
				<table  width="100%" border="0" cellspacing="0" cellpadding="2">
<?php
// show when product is linked
if (zen_get_product_is_linked($_GET['pID']) == 'true' and $_GET['pID'] > 0) {
?>
					<tr>
						<td class="main"><?php echo TEXT_MASTER_CATEGORIES_ID; ?></td>
						<td class="main">
							<?php
								// echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id);
								echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '&nbsp;&nbsp;';
								echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($_GET['pID']), $pInfo->master_categories_id); ?>
						</td>
					</tr>
<?php } else { ?>
					<tr>
						<td class="main"><?php echo TEXT_MASTER_CATEGORIES_ID; ?></td>
						<td class="main"><?php echo TEXT_INFO_ID . ($_GET['pID'] > 0 ? 
						$pInfo->master_categories_id	. ' ' . 
						zen_get_category_name($pInfo->master_categories_id, $languages_id_selected ) : 
						$current_category_id	. ' ' . 
						zen_get_category_name($current_category_id, $languages_id_selected )); 
						//jph added
						echo " (PRODUCT ID " . (int)$_GET['pID'] . " )";
						?>
					</tr>
<?php } ?>
					<tr>
						<td colspan="2" class="main"><?php echo TEXT_INFO_MASTER_CATEGORIES_ID; ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '2'); ?></td>
					</tr>
<?php
// hidden fields not changeable on products page
echo zen_draw_hidden_field('master_categories_id', $pInfo->master_categories_id);
echo zen_draw_hidden_field('products_discount_type', $pInfo->products_discount_type);
echo zen_draw_hidden_field('products_discount_type_from', $pInfo->products_discount_type_from);
echo zen_draw_hidden_field('products_price_sorter', $pInfo->products_price_sorter);
?>
					
					
<!-- jph version of in out of stock + date avail -->
		  <tr> 
		   <td class="main" colspan="3">
		 	<table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
			<td class="tmain" align="right" >
			<?php echo TEXT_PRODUCTS_STATUS . '&nbsp;&nbsp;'; ?></td>
            <td class="info_status_1" >
			<?php echo zen_draw_radio_field('products_status', '1', $in_status) . '&nbsp;' . 
			TEXT_PRODUCT_AVAILABLE . '&nbsp;'; ?></td>
			<td class="info_status_0">
			<?php echo zen_draw_radio_field('products_status', '0', $out_status) . '&nbsp;&nbsp;' . 
			TEXT_PRODUCT_NOT_AVAILABLE; ?></td>
            
			<td class="tmain" align="right"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE . '&nbsp;&nbsp;';?>
			<br /><small>(YYYY-MM-DD)</small>&nbsp;&nbsp;</td>
            <td class="main">
			<script language="javascript">dateAvailable.writeControl(); dateAvailable.dateFormat="yyyy-MM-dd";</script>
			</td>
			<tr>
		    </table>
		   </td>
		  </tr>
<!-- end jph version of in out of stock + date avail -->
<!-- qty tax and weight jph -->
		  	<tr>
		    	<td class="main1" colspan="3" >
		 		<table border="0" width="100%" cellspacing="0" cellpadding="0">
		   			<tr>
					 <td>
					  <table border="0" width="100%" cellspacing="0" cellpadding="0">
		   				<tr>
							<td width="50%" class="main1"   align="left">
							<?php echo TEXT_PRODUCTS_QUANTITY; ?>
							</td>
							<td width="50%" class="main1"   align="left">
							<?php echo zen_draw_input_field('products_quantity', $pInfo->products_quantity); 
							?>		
							</td>
						<tr>
						</tr>
							<td width="50%" class="main1"   align="left">
							<?php echo TEXT_PRODUCTS_WEIGHT; ?>
							</td>
							<td width="50%" class="main1"   align="left">
							<?php echo zen_draw_input_field('products_weight', $pInfo->products_weight); ?>
							</td>
						</tr>
					  </table>
					</td>
					
					<td width="50%" class="main1"  >
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
		   				<tr>
							<td width="50%"  class="main1">
							<?php echo TEXT_PRODUCTS_TAX_CLASS; ?>
							</td>
							<td width="50%"  class="main1" align="left">
							<?php echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, 
							$pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?>
							</td>
						</tr>
						<tr>
							<td width="50%"  class="main1">
							<?php echo TEXT_PRODUCTS_PRICE_NET; ?>
							</td>
							<td width="50%"  class="main1" align="left">
							<?php echo zen_draw_input_field('products_price', $pInfo->products_price, 
							'onKeyUp="updateGross()"'); ?>
							</td>
						</tr>
						<tr>
							<td width="50%"  class="main1">
							<?php echo TEXT_PRODUCTS_PRICE_GROSS; ?>
							</td>
							<td width="50%"  class="main1" align="left">
							<?php echo zen_draw_input_field('products_price_gross', $pInfo->products_price, 
						'OnKeyUp="updateNet()"'); ?>
							</td>
						</table>
					</td>					
					</tr>
		  		</table>
				</td>
			</tr>
<!-- end qty tax and weight jph -->
          <tr>
            <td colspan="3"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '1'); ?></td>
          </tr>
<!-- image block jph -->
<?php
	$dir = @dir(DIR_FS_CATALOG_IMAGES);
	$dir_info[] = array('id' => '', 'text' => "Main Directory");
	while ($file = $dir->read()) {
		if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
			$dir_info[] = array('id' => $file . '/', 'text' => $file);
		}
	}
	//===============================mod jph update for 137/138 v4
	  $dir->close();
  	  sort($dir_info);
	//=================================

	$default_directory = substr( $pInfo->products_image, 0,strpos( $pInfo->products_image, '/')+1);
?>					
			<tr class="main2" bgcolor="#99FF66">              
				<td  colspan="3" class="main"  valign="top">
					<?php echo TEXT_PRODUCTS_IMAGE . zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' .
					zen_draw_file_field('products_image') . '<br />' . 
					'&nbsp&nbsp&nbsp' . $pInfo->products_image . 
					zen_draw_hidden_field('products_previous_image', $pInfo->products_image); ?>
				</td>
			</tr>
			<tr  class="main2" bgcolor="#99FF66">
			  <td colspan="3">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
			 		<tr class="main2">
						<td  class="main2" width="25%" align="right">    
						<?php echo TEXT_PRODUCTS_IMAGE_DIR; ?>&nbsp;
						</td>				 
						<td  class="main2" width="25%" align="left">  
						<?php echo zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory); ?>
						</td>					  		
						<td  class="main2" width="25%" align="left">
						<?php echo TEXT_IMAGES_OVERWRITE ;?>
					    </td>
						<td  class="main2" width="25%" align="left">
						<?php echo zen_draw_radio_field('overwrite', '0', $off_overwrite) . '&nbsp;' . 
								TABLE_HEADING_NO . ' ' . 
								zen_draw_radio_field('overwrite', '1', $on_overwrite) . '&nbsp;' . 
								TABLE_HEADING_YES; ?>
					
				        </td>						
					 </tr>
					 <tr class="main2">
						<td  class="main2" width="25%" align="right">&nbsp;    
						
						</td>				 
						<td  class="main2" width="25%" align="left">&nbsp;  
						
						</td>					  		
						<td  class="main2" width="25%" align="left">
						<?php echo TEXT_PRODUCTS_IMAGE_MANUAL; ?>
					    </td>
						<td  class="main2" width="25%" align="left">						
<?php 
//============added for v136 jph mod
						echo  '&nbsp;' . zen_draw_input_field('products_image_manual'); 
//================= end mod
?>					
				        </td>						
					 </tr>
		    	  </table>
		  		</td>
			</tr>

<!-- end image block jph -->
          <tr >
            <td colspan="3" ><?php echo zen_draw_separator('pixel_trans.gif', '100%', '1'); ?></td>
          </tr>
<!-- name descr etc jph -->
<?php
//******************************************************beginning of the specific language entries sets


		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$language_id = $languages[$i]['id'];
	
?>			
	<tr>
    <td colspan="3" >
	<table cellpadding="3" cellspacing="0" border="1" align="left"  width="100%">
<?php
	if($i == 0 && sizeof($languages) > 1) {
//special for multi language COLLATE dropdowns 1=yes / beta2 red area warning
		$red_warn = false;
		if($pInfo->misc_bool_4 != 0) {
			$yes = true;
			$no = false;
			$red_warn = true;
		}else{
			$yes = false;
			$no = true;
		}	
		if (!isset($_GET['pID'])) {
			$yes = true;
			$no = false;
			$red_warn = true;			
		}
	
	}//END IF $ i == 0 && sizeof($ languages)
?>
			<tr>
			<td colspan="3" align="center" class="maincolor<?php echo $language_id ?>">
				<hr />

<?php
	if($i == 0 && sizeof($languages) > 1) {
		echo '<span class="alert">' . TEXT_PRODUCTS_BOOK_COLLATE_DROPDOWN_VALUES . '? : </span>&nbsp;&nbsp;' . 
				zen_draw_radio_field('misc_bool_4', '1', $yes) . '&nbsp;' . 
				TEXT_PRODUCTS_BOOK_YES_BOOL_4 . '&nbsp;&nbsp;' . 
				zen_draw_radio_field('misc_bool_4', '0', $no) . '&nbsp;' . 
				TEXT_PRODUCTS_BOOK_NO_BOOL_4 ;  
	}
?>
			<h1><?php echo $languages[$i]['name']; ?></h1>
			</td>
			</tr>
<?php
	if($i == 0 && sizeof($languages) > 1) {
//red block warning re. copying to ALL languages!
		if($red_warn) {
	?><tr><td  colspan="3" cellpadding="5" align="center" valign="center" class="warncolor">
    <?php echo '<span style="color:white;vertical-align:middle;font-size:1.5em;">' . 
					TEXT_PRODUCTS_BOOK_COLLATE_DROPDOWN_VALUES . " !</span>"; ?>
	  </td></tr>
	<?php
		$red_warn = false;
		}
	}
?>

			<tr align="left" class="maincolor<?php echo $language_id ?>">
				<td colspan="3">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
			 		<tr class="maincolor<?php echo $language_id ?>">
					    <td width="15%" class="maincolor<?php echo $language_id ?>">
						<?php echo TEXT_PRODUCTS_NAME; ?>
						</td>
						<td class="maincolor<?php echo $language_id ?>" colspan="2" align="left">
						<?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . 
						zen_draw_input_field('products_name[' . $languages[$i]['id'] . ']', 
						(isset($products_name[$languages[$i]['id']]) ? 
						stripslashes($products_name[$languages[$i]['id']]) : 
						zen_get_products_name($pInfo->products_id, $languages[$i]['id'])), 
						zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_name')); ?>
						</td>
					</tr>
			  	</table>
				</td>
			</tr>
			
			<tr align="left" class="maincolor<?php echo $language_id ?>">
			  <td colspan="3">
			  <table border="0" cellspacing="0" cellpadding="0" width="100%">
			  <tr>	  
				<td class="maincolor<?php echo $language_id ?>" width="15%" valign="top" >
				<?php echo TEXT_PRODUCTS_DESCRIPTION; ?>
				</td>
				<td class="maincolor<?php echo $language_id ?>" width="85%">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr class="maincolor<?php echo $language_id ?>">
						<td class="maincolor<?php echo $language_id ?>" valign="top">
						<?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . 
						'/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;
						</td>
						<td class="maincolor<?php echo $language_id ?>" width="100%" align="left">
						<?php if (is_null($_SESSION['html_editor_preference_status'])) echo TEXT_HTML_EDITOR_NOT_DEFINED; ?>
						<?php 

//updated 137 code jph apr 2007
						if ("FCKEDITOR" == $_SESSION['html_editor_preference_status']) {

                		$oFCKeditor = new FCKeditor('products_description[' . $languages[$i]['id'] . ']') ;
						//courtesy of _samples/php/sample02.php in distribution fck 2.5
						if ( $languages_code_selected <> '' ){
							$oFCKeditor->Config['AutoDetectLanguage']	= false ;
							$oFCKeditor->Config['DefaultLanguage']		= $languages_code_selected ;
						}else{
							$oFCKeditor->Config['AutoDetectLanguage']	= true ;
							$oFCKeditor->Config['DefaultLanguage']		= 'en' ;
						}
                		$oFCKeditor->Value = (isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($pInfo->products_id, $languages[$i]['id']) ;
                		$oFCKeditor->Width  = '99%' ;
                		$oFCKeditor->Height = '330' ;
                		$output = $oFCKeditor->CreateHtml() ;  echo $output;
						} else { // using HTMLAREA or just raw "source"
          				echo zen_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '30', (isset($products_description[$languages[$i]['id']])) ? htmlspecialchars(stripslashes($products_description[$languages[$i]['id']])) : htmlspecialchars(zen_get_products_description($pInfo->products_id, $languages[$i]['id']))); //,'id="'.'products_description' . $languages[$i]['id'] . '"');
						} ?>
				 	</td>
					</tr>
				  </table>
		 		</td>
			  </tr>
			  </table>
			  </td>
			</tr>

<!-- end name descr etc jph -->



<!-- subtitle -->
		  <tr class="maincolor<?php echo $language_id ?>">
		  	<td colspan="3">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
			 	<tr class="maincolor<?php echo $language_id ?>">
					<td class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_SUBTITLE; ?>
					</td>
					<td class="maincolor<?php echo $language_id ?>" colspan="2" align="left">
		<?php    //only show subtitle as text box if it is selected page language 	
		if((int)$languages_id_selected  == $languages[$i]['id']){
			echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' .
					zen_draw_input_field('subtitle', $pInfo->subtitle, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 
					'subtitle'));	
		}else{//show existing value in text
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->subtitle . "</strong>";
		}
		 ?>
					</td>
					</tr>
			  	</table>
			</td>
		  </tr>
<!-- /subtitle -->	

<!-- book_type SUBTYPE now single select LANG MOD-->
		<tr class="maincolor<?php echo $language_id ?>" >
			<td colspan="3">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
			 		<tr class="maincolor<?php echo $language_id ?>">
						<td  valign="top" class="maincolor<?php echo $language_id ?>" width="15%">
						<?php echo TEXT_PRODUCTS_BOOK_TYPE . '<br />' . TEXT_PRODUCTS_BOOK_TYPE_ADD_NEW; ?>
						</td>
						<td valign="top" class="maincolor<?php echo $language_id ?>" align="left">
						<?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' .   
		zen_draw_pull_down_menu('book_types_id' . $i , $book_type_array[$i], $book_types_default[$i]); 

?>

						</td>
					</tr>
				</table>
			</td>
		</tr>
<!-- /book_type SUBTYPE -->		  
<!-- book_genre = SUBJECTS MULTIPLE selection -->
		<tr class="maincolor<?php echo $language_id ?>" >
			<td colspan="3" align="left">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
			 		<tr class="maincolor<?php echo $language_id ?>">
						<td valign="top" class="maincolor<?php echo $language_id ?>" width="15%" align="left">
						<?php echo TEXT_PRODUCTS_BOOK_GENRE . "&nbsp;&nbsp;" . '<br />' . 
						TEXT_PRODUCTS_BOOK_GENRE_ADD_NEW . "&nbsp;&nbsp;" ?>
						</td>
						<td class="maincolor<?php echo $language_id ?>" width="25px" align="left" valign="top">						
		<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';  ?>   
						</td>
						<td class="maincolor<?php echo $language_id ?>"  align="left" valign="top">						
			<?php echo  zen_draw_pull_down_menu_multiple('book_genres_id' . $i . '[]', $book_genre_array[$i], 
						$book_genres_default_array[$i], 'multiple="multiple"', 25) . '<br />' .
						TEXT_PRODUCTS_BOOK_GENRE_HELP; ?>

<?php   
//test arrays
/*
	for($k=0;$k<sizeof($book_genre_array[$i][$k]);$k++){
		echo "<br />L i.d =" . $i . '(' . $k . ") ,book_genre_array i.d = " . $book_genre_array[$i][$k]['id'] .
			",book_genre_array name = " . $book_genre_array[$i][$k]['text'] ;
	}
//
*/
?> 	
						</td>					
					</tr>					
				</table>
			  </td>
			</tr>
<!-- /book_genre = SUBJECT -->

<!-- book_condition DD LANG MOD-->
			<tr class="maincolor<?php echo $language_id ?>" align="left">
				<td colspan="3" >
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
				    <tr class="maincolor<?php echo $language_id ?>">	  
						<td width="15%" class="maincolor<?php echo $language_id ?>">
						<?php echo TEXT_PRODUCTS_BOOK_CONDITION; ?></td>
						
						<td class="maincolor<?php echo $language_id ?>" align="left" valign="top">
						<?php
    					if((int)$languages_id_selected  == $languages[$i]['id']){
							echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' .
						 		zen_draw_pull_down_menu('book_condition_id', $book_condition_array[$i], 
								$pInfo->book_condition_id);
						}else{
							echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $book_condition_text . "</strong>";
						}
						 ?>
						</td>
					</tr>
				</table>				
				</td>
			</tr>
<!-- /book_condition DD-->					

			<tr><td colspan="3" class="maincolor<?php echo $language_id ?>"></td></tr>
<!-- MULTIPLE selection book_authors lang-->
			<tr class="maincolor<?php echo $language_id ?>">
				<td colspan="3">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
			 		<tr class="maincolor<?php echo $language_id ?>">
						<td  valign="top" class="maincolor<?php echo $language_id ?>" width="15%">
						<?php echo TEXT_PRODUCTS_BOOK_AUTHORS . '<br />' . TEXT_PRODUCTS_BOOK_AUTHORS_ADD_NEW; ?>
						</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">
					<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']); ?>
 					</td>
					<td class="maincolor<?php echo $language_id ?>" align="left" valign="top">
					<?php 
					
					echo zen_draw_pull_down_menu_multiple('book_authors_id' . $i . '[]', 
					$book_authors_array[$i], $book_authors_default_array[$i], ' multiple="multiple"', 25) . 
					"<br />" . TEXT_PRODUCTS_BOOK_AUTHORS_HELP; ?>
					</td>
				</table>				
				</td>
			</tr>
<!-- /book_authors -->

<!-- manufacturer PUBLISHER -->
			<tr class="maincolor<?php echo $language_id ?>" >
			  <td colspan="3">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
					<td class="maincolor<?php echo $language_id ?>" width="15%" align="left">
				<?php echo TEXT_PRODUCTS_BOOK_PUBLISHER . '<br />' . 
					TEXT_PRODUCTS_BOOK_PUBLISHER_ADD_NEW; ?>
					</td>
					<td class="maincolor<?php echo $language_id ?>"  align="left">
		<?php    //only show publisher as dd if it is selected page language 	
		if((int)$languages_id_selected  == $language_id){
			echo zen_draw_separator('pixel_trans.gif', '24', '15') .
					'&nbsp;' . zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, 
					$pInfo->manufacturers_id);		
		}else{//show existing value in text
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $manufacturers_array[$pInfo->manufacturers_id]['text'] . "</strong>";
		}
		if($get_man_urls_value[$i] <> '') echo " (" . $get_man_urls_value[$i] . ")";
		 ?>
					</td>
				  </tr>
				</table>				
			  </td>
			</tr>	
<!-- /manufacturer PUBLISHER -->		
<!-- pub_date PUB_YEAR,  PUB MONTH single select-->
			<tr class="maincolor<?php echo $language_id ?>" >
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
					<td class="maincolor<?php echo $language_id ?>" width="15%" valign="top" align="left">
					<?php echo TEXT_PRODUCTS_BOOK_PUB_DATE . "&nbsp;&nbsp;" ?>					
					</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">
					<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']); ?>
 					</td>
					<td class="maincolor<?php echo $language_id ?>" width="10%" valign="top" align="right">
					<?php echo TEXT_PRODUCTS_BOOK_MISC_DD1 . "&nbsp;&nbsp;" ?>
					</td>
					<td class="maincolor<?php echo $language_id ?>" width="15%" valign="top" align="left">
		<?php //month is dd1
		//theoretically multiple, but the single value-per-lang in db is stored in misc_1 field
			echo zen_draw_pull_down_menu('book_dd1_id' . $i . '[]', $book_dd1_array[$i], $book_dd1_default_array[$i][0])
		?>
					</td>				
					<td  class="maincolor<?php echo $language_id ?>" valign="top" align="left">
		(YYYY)&nbsp;
		<?php    //only show year as dd if it is selected page language 	//zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'pub_date')
		if((int)$languages_id_selected  == $language_id){
			echo zen_draw_input_field('pub_date', $pInfo->pub_date, ' size="4" maxlength="4" ');		
		}else{//show existing value in text
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->pub_date . "</strong>";
		}
		 ?>
					</td>
					<tr>
				</table>				
			  </td>
			</tr>					
<!-- end pub_date PUB_YEAR + PUB MONTH -->
<!--  FIRST_EDITION-->
		<tr class="maincolor<?php echo $language_id ?>" >
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  				
					<td class="maincolor<?php echo $language_id ?>" width="15%" align="left" valign="top">
					<?php echo TEXT_PRODUCTS_BOOK_MISC_INT_5_2 . '&nbsp;' ?>
					</td>		
					<td  width="15%" class="maincolor<?php echo $language_id ?>" valign="top" align="left">
		<?php    //only first ed. year if it is selected page language 	//zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'pub_date')
		if((int)$languages_id_selected  == $language_id){
			echo zen_draw_separator('pixel_trans.gif', '24', '15') . "&nbsp;" . 
						zen_draw_input_field('misc_int_5_2', $pInfo->misc_int_5_2, ' size="4" maxlength="4" ');		
		}else{//show existing value in text
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->misc_int_5_2 . "</strong>";
		}
		 ?>
					</td>
	<?php if($fg_show_product_book_info_misc_bool_3 == 1){?>
				<td valign="top" align="left" class="maincolor<?php echo $language_id ?>">
		<?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_3; ?>
		<?php 
		if((int)$languages_id_selected  == $language_id){
			echo '<br />&nbsp;&nbsp;' . zen_draw_radio_field('misc_bool_3', '1', ($pInfo->misc_bool_3!=0)) . '&nbsp;' . 
			TEXT_PRODUCTS_BOOK_YES_BOOL_3 . '&nbsp;&nbsp;' . 
			zen_draw_radio_field('misc_bool_3', '0', ($pInfo->misc_bool_3==0)) . '&nbsp;' . 
			TEXT_PRODUCTS_BOOK_NO_BOOL_3;
		}else{//text
			if($pInfo->misc_bool_3 == 1) {
				echo "<br />&nbsp;&nbsp;<strong>" . TEXT_PRODUCTS_BOOK_YES_BOOL_3 . "</strong>";
			}else{
				echo "<br />&nbsp;&nbsp;<strong>" . TEXT_PRODUCTS_BOOK_NO_BOOL_3 . "</strong>";
			}
		}
		?>
				</td>
	<?php 
			}
	?>			
					<tr>
				</table>				
			  </td>
			</tr>				
<!-- end FIRST_EDITION-->
<?php //==========================================================inserted dd2 to 6 here ?>
<? //====== dd2		
			if($fg_show_product_book_info_misc_dd2 == 1){?>
			<tr class="maincolor<?php echo $language_id ?>">
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
				<tr class="maincolor<?php echo $language_id ?>">
				<td  valign="top" class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_DD2; ?>
				</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">

<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';  ?>   
						</td>
						<td class="maincolor<?php echo $language_id ?>"  align="left" valign="top">						
			<?php echo  zen_draw_pull_down_menu_multiple('book_dd2_id' . $i . '[]', $book_dd2_array[$i], $book_dd2_default_array[$i],  'multiple="multiple"', 15)
?>
				</td> 	  
				</tr>
				</table>
			  </td>
			</tr>
<?			 }   
//=========end dd2 
?>	
<? //====== dd3		
			if($fg_show_product_book_info_misc_dd3 == 1){?>
			<tr class="maincolor<?php echo $language_id ?>">
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
				<tr class="maincolor<?php echo $language_id ?>">
				<td valign="top" class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_DD3; ?>
				</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">
<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';  ?>   
						</td>
						<td class="maincolor<?php echo $language_id ?>"  align="left" valign="top">						
			<?php echo  
zen_draw_pull_down_menu_multiple('book_dd3_id' . $i . '[]', $book_dd3_array[$i], $book_dd3_default_array[$i],  'multiple="multiple"',15)
?>
					</td> 	  
				</tr>
				</table>
			  </td>
			</tr>
<?			 }   
//=========end dd3 
?>					
<? //====== dd4		
			if($fg_show_product_book_info_misc_dd4 == 1){?>
			<tr class="maincolor<?php echo $language_id ?>">
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
				<tr class="maincolor<?php echo $language_id ?>">
				<td  valign="top" class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_DD4; ?>
				</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">
<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';  ?>   
						</td>
						<td class="maincolor<?php echo $language_id ?>"  align="left" valign="top">						
			<?php echo  zen_draw_pull_down_menu_multiple('book_dd4_id' . $i . '[]', $book_dd4_array[$i], $book_dd4_default_array[$i],  'multiple="multiple"',15)
?>
					</td> 	  
				</tr>
				</table>
			  </td>
			</tr>
<?			 }   
//=========end dd4 
?>					
<? //====== dd5		
			if($fg_show_product_book_info_misc_dd5 == 1){?>
			<tr class="maincolor<?php echo $language_id ?>">
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
				<tr class="maincolor<?php echo $language_id ?>">
				<td  valign="top" class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_DD5; ?>
				</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">
<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';  ?>   
						</td>
						<td class="maincolor<?php echo $language_id ?>"  align="left" valign="top">						
			<?php echo  zen_draw_pull_down_menu_multiple('book_dd5_id' . $i . '[]', $book_dd5_array[$i], $book_dd5_default_array[$i],  'multiple="multiple"',15)
?>
				</td> 	  
				</tr>
				</table>
			  </td>
			</tr>
<?			 }   
//=========end dd5 
?>					
<? //====== dd6		
			if($fg_show_product_book_info_misc_dd6 == 1){?>
			<tr class="maincolor<?php echo $language_id ?>">
			  <td colspan="3" valign="top">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">	  
				<tr class="maincolor<?php echo $language_id ?>">
				<td  valign="top" class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_DD6; ?>
				</td>
					<td class="maincolor<?php echo $language_id ?>"  width="25px" valign="top">
<?php echo  zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . 
						$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';  ?>   
						</td>
						<td class="maincolor<?php echo $language_id ?>"  align="left" valign="top">						
			<?php echo  zen_draw_pull_down_menu_multiple('book_dd6_id' . $i . '[]', $book_dd6_array[$i], $book_dd6_default_array[$i],  'multiple="multiple"',15)
?>
				</td> 	  
				</tr>
				</table>
			  </td>
			</tr>
		
<?			 }   
//=========end dd6 
?>				
<?php //==========================================dd2 to dd6 above ?>


	

					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '1'); ?></td>
					</tr>
<!-- book_color = FORMAT DD -->
					<tr  class="maincolor<?php echo $language_id ?>">
						<td class="maincolor<?php echo $language_id ?>" width="15%">
						<?php echo TEXT_PRODUCTS_BOOK_COLOR; ?>
						</td>
						<td class="maincolor<?php echo $language_id ?>">
		<?php    //only format box if it is selected page language 	
		if((int)$languages_id_selected  == $language_id){
			echo zen_draw_pull_down_menu('book_color_id', $book_color_array, $pInfo->book_color_id);
		}else{//show existing value in text
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $book_color_array[$pInfo->book_color_id]['text'] . "</strong>";
		}
		  ?></td>


					</tr>
<!-- /book_color = FORMAT DD-->
<?php

?>

<!-- condition text jph -->
					<tr  class="maincolor<?php echo $language_id ?>">				
						<td class="maincolor<?php echo $language_id ?>" width="15%">
						<?php echo TEXT_PRODUCTS_BOOK_CONDITION . '&nbsp;&nbsp;'; ?>
						</td>
						<td class="maincolor<?php echo $language_id ?>">
<?php    //only condition text box if it is selected page language 	
		if((int)$languages_id_selected  == $language_id){
			echo zen_draw_input_field('misc_2', 
						$pInfo->misc_2, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_2'));
		}else{//show existing value in text
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->misc_2 . "</strong>";
		}
		  ?>
						</td>

						</td>
					</tr>
<!-- /condition text -->

<!-- isbn -->
					<tr>
						<td class="maincolor<?php echo $language_id ?>" width="15%"><?php echo TEXT_PRODUCTS_BOOK_ISBN; ?></td>
						<td class="maincolor<?php echo $language_id ?>">
	
<?php    //only isbn text box if it is selected page language 	
					if((int)$languages_id_selected  == $language_id){
						echo zen_draw_input_field('products_model', $pInfo->products_model, 
						zen_set_field_length(TABLE_PRODUCTS, 'products_model'));
					}else{//show existing value in text
						echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->products_model . "</strong>";
					}
?>

				</tr>
<!-- /isbn -->

<!-- pages -->
					<tr class="maincolor<?php echo $language_id ?>">
						<td class="maincolor<?php echo $language_id ?>" width="15%">
			<?php echo TEXT_PRODUCTS_BOOK_PAGES; ?></td>
						<td class="maincolor<?php echo $language_id ?>">

<?php    //only isbn text box if it is selected page language 	
					if((int)$languages_id_selected  == $language_id){
						echo zen_draw_input_field('pages', $pInfo->pages, 
								zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'pages'));
					}else{//show existing value in text
						echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->pages . "</strong>";
					}
?>
					</tr>
<!-- /pages -->
<!-- size -->
					<tr class="maincolor<?php echo $language_id ?>">
						<td class="maincolor<?php echo $language_id ?>" width="15%">
							<?php echo TEXT_PRODUCTS_BOOK_SIZE; ?></td>
						<td class="maincolor<?php echo $language_id ?>">
<?php    //only isbn text box if it is selected page language 	
					if((int)$languages_id_selected  == $language_id){
						echo zen_draw_input_field('size', $pInfo->size, 
											zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'size'));
					}else{//show existing value in text
						echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>" . $pInfo->size . "</strong>";
					}
?>
					</tr>
<!-- /size -->
	</table>
	</td>
	</tr>
<?php //eof LANG MOD may 2007
		}

?>
					<tr>
						<td colspan="3"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
<!-- start of jph switchable fields -->
			<tr class="main5">
				<td colspan="3">
				<table border="0" cellspacing="0" cellpadding="0" width="100%">

<? //======MISC_1		
			if($fg_show_product_book_info_misc_1 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_1; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_1', $pInfo->misc_1, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_1'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_1 
?>
<? /* used already //======MISC_2		
			if($fg_show_product_book_info_misc_2 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_2; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_2', $pInfo->misc_2, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_2'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_2  */
?>
<? //======MISC_3		
			if($fg_show_product_book_info_misc_3 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_3; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_3', $pInfo->misc_3, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_3'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_3 
?>
<? //======MISC_4		
			if($fg_show_product_book_info_misc_4 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_4; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_4', $pInfo->misc_4, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_4'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_4 
?>	
<? //======MISC_5		
			if($fg_show_product_book_info_misc_5 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_5; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_5', $pInfo->misc_5, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_5'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_5 
?>						
<? //======MISC_6		
			if($fg_show_product_book_info_misc_6 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_6; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_6', $pInfo->misc_6, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_6'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_6
?>						
<? //======MISC_7		
			if($fg_show_product_book_info_misc_7 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_7; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_7', $pInfo->misc_7, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_7'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_7 
?>						
<? //======MISC_8		
			if($fg_show_product_book_info_misc_8 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_8; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_8', $pInfo->misc_8, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_8'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_8 
?>						
<? //======MISC_9		
			if($fg_show_product_book_info_misc_9 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_9; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_9', $pInfo->misc_9, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_9'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_9 
?>						
<? //======MISC_10		
			if($fg_show_product_book_info_misc_10 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_10; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_input_field('misc_10', $pInfo->misc_10, zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA, 'misc_10'));
?>
				</td> 	  
				</tr>
<?			 }   
//=========end MISC_10 
?>
<?php //LANG MOD may 2007
		//$languages = zen_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
<? /* used as pub nmonth above, single value dropdown select
//====== dd1		
			if($fg_show_product_book_info_misc_dd1 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_DD1; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . 
zen_draw_pull_down_menu_multiple('book_dd1_id' . $i . '[]'', $book_dd1_array[$i], $book_dd1_default_array[$i],  'multiple="multiple"',20)
?>
				</td> 	  
				</tr>
<?			 }   
//=========end dd1 
*/
?>			

<?	}   
//=========end LANG mods dd1->6 
?>

	
<? //====== misc_bool_1		
			if($fg_show_product_book_info_misc_bool_1 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_1; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_1', '1', ($pInfo->misc_bool_1!=0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_YES_BOOL_1 . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_1', '0', ($pInfo->misc_bool_1==0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_NO_BOOL_1; 
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_bool_1 
?>					
<? //====== misc_bool_2		
			if($fg_show_product_book_info_misc_bool_2 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_2; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_2', '1', ($pInfo->misc_bool_2!=0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_YES_BOOL_2 . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_2', '0', ($pInfo->misc_bool_2==0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_NO_BOOL_2; 
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_bool_2 
?>					
<? //====== misc_bool_3		
/* used inside lang block
			if($fg_show_product_book_info_misc_bool_3 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_3; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_3', '1', ($pInfo->misc_bool_3!=0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_YES_BOOL_3 . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_3', '0', ($pInfo->misc_bool_3==0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_NO_BOOL_3; 

?>
				</td> 	  
				</tr>
<?			 } 
  */
//=========end misc_bool_3 
?>	
<? //====== misc_bool_4
/* used in languages version only, for collating lang entries		
			if($fg_show_product_book_info_misc_bool_4 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_4; ?>
				</td>
				<td class="main5">
<?php //special sets radio to yes as default for blank record
	if($pInfo->misc_bool_4 != 0) {
		$yes = true;
		$no = false;
	}else{
		$yes = false;
		$no = true;
	}	
	if (!isset($_GET['pID'])) {
		$yes = true;
		$no = false;
	}
	echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_4', '1', $yes) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_YES_BOOL_4 . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_4', '0', $no) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_NO_BOOL_4; 
?>
				</td> 	  
				</tr>
<?			 } 
*/  
//=========end misc_bool_4 
?>					
<? //====== misc_bool_5		(NEW/USED radio boxes)
			if($fg_show_product_book_info_misc_bool_5 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_5; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_5', '1', ($pInfo->misc_bool_5!=0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_YES_BOOL_5 . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_5', '0', ($pInfo->misc_bool_5==0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_NO_BOOL_5; 
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_bool_5 
?>					
<? //====== misc_bool_6		
			if($fg_show_product_book_info_misc_bool_6 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_BOOL_6; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_6', '1', ($pInfo->misc_bool_6!=0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_YES_BOOL_6 . '&nbsp;&nbsp;' . 
	zen_draw_radio_field('misc_bool_6', '0', ($pInfo->misc_bool_6==0)) . '&nbsp;' . 
	TEXT_PRODUCTS_BOOK_NO_BOOL_6; 
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_bool_6 
?>																				
<? //====== misc_int_5_1		
			if($fg_show_product_book_info_misc_int_5_1 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_INT_5_1; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
			zen_draw_input_field('misc_int_5_1', $pInfo->misc_int_5_1, 
			zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'misc_int_5_1'))
			
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_int_5_1 
?>																				
<? //====== misc_int_5_2	
/* USED AS FIRST PUB YEAR IN LANG BLOCK	
			if($fg_show_product_book_info_misc_int_5_2 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_INT_5_2; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
			zen_draw_input_field('misc_int_5_2', $pInfo->misc_int_5_2, 
			zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'misc_int_5_2'))
?>
				</td> 	  
				</tr>
<?			 }  
*/ 
//=========end misc_int_5_2 
?>	
<? //====== misc_int_5_3		
			if($fg_show_product_book_info_misc_int_5_3 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_INT_5_3; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
			zen_draw_input_field('misc_int_5_3', $pInfo->misc_int_5_3, 
			zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'misc_int_5_3'))
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_int_5_3 
?>																																							
<? //====== misc_int_11_1		 
			if($fg_show_product_book_info_misc_int_11_1 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_INT_11_1; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' .
			zen_draw_input_field('misc_int_11_1', $pInfo->misc_int_11_1, 
			zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'misc_int_11_1'))
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_int_11_1
?>																				
<? //====== misc_int_11_2		
			if($fg_show_product_book_info_misc_int_11_2 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_INT_11_2; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
			zen_draw_input_field('misc_int_11_2', $pInfo->misc_int_11_2, 
			zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'misc_int_11_2'))
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_int_11_2
?>																				
<? //====== misc_int_11_3		
			if($fg_show_product_book_info_misc_int_11_3 == 1){?>
				<tr class="main5">
				<td class="main5" width="15%"><?php echo TEXT_PRODUCTS_BOOK_MISC_INT_11_3; ?>
				</td>
				<td class="main5">
<?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;&nbsp;' . 
			zen_draw_input_field('misc_int_11_3', $pInfo->misc_int_11_3, 
			zen_set_field_length(TABLE_PRODUCT_BOOK_EXTRA,'misc_int_11_3'))
?>
				</td> 	  
				</tr>
<?			 }   
//=========end misc_int_11_3
?>																		
			</table>
			</tr>
<!-- end of jph switchable fields -->
					<tr>
						<td class="main"><?php echo TEXT_PRODUCT_IS_FREE; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('product_is_free', '1', ($in_product_is_free==1)) . '&nbsp;' . TEXT_YES . '&nbsp;&nbsp;' . zen_draw_radio_field('product_is_free', '0', ($in_product_is_free==0)) . '&nbsp;' . TEXT_NO . ' ' . ($pInfo->product_is_free == 1 ? '<span class="errorText">' . TEXT_PRODUCTS_IS_FREE_EDIT . '</span>' : ''); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo TEXT_PRODUCT_IS_CALL; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('product_is_call', '1', ($in_product_is_call==1)) . '&nbsp;' . TEXT_YES . '&nbsp;&nbsp;' . zen_draw_radio_field('product_is_call', '0', ($in_product_is_call==0)) . '&nbsp;' . TEXT_NO . ' ' . ($pInfo->product_is_call == 1 ? '<span class="errorText">' . TEXT_PRODUCTS_IS_CALL_EDIT . '</span>' : ''); ?></td>
					</tr>

					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('products_priced_by_attribute', '1', $is_products_priced_by_attribute) . '&nbsp;' . TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE . '&nbsp;&nbsp;' . zen_draw_radio_field('products_priced_by_attribute', '0', $not_products_priced_by_attribute) . '&nbsp;' . TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE . ' ' . ($pInfo->products_priced_by_attribute == 1 ? '<span class="errorText">' . TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT . '</span>' : ''); ?></td>
					</tr>

					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					
					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_VIRTUAL; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('products_virtual', '1', $is_virtual) . '&nbsp;' . TEXT_PRODUCT_IS_VIRTUAL . '&nbsp;' . zen_draw_radio_field('products_virtual', '0', $not_virtual) . '&nbsp;' . TEXT_PRODUCT_NOT_VIRTUAL . ' ' . ($pInfo->products_virtual == 1 ? '<br /><span class="errorText">' . TEXT_VIRTUAL_EDIT . '</span>' : ''); ?></td>
					</tr>

					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main" valign="top"><?php echo TEXT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING; ?></td>
						<td class="main" valign="top"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('product_is_always_free_shipping', '1', $is_product_is_always_free_shipping) . '&nbsp;' . TEXT_PRODUCT_IS_ALWAYS_FREE_SHIPPING . '&nbsp;' . zen_draw_radio_field('product_is_always_free_shipping', '0', $not_product_is_always_free_shipping) . '&nbsp;' . TEXT_PRODUCT_NOT_ALWAYS_FREE_SHIPPING  . '<br />' . zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('product_is_always_free_shipping', '2', $special_product_is_always_free_shipping) . '&nbsp;' . TEXT_PRODUCT_SPECIAL_ALWAYS_FREE_SHIPPING . ' ' . ($pInfo->product_is_always_free_shipping == 1 ? '<br /><span class="errorText">' . TEXT_FREE_SHIPPING_EDIT . '</span>' : ''); ?></td>
					</tr>

					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_QTY_BOX_STATUS; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('products_qty_box_status', '1', $is_products_qty_box_status) . '&nbsp;' . TEXT_PRODUCTS_QTY_BOX_STATUS_ON . '&nbsp;' . zen_draw_radio_field('products_qty_box_status', '0', $not_products_qty_box_status) . '&nbsp;' . TEXT_PRODUCTS_QTY_BOX_STATUS_OFF . ' ' . ($pInfo->products_qty_box_status == 0 ? '<br /><span class="errorText">' . TEXT_PRODUCTS_QTY_BOX_STATUS_EDIT . '</span>' : ''); ?></td>
					</tr>

					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>

					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_QUANTITY_MIN_RETAIL; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_quantity_order_min', ($pInfo->products_quantity_order_min == 0 ? 1 : $pInfo->products_quantity_order_min)); ?></td>
					</tr>

					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_quantity_order_max', $pInfo->products_quantity_order_max); ?>&nbsp;&nbsp;<?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT; ?></td>
					</tr>

					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_quantity_order_units', ($pInfo->products_quantity_order_units == 0 ? 1 : $pInfo->products_quantity_order_units)); ?></td>
					</tr>

					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_MIXED; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('products_quantity_mixed', '1', $in_products_quantity_mixed) . '&nbsp;' . TEXT_YES . '&nbsp;&nbsp;' . zen_draw_radio_field('products_quantity_mixed', '0', $out_products_quantity_mixed) . '&nbsp;' . TEXT_NO; ?></td>
					</tr>

					<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>

<script language="javascript"><!--
updateGross();
//--></script>
<?php
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>

<?php
		}
?>
			<tr>
						<td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
					</tr>
					<tr>
						<td class="main"><?php echo TEXT_PRODUCTS_SORT_ORDER; ?></td>
						<td class="main"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_sort_order', $pInfo->products_sort_order); ?></td>
					</tr>
<?php // BEGIN CEON URI MAPPING 2 of 2
          echo $ceon_uri_mapping_admin->collectInfoBuildURIMappingFields();
// END CEON URI MAPPING 2 of 2 ?>
				</table></td>
			</tr>
			<tr>
				<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
			</tr>
			<tr>
				<td class="main" align="right"><?php echo zen_draw_hidden_field('products_date_added', (zen_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) . zen_image_submit('button_preview.gif', IMAGE_PREVIEW) . '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
			</tr>
		</table></form>