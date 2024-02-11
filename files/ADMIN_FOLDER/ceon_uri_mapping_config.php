<?php
//steve css select disabled
/**
 * Ceon URI Mapping Configuration Utility HTML Output Page.
 *
 * Builds the main Zen Cart output HTML then instantiates and outputs the Config Utility's output.
 * 
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @copyright   Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_config.php 1027 2012-07-17 20:31:10Z conor $
 */

require('includes/application_top.php');

$languages = zen_get_languages();
$num_languages = count($languages);

/**
 * Load in the Ceon URI Mapping Config Utility class
 */
require_once(DIR_WS_CLASSES . 'class.CeonURIMappingConfigUtility.php');

$config_utility = new CeonURIMappingConfigUtility();

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<?php
if (is_file(DIR_WS_INCLUDES . 'admin_html_head.php')) {
  require DIR_WS_INCLUDES . 'admin_html_head.php';
} else {
?>
	<title><?php echo HEADING_TITLE; ?></title><!--steve edit-->
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=9">
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
	<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
	<script type="text/javascript" src="includes/menu.js"></script>
<?php } ?>
	<script type="text/javascript" src="includes/general.js"></script>
	<script type="text/javascript">
		<!--
		function init()
		{
			cssjsmenu('navbar');
			if (document.getElementById)
			{
				var kill = document.getElementById('hoverJS');
				kill.disabled = true;
			}
		}
		// -->
	</script>
	<style type="text/css">
	#ceon-uri-mapping-wrapper {
		margin: 0 0.8em 0 0.8em;
	}
	h1.CeonAdminPageHeading { margin: 0; padding-top: 1em; padding-bottom: 1.2em; }
	fieldset { padding: 0.8em 0.8em; }
	fieldset fieldset { margin-bottom: 1em; padding-bottom: 1.2em; }
	fieldset fieldset legend { font-size: 1.1em; }
	legend { font-weight: bold; font-size: 1.3em; }
	.SpacerSmall { clear: both; }
	
	fieldset.CeonPanel {
		display: none;
		background: #fff;
		border: 1px solid #296629;
	}
	fieldset.CeonPanel legend { background: #fff; padding: 0.1em 0.4em 1em 0.4em; }
	
	fieldset.CeonPanel fieldset legend {
		padding-bottom: 0.4em;
	}
	
	fieldset fieldset {
		margin-top: 1em;
		background: #f3f3f3;
		box-shadow: inset 0px 0px 8px 1px #e3e3e3;
	}
	fieldset.CeonPanel fieldset legend {
		background: transparent;
	}
	
	ul#ceon-panels-menu {
		list-style: none;
		margin: 1em 0 0 0;
		padding: 0 0 0.6em 0;
		background: #599659;
		white-space: nowrap;
	}
	ul#ceon-panels-menu li {
		display: inline;
		padding: 0;
		margin: 0;
	}
	ul#ceon-panels-menu li a {
		background: #79b679 url(<?php echo DIR_WS_IMAGES; ?>ceon-tab-background.png) bottom left repeat-x;
		color: #fff;
		padding: 0.4em 2em 0.8em 2em;
		font-weight: bold;
		margin: 0 0.4em 0 0;
		border-left: 1px solid #79b679;
		border-top: 1px solid #79b679;
	}
	ul#ceon-panels-menu li a:hover {
		background: #89c689;
		border-left: 1px solid #89c689;
		border-top: 1px solid #89c689;
	}
	ul#ceon-panels-menu li a:visited, ul#ceon-panels-menu li a:active,
	ul#ceon-panels-menu li a:focus {
		outline: none;
	}
	
	ul#ceon-panels-menu li.CeonPanelTabSelected {
		display: inline;
		padding: 0;
	}
	ul#ceon-panels-menu li.CeonPanelTabSelected a {
		background: #599659 url(<?php echo DIR_WS_IMAGES; ?>ceon-tab-background-selected.png) top left repeat-x;
		padding: 0.8em 2em;
		border-left: 1px solid #69a669;
		border-top: 1px solid #69a669;
	}
	ul#ceon-panels-menu li.CeonPanelTabSelected a:hover {
		text-decoration: none;
		background: #599659;
		border-left: 1px solid #69a669;
		border-top: 1px solid #69a669;
	}
	ul#ceon-panels-menu li.CeonPanelTabSelected a:visited,
	ul#ceon-panels-menu li.CeonPanelTabSelected a:active,
	ul#ceon-panels-menu li.CeonPanelTabSelected a:focus {
		outline: none;
	}
	
	#ceon-panels-wrapper {
		border: 1px solid #599659;
		background: #599659;
		padding: 1em;
		padding-top: 1.4em;
		margin-bottom: 0.8em;
	}
	
	.CeonFormItemLabel, .CeonFormItemField, .CeonFormItemDesc {
		vertical-align: top;
	}
	.CeonFormItemLabel {
		font-weight: bold;
		font-size: 1.1em;
		line-height: 1.3;
		padding-right: 1em;
	}
	.CeonFormItemLabel { width: 20%; }
	.CeonFormItemDesc p { margin-bottom: 0.5em; }
	.CeonFormItemField { padding-bottom: 1.3em; }
	
	h2 {
		font-size: 1.4em;
		margin: 0 0 0.4em 0;
	}
	
	p {
		margin: 0 0 0.8em 0;
		line-height: 1.3;
	}
	
	.ErrorIntro {
		margin: 0em 0 1.5em 0em;
		background: #f00;
		color: #fff;
		padding: 0.4em;
		font-weight: bold;
	}
	.FormError { font-weight: bold; color: #f00; }
	
	td.AutoManagedProductURIsURIParts {
		padding-top: 1em;
	}
	
	.DoubleSpaceAbove { margin-top: 2em; }
	
	.Collapse { display:  none; }
	
	input.Textfield, textarea {
		background: #fff url(<?php echo DIR_WS_IMAGES; ?>ceon-input-fade-tb-gray-white.png) repeat-x left top;
		border: 1px solid #ccc;
		color: #000;
		padding: 4px;
		font-size: 1.1em;
		line-height: 1;
	}
	input.Textfield:hover, input.Textfield:focus, textarea:hover, textarea:focus {
		border: 1px solid #999;
	}
	
	input.Textfield {
		margin-left: 5px;
	}
	
	textarea {
		line-height: 1.3;
	}
	#save { margin-right: 1em; }
	
	#footer {
		margin-top: 1.5em;
		border-top: 1px solid #000;
		padding-top: 1em;
		text-align: right;
		font-size: 0.9em;
		padding-bottom: 2em;
	}
	#footer img {
		border: none;
	}
	#ceon-button-logo {
		float: left;
		margin-right: 14px;
	}
	#footer p {
		margin: 0 0 0.8em 0;
	}
	#footer p#version-info {
		padding: 0;
		line-height: 1.3
	}
	#footer p.Error {
		font-size: 1.1em;
	}
	</style>
	<!--[if IE]>
	<style type="text/css">
	fieldset {
		position: relative;
		padding-top: 2.2em;
	}
	legend, fieldset.CeonPanel legend {
		position: absolute;
		top: -0.55em;
		left: 0.2em;
		padding: 0 0.4em 0 0.4em;
	}
	fieldset fieldset {
		margin-bottom: 1.8em;
	}
	</style>
	<![endif]-->
</head>
<body onload="init()">
<a name="top" id="top"></a>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="ceon-uri-mapping-wrapper">
<?php echo zen_draw_form('ceon-uri-mapping', FILENAME_CEON_URI_MAPPING_CONFIG, zen_get_all_get_params(), 'post',
	'onsubmit="" id="ceon-uri-mapping"', true);
echo zen_hide_session_id(); ?>
	<h1 class="pageHeading CeonAdminPageHeading"><?php echo HEADING_TITLE; ?></h1>

<?php

echo $config_utility->getOutput();

?>
</form>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>