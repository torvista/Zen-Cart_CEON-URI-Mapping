<?php

/** renamed from class.String for php7: String is a reserved class
 * String class provides methods for charset and locale-safe string manipulation. Included as a library used by the
 * Ceon URI Mapping module.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Jan Schneider <jan@horde.org>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2008 The Horde Project (http://www.horde.org/)
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.fsf.org/copyleft/lgpl.html Lesser GNU Public License
 * @version     $Id: class.CeonString.php 1027 2024-01-04 20:31:10Z conor updated 5.1.1$
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}

/**
 * Load in the Transliteration class to carry out actual transliteration.
 */
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.Transliteration.php');


// {{{ Constants

/**
 * Global record of character set being used as the default. Since Zen Cart loads this file before it initialises
 * the default defines it must be initialised the first time a function tries to use it.
 */
$GLOBALS['string_charset'] = null;

/**
 * Global record of language being used as the default. Since Zen Cart loads this file before it initialises the
 * default defines it must be initialised the first time a function tries to use it.
 */
$GLOBALS['string_language'] = null;

// }}}


// {{{ CeonString

/** 
 * CeonString class provides methods for charset and locale-safe string manipulation.
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Jan Schneider <jan@horde.org>
 * @copyright   Copyright 2008-2024 Ceon
 * @copyright   Copyright 2003-2008 The Horde Project (http://www.horde.org/)
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @license     http://www.fsf.org/copyleft/lgpl.html Lesser GNU Public License
 */
class CeonString
{
	// {{{ setDefaultCharset()
	
	/**
	 * Sets a default charset that the methods of this class will use if none is explicitly specified.
	 *
	 * @access  public
	 * @static
	 * @param  string     $charset   The charset to use as the default.
	 */
	public static function setDefaultCharset($charset)
	{
		$GLOBALS['string_charset'] = $charset;
		
		if (CeonString::extensionExists('mbstring') && function_exists('mb_regex_encoding')) {
			$old_error = error_reporting(0);
			mb_regex_encoding(CeonString::_mbstringCharset($charset));
			error_reporting($old_error);
		}
	}
	
	// }}}
	
	
	// {{{ convertCharset

	/**
	 * Converts a string from one charset to another.
	 *
	 * Works if the iconv extension is available or the mbstring extension is available. The original string is
	 * returned if conversion failed or none of the conversion functions/methods are available.
	 *
	 * @access  public
	 * @static
	 * @param   string|array   $input   The data to be converted. If $input is an an array, the array's values get
	 *                                  converted recursively.
	 * @param   string         $from    The string's current charset.
	 * @param   string         $to      The charset to convert the string to. If not specified, the global default
	 *                                  will be used.
	 * @return  string|array   The converted input data.
	 */
	public static function convertCharset($input, $from, $to = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		// Get the user's default character set if none passed in.
		if (is_null($to)) {
			$to = $GLOBALS['string_charset'];
		}
		
		// If the from and to character sets are identical, return now.
		$from = CeonString::toLowercase($from);
		$to = CeonString::toLowercase($to);
		
		if ($from == $to) {
			return $input;
		}
		
		if (is_array($input)) {
			$tmp = array();
			
			foreach ($input as $key => $val) {
				$tmp[CeonString::_convertCharset($key, $from, $to)] =
					CeonString::convertCharset($val, $from, $to);
			}
			
			return $tmp;
		}
		
		if (!is_string($input)) {
			return $input;
		}
		
		return CeonString::_convertCharset($input, $from, $to);
	}
	
	// }}}
	
	
	// {{{ _convertCharset()
	
	/**
	 * Internal function used to do charset conversion.
	 *
	 * @access  private
	 * @static
	 * @param   string    $input   See CeonString::convertCharset().
	 * @param   string    $from    See CeonString::convertCharset().
	 * @param   string    $to      See CeonString::convertCharset().
	 * @return  string    The converted string.
	 */
	protected static function _convertCharset($input, $from, $to)
	{
		$output = null;
		$from_ascii = (($from == 'iso-8859-1') || ($from == 'us-ascii'));
		$to_ascii = (($to == 'iso-8859-1') || ($to == 'us-ascii'));
		
		// Use utf8_[en|de]code() if possible and if the string isn't too large (less than 16 MB =
		// 16 * 1024 * 1024 = 16777216 bytes) - these functions use more memory.
		if (strlen($input) < 16777216 ||
				(!CeonString::extensionExists('iconv') && !CeonString::extensionExists('mbstring'))) {
			if ($from_ascii && ($to == 'utf-8')) {
				return $input;
			}
			
			if (($from == 'utf-8') && $to_ascii) {
				return $input;
			}
		}
		
		// Next try iconv with transliteration
		if (($from != 'utf7-imap') && ($to != 'utf7-imap') && CeonString::extensionExists('iconv')) {
			// Need to tack an extra character temporarily because of a bug in iconv() if the last character is not
			// a 7 bit ASCII character.
			$old_track_errors = ini_set('track_errors', 1);
			
			unset($php_errormsg);
			
			$output = @iconv($from, $to . '//TRANSLIT', $input . 'x');
			$output = (isset($php_errormsg)) ? null : CeonString::substr($output, 0, -1, $to);
			
			ini_set('track_errors', $old_track_errors);
		}
		
		// Next try mbstring
		if (is_null($output) && CeonString::extensionExists('mbstring')) {
			$old_error = error_reporting(0);
			
			$output = mb_convert_encoding($input, $to, CeonString::_mbstringCharset($from));
			
			error_reporting($old_error);
		}
		
		// Lastly try imap_utf7_[en|de]code if appropriate
		if (is_null($output) && CeonString::extensionExists('imap')) {
			if ($from_ascii && ($to == 'utf7-imap')) {
				return @imap_utf7_encode($input);
			}
			
			if (($from == 'utf7-imap') && $to_ascii) {
				return @imap_utf7_decode($input);
			}
		}
		
		return (is_null($output)) ? $input : $output;
	}
	
	// }}}
	
	
	// {{{ transliterate()

	/**
	 * Converts a string from to ASCII, transliterating any non-ASCII characters to their ASCII  equivalent.
	 *
	 * The original string is returned if conversion failed or none of the conversion functions/methods are
	 * available.
	 *
	 * In future will make use of PHP6 functionality if available.
	 *
	 * Falls back to using the iconv extension (if available) or the mbstring extension (if available).
	 * 
	 * @access  public
	 * @static
	 * @param   string|array   $input          The data to be converted. If $input is an an array, the array's
	 *                                         values get converted recursively.
	 * @param   string         $from_charset   The input data's current charset, or the default charset if none
	 *                                         specified.
	 * @param   string         $to_language    An optional ISO 639 language code, used to determine the exact
	 *                                         transliterations to be used for particular characters.
	 * @return  string|array   The converted input data.
	 */
	public static function transliterate($input, $from_charset = null, $to_language = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		// Get the user's default character set if none passed in.
		if (is_null($from_charset)) {
			$from_charset = $GLOBALS['string_charset'];
		}
		
		// Use the default language if none passed in.
		/*if (is_null($to_language)) {
			$to_charset = $GLOBALS['string_language'];
		}*/
		
		// If the from and to character sets and languages are identical, return now.
		/*$from_charset = CeonString::toLowercase($from_charset);
		$from_language = CeonString::toLowercase($from_language);
		$to_charset = CeonString::toLowercase($to_charset);
		$to_language = CeonString::toLowercase($to_language);
		if ($from_charset == $to_charset && $from_language == $to_language) {
			return $input;
		}*/
		
		// Handle an array
		if (is_array($input)) {
			$tmp = array();
			
			foreach ($input as $key => $val) {
				// Convert the characterset to UTF-8 if necessary
				$key = CeonString::convertCharset($key, $from_charset, 'utf-8');
				$val = CeonString::convertCharset($val, $from_charset, 'utf-8');
				
				// Transliterate the key and value
				$key = CeonString::_transliterate($key, $to_language);
				$val = CeonString::_transliterate($val, $to_language);
				
				$tmp[$key] = $val;
			}
			
			return $tmp;
		}
		
		if (!is_string($input)) {
			return $input;
		}
		
		// Convert the characterset to UTF-8 if necessary
		$input = CeonString::convertCharset($input, $from_charset, 'utf-8');
		
		//there are 5 parameters available but method uses only 2. return CeonString::_transliterate($input, $from_charset, $from_language, $to_charset, $to_language);
		return CeonString::_transliterate($input, $to_language);
	}
	
	// }}}
	
	
	// {{{ _transliterate()
	
	/**
	 * Internal function used to do transliteration.
	 *
	 * @access  private
	 * @static
	 * @param   string    $input         The UTF-8 string to be converted.
	 * @param   string    $to_language   An optional ISO 639 language code, used to determine the exact
	 *                                   transliterations to be used for particular  characters.
	 * @return  string    The transliterated string.
	 */
	protected static function _transliterate($input, $to_language)
	{
		$output = null;
		
		// Call the Transliteration class, which is aware of the differing transliterations which should be used
		// depending on the language/dialect being converted to.
		// @TODO Also possibly implement contextual transliteration support for more accurate transliteration.
		$output = Transliteration::transliterate($input, '?', $to_language);
		
		// If PHP6 is in use, try using its ICU functionality
		if (function_exists('str_transliterate')) {
			
		}
		
		return (is_null($output)) ? $input : $output;
	}
	
	// }}}
	
	
	// {{{ toLowercase()
	
	/**
	 * Converts a string to lowercase.
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to be converted.
	 * @param   boolean   $locale    If true, the string will be converted based on a given charset, locale 
	 *                               independent otherwise.
	 * @param   string    $charset   If $locale is true, the charset to use when converting. If not provided the 
	 *                               current charset is used.
	 * @return  string    The string converted to lowercase.
	 */
	public static function toLowercase($string, $locale = false, $charset = null)
	{
		static $lowers;
		
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if ($locale) {
			if (CeonString::extensionExists('mbstring') && function_exists('mb_strtolower')) {
				if (is_null($charset)) {
					$charset = $GLOBALS['string_charset'];
				}
				
				$old_error = error_reporting(0);
				$ret = mb_strtolower($string, CeonString::_mbstringCharset($charset));
				
				error_reporting($old_error);
				
				if (!empty($ret)) {
					return $ret;
				}
			}
			
			return strtolower($string);
		}
		
		if (!isset($lowers)) {
			$lowers = array();
		}
		
		if (!isset($lowers[$string])) {
			$language = setlocale(LC_CTYPE, 0);
			
			setlocale(LC_CTYPE, 'C');
			
			$lowers[$string] = strtolower($string);
			
			setlocale(LC_CTYPE, $language);
		}
		
		return $lowers[$string];
	}
	
	// }}}
	
	
	// {{{ toUppercase()
	
	/**
	 * Converts a string to uppercase.
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to be converted.
	 * @param   boolean   $locale    If true, the string will be converted based on a given charset, locale 
	 *                               independent otherwise.
	 * @param   string    $charset   If $locale is true, the charset to use when converting. If not provided the
	 *                               current charset is used.
	 * @return  string    The string converted to uppercase.
	 */
	public static function toUppercase($string, $locale = false, $charset = null)
	{
		static $uppers;
		
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if ($locale) {
			if (CeonString::extensionExists('mbstring') && function_exists('mb_strtoupper')) {
				if (is_null($charset)) {
					$charset = $GLOBALS['string_charset'];
				}
				
				$old_error = error_reporting(0);
				$ret = mb_strtoupper($string, CeonString::_mbstringCharset($charset));
				
				error_reporting($old_error);
				
				if (!empty($ret)) {
					return $ret;
				}
			}
			return strtoupper($string);
		}
		
		if (!isset($uppers)) {
			$uppers = array();
		}
		
		if (!isset($uppers[$string])) {
			$language = setlocale(LC_CTYPE, 0);
			
			setlocale(LC_CTYPE, 'C');
			
			$uppers[$string] = strtoupper($string);
			
			setlocale(LC_CTYPE, $language);
		}
		
		return $uppers[$string];
	}
	
	// }}}
	
	
	// {{{ toUCFirst()
	
	/**
	 * Returns a string with the first letter capitalised (if it is alphabetic).
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to have its first letter capitalised.
	 * @param   boolean   $locale    If true, the string will be converted based on a given charset, locale
	 *                               independent otherwise.
	 * @param   string    $charset   The charset to use, defaults to current charset.
	 * @return  string    The capitalised string.
	 */
	public static function toUCFirst($string, $locale = false, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		if ($locale) {
			$first = CeonString::substr($string, 0, 1, $charset);
			
			if (CeonString::isAlpha($first, $charset)) {
				$string = CeonString::toUppercase($first, true, $charset) . CeonString::substr($string, 1, null, $charset);
			}
		} else {
			$string = CeonString::toUppercase(substr($string, 0, 1), false) . substr($string, 1);
		}
		return $string;
	}
	
	// }}}
	
	
	// {{{ toUCWords()
	
	/**
	 * Returns a string with the first letter of each word within capitalised (if it is alphabetic).
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to have its words capitalised.
	 * @param   boolean   $locale    If true, the string will be converted based on a given charset, locale
	 *                               independent otherwise.
	 * @param   string    $charset   The charset to use, defaults to current charset.
	 * @return  string    The capitalised string.
	 */
	public static function toUCWords($string, $locale = false, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		$input_string = $string;
		$output_string = '';
		
		while ($input_string != '') {
			$matches = null;
			
			$matched = CeonString::regexpMatch($input_string, '/^([^\s]+\s+)/U', $matches, $charset);
			
			if ($matched) {
				// String has at least one space in it, extract first word
				$current_word = $matches[1];
				
				if (CeonString::length($current_word) < CeonString::length($input_string)) {
					$input_string = CeonString::substr($input_string, CeonString::length($current_word),
						(CeonString::length($input_string) - CeonString::length($current_word)));
				} else {
					$input_string = '';
				}
			} else {
				// String consists of a single word only
				$current_word = $input_string;
				$input_string = '';
			}
			
			if ($locale) {
				$first = CeonString::substr($current_word, 0, 1, $charset);
				
				if (CeonString::isAlpha($first, $charset)) {
					$current_word = CeonString::toUppercase($first, true, $charset) .
						CeonString::substr($current_word, 1, null, $charset);
				}
			} else {
				$current_word = CeonString::toUppercase(substr($current_word, 0, 1), false) .
					substr($current_word, 1);
			}
			
			$output_string .= $current_word;
		}
		
		return $output_string;
	}
	
	// }}}
	
	
	// {{{ substr
	
	/**
	 * Returns part of a string.
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to be converted.
	 * @param   integer   $start     The part's start position, (beginning at zero).
	 * @param   integer   $length    The part's length.
	 * @param   string    $charset   The charset to use when calculating the part's position and length, defaults 
	 *                               to current charset.
	 * @return  string    The string's part.
	 */
	public static function substr($string, $start, $length = null, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($length)) {
			$length = CeonString::length($string, $charset) - $start;
		}
		
		if ($length == 0) {
			return '';
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		$charset = CeonString::toLowercase($charset);
		
		// Use PHP5.3+ intl extension if available
		if (CeonString::extensionExists('intl') && ($charset == 'utf-8' || $charset == 'utf8')) {
			return grapheme_substr($string, $start, $length);
		}
		
		// Use multi-byte functionality if available
		if (CeonString::extensionExists('mbstring')) {
			$old_error = error_reporting(0);
			
			$ret = mb_substr($string, $start, $length, CeonString::_mbstringCharset($charset));
			
			error_reporting($old_error);
			
			if (!empty($ret)) {
				return $ret;
			}
		}
		
		// Use standard PHP functionality
		return substr($string, $start, $length);
	}
	
	// }}}
	
	
	// {{{ length()
	
	/**
	 * Returns the character length of a string (not its byte length).
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to return the length of.
	 * @param   string    $charset   The charset to use when calculating the string's length.
	 * @return  integer   The string's length.
	 */
	public static function length($string, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		$charset = CeonString::toLowercase($charset);
		
		if ($charset == 'utf-8' || $charset == 'utf8') {
			// Use PHP5.3+ intl extension if available
			if (CeonString::extensionExists('intl')) {
				return grapheme_strlen($string);
			} else {
				// Use standard PHP function
				return strlen(mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8'));
			}
		}
		
		if (CeonString::extensionExists('mbstring')) {
			$old_error = error_reporting(0);
			
			$ret = mb_strlen($string, CeonString::_mbstringCharset($charset));
			
			error_reporting($old_error);
			
			if (!empty($ret)) {
				return $ret;
			}
		}
		return strlen($string);
	}
	
	// }}}
	
	
	// {{{ strpos
	
	/**
	 * Returns the numeric position of the first occurrence of $needle in the $haystack string.
	 *
	 * @access  public
	 * @static
	 * @param   string    $haystack   The string to search through.
	 * @param   string    $needle     The string to search for.
	 * @param   integer   $offset     Allows the specification of the character in haystack from which to start
	 *                                searching.
	 * @param   string    $charset    The charset to use when searching.
	 * @return  integer   The position of first occurrence.
	 */
	public static function pos($haystack, $needle, $offset = 0, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		$charset = CeonString::toLowercase($charset);
		
		if ($charset == 'utf-8' || $charset == 'utf8') {
			// Use PHP5.3+ intl extension if available
			if (CeonString::extensionExists('intl')) {
				return grapheme_strpos($haystack, $needle, $offset);
			}
		}
		
		if (CeonString::extensionExists('mbstring')) {
			$track_errors = ini_set('track_errors', 1);
			$old_error = error_reporting(0);
			
			$ret = mb_strpos($haystack, $needle, $offset, CeonString::_mbstringCharset($charset));
			
			error_reporting($old_error);
			
			ini_set('track_errors', $track_errors);
			
			if (!isset($php_errormsg)) {
				return $ret;
			}
		}
		
		return strpos($haystack, $needle, $offset);
	}
	
	// }}}
	
	
	// {{{
	
	/**
	 * Returns true if the every character in the string is an alphabetic character.
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to test.
	 * @param   string    $charset   The charset to use when testing the string.
	 * @return  boolean   True if the string consists solely of alphabetic characters.
	 */
	public static function isAlpha($string, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		if (CeonString::extensionExists('mbstring')) {
			$charset = CeonString::_mbstringCharset($charset);
			
			$old_charset = mb_regex_encoding();
			
			$old_error = error_reporting(0);
			
			if ($charset != $old_charset) {
				mb_regex_encoding($charset);
			}
			
			$alpha = !mb_ereg_match('[^[:alpha:]]', $string);
			
			if ($charset != $old_charset) {
				mb_regex_encoding($old_charset);
			}
			
			error_reporting($old_error);
			
			return $alpha;
		}
		
		return ctype_alpha($string);
	}
	
	// }}}
	
	
	// {{{ isLower()
	
	/**
	 * Returns true if every character in the string is a lowercase letter (according to the locale for the
	 * charset).
	 *
	 * @access  public
	 * @static
	 * @param   string    $string    The string to test.
	 * @param   string    $charset   The charset to use when testing the string.
	 * @return  boolean   True if the string is lowercase.
	 */
	public static function isLower($string, $charset = null)
	{
		return ((CeonString::toLowercase($string, true, $charset) === $string) &&
			CeonString::isAlpha($string, $charset));
	}
	
	// }}}
	
	
	// {{{ isUpper()
	
	/**
	 * Returns true if every character in the string is an uppercase letter (according to the locale for the
	 * charset).
	 * 
	 * @access  public
	 * @static
	 * @param   string    $string    The string to test.
	 * @param   string    $charset   The charset to use when testing the string.
	 * @return  boolean   True if the string is uppercase.
	 */
	public static function isUpper($string, $charset = null)
	{
		return ((CeonString::toUppercase($string, true, $charset) === $string) &&
			CeonString::isAlpha($string, $charset));
	}
	
	// }}}
	
	
	// {{{ regexpMatch()
	
	/**
	 * Performs a multibyte-safe regex match search on the text provided.
	 * NOTE: Does NOT use same parameter order as PHP regexp functions!
	 *
	 * @access  public
	 * @static
	 * @param   string    $text      The text to search.
	 * @param   string    $regexp    The regular expression(s) to use.
	 * @param   array     $matches   A reference to a variable to be populated with an array of the matches if the
	 *                               regexp matches.
	 * @param   string    $charset   The character set of the text.
	 * @return  boolean   Whether the regexp matched or not.
	 */
	public static function regexpMatch($text, $regexp, &$matches, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		$regexp = CeonString::convertCharset($regexp, $charset, 'utf-8');
		$text = CeonString::convertCharset($text, $charset, 'utf-8');
		
		$num_matches = preg_match($regexp . 'u', $text, $matches);
		
		if (!empty($charset) && $num_matches !== 0 && sizeof($matches) > 0) {
			$matches = CeonString::convertCharset($matches, 'utf-8', $charset);
		}
		
		return ($num_matches == 1 ? true : false);
	}
	
	// }}}
	
	
	// {{{ regexpReplace()
	
	/**
	 * Performs a multibyte-safe regex replace on the text/array provided.
	 * NOTE: Does NOT use same parameter order as PHP regexp functions!
	 *
	 * @access  public
	 * @static
	 * @param   string|array    $text      The text (or array of text) to search.
	 * @param   string|array    $regexp    The regular expression(s) to use.
	 * @param   string|array    $replace   The replacement(s) to use.
	 * @param   string          $charset   The character set of the text.
	 * @return  string|array|boolean   The updated text or array or false if a problem occurred
	 *                                 parsing the regular expression.
	 */
	public static function regexpReplace($text, $regexp, $replace, $charset = null)
	{
		if (is_null($GLOBALS['string_charset'])) {
			$GLOBALS['string_charset'] = CHARSET;
			$GLOBALS['string_language'] = DEFAULT_LANGUAGE;
		}
		
		if (is_null($charset)) {
			$charset = $GLOBALS['string_charset'];
		}
		
		$regexp = CeonString::convertCharset($regexp, $charset, 'utf-8');
		$text = CeonString::convertCharset($text, $charset, 'utf-8');
		
		if (!is_array($regexp)) {
			if (!CeonString::validateRegexp($regexp)) {
				return false;
			}
			
			$regexp = $regexp . 'u';
			
		} else {
			for ($i = 0, $n = sizeof($regexp); $i < $n; $i++) {
				if (!CeonString::validateRegexp($regexp[$i])) {
					return false;
				}
				
				$regexp[$i] = $regexp[$i] . 'u';
			}
		}
		
		// Can a fallback be used if any UTF-8 error occurs?
		$handle_utf_error_fallback = defined('PREG_BAD_UTF8_ERROR') && function_exists('preg_last_error');
		
		$tmp = preg_replace($regexp, $replace, $text);
		
		if ($handle_utf_error_fallback && (preg_last_error() == PREG_BAD_UTF8_ERROR)) {
			$text = CeonString::convertCharset($text, 'utf-8', 'us-ascii');
			$tmp = preg_replace(substr($regexp, 0, strlen($regexp) - 1), $replace, $text);
		}
		
		$text = $tmp;
		
		$text = CeonString::convertCharset($text, 'utf-8', $charset);
		
		return $text;
	}
	
	// }}}
	
	
	// {{{ validateRegexp()
	
	/**
	 * Checks if a regular expression is in a valid format.
	 *
	 * @access  public
	 * @static
	 * @param   string    $regexp   The regular expression to validate.
	 * @return  boolean   Whether or not the regexp is valid.
	 * @TODO Have this throw exceptions
	 */
	public static function validateRegexp($regexp)
	{
		$matches = null;
		if (CeonString::length($regexp) == 0 ||
				CeonString::regexpMatch($regexp, '/^[\w0-9\/]/', $matches) == false) {
			// Caller hasn't delimited the regexp. More than likely, the order of the parameters has been used
			// incorrectly, easily done since they differ from the standard PHP order.
			return false;
		} else if (CeonString::length($regexp) < 2) {
			// End delimiter missing
			return false;
		}
		
		return true;
	}
	
	// }}}
	
	
	// {{{ _mbstringCharset()
	
	/**
	 * Workaround charsets that don't work with mbstring functions.
	 *
	 * @access  private
	 * @static
	 * @param   string    $charset   The original charset.
	 * @return  string    The charset to use with mbstring functions.
	 */
	protected static function _mbstringCharset($charset)
	{
		// mbstring functions do not handle the 'ks_c_5601-1987' & 'ks_c_5601-1989' charsets. However, these
		// charsets are used, for example, by various versions of Outlook to send Korean characters. Use UHC
		// (CP949) encoding instead. See, e.g.
		// http://lists.w3.org/Archives/Public/ietf-charsets/2001AprJun/0030.html
		if (in_array(CeonString::toLowercase($charset), array('ks_c_5601-1987', 'ks_c_5601-1989'))) {
			$charset = 'UHC';
		}
		
		return $charset;
	}
	
	// }}}
	

	// {{{ extensionExists()
	
	/**
	 * Caches the result of extension_loaded() calls.
	 *
	 * @access  private
	 * @static
	 * @param   string    $extension   The extension's name.
	 * @return  boolean   Whether the extension is loaded in PHP.
	 */
	protected static function extensionExists($extension)
	{
		static $cache = array();
		
		if (!isset($cache[$extension])) {
			$cache[$extension] = extension_loaded($extension);
		}
		
		return $cache[$extension];
	}
	
	// }}}

}

// }}}