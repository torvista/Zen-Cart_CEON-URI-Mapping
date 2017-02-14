<?php

/** 
 * Ceon URI Mapping Installation Check Language Defines.
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

define('HEADING_TITLE', 'Ceon URI Mapping Installation Check');

define('TEXT_INSTALLATION_CHECK', 'Installation Check');

define('TEXT_NO_ERRORS_FOUND_TITLE', 'Congratulations! All the checks passed!');
define('TEXT_NO_ERRORS_FOUND', 'No problems were found with the settings in the store\'s configuration files, and all the modified core files have the correct modifications present!');
define('TEXT_NO_CHECKS_MADE_FOR_REWRITE_RULE', 'This installation check does not check the rewrite rule for the store though, so please read and apply the following if you have not already done so..');

define('TEXT_ERROR_FOUND_TITLE', 'Error Found!');
define('TEXT_ERRORS_FOUND_TITLE', 'Errors Found!');
define('TEXT_CONFIG_FILE_ERROR_SO_NO_REWRITE_RULE_OUTPUT', 'A problem was found the settings in the store\'s configuration files, so the example rewrite rule panel cannot yet be built.');
define('TEXT_CONFIG_FILE_ERRORS_SO_NO_REWRITE_RULE_OUTPUT', '%s problems were found the settings in the store\'s configuration files, so the example rewrite rule panel cannot yet be built.');
define('TEXT_PLEASE_FIX_CONFIG_FILE_ERROR', 'Please fix the error in the configuration files, as listed below...');
define('TEXT_PLEASE_FIX_CONFIG_FILE_ERRORS', 'Please fix the errors in the configuration files, as listed below...');

define('TEXT_MODIFIED_CORE_FILE_ERROR', 'A problem was found with one of the core files that needs to be modified for Ceon URI Mapping to work optimally.');
define('TEXT_MODIFIED_CORE_FILE_ERRORS', '%s problems were found with core files that need to be modified for Ceon URI Mapping to work optimally.');
define('TEXT_PLEASE_FIX_MODIFIED_CORE_FILE_ERROR', 'The error should be fixed as soon as possible. In the meantime though, since the store\'s configure files have been checked and found to be valid, information about the example rewrite rule for the store was able to built.');
define('TEXT_PLEASE_FIX_MODIFIED_CORE_FILE_ERRORS', 'The errors should be fixed as soon as possible. In the meantime though, since the store\'s configure files have been checked and found to be valid, information about the example rewrite rule for the store was able to built.');

define('TEXT_OLD_FILE_DIR_ERROR', 'A file/directory for an older version of Ceon URI Mapping was found. The attempt to delete it failed, so it must be manually deleted.');
define('TEXT_OLD_FILE_DIR_ERRORS', '%s files/directories for an older version of Ceon URI Mapping were found. The attempt to delete them failed, so they must be manually deleted.');
define('TEXT_PLEASE_FIX_OLD_FILE_DIR_ERROR', 'The file/directory should be deleted as soon as possible as otherwise a blank page may be displayed. In the meantime though, since the store\'s configure files have been checked and found to be valid, information about the example rewrite rule for the store was able to built.');
define('TEXT_PLEASE_FIX_OLD_FILE_DIR_ERRORS', 'The files/directories should be deleted as soon as possible as otherwise a blank page may be displayed. In the meantime though, since the store\'s configure files have been checked and found to be valid, information about the example rewrite rule for the store was able to built.');

define('TEXT_STORE_CONFIGURATION_FILES_CHECK', 'Store Configuration Files Check');
define('TEXT_MODIFIED_CORE_FILES_CHECK', 'Modified Core Files Check');
define('TEXT_MODIFIED_OLD_FILES_DIRS_CHECK', 'Old Version Files/Directories Check');

define('TEXT_ERROR_VALUE_FOR_ADMIN_SERVER_VARIABLE', 'The value for <code>%s</code> in the store\'s <strong>admin</strong> configure.php file is wrong.');
define('TEXT_ERROR_VALUE_FOR_SERVER_VARIABLE', 'The value for <code>%s</code> in the <strong>store side\'s</strong> configure.php file is wrong.');
define('TEXT_CURRENT_INCORRECT_VALUE_FOR_SERVER_VARIABLE', 'The current value for <code>%s</code> is &ldquo;%s&rdquo;.');

define('TEXT_HTTP_SERVER_VALUES_CANNOT_HAVE_A_SLASH_AT_END', 'The value for <code>%s</code> cannot have a slash at the end.');
define('TEXT_REMOVE_SLASH_FROM_END', 'You must update the value for <code>%s</code> to remove the slash from the end.');
define('TEXT_HTTP_SERVER_VALUES_CANNOT_USE_SUBDIRECTORY', 'The value for <code>%s</code> <strong>cannot</strong> include a subdirectory.');
define('TEXT_HTTP_SERVER_VALUE_MUST_BE', 'This value must be a domain name preceded by <code>http://</code>, with  <strong>no subdirectory after the domain name</strong> and <strong>no slash at the end</strong>.');
define('TEXT_HTTPS_SERVER_VALUE_MUST_BE', 'This value must be a domain name preceded by <code>https://</code>, with  <strong>no subdirectory after the domain name</strong> and <strong>no slash at the end</strong>.');
define('TEXT_HTTP_SERVER_FORMAT_EXAMPLE', 'The only valid formats are <code>http://store-domain.com</code> or <code>http://www.store-domain.com</code> etc.');
define('TEXT_HTTPS_SERVER_FORMAT_EXAMPLE', 'The only valid formats are <code>https://store-domain.com</code> or <code>https://www.store-domain.com</code> etc.');
define('TEXT_REMOVE_SUBDIRECTORY_FROM_END', 'The path to the subdirectory must be removed from after the domain name definition in <code>%s</code>.');
define('TEXT_SUBDIRECTORY_SETTINGS_INFO', 'If the store is in a subdirectory, then the name of the subdirectory should instead be placed in <code>DIR_WS_CATALOG</code>, preceded and followed by slashes, e.g. <code>/store/</code> - the subdirectory name <strong>cannot</strong> be part of the <code>%s</code> domain name setting.</p>');

define('TEXT_ERROR_VALUE_NOT_SPECIFIED_FOR_ADMIN_SERVER_VARIABLE', 'The value for <code>%s</code> has not been specified in the store\'s <strong>admin</strong> configure.php file.');
define('TEXT_ERROR_VALUE_NOT_SPECIFIED_FOR_SERVER_VARIABLE', 'The value for <code>%s</code> has not been specified in the <strong>store side\'s</strong> configure.php file.');
define('TEXT_SERVER_VALUE_REQUIRED', 'A value is required for this variable.');
define('TEXT_SET_VALUE_TO_SLASH', 'If the store is at the root of the site (not in a subdirectory), set the value for <code>%s</code> to <code>/</code>');
define('TEXT_SET_VALUE_TO_SUBDIRECTORY_NAME', 'If the store is in a subdirectory, set the value for <code>%s</code> to the name of the subdirectory, with a slash at the start and a slash at the end. For example: <code>/store/</code>');

define('TEXT_VALUE_MUST_START_AND_END_WITH_SLASH', 'The value for <code>%s</code> <strong>must</strong> start <strong>and</strong> end with a slash.');
define('TEXT_VALUE_MUST_START_WITH_SLASH', 'The value for <code>%s</code> <strong>must</strong> start with a slash.');
define('TEXT_VALUE_MUST_END_WITH_SLASH', 'The value for <code>%s</code> <strong>must</strong> end with a slash.');
define('TEXT_ADD_A_STARTING_SLASH', 'Add a slash, <code>/</code>, to the <strong>start</strong> of <code>%s</code>.');
define('TEXT_ADD_AN_ENDING_SLASH', 'Add a slash, <code>/</code>, to the <strong>end</strong> of <code>%s</code>.');

define('TEXT_ERROR_ADMIN_HTTP_MUST_MATCH_HTTPS', 'The value for <code>%s</code> does not match the value for <code>%s</code> in the store\'s <strong>admin</strong> configure.php file.');
define('TEXT_ERROR_HTTP_MUST_MATCH_HTTPS', 'The value for <code>%s</code> does not match the value for <code>%s</code> in the <strong>store side\'s</strong> configure.php file.');
define('TEXT_WHEN_USING_STATIC_URIS_HTTP_MUST_MATCH_HTTPS', 'As Ceon URI Mapping uses static URIs, it is not possible for the store to use different paths for the base of the store\'s URIs. For example, different subdirectories cannot be used.');
define('TEXT_SHARED_SSL_CANNOT_BE_USED', 'This means a shared SSL certificate - for example, of the format <code>http://shared-ssl-domain.com/store-username</code> - cannot be used for the site once static URIs are being used.');
define('TEXT_CHANGE_HTTP_TO_MATCH_HTTPS', 'You must change the value for <code>%s</code> to match that of <code>%s</code>.');
define('TEXT_MAY_MEAN_PURCHASING_SSL_CERTIFICATE', 'To be able to make this change, and still have the site work correctly, may require purchasing and installing an SSL certificate for the store\'s domain.');

define('TEXT_ERROR_UNABLE_TO_OPEN_STORE_CONFIGURE_FILE', 'The store side\'s configure.php file could not be opened so that its values could be checked.');
define('TEXT_PATH_TO_STORE_CONFIGURE_FILE', 'The path to the store side\'s configure.php file was being worked out as %s');
define('TEXT_CHECK_PATH_TO_STORE_CONFIGURE_FILE', 'Check that this path is correct and that the correct permissions are set.');


/**
 * Error messages, info messages and instructions for core file modifications
 */
if (!defined('TEXT_OR')) {
	define('TEXT_OR', 'or');
}

define('TEXT_THE_PATH_TO_THE_FILE_IS', 'The path to the file is <code>%s</code>');

define('TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REMOVE_MODIFICATIONS', 'Alternatively, if the file was modified only for Ceon URI Mapping and not for any other module, back up the file (e.g. copy it to your local computer) and replace it with a &ldquo;fresh&rdquo; version of the file from a &ldquo;fresh&rdquo; set of Zen Cart files.');
define('TEXT_ALTERNATIVELY_REPLACE_FILE_TO_REAPPLY_MODIFICATIONS', 'Alternatively, if the file was modified only for Ceon URI Mapping and not for any other module, back up the file (e.g. copy it to your local computer), and replace it with the pre-modified sample file from the distribution, for the version of Zen Cart being used by the store.');
define('TEXT_ALTERNATIVELY_REPLACE_FILE_TO_APPLY_MODIFICATIONS', 'Alternatively, if the file has not been modified for the store yet, back up the file (e.g. copy it to your local computer), and replace it with the pre-modified sample file from the distribution, for the version of Zen Cart being used by the store.');

/**
 * Error messages for file that is missing
 */
define('TEXT_ERROR_FILE_MUST_BE_MODIFIED_BUT_DOES_NOT_EXIST', 'A file must have a modification made to it, to work with Ceon URI Mapping, but the file does not exist at all!');
define('TEXT_ERROR_FILE_MUST_HAVE_MULTIPLE_MODS_BUT_DOES_NOT_EXIST', 'A file must have modifications made to it, to work with Ceon URI Mapping, but the file does not exist at all!');

define('TEXT_ADD_MISSING_FILE_WITH_MODIFICATIONS', 'Add the missing file and make sure you apply the modifications required for Ceon URI Mapping.');

/**
 * Error messages for files that no longer have modifications
 */
define('TEXT_ERROR_MODS_NO_LONGER_ONE_OLD_REMAINS', 'A file has had a modification made to it, for an older version of Ceon URI Mapping, but this modification is no longer needed.');
define('TEXT_ERROR_MODS_NO_LONGER_X_REMAIN', 'A file has had <em>%s</em> modifications made to it, for an older version of Ceon URI Mapping, but the modifications are no longer needed.');
define('TEXT_ERROR_MODS_NO_LONGER_COMMENTS_REMAIN', 'A file has had modifications made to it, for an older version of Ceon URI Mapping, but the modifications are no longer needed.');
define('TEXT_ERROR_MODS_NO_LONGER_COMMENT_REMAINS', 'A file has had modifications made to it, for an older version of Ceon URI Mapping, but the modifications are no longer needed.');

define('TEXT_FILE_MODIFICATION_NO_LONGER_REQUIRED', 'Although the file had to be modified for a previous version of the module, this version works differently and the file is <strong>not</strong> modified for this version.');

define('TEXT_REMOVE_THE_MODIFICATION', 'Remove the modification from the file by editing the file and removing the block beginning with <code>// BEGIN CEON URI MAPPING 1 of 1</code> and ending with <code>// END CEON URI MAPPING 1 of 1</code> - <strong>including</strong> those two &ldquo;marker comments&rdquo;.');
define('TEXT_REMOVE_THE_MODIFICATIONS', 'Remove the modifications from the file by editing the file and removing the blocks beginning with <code>// BEGIN CEON URI MAPPING x of y</code> and ending with <code>// END CEON URI MAPPING x of y</code>  (where x is the number of the modification and y is the total number of modifications in the file).');
define('TEXT_REMOVE_THE_MODIFICATIONS_2', 'Make sure to <strong>also</strong> remove the two &ldquo;marker comments&rdquo; for each modification.');

define('TEXT_MARKER_COMMENT_FOUND', 'Although this check hasn\'t identified old Ceon URI Mapping code in the file, it has identified that a marker comment remains in the file.');
define('TEXT_MARKER_COMMENTS_FOUND', 'Although this check hasn\'t identified old Ceon URI Mapping code in the file, it has identified that %s marker comments remain in the file.');
define('TEXT_MARKER_COMMENTS_FOUND_2', 'This could mean that the modification(s) weren\'t removed properly and that old code remains.');

define('TEXT_CHECK_MODIFICATIONS_REMOVED_PROPERLY_1_COMMENT', 'It would be a good idea to recheck the file to make sure all previous traces of the modifications have been removed, including the marker comment this check has found.');
define('TEXT_CHECK_MODIFICATIONS_REMOVED_PROPERLY_X_COMMENTS', 'It would be a good idea to recheck the file to make sure all previous traces of the modifications have been removed, including the marker comments this check has found.');
define('TEXT_MARKER_COMMENTS_INFO', 'Marker comments look like <code>// BEGIN CEON URI MAPPING x of y</code> or <code>// END CEON URI MAPPING x of y</code>  (where x is the number of the modification and y is the total number of modifications in the file).');

/**
 * Error messages for files that are missing all of their new modifications
 */
define('TEXT_ERROR_OLD_MOD_PRESENT', 'A file requires a modification to be made for it but the modification made to it is for an older version of Ceon URI Mapping.');
define('TEXT_ERROR_X_OLD_MODS_PRESENT', 'A file requires %s modifications to be made for it but the modifications made to it are for an older version of Ceon URI Mapping.');
define('TEXT_ERROR_MOD_MISSING_OLD_MODS_PRESENT', 'A file requires a modification to be made for it but the modification has not yet been made. The file does however have old modifications, made to it for an older version of Ceon URI Mapping.');
define('TEXT_ERROR_ALL_X_MODS_MISSING_OLD_MOD_REMAINS', 'A file requires %s modifications to be made for it but they have not yet been made. The file has an old modification in it, made to it for an older version of Ceon URI Mapping.');
define('TEXT_ERROR_ALL_X_MODS_MISSING_X_OLD_MODS_REMAIN', 'A file requires %s modifications to be made for it but they have not yet been made. The file has %s old modifications in it, made to it for an older version of Ceon URI Mapping.');
define('TEXT_ERROR_MOD_MISSING_X_COMMENTS_PRESENT', 'A file requires a modification to be made for it but the modification has not yet been made. The file does however have %s comment marker(s) in it.');
define('TEXT_ERROR_ALL_X_MODS_MISSING_X_COMMENTS_PRESENT', 'A file requires %s modifications to be made for it but the modifications have not yet been made. The file does however have %s comment marker(s) in it.');
define('TEXT_ERROR_MOD_MISSING', 'A file requires a modification to be made for it but the modification has not been made.');
define('TEXT_ERROR_X_MODS_MISSING', 'A file requires %s modifications to be made for it but none of the modifications have been made.');

define('TEXT_FILE_MODIFICATION_NEEDS_UPDATING', 'The file appears to have been modified for an older version of Ceon URI Mapping but the modification has not been updated for this version of the software.');
define('TEXT_FILE_MODIFICATIONS_NEED_UPDATING', 'The file appears to have been modified for an older version of Ceon URI Mapping but the modifications have not been updated for this version of the software.');
define('TEXT_FILE_MODIFICATION_NEEDS_APPLYING_OLD_REMOVING', 'The file appears to have been modified for an older version of Ceon URI Mapping but the modification for this version of the software has not been applied.');
define('TEXT_FILE_HAS_BEEN_MODIFIED_NEW_MODS_NEED_APPLYING', 'The file appears to have been modified for an older version of Ceon URI Mapping but the modifications for this version of the software have not been applied.');
define('TEXT_FILE_HAS_BEEN_MODIFIED_NEW_MODS_NEED_APPLYING', 'The file appears to have been modified for an older version of Ceon URI Mapping but the modifications for this version of the software have not been applied.');
define('TEXT_FILE_HAS_BEEN_MODIFIED_SOMEHOW_MOD_MISSING', 'The file appears to have been modified in some way for Ceon URI Mapping, but the required modification could not be identified, it does not appear to have been applied correctly.');
define('TEXT_FILE_HAS_BEEN_MODIFIED_SOMEHOW_MODS_MISSING', 'The file appears to have been modified in some way for Ceon URI Mapping, but the required modifications could not be identified, they don\'t appear to have been applied correctly.');

define('TEXT_UPDATE_THE_MODIFICATION', 'Update the modification by removing the old modification and applying the new modification.');
define('TEXT_UPDATE_THE_MODIFICATIONS', 'Update the modifications by removing the %s old modifications and applying the %s new modifications.');
define('TEXT_REMOVE_X_OLD_MODS_APPLY_NEW', 'Remove <strong>all</strong> the old modifications (there are %s), and then apply the new modification (there is only one in this version).');
define('TEXT_REMOVE_OLD_MOD_APPLY_X_NEW', 'Remove the old modification (there is just one), and then apply the %s new modifications.');
define('TEXT_REMOVE_X_OLD_MODS_APPLY_X_NEW', 'Remove <strong>all</strong> the old modifications (there are %s), and then apply the %s new modifications.');
define('TEXT_CHECK_FILE_APPLY_MOD', 'Check the file and remove any old pieces of code or code that wasn\'t fully applied. Then apply the modification required for this version of the software.');
define('TEXT_CHECK_FILE_APPLY_MODS', 'Check the file and remove any old pieces of code or code that wasn\'t fully applied. Then apply the %s modifications required for this version of the software.');
define('TEXT_APPLY_MOD', 'Apply the modification required for Ceon URI Mapping.');
define('TEXT_APPLY_X_MODS', 'Apply the %s modifications required for Ceon URI Mapping.');

/**
 * Error messages for files that have one new modification applied but one is missing
 */
define('TEXT_ERROR_SECOND_MOD_PRESENT_FIRST_MISSING_OLD_MOD_PRESENT', 'A file requires 2 modifications to be made for it, the <strong>second</strong> of which has been made, but the <strong>first</strong> modification is missing and a modification for an older version of Ceon URI Mapping has been found.');
define('TEXT_ERROR_FIRST_MOD_PRESENT_SECOND_MISSING_OLD_MOD_PRESENT', 'A file requires 2 modifications to be made for it, the <strong>first</strong> of which has been made, but the <strong>second</strong> modification is missing and a modification for an older version of Ceon URI Mapping has been found.');
define('TEXT_ERROR_SECOND_MOD_PRESENT_FIRST_MISSING_X_OLD_MODS_PRESENT', 'A file requires 2 modifications to be made for it, the <strong>second</strong> of which has been made, but the <strong>first</strong> modification is missing and %s modifications for an older version of Ceon URI Mapping have been found.');
define('TEXT_ERROR_FIRST_MOD_PRESENT_SECOND_MISSING_X_OLD_MODS_PRESENT', 'A file requires 2 modifications to be made for it, the <strong>first</strong> of which has been made, but the <strong>second</strong> modification is missing and %s modifications for an older version of Ceon URI Mapping have been found.');
define('TEXT_ERROR_SECOND_MOD_PRESENT_FIRST_MISSING', 'A file requires 2 modifications to be made for it, the <strong>second</strong> of which has been made, but the <strong>first</strong> modification is missing.');
define('TEXT_ERROR_FIRST_MOD_PRESENT_SECOND_MISSING', 'A file requires 2 modifications to be made for it, the <strong>first</strong> of which has been made, but the <strong>second</strong> modification is missing.');

define('TEXT_COULD_BE_THAT_FIRST_MISSED_OLD_REMAINS', 'It could be that modification number 2 for this version of the software was made, and that an old modification, for an older version of Ceon URI Mapping, was left in by mistake, <strong>instead</strong> of updating modification number 1.');
define('TEXT_COULD_BE_THAT_SECOND_MISSED_OLD_REMAINS', 'It could be that modification number 1 for this version of the software was made, and that an old modification, for an older version of Ceon URI Mapping, was left in by mistake, <strong>instead</strong> of updating modification number 2.');
define('TEXT_COULD_BE_THAT_FIRST_MOD_MISSED_EXTRA_OLD_REMAINS', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification number 2 was applied correctly, modification number 1 was missed out, and an additional, old modification, for an older version of Ceon URI Mapping, was <strong>also</strong> left in by mistake.');
define('TEXT_COULD_BE_THAT_SECOND_MOD_MISSED_EXTRA_OLD_REMAINS', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification number 1 was applied correctly, modification number 2 was missed out, and an additional, old modification, for an older version of Ceon URI Mapping, was <strong>also</strong> left in by mistake.');

define('TEXT_COULD_BE_THAT_FIRST_MISSED_X_OLD_REMAIN', 'It could be that modification number 2 for this version of the software was made and that %s old modifications, for an older version of Ceon URI Mapping, were left in by mistake, <strong>instead</strong> of updating modification number 1.');
define('TEXT_COULD_BE_THAT_SECOND_MISSED_X_OLD_REMAIN', 'It could be that modification number 1 for this version of the software was made and that %s old modifications, for an older version of Ceon URI Mapping, were left in by mistake, <strong>instead</strong> of updating modification number 2.');
define('TEXT_COULD_BE_THAT_FIRST_MOD_MISSED_X_EXTRA_OLD_REMAIN', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification number 2 was applied correctly, modification number 1 was missed out, and %s additional, old modifications, for an older version of Ceon URI Mapping, were <strong>also</strong> left in by mistake.');
define('TEXT_COULD_BE_THAT_SECOND_MOD_MISSED_X_EXTRA_OLD_REMAIN', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification number 1 was applied correctly, modification number 2 was missed out, and %s additional, old modifications, for an older version of Ceon URI Mapping, were <strong>also</strong> left in by mistake.');

define('TEXT_CHECK_FILE_APPLY_MISSING_MOD_X', 'Check the file and remove any old pieces of code. Then apply modification number %s, which was missed out, but which is required for this version of the software.');
define('TEXT_APPLY_MISSING_MOD_X', 'Apply modification number %s, which was missed out, but which is required for this version of the software.');

/**
 * Error messages for files that have several new modifications applied but are missing one
 */
define('TEXT_ERROR_X_MODS_PRESENT_MOD_X_MISSING_OLD_MOD_PRESENT', 'A file requires %s modifications to be made for it, modification numbers <strong>%s</strong> have been made, but modification number <strong>%s</strong> is missing and a modification for an older version of Ceon URI Mapping has been found.');
define('TEXT_ERROR_X_MODS_PRESENT_MOD_X_MISSING_X_OLD_MODS_PRESENT', 'A file requires %s modifications to be made for it, modification numbers <strong>%s</strong> have been made, but modification number <strong>%s</strong> is missing and %s modifications for an older version of Ceon URI Mapping have been found.');
define('TEXT_ERROR_X_MODS_PRESENT_MOD_X_MISSING', 'A file requires %s modifications to be made for it, modification numbers <strong>%s</strong> have been made, but modification number <strong>%s</strong> is missing.');

define('TEXT_COULD_BE_THAT_MOD_X_MISSED_OLD_REMAINS', 'It could be that modification numbers %s for this version of the software were made, and that an old modification, for an older version of Ceon URI Mapping, was left in by mistake, <strong>instead</strong> of updating modification number %s.');
define('TEXT_COULD_BE_THAT_X_MODS_MADE_X_OLD_REMAIN_MOD_X_MISSED', 'It could be that modification numbers %s for this version of the software were made and that %s old modifications, for an older version of Ceon URI Mapping, were left in by mistake, <strong>instead</strong> of updating modification number %s.');
define('TEXT_COULD_BE_THAT_MOD_X_MISSED_EXTRA_OLD_REMAINS', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification numbers %s were applied correctly, modification number %s was missed out, and an additional, old modification, for an older version of Ceon URI Mapping, was <strong>also</strong> left in by mistake.');
define('TEXT_COULD_BE_THAT_X_MODS_MADE_MOD_MISSED_X_EXTRA_OLD_REMAIN', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification numbers %s were applied correctly, modification number %s was missed out, and %s additional, old modifications, for an older version of Ceon URI Mapping, were <strong>also</strong> left in by mistake.');

/**
 * Error messages for files that have one new modification applied but are missing several
 */
define('TEXT_ERROR_MOD_X_PRESENT_X_MODS_MISSING_OLD_MOD_PRESENT', 'A file requires %s modifications to be made for it, modification number <strong>%s</strong> has been made, but modification numbers <strong>%s</strong> are missing and a modification for an older version of Ceon URI Mapping has been found.');
define('TEXT_ERROR_MOD_X_PRESENT_X_MODS_MISSING_X_OLD_MODS_PRESENT', 'A file requires %s modifications to be made for it, modification number <strong>%s</strong> has been made, but modification numbers <strong>%s</strong> are missing and %s modifications for an older version of Ceon URI Mapping have been found.');
define('TEXT_ERROR_MOD_X_PRESENT_X_MODS_MISSING', 'A file requires %s modifications to be made for it, modification number <strong>%s</strong> has been made, but modification numbers <strong>%s</strong> are missing.');

define('TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_OLD_REMAINS', 'It could be that modification number %s for this version of the software was made, but somehow modification numbers %s were missed, and that an old modification, for an older version of Ceon URI Mapping, was left in by mistake, <strong>instead</strong> of updating one of modification numbers %s.');
define('TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_X_OLD_REMAIN', 'It could be that modification number %s for this version of the software was made, but somehow modification numbers %s were missed, and that %s old modifications, for an older version of Ceon URI Mapping, were left in by mistake, <strong>instead</strong> of updating the equivalent modification (modification number %s).');
define('TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_EXTRA_OLD_REMAINS', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification number %s was applied correctly, modification numbers %s were missed out, and an additional, old modification, for an older version of Ceon URI Mapping, was <strong>also</strong> left in by mistake.');
define('TEXT_COULD_BE_THAT_MOD_X_MADE_MODS_X_MISSED_X_EXTRA_OLD_REMAIN', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification number %s was applied correctly, modification numbers %s were missed out, and %s additional, old modifications, for an older version of Ceon URI Mapping, were <strong>also</strong> left in by mistake.');

define('TEXT_CHECK_FILE_APPLY_MISSING_MODS_X', 'Check the file and remove any old pieces of code. Then apply modification numbers %s, which were missed out, but which are required for this version of the software.');
define('TEXT_APPLY_MISSING_MODS_X', 'Apply modification numbers %s, which were missed out, but which are required for this version of the software.');

/**
 * Error messages for files that have one several modification applied but are still missing several
 */
define('TEXT_ERROR_MODS_X_PRESENT_MODS_X_MISSING_OLD_MOD_PRESENT', 'A file requires %s modifications to be made for it, modification numbers <strong>%s</strong> have been made, but modification numbers <strong>%s</strong> are missing and a modification for an older version of Ceon URI Mapping has been found.');
define('TEXT_ERROR_MODS_X_PRESENT_MODS_X_MISSING_X_OLD_MODS_PRESENT', 'A file requires %s modifications to be made for it, modification numbers <strong>%s</strong> have been made, but modification numbers <strong>%s</strong> are missing and %s modifications for an older version of Ceon URI Mapping have been found.');
define('TEXT_ERROR_X_MODS_PRESENT_X_MODS_MISSING', 'A file requires %s modifications to be made for it, modification numbers <strong>%s</strong> have been made, but modification numbers <strong>%s</strong> are missing.');

define('TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_OLD_REMAINS', 'It could be that modification numbers %s for this version of the software were made, but somehow modification numbers %s were missed, and that an old modification, for an older version of Ceon URI Mapping, was left in by mistake, <strong>instead</strong> of updating one of modification numbers %s.');
define('TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_SAME_X_OLD_REMAIN', 'It could be that modification numbers %s for this version of the software were made, but somehow modification numbers %s were missed, and that %s old modifications, for an older version of Ceon URI Mapping, were left in by mistake, <strong>instead</strong> of updating modification numbers %s.');
define('TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_X_OLD_REMAIN', 'It could be that modification numbers %s for this version of the software were made, but somehow modification numbers %s were missed, and that %s old modifications, for an older version of Ceon URI Mapping, were left in by mistake, instead of updating the appropriate modifications and/or being removed altogether.');
define('TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_EXTRA_OLD_REMAINS', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification numbers %s were applied correctly, modification numbers %s were missed out, and an additional, old modification, for an older version of Ceon URI Mapping, was <strong>also</strong> left in by mistake');
define('TEXT_COULD_BE_THAT_MODS_X_MADE_MODS_X_MISSED_EXTRA_X_OLD_REMAIN', 'Or it could be that this version of the software has different modifications from the previous version and that, although modification numbers %s were applied correctly, modification numbers %s were missed out, and %s additional, old modifications, for an older version of Ceon URI Mapping, were <strong>also</strong> left in by mistake');

define('TEXT_ERROR_MOD_PRESENT_OLD_MOD_REMAINS', 'A file requires a modification to be made for it, which has been made successfully, but an old modification, for an older version of Ceon URI Mapping, still remains.');
define('TEXT_ERROR_MOD_PRESENT_X_OLD_MODS_REMAIN', 'A file requires a modification to be made for it, which has been made successfully, but %s old modifications, for an older version of Ceon URI Mapping, still remain.');
define('TEXT_ERROR_X_MODS_PRESENT_OLD_MOD_REMAINS', 'A file requires %s modification to be made for it, which have been made successfully, but an old modification, for an older version of Ceon URI Mapping, still remains.');
define('TEXT_ERROR_X_MODS_PRESENT_X_OLD_MODS_REMAIN', 'A file requires %s modification to be made for it, which have been made successfully, but %s old modifications, for an older version of Ceon URI Mapping, still remain.');

define('TEXT_REMOVE_OLD_MOD', 'Check the file and remove any old pieces of code. ');


/**
 * Error messages, info messages and instructions for old files/dirs
 */
define('TEXT_THE_PATH_TO_THE_DIR_IS', 'The path to the directory is <code>%s</code>');

define('TEXT_ERROR_OLD_FILE_REMAINS', 'A file for an older version of Ceon URI Mapping was found. The attempt to delete it failed, so it must be manually deleted.');
define('TEXT_ERROR_OLD_DIR_REMAINS', 'A directory for an older version of Ceon URI Mapping was found. The attempt to delete it failed, so it must be manually deleted.');

define('TEXT_DELETE_FILE', 'Delete the file to prevent it causing any problems.');
define('TEXT_DELETE_DIR', 'Delete the directory to prevent its contents causing any problems.');


/**
 * Defines for the example rewrite rule functionality
 */
define('TEXT_EXAMPLE_REWRITE_RULE', 'Example Rewrite Rule');

define('TEXT_EXAMPLE_REWRITE_RULE_BUILT_INTRO', 'The installation check has analysed the store\'s directories and built the following example rewrite rule for the store.');
define('TEXT_EXAMPLE_REWRITE_RULE_GOOD_BASIS', 'It is likely to be a good basis for the rewrite rule to be used for the store, but it is up to <strong>you</strong> to adapt it as necessary. You can read up on rewrite rules on the internet if needs be, please don\'t contact Ceon for help with customising this rule, unless you\'re willing to pay for the support.');

define('TEXT_APACHE_TITLE', 'Apache Webserver');
define('TEXT_SERVER_APPEARS_TO_BE_APACHE', 'The server appears to be using the Apache webserver.');
define('TEXT_PLACE_REWRITE_RULE_IN_HTACCESS_FILE_OR_VIRTUALHOST_DIRECTIVE', 'The rule below should be placed in a .htaccess file or the VirtualHost Directive for the site (although in this case the rule will have to modified slightly).');
define('TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_TITLE', 'If using a .htaccess file');
define('TEXT_ERROR_DIR_FS_CATALOG_PROBLEM', 'The path to the .htaccess file can\'t be constructed as there appears to be a problem with the paths in the store\'s configure.php file. The catalog folder, <code>%s</code>, is not the end part of the path to the store: <code>%s</code>.');
define('TEXT_ERROR_DIR_FS_CATALOG_PROBLEM_POSSIBLE_REASON', 'The values for <code>DIR_WS_CATALOG</code> and <code>DIR_FS_CATALOG</code> should be checked and fixed.');
define('TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO', 'If placing the rule in a .htaccess file, the .htaccess file into which it should be placed is the <strong>site root\'s .htaccess file</strong>, which should be created if it doesn\'t exist:');
define('TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO_EXISTS', 'If placing the rule in a .htaccess file, the .htaccess file into which it should be placed is the <strong>site root\'s .htaccess file</strong>, which is:');
define('TEXT_PLACE_REWRITE_RULE_HTACCESS_FILE_INFO_SUBDIR', 'Although the store is in a subdirectory, the .htaccess file into which the example rewrite rule should be placed is <strong>NOT</strong> a .htaccess file within the Zen Cart folder, the rewrite rule below is for the <strong>site root\'s .htaccess file</strong> - place it in the above file!');

define('TEXT_PLACE_REWRITE_RULE_VIRTUALHOST_DIRECTIVE_TITLE', 'If using an Apache VirtualHost Directive');
define('TEXT_PLACE_REWRITE_RULE_VIRTUALHOST_DIRECTIVE_INFO', 'If the store runs on a server using Apache and it is possible to modify the VirtualHost Directive for the store, then the rule can be modified and entered into the VirtualHost Directive for the store.');
define('TEXT_PLACE_REWRITE_RULE_VIRTUALHOST_DIRECTIVE_INSTRUCTIONS', 'Simply add a slash to the front of <code>%s</code>, to make the rule compatible with the VirtualHost Directive: <br/><br /><code>%s</code>');

define('TEXT_IIS_ISAPI_REWRITE_TITLE', 'IIS Server with IIS ISAPI_Rewrite');
define('TEXT_SERVER_APPEARS_TO_BE_IIS_ISAPI_REWRITE', 'The server appears to be using the Micro$oft IIS webserver with the ISAPI_Rewrite module.');
define('TEXT_PLACE_REWRITE_RULE_ISAPI_REWRITE_LITE_VERSION_TITLE', 'If using the &ldquo;Lite&rdquo; version of ISAPI_Rewrite');
define('TEXT_PLACE_REWRITE_RULE_IN_ISAPI_REWRITE_GLOBAL_HTTPD_CONF_INFO', 'The Lite version of ISAPI_Rewrite only allows rewrite rules to be placed in a global file called <code>httpd.conf</code>, which should be in the ISAPI_Rewrite installation folder. Place the example rewrite rule in that file.');
define('TEXT_PLACE_REWRITE_RULE_ISAPI_REWRITE_FULL_VERSION_DIRECTIVE_TITLE', 'If using the &ldquo;Full&rdquo; version of ISAPI_Rewrite');
define('TEXT_PLACE_REWRITE_RULE_IN_ISAPI_REWRITE_LOCAL_HTACCESS_INFO', 'The full version of ISAPI_Rewrite allows rewrite rules to be placed in a file called <code>.htaccess</code>, which is placed in the <strong>site root\'s folder</strong>, and which should be created if it doesn\'t yet exist:');
define('TTEXT_PLACE_REWRITE_RULE_IN_ISAPI_REWRITE_LOCAL_HTACCESS_INFO_EXISTS', 'The full version of ISAPI_Rewrite allows rewrite rules to be placed in a file called <code>.htaccess</code>, which is placed in the <strong>site root\'s folder</strong>:');

define('TEXT_IIS_URL_REWRITE_TITLE', 'IIS Server with IIS URL Rewrite');
define('TEXT_SERVER_APPEARS_TO_BE_IIS_URL_REWRITE', 'The server appears to be using the Micro$oft IIS webserver with the IIS URL Rewrite module.');
define('TEXT_IMPORT_REWRITE_RULE_IIS_URL_REWRITE_TITLE', 'Import the rewrite rule');
define('TEXT_IMPORT_REWRITE_RULE_IIS_URL_REWRITE_INFO', 'On servers using IIS URL Rewrite, the rule below can be imported through its administration interface.');
define('TEXT_IMPORT_REWRITE_RULE_IIS_URL_REWRITE_HINTS', 'In IIS Manager\'s &ldquo;Features View&rdquo; for a domain, look for &ldquo;URL Rewrite&rdquo;. Then in the &ldquo;Actions&rdquo; pane, click &ldquo;Import Rules&rdquo;. The rule below can then be copied and pasted into the text area.');

define('TEXT_NGINX_TITLE', 'nginx');
define('TEXT_SERVER_APPEARS_TO_BE_NGINX', 'The server appears to be using the nginx webserver.');
define('TEXT_PLACE_REWRITE_DIRECTIVE_NGINX_TITLE', 'Add directive to main nginx.conf or virtual host file');
define('TEXT_PLACE_REWRITE_DIRECTIVE_NGINX_INFO', 'On servers using nginx, the directive below can be added to the <code><strong>location %s</strong></code> block of the <code>server</code> block in the main nginx.conf file, or to the <code><strong>location %s</strong></code> block of the <code>server</code> block in any virtual host file which has been created for the domain (e.g. something like <code>/etc/nginx/sites-available/store.com</code>).');
define('TEXT_NGINX_EXAMPLE_INTRO', 'It doesn\'t really matter where in the block you place it. As an example, the location block could end up looking something like:');
define('TEXT_NGINX_EXAMPLE', 'location %s {
&nbsp;&nbsp;&nbsp;&nbsp;root&nbsp;&nbsp;&nbsp;html;
&nbsp;&nbsp;&nbsp;&nbsp;index&nbsp;&nbsp;index.php;
&nbsp;&nbsp;&nbsp;&nbsp;%s
}');

define('TEXT_REWRITE_RULE_PROBLEMS_TITLE', 'Getting 404 or 500 errors after adding the rule?');
define('TEXT_REWRITE_RULE_ERRORS_INTRO', 'If after adding this rule (and a URI mapping to test) navigating to the new static URI results in a 404 or a 500 error, then it may be worth trying a slight variation of the rule, which some servers seem to require.');
define('TEXT_REWRITE_RULE_ERROR_TRY_ADDING_SLASH', 'Try adding a slash to the front of <code>%s</code>, this may resolve the problem: <code>%s</code>');

define('TEXT_SELECT_ALL_AND_COPY', 'Click this link to Select All of the above text&nbsp;&nbsp;(&amp; automatically Copy it to the Clipboard in IE)');
