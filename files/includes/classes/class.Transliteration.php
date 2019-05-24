<?php

/** 
 * Transliteration class provides transliteration of strings. Included as a library used by the Ceon URI Mapping
 * module.
 *
 * Based on Drupal's Transliteration module, which in turn is based on CPAN's Text::Unidecode and Mediawiki's
 * UTFNormal::quickIsNFCVerify():
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @author      Stefan M. Kudwien (smk-ka) - dev@unleashedmind.com
 * @author      Daniel F. Kudwien (sun) - dev@unleashedmind.com
 * @copyright   Copyright 2008-2019 Ceon
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @link        http://drupal.org/project/transliteration
 * @link        http://www.mediawiki.org
 * @link        http://search.cpan.org/~sburke/Text-Unidecode-0.04/lib/Text/Unidecode.pm
 * @license     http://www.fsf.org/copyleft/lgpl.html Lesser GNU Public License
 * @version     $Id: class.Transliteration.php 1027 2012-07-17 20:31:10Z conor $
 */

if (!defined('IS_ADMIN_FLAG')) {
	die('Illegal Access');
}


// {{{ Constants

/**
 * Define the path to the Transliteration library.
 */
$GLOBALS['transliteration_library_path'] = DIR_FS_CATALOG . DIR_WS_CLASSES . 'transliteration/';

// }}}


// {{{ Transliteration

/** 
 * Transliteration class provides transliteration of strings.
 *
 * Based on Drupal's Transliteration module, which in turn is based on CPAN's Text::Unidecode and Mediawiki's
 * UTFNormal::quickIsNFCVerify().
 *
 * @package     ceon_uri_mapping
 * @author      Conor Kerr <zen-cart.uri-mapping@ceon.net>
 * @copyright   Copyright 2008-2019 Ceon
 * @link        http://ceon.net/software/business/zen-cart/uri-mapping
 * @link        http://drupal.org/project/transliteration
 * @link        http://www.mediawiki.org
 * @link        http://search.cpan.org/~sburke/Text-Unidecode-0.04/lib/Text/Unidecode.pm
 * @license     http://www.fsf.org/copyleft/lgpl.html Lesser GNU Public License
 */
class Transliteration
{
	// {{{ transliterate()
	
	/**
	 * Transliterate UTF-8 input to plain ASCII.
	 *
	 * @author  Conor Kerr <zen-cart.uri-mapping@ceon.net>
	 * @author  Stefan M. Kudwien (smk-ka) - dev@unleashedmind.com
	 * @author  Daniel F. Kudwien (sun) - dev@unleashedmind.com
	 * @access  public
	 * @static
	 * @param   string    $string    UTF-8 text input.
	 * @param   string    $unknown   Replacement for unknown characters and illegal UTF-8 sequences.
	 * @param   string    $language_code   Optional ISO 639 language code used to import language specific 
	 *                                     replacements. Defaults to the current display language.
	 * @return  string    Plain ASCII output.
	 */
	static function transliterate($string, $unknown = '?', $language_code = null)
	{
		// Screen out some characters that eg won't be allowed in XML
		$string = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $unknown, $string);
		
		// ASCII is always valid NFC!
		// If we're only ever given plain ASCII, we can avoid the overhead of initialising the decomposition tables
		// by skipping out early
		if (!preg_match('/[\x80-\xff]/', $string)) {
			return $string;
		}
		
		static $tailBytes;
		
		if (!isset($tailBytes)) {
			// Each UTF-8 head byte is followed by a certain number of tail bytes
			$tailBytes = array();
			
			for ($n = 0; $n < 256; $n++) {
				if ($n < 0xc0) {
					$remaining = 0;
				} else if ($n < 0xe0) {
					$remaining = 1;
				} else if ($n < 0xf0) {
					$remaining = 2;
				} else if ($n < 0xf8) {
					$remaining = 3;
				} else if ($n < 0xfc) {
					$remaining = 4;
				} else if ($n < 0xfe) {
					$remaining = 5;
				} else {
					$remaining = 0;
				}
				$tailBytes[chr($n)] = $remaining;
			}
		}
		
		// Chop the text into pure-ASCII and non-ASCII areas. Large ASCII parts can be handled much more quickly.
		// Don't chop up Unicode areas for punctuation though, that wastes energy
		preg_match_all('/[\x00-\x7f]+|[\x80-\xff][\x00-\x40\x5b-\x5f\x7b-\xff]*/', $string, $matches);
		
		$result = '';
		
		foreach($matches[0] as $str) {
			if ($str{0} < "\x80") {
				// ASCII chunk: guaranteed to be valid UTF-8  and in normal form C, so skip over it
				$result .= $str;
				continue;
			}
			
			// Examine the chunk byte by byte to ensure that it consists of valid UTF-8 sequences, and to see if
			// any of them might not be normalized
			//
			// Since PHP is not the fastest language on earth, some of this code is a little ugly with inner loop
			// optimizations
			
			$head = '';
			$chunk = strlen($str);
			$len = $chunk + 1; // Counting down is faster. I'm *so* sorry
			
			for ($i = -1; --$len;) {
				$c = $str{++$i};
				
				if ($remaining = $tailBytes[$c]) {
					// UTF-8 head byte!
					$sequence = $head = $c;
					
					do {
						// Look for the defined number of tail bytes...
						if (--$len && ($c = $str{++$i}) >= "\x80" && $c < "\xc0") {
							// Legal tail bytes are nice
							$sequence .= $c;
						} else {
							if ($len == 0) {
								// Premature end of string!
								// Drop a replacement character into output to represent the invalid UTF-8 sequence
								$result .= $unknown;
								break 2;
							} else {
								// Illegal tail byte; abandon the sequence.
								$result .= $unknown;
								// Back up and reprocess this byte. It may itself be a legal ASCII or UTF-8
								// sequence head
								--$i;
								++$len;
								continue 2;
							}
						}
					} while(--$remaining);
					
					$n = ord($head);
					
					if ($n <= 0xdf) {
						$ord = ($n-192)*64 + (ord($sequence{1})-128);
					} else if ($n <= 0xef) {
						$ord = ($n-224)*4096 + (ord($sequence{1})-128)*64 + (ord($sequence{2})-128);
					} else if ($n <= 0xf7) {
						$ord = ($n-240)*262144 + (ord($sequence{1})-128)*4096 +
							(ord($sequence{2})-128)*64 + (ord($sequence{3})-128);
					} else if ($n <= 0xfb) {
						$ord = ($n-248)*16777216 + (ord($sequence{1})-128)*262144 +
							(ord($sequence{2})-128)*4096 + (ord($sequence{3})-128)*64 + (ord($sequence{4})-128);
					} else if ($n <= 0xfd) {
						$ord = ($n-252)*1073741824 + (ord($sequence{1})-128)*16777216 +
							(ord($sequence{2})-128)*262144 + (ord($sequence{3})-128)*4096 +
							(ord($sequence{4})-128)*64 + (ord($sequence{5})-128);
					}
					
					$result .= Transliteration::_replace($ord, $unknown, $language_code);
					$head = '';
				} else if ($c < "\x80") {
					// ASCII byte
					$result .= $c;
					$head = '';
				} else if ($c < "\xc0") {
					// Illegal tail bytes
					if ($head == '') {
						$result .= $unknown;
					}
				} else {
					// Miscellaneous freaks
					$result .= $unknown;
					$head = '';
				}
			}
		}
		return $result;
	}
	
	// }}}
	
	
	// {{{

	/**
	 * Looks up the transliterated character for a unicode character code from the transliteration database,
	 * adjusting the database used depending on the language code supplied.
	 *
	 * @author  Conor Kerr <zen-cart.uri-mapping@ceon.net>
	 * @author  Stefan M. Kudwien (smk-ka) - dev@unleashedmind.com
	 * @author  Daniel F. Kudwien (sun) - dev@unleashedmind.com
	 * @access  private
	 * @static
	 * @param   integer   $ord       A unicode ordinal character code.
	 * @param   string    $unknown   Replacement for unknown characters.
	 * @param   string    $language_code   Optional ISO 639 language code used to import language 
	 *                                     specific replacements. Defaults to the current display language.
	 * @return  string    Plain ASCII replacement character.
	 */
	static function _replace($ord, $unknown = '?', $language_code = null)
	{
		if (!isset($language_code)) {
			$language_code = $GLOBALS['string_language'];
		}
		
		static $map = array();
		static $template = array();
		
		$bank = $ord >> 8;
		
		// Check if a new bank needs to be loaded 
		if (!isset($template[$bank])) {
			$file = $GLOBALS['transliteration_library_path'] . sprintf('x%02x', $bank) . '.php';
			if (file_exists($file)) {
				$template[$bank] = include($file);
			} else {
				$template[$bank] = array('en' => array());
			}
		}
		
		// Check if new mappings with language specific alterations need to be created
		if (!isset($map[$bank][$language_code])) {
			if ($language_code != 'en' && isset($template[$bank][$language_code])) {
				// Merge language specific mappings with the default transliteration table
				$map[$bank][$language_code] = $template[$bank][$language_code] + $template[$bank]['en'];
			} else {
				$map[$bank][$language_code] = $template[$bank]['en'];
			}
		}
		
		$ord = $ord & 255;
		
		return (isset($map[$bank][$language_code][$ord]) ? $map[$bank][$language_code][$ord] : $unknown);
	}
	
	// }}}
}

// }}}