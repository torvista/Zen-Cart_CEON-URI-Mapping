<?php //beta2
//multiple LANG mods marked jph may 2007
//for 137 update imported ll 94-104 jph mar 2007  ll 142
// 2006-03-30 : moku

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
//defensive
if (!isset($_SESSION['language'])) {
  die('Language Session Missing');
}

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

    if (zen_not_null($_POST)) {
      $pInfo = new objectInfo($_POST);
      $products_name = $_POST['products_name'];
      $products_description = $_POST['products_description'];
      $products_url = $_POST['products_url'];
//beta 2 addition
	 $misc_bool_4 = $_POST['misc_bool_4'];
// 2006-03-30 : moku
//removed, lang mod $book_authors_id = $_POST['book_authors_id'];
//removed, lang mod $book_genres_id = $_POST['book_genres_id'];
//removed, $book_types_id = $_POST['book_types_id'];
//============== added jph for 6 single selection dropdowns
//$book_dd1_id = $_POST['book_dd1_id'];
//$book_dd2_id = $_POST['book_dd2_id'];
//$book_dd3_id = $_POST['book_dd3_id'];
//$book_dd4_id = $_POST['book_dd4_id'];
//$book_dd5_id = $_POST['book_dd5_id'];
//$book_dd6_id = $_POST['book_dd6_id'];
//=====================================

      // BEGIN CEON URI MAPPING 1 of 4
      require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
      
      $ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
      
      $ceon_uri_mapping_admin->productPreviewProcessSubmission($current_category_id);
      
      // END CEON URI MAPPING 1 of 4
    } else {
      $product = $db->Execute("select p.products_id, pd.language_id, pd.products_name,
                                      pd.products_description, pd.products_url, p.products_quantity,
                                      p.products_model, p.products_image, p.products_price, p.products_virtual,
                                      p.products_weight, p.products_date_added, p.products_last_modified,
                                      p.products_date_available, p.products_status, p.manufacturers_id,
                                      p.products_quantity_order_min, p.products_quantity_order_units, p.products_priced_by_attribute,
                                      p.product_is_free, p.product_is_call, p.products_quantity_mixed,
                                      p.product_is_always_free_shipping, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_sort_order
                               from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                               where p.products_id = pd.products_id
                               and p.products_id = '" . (int)$_GET['pID'] . "'");

      $pInfo = new objectInfo($product->fields);
      $products_image_name = $pInfo->products_image;
      
      // BEGIN CEON URI MAPPING 2 of 4
      require_once(DIR_WS_CLASSES . 'class.CeonURIMappingAdminProductPages.php');
      
      $ceon_uri_mapping_admin = new CeonURIMappingAdminProductPages();
      
      $ceon_uri_mapping_admin->productPreviewInitialLoad((int) $_GET['pID'],
        $zc_products->get_handler((int) $_GET['product_type']));
      
      // END CEON URI MAPPING 2 of 4
    }

    $form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';

//removed in 137 jph    echo zen_draw_form................

    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
        $pInfo->products_name = zen_get_products_name($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_description = zen_get_products_description($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_url = zen_get_products_url($pInfo->products_id, $languages[$i]['id']);
      } else {
        $pInfo->products_name = zen_db_prepare_input($products_name[$languages[$i]['id']]);
        $pInfo->products_description = zen_db_prepare_input($products_description[$languages[$i]['id']]);
        $pInfo->products_url = zen_db_prepare_input($products_url[$languages[$i]['id']]);
      }

      $specials_price = zen_get_products_special_price($pID);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
//beta2 addition
	if($misc_bool_4 == '1' && $i == 0){
	?><tr><td  cellpadding="5" align="center" valign="center" class="warncolor">
    <?php echo '<span style="color:white;vertical-align:middle;font-size:1.5em;">' . 
					TEXT_PRODUCTS_BOOK_COLLATE_DROPDOWN_VALUES . " !</span>"; ?>
	  </td></tr>
	<?php
	}
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . 
				$languages[$i]['directory'] . '/images/' . 
				$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . 
				$pInfo->products_name; ?>
			</td>
            <td class="pageHeading" align="right">
		<?php 
		//uses uk price function jph mod
		echo $currencies->format($pInfo->products_price) . ($pInfo->products_virtual == 1 ? 
		'<span class="errorText">' . '<br />' . TEXT_VIRTUAL_PREVIEW . '</span>' : '') . 
		($pInfo->product_is_always_free_shipping == 1 ? '<span class="errorText">' . '<br />' . 
		TEXT_FREE_SHIPPING_PREVIEW . '</span>' : '') . ($pInfo->products_priced_by_attribute == 1 ? 
		'<span class="errorText">' . '<br />' . 
		TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_PREVIEW . '</span>' : '') . ($pInfo->product_is_free == 1 ? 
		'<span class="errorText">' . '<br />' . TEXT_PRODUCTS_IS_FREE_PREVIEW . '</span>' : '') . 
		($pInfo->product_is_call == 1 ? '<span class="errorText">' . '<br />' . 
		TEXT_PRODUCTS_IS_CALL_PREVIEW . '</span>' : '') . ($pInfo->products_qty_box_status == 0 ? 
		'<span class="errorText">' . '<br />' . TEXT_PRODUCTS_QTY_BOX_STATUS_PREVIEW . '</span>' : '') . 
		($pInfo->products_priced_by_attribute == 1 ? '<br />' . 
		zen_get_products_display_price($_GET['pID']) : ''); 
//jph uk not used, mod corrected issue 2 ?></td>

          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main">
          <?php
//auto replace with defined missing image
            if ($_POST['products_image_manual'] != '') {
              $products_image_name = $_POST['img_dir'] . $_POST['products_image_manual'];
              $pInfo->products_name = $products_image_name;
            }
            if ($_POST['image_delete'] == 1 || $products_image_name == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
              echo zen_image(DIR_WS_CATALOG_IMAGES . PRODUCTS_IMAGE_NO_IMAGE, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description;
            } else {
              echo zen_image(DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description;
            }
          ?>
        </td>
      </tr>
<?php
      if ($pInfo->products_url) {
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></td>
      </tr>
<?php
      }
?>
<?php // BEGIN CEON URI MAPPING 3 of 4
      $ceon_uri_mapping_admin->productPreviewOutputURIMappingInfo($languages[$i]);
      // END CEON URI MAPPING 3 of 4 ?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
      if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, zen_date_long($pInfo->products_date_available)); ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, zen_date_long($pInfo->products_date_added)); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    }

    if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
      if (isset($_GET['origin'])) {
        $pos_params = strpos($_GET['origin'], '?', 0);
        if ($pos_params != false) {
          $back_url = substr($_GET['origin'], 0, $pos_params);
          $back_url_params = substr($_GET['origin'], $pos_params + 1);
        } else {
          $back_url = $_GET['origin'];
          $back_url_params = '';
        }
      } else {
        $back_url = FILENAME_CATEGORIES;
        $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
      }
?>
      <tr>
        <td align="right"><?php echo '<a href="' . zen_href_link($back_url, $back_url_params . (isset($_POST['search']) ? '&search=' . $_POST['search'] : ''), 'NONSSL') . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
    } else {
      echo zen_draw_form($form_action, $type_admin_handler, 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"');
?>
      <tr>
        <td align="right" class="smallText">
<?php
/* Re-Post all POST'ed variables */
      reset($_POST);
      while (list($key, $value) = each($_POST)) {
        if (!is_array($_POST[$key])) {
          echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
        }
      }


//LANG mod s
//each genre $i can have multiple, is therefore an array
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_genres_id_arr = $_POST['book_genres_id' . $i];
	for ($j=0; $j<sizeof($book_genres_id_arr); $j++) {
		echo zen_draw_hidden_field('book_genres_id'.$i . '[]', $book_genres_id_arr[$j]) . "\n";
	}
}
//type is single selection per lang
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		echo zen_draw_hidden_field('book_types_id'.$i , $_POST['book_types_id' . $i]) . "\n";
}
//each $i can have multiple, is therefore an array
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_authors_id_arr = $_POST['book_authors_id' . $i];
	for ($j=0; $j<sizeof($book_authors_id_arr); $j++) {
		echo zen_draw_hidden_field('book_authors_id'.$i . '[]', $book_authors_id_arr[$j]) . "\n";
//echo '<br />book_authors_id'.$i . '[]', $book_authors_id_arr[$j];
	}
}
//dd1 to dd6
//each $i can have multiple, is therefore an array
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_dd1_id_arr = $_POST['book_dd1_id' . $i];
	for ($j=0; $j<sizeof($book_dd1_id_arr); $j++) {
		echo zen_draw_hidden_field('book_dd1_id'.$i . '[]', $book_dd1_id_arr[$j]) . "\n";
//echo '<br />book_dd1_id'.$i . '[]', $book_dd1_id_arr[$j];
	}
}
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_dd2_id_arr = $_POST['book_dd2_id' . $i];
	for ($j=0; $j<sizeof($book_dd2_id_arr); $j++) {
		echo zen_draw_hidden_field('book_dd2_id'.$i . '[]', $book_dd2_id_arr[$j]) . "\n";
//echo '<br />book_dd2_id'.$i . '[]', $book_dd2_id_arr[$j];
	}
}
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_dd3_id_arr = $_POST['book_dd3_id' . $i];
	for ($j=0; $j<sizeof($book_dd3_id_arr); $j++) {
		echo zen_draw_hidden_field('book_dd3_id'.$i . '[]', $book_dd3_id_arr[$j]) . "\n";
//echo '<br />book_dd3_id'.$i . '[]', $book_dd3_id_arr[$j];
	}
}
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_dd4_id_arr = $_POST['book_dd4_id' . $i];
	for ($j=0; $j<sizeof($book_dd4_id_arr); $j++) {
		echo zen_draw_hidden_field('book_dd4_id'.$i . '[]', $book_dd4_id_arr[$j]) . "\n";
//echo '<br />book_dd4_id'.$i . '[]', $book_dd4_id_arr[$j];
	}
}
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_dd5_id_arr = $_POST['book_dd5_id' . $i];
	for ($j=0; $j<sizeof($book_dd5_id_arr); $j++) {
		echo zen_draw_hidden_field('book_dd5_id'.$i . '[]', $book_dd5_id_arr[$j]) . "\n";
//echo '<br />book_dd5_id'.$i . '[]', $book_dd5_id_arr[$j];
	}
}
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	$book_dd6_id_arr = $_POST['book_dd6_id' . $i];
	for ($j=0; $j<sizeof($book_dd6_id_arr); $j++) {
		echo zen_draw_hidden_field('book_dd6_id'.$i . '[]', $book_dd6_id_arr[$j]) . "\n";
//echo '<br />book_dd6_id'.$i . '[]', $book_dd6_id_arr[$j];
	}
}

//eof LANG mod


//===============ADD new single dropdowns jph
/*
for ($i=0; $i<sizeof($book_dd1_id); $i++) {
	echo zen_draw_hidden_field('book_dd1_id['.$i.']', htmlspecialchars(stripslashes($book_dd1_id[$i]))) . "\n";
}
for ($i=0; $i<sizeof($book_dd2_id); $i++) {
	echo zen_draw_hidden_field('book_dd2_id['.$i.']', htmlspecialchars(stripslashes($book_dd2_id[$i]))) . "\n";
}
for ($i=0; $i<sizeof($book_dd3_id); $i++) {
	echo zen_draw_hidden_field('book_dd3_id['.$i.']', htmlspecialchars(stripslashes($book_dd3_id[$i]))) . "\n";
}
for ($i=0; $i<sizeof($book_dd4_id); $i++) {
	echo zen_draw_hidden_field('book_dd4_id['.$i.']', htmlspecialchars(stripslashes($book_dd4_id[$i]))) . "\n";
}
for ($i=0; $i<sizeof($book_dd5_id); $i++) {
	echo zen_draw_hidden_field('book_dd5_id['.$i.']', htmlspecialchars(stripslashes($book_dd5_id[$i]))) . "\n";
}
for ($i=0; $i<sizeof($book_dd6_id); $i++) {
	echo zen_draw_hidden_field('book_dd6_id['.$i.']', htmlspecialchars(stripslashes($book_dd6_id[$i]))) . "\n";
}
*/
//=====================


      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        echo zen_draw_hidden_field('products_name[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_name[$languages[$i]['id']])));
        echo zen_draw_hidden_field('products_description[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_description[$languages[$i]['id']])));
        echo zen_draw_hidden_field('products_url[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_url[$languages[$i]['id']])));
      }
      echo zen_draw_hidden_field('products_image', stripslashes($products_image_name));
      echo ( (isset($_GET['search']) && !empty($_GET['search'])) ? zen_draw_hidden_field('search', $_GET['search']) : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? zen_draw_hidden_field('search', $_POST['search']) : '');

// BEGIN CEON URI MAPPING 4 of 4
      echo $ceon_uri_mapping_admin->productPreviewBuildHiddenFields();
// END CEON URI MAPPING 4 of 4

      echo zen_image_submit('button_back.gif', IMAGE_BACK, 'name="edit"') . '&nbsp;&nbsp;';

      if (isset($_GET['pID'])) {
        echo zen_image_submit('button_update.gif', IMAGE_UPDATE);
      } else {
        echo zen_image_submit('button_insert.gif', IMAGE_INSERT);
      }
     echo '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?>
        </td>
      </tr>
    </table></form>
<?php
    }
?>