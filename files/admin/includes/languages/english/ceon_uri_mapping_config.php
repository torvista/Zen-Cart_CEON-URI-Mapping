<?php

/** 
 * Ceon URI Mapping Configuration Admin Language Defines.
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

define('HEADING_TITLE', 'Ceon URI Mapping (SEO) Config');

define('SUCCESS_MODULE_INSTALLED', 'Module (version %s) was installed successfully.');
define('SUCCESS_DATABASE_AND_CONFIG_UPDATED', 'Database and configuration were successfully updated to version %s');
define('SUCCESS_DATABASE_AND_CONFIG_UP_TO_DATE', 'Database and configuration are up to date, using version %s');

if (!defined('TEXT_YES')) { define('TEXT_YES', 'Yes'); }
if (!defined('TEXT_NO')) { define('TEXT_NO', 'No'); }

define('TEXT_AUTOGEN_CONFIG', 'URI Auto-generation Settings');
define('TEXT_LABEL_AUTOGEN_ENABLED_NEW', 'Offer Auto-generation of URIs for Categories, Products, Manufacturers and EZ-Pages:');
define('TEXT_CONFIG_DESC_AUTOGEN_ENABLED_NEW', '<p>Should the option to auto-generate URIs be offered in the admin for Categories/Products/Manufacturers/EZ-Pages?</p><p>Generated URIs are based on their Category Names/Product Names/Manufacturer Names/EZ-Page Titles. For Products/Categories the full Category Path is used.</p>');

define('TEXT_LABEL_WHITESPACE_REPLACEMENT', 'Whitespace Replacement:');
define('TEXT_CONFIG_DESC_WHITESPACE_REPLACEMENT', '<p>What should whitespace be replaced with in auto-generated URIs?</p>');

define('TEXT_WHITESPACE_REPLACEMENT_SINGLE_UNDERSCORE', 'Single Underscore &quot;_&quot;');
define('TEXT_WHITESPACE_REPLACEMENT_SINGLE_DASH', 'Single Dash &quot;-&quot;');
define('TEXT_WHITESPACE_REPLACEMENT_SINGLE_FULL_STOP', 'Single Full Stop/Period &quot;.&quot;');
define('TEXT_WHITESPACE_REPLACEMENT_REMOVE', 'Remove whitespace altogether');

define('TEXT_LABEL_CAPITALISATION', 'Capitalisation:');
define('TEXT_CONFIG_DESC_CAPITALISATION', '<p>What capitalisation should be used in auto-generated URIs?</p><p>Please note:  Customers can use any capitalisation they want without breaking the store as all URIs are mapped case-insensitively. Using capitalisation can make the store URIs look &ldquo;prettier&rdquo; though!</p>');

define('TEXT_CAPITALISATION_LOWERCASE', 'Convert Category Names/Product Names/Manufacturer Names/EZ-Page Titles to lowercase (E.g. &ldquo;medalofhonor&rdquo;/&ldquo;medal-of-honor&rdquo;).');
define('TEXT_CAPITALISATION_AS_IS', 'Use the names/titles as they are (E.g. &ldquo;MedalofHonor&rdquo;/&ldquo;Medal-of-Honor&rdquo;).');
define('TEXT_CAPITALISATION_UCFIRST', 'Capitalise the first letter of each word in the names/titles (E.g. &ldquo;MedalOfHonor&rdquo;/&ldquo;Medal-Of-Honor&rdquo;).');

define('TEXT_LABEL_REMOVE_WORDS', 'Remove Words:');
define('TEXT_CONFIG_DESC_REMOVE_WORDS', '<p>This is a comma-separated list of words which will be removed from the auto-generated URIs. Single characters can also be entered but will only be removed if they are surrounded by spaces (e.g. &ldquo;-&rdquo; entered below will remove the dash from &ldquo;Star Wars - Director\'s Cut&rdquo; but not from &ldquo;Spider-Man&rdquo;).</p><p>For example, if the store would like to remove &ldquo;-&rdquo;, &ldquo;and&rdquo; and &ldquo;an&rdquo; from the URIs you\'d enter the following:</p><p style="margin-top: 0.2em;"><code>-, and, an</code></p>');

define('TEXT_LABEL_CHAR_STR_REPLACEMENTS', 'Character/String Replacements:');
define('TEXT_CONFIG_DESC_CHAR_STR_REPLACEMENTS', '<p>This is a comma-separated list of pairs of characters/strings and replacement characters/strings.</p> <p>The characters/strings to be replaced in the auto-generated URIs should be separated from their replacements using &ldquo;=&gt;&rdquo;. E.g. &ldquo;&pound;=&gt;GBP, $=&gt;USD&rdquo;.</p><p>Any spacing entered for a <strong>replacement</strong> will be used but replaced with the usual whitespace replacement character (so be careful not leave an extra space at the end of the text entered below if it is not intended!). Spacing entered for the strings/characters to be replaced will be removed/ignored; so it\'s fine to add a space after each comma, to make things easier to read.</p><p>To remove a character/string simply replace it with nothing. E.g. &ldquo;&pound;=&gt;, $=&gt;&rdquo; removes &pound; and $ dollar signs from the auto-generated URI.</p>');

define('TEXT_LABEL_MAPPING_CLASH_ACTION', 'Mapping Clash Action:');
define('TEXT_CONFIG_DESC_MAPPING_CLASH_ACTION', '<p>What action should be taken when a URI mapping being auto-generated clashes with an existing URI mapping?</p><p>(Please note the auto-append functionality <strong>only applies for product mappings</strong>; any clashes which occur with other page types will simply result in a warning being displayed and no changes being made).</p>');

define('TEXT_LABEL_LANGUAGE_CODE_ADD_ENABLED', 'Add Language identifier to URI:');
define('TEXT_CONFIG_DESC_LANGUAGE_CODE_ADD_ENABLED', '<p>Should the option to auto-generate URIs be offered in the admin for Categories/Products/Manufacturers/EZ-Pages with the language code (en, de, etc...)?</p><p>Generated URIs based on their Category Names/Product Names/Manufacturer Names/EZ-Page Titles can include the language code identifier. For Products/Categories the full Category Path is used with the language code before any other rewritten URI part.</p>');

define('TEXT_MAPPING_CLASH_ACTION_WARN', 'Warn User - The mapping should not be created and instead the user should be warned that the mapping could not be saved/auto-generated.');
define('TEXT_MAPPING_CLASH_ACTION_AUTO_APPEND', 'Auto-append a Number - A unique mapping should be automatically generated by appending a number (an integer) to the end of any <strong>product</strong> mapping that clashes. E.g. if the mapping <code>/books/life-is-good</code> already existed, an attempt would be made to use <code>/books/life-is-good1</code>, then <code>/books/life-is-good2</code> etc.');

define('TEXT_AUTO_MANAGED_PRODUCT_URIS', 'Auto-managed Product Page URIs');
define('TEXT_LABEL_AUTO_MANAGED_PRODUCT_URIS', 'Automatically Managed URIs for Pages Related to a Product:');
define('TEXT_CONFIG_DESC_AUTO_MANAGED_PRODUCT_URIS', '<p>When adding/updating a URI mapping for a product, mappings can also be automatically added/updated for pages related to the product page.</p> <p>For any page type enabled below, whenever a product URI is added/updated, the respective &ldquo;URI part&rdquo;, for the appropriate language, will be appended to the product\'s URI and saved as the URI mapping for that page.</p> <p>For example, if a new product is added with a URI mapping of &ldquo;<code>/books/life-is-good</code>&rdquo;, and auto-adding is enabled for the &ldquo;tell a friend&rdquo; page, with a URI part of &ldquo;<code>tell-a-friend</code>&rdquo;, a new URI mapping of &ldquo;<code>/books/life-is-good/tell-a-friend</code>&rdquo; will be added for that product\'s tell a friend page.</p> <p>Whenever a product\'s URI is changed, the page types enabled below will also have their URIs updated accordingly.</p>');
define('TEXT_INSTRUCTIONS_AUTO_MANAGED_PRODUCT_URIS', 'Tick the page types which should have their URIs auto-managed...');
define('TEXT_LABEL_AUTO_MANAGED_URI_REVIEWS', 'Products\' Reviews Pages');
define('TEXT_LABEL_AUTO_MANAGED_URI_REVIEW_INFO', 'Products\' Review Info Pages');
define('TEXT_LABEL_AUTO_MANAGED_URI_WRITE_A_REVIEW', 'Products\' Write A Review Pages');
define('TEXT_LABEL_AUTO_MANAGED_URI_TELL_A_FRIEND', 'Products\' Tell A Friend Pages');
define('TEXT_LABEL_AUTO_MANAGED_URI_ASK_A_QUESTION', 'Products\' Ask A Question Pages');
define('TEXT_LABEL_AUTO_MANAGED_PRODUCT_URIS_URI_PARTS', 'URI Parts Text:');
define('TEXT_CONFIG_DESC_AUTO_MANAGED_PRODUCT_URIS_URI_PARTS', '<p>Enter the text to be used for the URI parts to be appended to the product URIs when auto-managing the respective pages.</p><p>Whenever a URI mapping is being created using these URI parts, the standard auto-generation rules (Whitespace Replacement, Capitalisation etc.) will be applied, so these URI parts\' text can contain spaces and capital letters, which will then be adjusted if necessary when creating a mapping, according to the store\'s settings above.</p>');

define('TEXT_ERROR_URI_PART_MUST_BE_ENTERED', 'A URI part must be entered here.');

define('TEXT_VERSION_CHECKING', 'Version Checking');
define('TEXT_LABEL_AUTOMATIC_VERSION_CHECKING', 'Automatic Version Checking:');
define('TEXT_CONFIG_DESC_AUTOMATIC_VERSION_CHECKING', '<p>Use Automatic or Manual Version Checking?</p><p>With automatic version checking, the latest version information is displayed at the bottom right of this page. (The check only takes place once, when this Config Utility page is first loaded).</p><p>With manual checking, you must click the link at the bottom right of this page to find out what the latest version of the software is.</p>');
define('TEXT_AUTOMATIC_VERSION_CHECKING', 'Automatic Version Checking');
define('TEXT_MANUAL_VERSION_CHECKING', 'Manual Version Checking');

define('TEXT_INSTALLATION_CHECK', 'Installation Check');
define('TEXT_INSTALLATION_DESC', 'The installation check will examine the store\'s configuration files and warn you about any mistakes in the files which you need to correct, and will even provide full information about what you should do.</p><p>It will also check all the core files that must be modified for Ceon URI Mapping to work optimally, and warn you if any of the modifications are missing or out of date, again giving information on what you should do!');
define('TEXT_LINK_TO_INSTALLATION_CHECK', 'Click here to go to the Ceon URI Mapping Installation Check page.');
define('TEXT_CONFIG_CHECK', 'Configuration Check');
define('TEXT_CONFIG_DESC', 'If a problem occurred when installing or upgrading Ceon URI Mapping, the configuration can be checked and, hopefully, repaired automagically.');
define('TEXT_LINK_TO_CONFIG_CHECK', 'Click here to run the configuration checker.');

define('TEXT_ERROR_IN_CONFIG', 'A problem was found with the configuration. Please fix the error highlighted below, then try again to save the changes.');
define('TEXT_ERRORS_IN_CONFIG', '%s problems were found with the configuration. Please fix the errors highlighted below, then try again to save the changes.');

define('SUCCESS_CONFIGURATION_SAVED', 'Configuration was successfully saved!');
define('SUCCESS_CONFIGURATION_SAVED_DEMO', '[DEMO MODE] Configuration would have been saved if module was not in demo mode!');
