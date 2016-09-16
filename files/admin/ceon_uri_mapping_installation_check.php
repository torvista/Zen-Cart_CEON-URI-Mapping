<?php

/**
 * Ceon URI Mapping Installation Check.
 *
 * Aids the installation of the module by trying to identify any installation issues and by
 * providing example RewriteRules to use.
 * 
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2012 Ceon
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: ceon_uri_mapping_installation_check.php 1027 2012-07-17 20:31:10Z conor $
 */

require('includes/application_top.php');

$languages = zen_get_languages();
$num_languages = sizeof($languages);

/**
 * Load in the Ceon URI Mapping Installation Check class
 */
require_once(DIR_WS_CLASSES . 'class.CeonURIMappingInstallationCheck.php');

$installation_check = new CeonURIMappingInstallationCheck();

$installation_check->performChecks();

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
	<title><?php echo HEADING_TITLE; ?></title><!--steve edit-->
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=9">
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
	<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
	<script type="text/javascript" src="includes/menu.js"></script>
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
	
	.DisplayNone { display: none; }
	
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
	
	#intro {
		margin-bottom: 2em;
	}
	
	h2 {
		font-size: 1.4em;
		margin: 0 0 0.4em 0;
	}
	
	h3 {
		font-size: 1.2em;
		margin: 1.4em 0 0.3em 0;
	}
	
	p {
		margin: 0 0 0.8em 0;
		line-height: 1.3;
	}
	
	.InstallationError {
		border: 1px solid #ff0000;
		padding: 0;
		margin-bottom: 1.5em;
	}
	
	.InstallationError p {
		margin: 0 0 0.8em 0;
		padding: 0 0.6em 0 0.6em;
	}
	
	.InstallationError ul li {
		margin: 0 0 0.5em 0;
	}
	
	h3.ErrorInitialDesc {
		font-size: 1.3em;
		margin: 0 0 0.5em 0;
		padding: 0.2em 0.4em 0.4em 0.4em;
		background: #ff6666;
		color: #fff;
	}
	
	h3.ErrorInitialDesc strong {
		text-decoration: underline;
	}
	
	p.ErrorCurrentValue {
		font-size: 1.15em;
		margin: 0 0 0.3em 0; 
	}
	
	ul.ErrorExtraDesc {
		margin: 1em 0 1em 0;
	}
	
	ul.ErrorInstructions {
		background: #eee;
		border: 1px solid #999;
		margin: 0.2em 0.6em 0.6em 0.6em;
		padding: 0em 1em 0.6em 2em;
	}
	
	ul.ErrorInstructions li {
		margin: 0.5em 0 0 0;
		line-height: 1.3;
	}
	
	.InstallationError code {
		margin: 0 1.5em;
	}
	
	textarea {
		background: #fff url(<?php echo DIR_WS_IMAGES; ?>ceon-input-fade-tb-gray-white.png) repeat-x left top;
		border: 1px solid #ccc;
		color: #000;
		padding: 4px;
		font-size: 1em;
		line-height: 1.3;
	}
	
	#example-rewrite-rule h2 {
		margin-top: 1.5em;
	}
	
	#example-rewrite-rule h3 {
		margin-top: 1em;
	}
	
	#nginx-example-intro {
		margin-top: 2em;
	}
	
	p.SelectAndCopy {
		margin-top: 1.2em;
	}
	
	.Collapse { display:  none; }
	
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
	#intro {
		margin-bottom: 2.8em;
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
	<h1 class="pageHeading CeonAdminPageHeading"><?php echo HEADING_TITLE; ?></h1>

<?php

echo $installation_check->getOutput();

?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>