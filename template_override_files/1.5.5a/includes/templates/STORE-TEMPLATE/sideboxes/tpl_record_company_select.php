<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_record_company_select.php 15882 2010-04-11 16:37:54Z wilt $
 */
  $content = "";
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
  $content .= zen_draw_form('record_company_form', zen_href_link(FILENAME_DEFAULT, '', $request_type, false), 'get');
  $content .= zen_draw_hidden_field('main_page', FILENAME_DEFAULT) . zen_hide_session_id() . zen_draw_hidden_field('typefilter', 'record_company');
// BEGIN CEON URI MAPPING 1 of 2
  // Build the JavaScript necessary to have the record company selection directly redirect to the page for the
  // record company. This will result in the static URI for the record company being used if one is associated with
  // the record company page, or simply the default dynamic URI being used
  $content .= '<script type="text/javascript">' . "\n<!--\n";
  $content .= "record_company_sidebox_uri_mappings = new Array;\n\n";
  
  foreach ($record_company_array as $record_company_sidebox_info) {
    if (strlen($record_company_sidebox_info['id']) > 0) {
      $content .= "record_company_sidebox_uri_mappings[" . $record_company_sidebox_info['id'] . "] = '" .
        addslashes(str_replace('&amp;', '&',
        zen_href_link(FILENAME_DEFAULT, 'typefilter=record_company&record_company_id=' .
        $record_company_sidebox_info['id'], $request_type))) . "';\n";
    }
  }
  
  $content .= "function ceon_uri_mappingRecordCompanySideboxRedirect(el)\n{\n\t";
  $content .= "if (el.options[el.selectedIndex].value != '') {\n\t\t";
  $content .= "window.location = record_company_sidebox_uri_mappings[" .
    "el.options[el.selectedIndex].value];\n\t";
  $content .= "}\n";
  $content .= "}\n";
  $content .= "//-->\n</script>\n";
  
  $content .= zen_draw_pull_down_menu('record_company_id', $record_company_array,
    (isset($_GET['record_company_id']) ? $_GET['record_company_id'] : ''),
    'onchange="javascript:ceon_uri_mappingRecordCompanySideboxRedirect(this);" size="' . MAX_RECORD_COMPANY_LIST .
    '" style="width: 90%; margin: auto;"') . zen_hide_session_id();
/*
// END CEON URI MAPPING 1 of 2
  $content .= zen_draw_pull_down_menu('record_company_id', $record_company_array, (isset($_GET['record_company_id']) ? $_GET['record_company_id'] : ''), 'onchange="this.form.submit();" size="' . MAX_RECORD_COMPANY_LIST . '" style="width: 90%; margin: auto;"');
// BEGIN CEON URI MAPPING 2 of 2
*/
// END CEON URI MAPPING 2 of 2
  $content .= '</form>';
  $content .= '</div>';
?>