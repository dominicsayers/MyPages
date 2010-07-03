<?php
/**
 * A simple, but surprisingly cunning, templating and content management system for PHP websites.
 * 
 * MyPages is a single-file CMS that allows you to focus on your site's content and not
 * on its consistency or those boring administrative tasks.
 * 
 * Copyright (c) 2008-2009, Dominic Sayers
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 * 	- Redistributions of source code must retain the above copyright notice,
 * 	  this list of conditions and the following disclaimer.
 * 	- Redistributions in binary form must reproduce the above copyright notice,
 * 	  this list of conditions and the following disclaimer in the documentation
 * 	  and/or other materials provided with the distribution.
 * 	- Neither the name of Dominic Sayers nor the names of its contributors may be
 * 	  used to endorse or promote products derived from this software without
 * 	  specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package	MyPages
 * @author	Dominic Sayers <dominic_sayers@hotmail.com>
 * @copyright	2009 Dominic Sayers
 * @license	http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link	http://code.google.com/p/mypages/
 * @version	0.10 - Adds support for controllers
 */

// The quality of this code has been improved greatly by using PHPLint
// PHPLint is copyright (c) 2009 Umberto Salsi
// PHPLint is free software; see the license for copying conditions.
// More info: http://www.icosaedro.it/phplint/
/*.
require_module 'dom';
require_module 'pcre';
require_module 'hash';
require_module 'session';
.*/

/* Comment out profiling statements if not needed
function MyPages_time() {list($usec, $sec) = explode(" ",microtime()); return ((float)$usec + (float)$sec);}
$myPages_profile			= array();
$myPages_profile['REQUEST_TIME']	= $_SERVER['REQUEST_TIME'];
$myPages_profile['received']		= MyPages_time();
*/

/**
 * Common utility functions
 *
 * @package MyPages
 * @version 1.8 (revision number of common functions class only)
 */
interface I_MyPages_common {
	const	PACKAGE			= 'MyPages',

		HASH_FUNCTION		= 'SHA256',
		URL_SEPARATOR		= '/',

		// Behaviour settings for strleft()
		STRLEFT_MODE_NONE	= 0,
		STRLEFT_MODE_ALL	= 1,

		// Behaviour settings for getURL()
		URL_MODE_PROTOCOL	= 1,
		URL_MODE_HOST		= 2,
		URL_MODE_PORT		= 4,
		URL_MODE_PATH		= 8,
		URL_MODE_ALL		= 15,

		// Behaviour settings for getPackage()
		PACKAGE_CASE_DEFAULT	= 0,
		PACKAGE_CASE_LOWER	= 0,
		PACKAGE_CASE_CAMEL	= 1,
		PACKAGE_CASE_UPPER	= 2;

	// Basic utility functions
	public static /*.string.*/		function strleft(/*.string.*/ $haystack, /*.string.*/ $needle);
	public static /*.string.*/		function getInnerHTML(/*.string.*/ $html, /*.string.*/ $tag);
	public static /*.array[string]string.*/	function meta_to_array(/*.string.*/ $html);
	public static /*.string.*/		function var_dump_to_HTML(/*.string.*/ $var_dump, $offset = 0);
	public static /*.string.*/		function array_to_HTML(/*.array[mixed]mixed.*/ $source = NULL);

	// Environment functions
	public static /*.string.*/		function getPackage($mode = self::PACKAGE_CASE_DEFAULT);
	public static /*.string.*/		function getURL($mode = self::URL_MODE_PATH, $filename = '');
	public static /*.string.*/		function docBlock_to_HTML(/*.string.*/ $php);

	// File system functions
	public static /*.string.*/		function getFileContents(/*.string.*/ $filename, /*.int.*/ $flags = 0, /*.object.*/ $context = NULL, /*.int.*/ $offset = -1, /*.int.*/ $maxLen = -1);
	public static /*.string.*/		function findIndexFile(/*.string.*/ $folder);
	public static /*.string.*/		function findTarget(/*.string.*/ $target);

	// Data functions
	public static /*.string.*/		function makeId();
	public static /*.string.*/		function makeUniqueKey(/*.string.*/ $id);

	// Validation functions
	public static /*.boolean.*/		function is_email(/*.string.*/ $email, $checkDNS = false);
}

/**
 * Common utility functions
 */
abstract class MyPages_common implements I_MyPages_common {
/**
 * Return the beginning of a string, up to but not including the search term.
 *
 * @param string $haystack The string containing the search term
 * @param string $needle The end point of the returned string. In other words, if <var>needle<var> is found then the begging of <var>haystack</var> is returned up to the character before <needle>.
 * @param int $mode If <var>needle</var> is not found then <pre>FALSE</pre> will be returned. */
	public static /*.string.*/ function strleft(/*.string.*/ $haystack, /*.string.*/ $needle, /*.int.*/ $mode = self::STRLEFT_MODE_NONE) {
		$posNeedle = strpos($haystack, $needle);

		if ($posNeedle === false) {
			switch ($mode) {
			case self::STRLEFT_MODE_NONE:
				return $posNeedle;
				break;
			case self::STRLEFT_MODE_ALL:
				return $haystack;
				break;
			}
		} else {
			return substr($haystack, 0, $posNeedle);
		}
	}

/**
 * Return the contents of an HTML element, the first one matching the <var>tag</var> parameter.
 *
 * @param string $html The string containing the html to be searched
 * @param string $tag The type of element to search for. The contents of first matching element will be returned. If the element doesn't exist then <var>false</var> is returned.
 */
	public static /*.string.*/ function getInnerHTML(/*.string.*/ $html, /*.string.*/ $tag) {
		$pos_tag_open_start	= stripos($html, "<$tag")				; if ($pos_tag_open_start	=== false) return false;
		$pos_tag_open_end	= strpos($html, '>',		$pos_tag_open_start)	; if ($pos_tag_open_end		=== false) return false;
		$pos_tag_close		= stripos($html, "</$tag>",	$pos_tag_open_end)	; if ($pos_tag_close		=== false) return false;
		return substr($html, $pos_tag_open_end + 1, $pos_tag_close - $pos_tag_open_end - 1);
	}

/**
 * Return the <var>meta</var> tags from an HTML document as an array.
 *
 * The array returned will have a 'key' element which is an array of name/value pairs representing all the metadata
 * from the HTML document. If there are any <var>name</var> or <var>http-equiv</var> meta elements
 * these will be in their own sub-array. The 'key' sub-array combines all meta tags.
 *
 * Qualifying attributes such as <var>lang</var> and <var>scheme</var> have their own sub-arrays with the same key
 * as the main sub-array.
 *
 * Here are some example meta tags:
 *
 * <code>
 * <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
 * <meta name="description" content="Free Web tutorials" />
 * <meta name="keywords" content="HTML,CSS,XML,JavaScript" />
 * <meta name="author" content="Hege Refsnes" />
 * <meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" />
 * <META NAME="ROBOTS" CONTENT="NOYDIR">
 * <META NAME="Slurp" CONTENT="NOYDIR">
 * <META name="author" content="John Doe">
 *   <META name ="copyright" content="&copy; 1997 Acme Corp.">
 *   <META name= "keywords" content="corporate,guidelines,cataloging">
 *   <META name = "date" content="1994-11-06T08:49:37+00:00">
 *       <meta name="DC.title" lang="en" content="Services to Government" >
 *     <meta name="DCTERMS.modified" scheme="XSD.date" content="2007-07-22" >
 * <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 * <META name="geo.position" content="26.367559;-80.12172">
 * <META name="geo.region" content="US-FL">
 * <META name="geo.placename" content="Boca Raton, FL">
 * <META name="ICBM" content="26.367559, -80.12172">
 * <META name="DC.title" content="THE NAME OF YOUR SITE">
 * </code>
 *
 * Here is a dump of the returned array:
 *
 * <code>
 * array (
 *   'key' => 
 *   array (
 *     'Content-Type' => 'text/html; charset=iso-8859-1',
 *     'description' => 'Free Web tutorials',
 *     'keywords' => 'corporate,guidelines,cataloging',
 *     'author' => 'John Doe',
 *     'ROBOTS' => 'NOYDIR',
 *     'Slurp' => 'NOYDIR',
 *     'copyright' => '&copy; 1997 Acme Corp.',
 *     'date' => '1994-11-06T08:49:37+00:00',
 *     'DC.title' => 'THE NAME OF YOUR SITE',
 *     'DCTERMS.modified' => '2007-07-22',
 *     'geo.position' => '26.367559;-80.12172',
 *     'geo.region' => 'US-FL',
 *     'geo.placename' => 'Boca Raton, FL',
 *     'ICBM' => '26.367559, -80.12172',
 *   ),
 *   'http-equiv' => 
 *   array (
 *     'Content-Type' => 'text/html; charset=iso-8859-1',
 *   ),
 *   'name' => 
 *   array (
 *     'description' => 'Free Web tutorials',
 *     'keywords' => 'corporate,guidelines,cataloging',
 *     'author' => 'John Doe',
 *     'ROBOTS' => 'NOYDIR',
 *     'Slurp' => 'NOYDIR',
 *     'copyright' => '&copy; 1997 Acme Corp.',
 *     'date' => '1994-11-06T08:49:37+00:00',
 *     'DC.title' => 'THE NAME OF YOUR SITE',
 *     'DCTERMS.modified' => '2007-07-22',
 *     'geo.position' => '26.367559;-80.12172',
 *     'geo.region' => 'US-FL',
 *     'geo.placename' => 'Boca Raton, FL',
 *     'ICBM' => '26.367559, -80.12172',
 *   ),
 *   'lang' => 
 *   array (
 *     'DC.title' => 'en',
 *   ),
 *   'scheme' => 
 *   array (
 *     'DCTERMS.modified' => 'XSD.date',
 *   ),
 * </code>
 *
 * Note how repeated tags cause the previous value to be overwritten in the resulting array
 * (for example the <var>Content-Type</var> and <var>keywords</var> tags appear twice but the
 * final array only has one element for each - the lowest one in the original list).
 *
 * @param string $html The string containing the html to be parsed
 */
	public static /*.array[string]string.*/ function meta_to_array(/*.string.*/ $html) {
		$keyAttributes	= array('name', 'http-equiv', 'charset', 'itemprop');
		$tags		= array();
		$meta		= array();
		$query		= '?';
		$result		= preg_match_all("|<meta.+/$query>|i", $html, $tags);

		foreach ($tags[0] as $tag) {
			$attributes	= array();
			$result		= preg_match_all('|\s(\S+?)\s*=\s*"(.*?)"|', $tag, $attributes);
			$wip		= array();

			unset($key_type);
			unset($key);
			unset($content);

			for ($i = 0; $i < count($attributes[1]); $i++) {
				$attribute	= strtolower($attributes[1][$i]);
				$value		= $attributes[2][$i];

				if (in_array($attribute, $keyAttributes)) {
					$key_type		= $attribute;
					$key			= $value;
				} elseif ($attribute === 'content') {
					$content		= $value;
				} else {
					$wip[$attribute]	= $value;
				}
			}

			if (isset($key_type)) {
				$meta['key'][$key]	= $content;
				$meta[$key_type][$key]	= $content;

				foreach ($wip as $attribute => $value) {
					$meta[$attribute][$key] = $value;
				}
			}
		}

		return $meta;
	}

/**
 * Return the contents of a captured var_dump() as HTML. This is a recursive function.
 *
 * @param string $var_dump The captured <var>var_dump()</var>.
 * @param int $offset Whereabouts to start in the captured string. Defaults to the beginning of the string.
 */
	public static /*.string.*/ function var_dump_to_HTML(/*.string.*/ $var_dump, $offset = 0) {
		$html	= '';
		$indent	= '';

		while ($posStart = strpos($var_dump, '(', $offset)) {
			$type	= substr($var_dump, $offset, $posStart - $offset);
			$nests	= strrpos($type, ' ');

			if ($nests === false) $nests = 0; else $nests = ($nests + 1) / 2;

			$indent = str_pad('', $nests * 3, "\t");
			$type	= trim($type);
			$offset	= ++$posStart;
			$posEnd	= strpos($var_dump, ')', $offset); if ($posEnd === false) break;
			$offset	= $posEnd + 1;
			$value	= substr($var_dump, $posStart, $posEnd - $posStart);

			switch ($type) {
			case 'string':
				$length	= $value;
				$value	= '<pre>' . htmlspecialchars(substr($var_dump, $offset + 2, $length)) . '</pre>';
				$offset	+= $length + 3;
				break;
			case 'array':
				$elementTellTale	= "\n" . str_pad('', ($nests + 1) * 2) . '['; // Not perfect but the best var_dump will allow
				$elementCount		= (int) $value;
				$value			= "\n$indent<table>\n";

				for ($i = 1; $i <= $elementCount; $i++) {
					$posStart	= strpos($var_dump, $elementTellTale, $offset);	if ($posStart	=== false) break;
					$posStart	+= ($nests + 1) * 2 + 2;
					$offset		= $posStart;
					$posEnd		= strpos($var_dump, ']', $offset);		if ($posEnd	=== false) break;
					$offset		= $posEnd + 4; // Read past the =>\n
					$key		= substr($var_dump, $posStart, $posEnd - $posStart);

					if (!is_numeric($key)) $key = substr($key, 1, strlen($key) - 2); // Strip off the double quotes

					$search		= ($i === $elementCount) ? "\n" . str_pad('', $nests * 2) . '}' : $elementTellTale;
					$posStart	= strpos($var_dump, $search, $offset);		if ($posStart	=== false) break;
					$next		= substr($var_dump, $offset, $posStart - $offset);
					$offset		= $posStart;
					$inner_value	= self::var_dump_to_HTML($next);

					$value		.= "$indent\t<tr>\n";
					$value		.= "$indent\t\t<td>$key</td>\n";
					$value		.= "$indent\t\t<td>$inner_value</td>\n";
					$value		.= "$indent\t</tr>\n";
				}

				$value			.= "$indent</table>\n";
				break;
			case 'object':
				if ($value === '__PHP_Incomplete_Class') {
					$posStart	= strpos($var_dump, '(', $offset);	if ($posStart	=== false) break;
					$offset		= ++$posStart;
echo "$indent Corrected \$offset = $offset\n"; // debug
					$posEnd		= strpos($var_dump, ')', $offset);	if ($posEnd	=== false) break;
					$offset		= $posEnd + 1;
echo "$indent Corrected \$offset = $offset\n"; // debug
					$value		= substr($var_dump, $posStart, $posEnd - $posStart);
				}

				break;
			default:
				break;
			}

		}

		return $value;
	}

/**
 * Return the contents of an array as HTML (like <var>var_dump()</var> on steroids), including object members
 *
 * @param mixed $source The array to export. If it's empty then $GLOBALS is exported.
 */
	public static /*.string.*/ function array_to_HTML(/*.array[mixed]mixed.*/ $source = NULL) {
// If no specific array is passed we will export $GLOBALS to HTML
// Unfortunately, this means we have to use var_dump() because var_export() barfs on $GLOBALS
// In fact var_dump is easier to walk than var_export anyway so this is no bad thing.

		ob_start();
		if (empty($source)) var_dump($GLOBALS); else var_dump($source);
		$var_dump = ob_get_clean();

		return self::var_dump_to_HTML($var_dump);
	}

/**
 * Return the name of this package. By default this will be in lower case for use in Javascript tags etc.
 *
 * @param int $mode One of the <var>PACKAGE_CASE_XXX</var> predefined constants defined in this class
 */
	public static /*.string.*/ function getPackage($mode = self::PACKAGE_CASE_DEFAULT) {
		switch ($mode) {
		case self::PACKAGE_CASE_CAMEL:
			$package = self::PACKAGE;
			break;
		case self::PACKAGE_CASE_UPPER:
			$package = strtoupper(self::PACKAGE);
			break;
		default:
			$package = strtolower(self::PACKAGE);
			break;
		}

		return $package;
	}

/**
 * Return all or part of the URL of the current script.
 *
 * @param int $mode One of the <var>URL_MODE_XXX</var> predefined constants defined in this class
 * @param string $filename If this is not empty then the returned script name is forced to be this filename.
 */
	public static /*.string.*/ function getURL($mode = self::URL_MODE_PATH, $filename = self::PACKAGE) {
		$portInteger = array_key_exists('SERVER_PORT', $_SERVER) ? (int) $_SERVER['SERVER_PORT'] : 0;

		if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on') {
			$protocolType = 'https';
		} else if (array_key_exists('SERVER_PROTOCOL', $_SERVER)) {
			$protocolType = strtolower(self::strleft($_SERVER['SERVER_PROTOCOL'], self::URL_SEPARATOR, self::STRLEFT_MODE_ALL));
		} else if ($portInteger = 443) {
			$protocolType = 'https';
		} else {
			$protocolType = 'http';
		}

		if ($portInteger === 0) $portInteger = ($protocolType === 'https') ? 443 : 80;

		// Protocol
		if ($mode & self::URL_MODE_PROTOCOL) {
			$protocol = ($mode === self::URL_MODE_PROTOCOL) ? $protocolType : "$protocolType://";
		} else {
			$protocol = '';
		}

		// Host
		if ($mode & self::URL_MODE_HOST) {
			$host = array_key_exists('HTTP_HOST', $_SERVER) ? self::strleft($_SERVER['HTTP_HOST'], ':', self::STRLEFT_MODE_ALL) : '';
		} else {
			$host = '';
		}

		// Port
		if ($mode & self::URL_MODE_PORT) {
			$port = (string) $portInteger;

			if ($mode !== self::URL_MODE_PORT)
				$port = (($protocolType === 'http' && $portInteger === 80) || ($protocolType === 'https' && $portInteger === 443)) ? '' : ":$port";
		} else {
			$port = '';
		}

		// Path
		if ($mode & self::URL_MODE_PATH) {
			$includePath	= __FILE__;
			$scriptPath	= realpath($_SERVER['SCRIPT_FILENAME']);

			if (DIRECTORY_SEPARATOR !== self::URL_SEPARATOR) {
				$includePath	= (string) str_replace(DIRECTORY_SEPARATOR, self::URL_SEPARATOR , $includePath);
				$scriptPath	= (string) str_replace(DIRECTORY_SEPARATOR, self::URL_SEPARATOR , $scriptPath);
			}

/*
echo "<pre>\n"; // debug
echo "\$_SERVER['SCRIPT_FILENAME'] = " . $_SERVER['SCRIPT_FILENAME'] . "\n"; // debug
echo "\$_SERVER['SCRIPT_NAME'] = " . $_SERVER['SCRIPT_NAME'] . "\n"; // debug
echo "dirname(\$_SERVER['SCRIPT_NAME']) = " . dirname($_SERVER['SCRIPT_NAME']) . "\n"; // debug
echo "\$includePath = $includePath\n"; // debug
echo "\$scriptPath = $scriptPath\n"; // debug
//echo self::array_to_HTML(); // debug
echo "</pre>\n"; // debug
*/

			$start	= strpos(strtolower($scriptPath), strtolower($_SERVER['SCRIPT_NAME']));
			$path	= ($start === false) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(substr($includePath, $start));
			$path	.= self::URL_SEPARATOR . $filename;
		} else {
			$path = '';
		}

		return $protocol . $host . $port . $path;
	}

/**
 * Convert a DocBlock to HTML (see http://java.sun.com/j2se/javadoc/writingdoccomments/index.html)
 *
 * @param string $docBlock Some PHP code containing a valid DocBlock.
 */
	public static /*.string.*/ function docBlock_to_HTML(/*.string.*/ $php) {
		$package	= self::getPackage(self::PACKAGE_CASE_CAMEL);
		$eol		= "\r\n";
		$tagStart	= strpos($php, "/**$eol * ");

		if ($tagStart === false) return 'Development version';

		// Get summary and long description
		$tagStart	+= 8;
		$tagEnd		= strpos($php, $eol, $tagStart);
		$summary	= substr($php, $tagStart, $tagEnd - $tagStart);
		$tagStart	= $tagEnd + 7;
		$tagPos		= strpos($php, "$eol * @") + 2;
		$description	= substr($php, $tagStart, $tagPos - $tagStart - 7);
		$description	= str_replace(' * ', '' , $description);

		// Get tags and values from DocBlock
		do {
			$tagStart	= $tagPos + 4;
			$tagEnd		= strpos($php, "\t", $tagStart);
			$tag		= substr($php, $tagStart, $tagEnd - $tagStart);
			$offset		= $tagEnd + 1;
			$tagPos		= strpos($php, $eol, $offset);
			$value		= substr($php, $tagEnd + 1, $tagPos - $tagEnd - 1);
			$$tag		= htmlspecialchars($value);
			$tagPos		= strpos($php, " * @", $offset);
		} while ($tagPos);

		// Add some links
		// 1. License
		if (isset($license) && strpos($license, '://')) {
			$tagPos	= strpos($license, ' ');
			$license	= '<a href="' . substr($license, 0, $tagPos) . '">' . substr($license, $tagPos + 1) . '</a>';
		}

		// 2. Author
		if (isset($author) && preg_match('/&lt;.+@.+&gt;/', $author) > 0) {
			$tagStart	= strpos($author, '&lt;') + 4;
			$tagEnd		= strpos($author, '&gt;', $tagStart);
			$author		= '<a href="mailto:' . substr($author, $tagStart, $tagEnd - $tagStart) . '">' . substr($author, 0, $tagStart - 5) . '</a>';
		}

		// 3. Link
		if (isset($link) && strpos($link, '://')) {
			$link		= '<a href="' . $link . '">' . $link . '</a>';
		}

		// Build the HTML
		$html = <<<HTML
	<h1>$package</h1>
	<h2>$summary</h2>
	<pre>$description</pre>
	<hr />
	<table>

HTML;

		if (isset($version))	$html .= "\t\t<tr><td>Version</td><td>$version</td></tr>\n";
		if (isset($copyright))	$html .= "\t\t<tr><td>Copyright</td><td>$copyright</td></tr>\n";
		if (isset($license))	$html .= "\t\t<tr><td>License</td><td>$license</td></tr>\n";
		if (isset($author))	$html .= "\t\t<tr><td>Author</td><td>$author</td></tr>\n";
		if (isset($link))	$html .= "\t\t<tr><td>Link</td><td>$link</td></tr>\n";

		$html .= "	</table>\n";
		return $html;
	}

/**
 * Return file contents as a string. Fail silently if the file can't be opened.
 *
 * The parameters are the same as the built-in PHP function {@link http://www.php.net/file_get_contents file_get_contents}
 */
	public static /*.string.*/ function getFileContents(/*.string.*/ $filename, /*.int.*/ $flags = 0, /*.object.*/ $context = NULL, /*.int.*/ $offset = -1, /*.int.*/ $maxlen = -1) {
		// From the documentation of file_get_contents:
		// Note: The default value of maxlen is not actually -1; rather, it is an internal PHP value which means to copy the entire stream until end-of-file is reached. The only way to specify this default value is to leave it out of the parameter list.
		if ($maxlen = -1) {
			$contents = file_get_contents($filename, $flags, $context, $offset);
		} else {
			$contents = file_get_contents($filename, $flags, $context, $offset, $maxlen);
		}

		if ($contents === false) $contents = '';
		return $contents;
	}

/**
 * Return the name of the index file (e.g. <var>index.php</var>) from a folder
 *
 * @param string $folder The folder to look for the index file. If not a folder or no index file can be found then an empty string is returned.
 */
	public static /*.string.*/ function findIndexFile(/*.string.*/ $folder) {
		if (!is_dir($folder)) return '';
		$filelist = array('index.php', 'index.pl', 'index.cgi', 'index.asp', 'index.shtml', 'index.html', 'index.htm', 'default.php', 'default.pl', 'default.cgi', 'default.asp', 'default.shtml', 'default.html', 'default.htm', 'home.php', 'home.pl', 'home.cgi', 'home.asp', 'home.shtml', 'home.html', 'home.htm');

		foreach ($filelist as $filename) {
			$target = $folder . DIRECTORY_SEPARATOR . $filename;
			if (is_file($target)) return $target;
		}

		return '';
	}

/**
 * Return the name of the target file from a string that might be a directory or just a basename without a suffix. If it's a directory then look for an index file in the directory.
 *
 * @param string $target The file to look for or folder to look in. If no file can be found then an empty string is returned.
 */
	public static /*.string.*/ function findTarget(/*.string.*/ $target) {
		// Is it actually a file? If so, look no further
		if (is_file($target)) return $target;

		// Added in version 1.7
		// Is it a basename? i.e. can we find $target.html or something?
		$suffixes = array('shtml', 'html', 'php', 'pl', 'cgi', 'asp', 'htm');

		foreach ($suffixes as $suffix) {
			$filename = "$target.$suffix";
			if (is_file($filename)) return $filename;
		}

		// Otherwise, let's assume it's a directory and try to find an index file in that directory
		return self::findIndexFile($target);
	}

/**
 * Make a unique ID based on the current date and time
 */
	public static /*.string.*/ function makeId() {
// Note could also try this: return md5(uniqid(mt_rand(), true));
		list($usec, $sec) = explode(" ", (string) microtime());
		return base_convert($sec, 10, 36) . base_convert((string) mt_rand(0, 35), 10, 36) . str_pad(base_convert(($usec * 1000000), 10, 36), 4, '_', STR_PAD_LEFT);
	}

/**
 * Make a unique hash key from a string (usually an ID)
 */
	public static /*.string.*/ function makeUniqueKey(/*.string.*/ $id) {
		return hash(self::HASH_FUNCTION, $_SERVER['REQUEST_TIME'] . $id);
	}

/**
 * Check that an email address conforms to RFC5322 and other RFCs
 *
 * @param boolean $checkDNS If true then a DNS check for A and MX records will be made
 * @param boolean $diagnose If true then return an integer error number rather than true or false
 */
	public static /*.mixed.*/ function is_email (/*.string.*/ $email, $checkDNS = false, $diagnose = false) {
		// Check that $email is a valid address. Read the following RFCs to understand the constraints:
		// 	(http://tools.ietf.org/html/rfc5322)
		// 	(http://tools.ietf.org/html/rfc3696)
		// 	(http://tools.ietf.org/html/rfc5321)
		// 	(http://tools.ietf.org/html/rfc4291#section-2.2)
		// 	(http://tools.ietf.org/html/rfc1123#section-2.1)

		if (!defined('ISEMAIL_VALID')) {
			define('ISEMAIL_VALID'			, 0);
			define('ISEMAIL_TOOLONG'		, 1);
			define('ISEMAIL_NOAT'			, 2);
			define('ISEMAIL_NOLOCALPART'		, 3);
			define('ISEMAIL_NODOMAIN'		, 4);
			define('ISEMAIL_ZEROLENGTHELEMENT'	, 5);
			define('ISEMAIL_BADCOMMENT_START'	, 6);
			define('ISEMAIL_BADCOMMENT_END'		, 7);
			define('ISEMAIL_UNESCAPEDDELIM'		, 8);
			define('ISEMAIL_EMPTYELEMENT'		, 9);
			define('ISEMAIL_UNESCAPEDSPECIAL'	, 10);
			define('ISEMAIL_LOCALTOOLONG'		, 11);
			define('ISEMAIL_IPV4BADPREFIX'		, 12);
			define('ISEMAIL_IPV6BADPREFIXMIXED'	, 13);
			define('ISEMAIL_IPV6BADPREFIX'		, 14);
			define('ISEMAIL_IPV6GROUPCOUNT'		, 15);
			define('ISEMAIL_IPV6DOUBLEDOUBLECOLON'	, 16);
			define('ISEMAIL_IPV6BADCHAR'		, 17);
			define('ISEMAIL_IPV6TOOMANYGROUPS'	, 18);
			define('ISEMAIL_TLD'			, 19);
			define('ISEMAIL_DOMAINEMPTYELEMENT'	, 20);
			define('ISEMAIL_DOMAINELEMENTTOOLONG'	, 21);
			define('ISEMAIL_DOMAINBADCHAR'		, 22);
			define('ISEMAIL_DOMAINTOOLONG'		, 23);
			define('ISEMAIL_TLDNUMERIC'		, 24);
			define('ISEMAIL_DOMAINNOTFOUND'		, 25);
			define('ISEMAIL_NOTDEFINED'		, 99);
		}

		// the upper limit on address lengths should normally be considered to be 256
		// 	(http://www.rfc-editor.org/errata_search.php?rfc=3696)
		// 	NB I think John Klensin is misreading RFC 5321 and the the limit should actually be 254
		// 	However, I will stick to the published number until it is changed.
		//
		// The maximum total length of a reverse-path or forward-path is 256
		// characters (including the punctuation and element separators)
		// 	(http://tools.ietf.org/html/rfc5321#section-4.5.3.1.3)
		$emailLength = strlen($email);
		if ($emailLength > 256)			return $diagnose ? ISEMAIL_TOOLONG	: false;	// Too long

		// Contemporary email addresses consist of a "local part" separated from
		// a "domain part" (a fully-qualified domain name) by an at-sign ("@").
		// 	(http://tools.ietf.org/html/rfc3696#section-3)
		$atIndex = strrpos($email,'@');

		if ($atIndex === false)			return $diagnose ? ISEMAIL_NOAT		: false;	// No at-sign
		if ($atIndex === 0)			return $diagnose ? ISEMAIL_NOLOCALPART	: false;	// No local part
		if ($atIndex === $emailLength - 1)	return $diagnose ? ISEMAIL_NODOMAIN	: false;	// No domain part
	// revision 1.14: Length test bug suggested by Andrew Campbell of Gloucester, MA

		// Sanitize comments
		// - remove nested comments, quotes and dots in comments
		// - remove parentheses and dots from quoted strings
		$braceDepth	= 0;
		$inQuote	= false;
		$escapeThisChar	= false;

		for ($i = 0; $i < $emailLength; ++$i) {
			$char = $email[$i];
			$replaceChar = false;

			if ($char === '\\') {
				$escapeThisChar = !$escapeThisChar;	// Escape the next character?
			} else {
				switch ($char) {
				case '(':
					if ($escapeThisChar) {
						$replaceChar = true;
					} else {
						if ($inQuote) {
							$replaceChar = true;
						} else {
							if ($braceDepth++ > 0) $replaceChar = true;	// Increment brace depth
						}
					}

					break;
				case ')':
					if ($escapeThisChar) {
						$replaceChar = true;
					} else {
						if ($inQuote) {
							$replaceChar = true;
						} else {
							if (--$braceDepth > 0) $replaceChar = true;	// Decrement brace depth
							if ($braceDepth < 0) $braceDepth = 0;
						}
					}

					break;
				case '"':
					if ($escapeThisChar) {
						$replaceChar = true;
					} else {
						if ($braceDepth === 0) {
							$inQuote = !$inQuote;	// Are we inside a quoted string?
						} else {
							$replaceChar = true;
						}
					}

					break;
				case '.':	// Dots don't help us either
					if ($escapeThisChar) {
						$replaceChar = true;
					} else {
						if ($braceDepth > 0) $replaceChar = true;
					}

					break;
				default:
				}

				$escapeThisChar = false;
	//			if ($replaceChar) $email[$i] = 'x';	// Replace the offending character with something harmless
	// revision 1.12: Line above replaced because PHPLint doesn't like that syntax
				if ($replaceChar) $email = (string) substr_replace($email, 'x', $i, 1);	// Replace the offending character with something harmless
			}
		}

		$localPart	= substr($email, 0, $atIndex);
		$domain		= substr($email, $atIndex + 1);
		$FWS		= "(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t]+)|(?:[ \\t]+(?:(?:\\r\\n)[ \\t]+)*))";	// Folding white space
		// Let's check the local part for RFC compliance...
		//
		// local-part      =       dot-atom / quoted-string / obs-local-part
		// obs-local-part  =       word *("." word)
		// 	(http://tools.ietf.org/html/rfc5322#section-3.4.1)
		//
		// Problem: need to distinguish between "first.last" and "first"."last"
		// (i.e. one element or two). And I suck at regexes.
		$dotArray	= /*. (array[int]string) .*/ preg_split('/\\.(?=(?:[^\\"]*\\"[^\\"]*\\")*(?![^\\"]*\\"))/m', $localPart);
		$partLength	= 0;

		foreach ($dotArray as $element) {
			// Remove any leading or trailing FWS
			$element	= preg_replace("/^$FWS|$FWS\$/", '', $element);
			$elementLength	= strlen($element);

			if ($elementLength === 0)								return $diagnose ? ISEMAIL_ZEROLENGTHELEMENT	: false;	// Can't have empty element (consecutive dots or dots at the start or end)
	// revision 1.15: Speed up the test and get rid of "unitialized string offset" notices from PHP

			// We need to remove any valid comments (i.e. those at the start or end of the element)
			if ($element[0] === '(') {
				$indexBrace = strpos($element, ')');
				if ($indexBrace !== false) {
					if (preg_match('/(?<!\\\\)[\\(\\)]/', substr($element, 1, $indexBrace - 1)) > 0) {
														return $diagnose ? ISEMAIL_BADCOMMENT_START	: false;	// Illegal characters in comment
					}
					$element	= substr($element, $indexBrace + 1, $elementLength - $indexBrace - 1);
					$elementLength	= strlen($element);
				}
			}

			if ($element[$elementLength - 1] === ')') {
				$indexBrace = strrpos($element, '(');
				if ($indexBrace !== false) {
					if (preg_match('/(?<!\\\\)(?:[\\(\\)])/', substr($element, $indexBrace + 1, $elementLength - $indexBrace - 2)) > 0) {
														return $diagnose ? ISEMAIL_BADCOMMENT_END	: false;	// Illegal characters in comment
					}
					$element	= substr($element, 0, $indexBrace);
					$elementLength	= strlen($element);
				}
			}

			// Remove any leading or trailing FWS around the element (inside any comments)
			$element = preg_replace("/^$FWS|$FWS\$/", '', $element);

			// What's left counts towards the maximum length for this part
			if ($partLength > 0) $partLength++;	// for the dot
			$partLength += strlen($element);

			// Each dot-delimited component can be an atom or a quoted string
			// (because of the obs-local-part provision)
			if (preg_match('/^"(?:.)*"$/s', $element) > 0) {
				// Quoted-string tests:
				//
				// Remove any FWS
				$element = preg_replace("/(?<!\\\\)$FWS/", '', $element);
				// My regex skillz aren't up to distinguishing between \" \\" \\\" \\\\" etc.
				// So remove all \\ from the string first...
				$element = preg_replace('/\\\\\\\\/', ' ', $element);
				if (preg_match('/(?<!\\\\|^)["\\r\\n\\x00](?!$)|\\\\"$|""/', $element) > 0)	return $diagnose ? ISEMAIL_UNESCAPEDDELIM	: false;	// ", CR, LF and NUL must be escaped, "" is too short
			} else {
				// Unquoted string tests:
				//
				// Period (".") may...appear, but may not be used to start or end the
				// local part, nor may two or more consecutive periods appear.
				// 	(http://tools.ietf.org/html/rfc3696#section-3)
				//
				// A zero-length element implies a period at the beginning or end of the
				// local part, or two periods together. Either way it's not allowed.
				if ($element === '')								return $diagnose ? ISEMAIL_EMPTYELEMENT	: false;	// Dots in wrong place

				// Any ASCII graphic (printing) character other than the
				// at-sign ("@"), backslash, double quote, comma, or square brackets may
				// appear without quoting.  If any of that list of excluded characters
				// are to appear, they must be quoted
				// 	(http://tools.ietf.org/html/rfc3696#section-3)
				//
				// Any excluded characters? i.e. 0x00-0x20, (, ), <, >, [, ], :, ;, @, \, comma, period, "
				if (preg_match('/[\\x00-\\x20\\(\\)<>\\[\\]:;@\\\\,\\."]/', $element) > 0)	return $diagnose ? ISEMAIL_UNESCAPEDSPECIAL	: false;	// These characters must be in a quoted string
			}
		}

		if ($partLength > 64) return $diagnose ? ISEMAIL_LOCALTOOLONG	: false;	// Local part must be 64 characters or less

		// Now let's check the domain part...

		// The domain name can also be replaced by an IP address in square brackets
		// 	(http://tools.ietf.org/html/rfc3696#section-3)
		// 	(http://tools.ietf.org/html/rfc5321#section-4.1.3)
		// 	(http://tools.ietf.org/html/rfc4291#section-2.2)
		if (preg_match('/^\\[(.)+]$/', $domain) === 1) {
			// It's an address-literal
			$addressLiteral = substr($domain, 1, strlen($domain) - 2);
			$matchesIP	= array();

			// Extract IPv4 part from the end of the address-literal (if there is one)
			if (preg_match('/\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $addressLiteral, $matchesIP) > 0) {
				$index = strrpos($addressLiteral, $matchesIP[0]);

				if ($index === 0) {
					// Nothing there except a valid IPv4 address, so...
					return $diagnose ? ISEMAIL_VALID : true;
				} else {
					// Assume it's an attempt at a mixed address (IPv6 + IPv4)
					if ($addressLiteral[$index - 1] !== ':')	return $diagnose ? ISEMAIL_IPV4BADPREFIX	: false;	// Character preceding IPv4 address must be ':'
					if (substr($addressLiteral, 0, 5) !== 'IPv6:')	return $diagnose ? ISEMAIL_IPV6BADPREFIXMIXED	: false;	// RFC5321 section 4.1.3

					$IPv6		= substr($addressLiteral, 5, ($index ===7) ? 2 : $index - 6);
					$groupMax	= 6;
				}
			} else {
				// It must be an attempt at pure IPv6
				if (substr($addressLiteral, 0, 5) !== 'IPv6:')		return $diagnose ? ISEMAIL_IPV6BADPREFIX	: false;	// RFC5321 section 4.1.3
				$IPv6 = substr($addressLiteral, 5);
				$groupMax = 8;
			}

			$groupCount	= preg_match_all('/^[0-9a-fA-F]{0,4}|\\:[0-9a-fA-F]{0,4}|(.)/', $IPv6, $matchesIP);
			$index		= strpos($IPv6,'::');

			if ($index === false) {
				// We need exactly the right number of groups
				if ($groupCount !== $groupMax)				return $diagnose ? ISEMAIL_IPV6GROUPCOUNT	: false;	// RFC5321 section 4.1.3
			} else {
				if ($index !== strrpos($IPv6,'::'))			return $diagnose ? ISEMAIL_IPV6DOUBLEDOUBLECOLON : false;	// More than one '::'
				$groupMax = ($index === 0 || $index === (strlen($IPv6) - 2)) ? $groupMax : $groupMax - 1;
				if ($groupCount > $groupMax)				return $diagnose ? ISEMAIL_IPV6TOOMANYGROUPS	: false;	// Too many IPv6 groups in address
			}

			// Check for unmatched characters
			array_multisort($matchesIP[1], SORT_DESC);
			if ($matchesIP[1][0] !== '')					return $diagnose ? ISEMAIL_IPV6BADCHAR		: false;	// Illegal characters in address

			// It's a valid IPv6 address, so...
			return $diagnose ? ISEMAIL_VALID : true;
		} else {
			// It's a domain name...

			// The syntax of a legal Internet host name was specified in RFC-952
			// One aspect of host name syntax is hereby changed: the
			// restriction on the first character is relaxed to allow either a
			// letter or a digit.
			// 	(http://tools.ietf.org/html/rfc1123#section-2.1)
			//
			// NB RFC 1123 updates RFC 1035, but this is not currently apparent from reading RFC 1035.
			//
			// Most common applications, including email and the Web, will generally not
			// permit...escaped strings
			// 	(http://tools.ietf.org/html/rfc3696#section-2)
			//
			// the better strategy has now become to make the "at least one period" test,
			// to verify LDH conformance (including verification that the apparent TLD name
			// is not all-numeric)
			// 	(http://tools.ietf.org/html/rfc3696#section-2)
			//
			// Characters outside the set of alphabetic characters, digits, and hyphen MUST NOT appear in domain name
			// labels for SMTP clients or servers
			// 	(http://tools.ietf.org/html/rfc5321#section-4.1.2)
			//
			// RFC5321 precludes the use of a trailing dot in a domain name for SMTP purposes
			// 	(http://tools.ietf.org/html/rfc5321#section-4.1.2)
			$dotArray	= /*. (array[int]string) .*/ preg_split('/\\.(?=(?:[^\\"]*\\"[^\\"]*\\")*(?![^\\"]*\\"))/m', $domain);
			$partLength	= 0;
			$element	= ''; // Since we use $element after the foreach loop let's make sure it has a value
	// revision 1.13: Line above added because PHPLint now checks for Definitely Assigned Variables

			if (count($dotArray) === 1)					return $diagnose ? ISEMAIL_TLD	: false;	// Mail host can't be a TLD (cite? What about localhost?)

			foreach ($dotArray as $element) {
				// Remove any leading or trailing FWS
				$element	= preg_replace("/^$FWS|$FWS\$/", '', $element);
				$elementLength	= strlen($element);

				// Each dot-delimited component must be of type atext
				// A zero-length element implies a period at the beginning or end of the
				// local part, or two periods together. Either way it's not allowed.
				if ($elementLength === 0)				return $diagnose ? ISEMAIL_DOMAINEMPTYELEMENT	: false;	// Dots in wrong place
	// revision 1.15: Speed up the test and get rid of "unitialized string offset" notices from PHP

				// Then we need to remove all valid comments (i.e. those at the start or end of the element
				if ($element[0] === '(') {
					$indexBrace = strpos($element, ')');
					if ($indexBrace !== false) {
						if (preg_match('/(?<!\\\\)[\\(\\)]/', substr($element, 1, $indexBrace - 1)) > 0) {
											return $diagnose ? ISEMAIL_BADCOMMENTSTART	: false;	// Illegal characters in comment
						}
						$element	= substr($element, $indexBrace + 1, $elementLength - $indexBrace - 1);
						$elementLength	= strlen($element);
					}
				}

				if ($element[$elementLength - 1] === ')') {
					$indexBrace = strrpos($element, '(');
					if ($indexBrace !== false) {
						if (preg_match('/(?<!\\\\)(?:[\\(\\)])/', substr($element, $indexBrace + 1, $elementLength - $indexBrace - 2)) > 0)
											return $diagnose ? ISEMAIL_BADCOMMENTEND	: false;	// Illegal characters in comment

						$element	= substr($element, 0, $indexBrace);
						$elementLength	= strlen($element);
					}
				}

				// Remove any leading or trailing FWS around the element (inside any comments)
				$element = preg_replace("/^$FWS|$FWS\$/", '', $element);

				// What's left counts towards the maximum length for this part
				if ($partLength > 0) $partLength++;	// for the dot
				$partLength += strlen($element);

				// The DNS defines domain name syntax very generally -- a
				// string of labels each containing up to 63 8-bit octets,
				// separated by dots, and with a maximum total of 255
				// octets.
				// 	(http://tools.ietf.org/html/rfc1123#section-6.1.3.5)
				if ($elementLength > 63)				return $diagnose ? ISEMAIL_DOMAINELEMENTTOOLONG	: false;	// Label must be 63 characters or less

				// Any ASCII graphic (printing) character other than the
				// at-sign ("@"), backslash, double quote, comma, or square brackets may
				// appear without quoting.  If any of that list of excluded characters
				// are to appear, they must be quoted
				// 	(http://tools.ietf.org/html/rfc3696#section-3)
				//
				// If the hyphen is used, it is not permitted to appear at
				// either the beginning or end of a label.
				// 	(http://tools.ietf.org/html/rfc3696#section-2)
				//
				// Any excluded characters? i.e. 0x00-0x20, (, ), <, >, [, ], :, ;, @, \, comma, period, "
				if (preg_match('/[\\x00-\\x20\\(\\)<>\\[\\]:;@\\\\,\\."]|^-|-$/', $element) > 0) {
											return $diagnose ? ISEMAIL_DOMAINBADCHAR	: false;
				}
			}

			if ($partLength > 255) 						return $diagnose ? ISEMAIL_DOMAINTOOLONG	: false;	// Domain part must be 255 characters or less (http://tools.ietf.org/html/rfc1123#section-6.1.3.5)

			if (preg_match('/^[0-9]+$/', $element) > 0)			return $diagnose ? ISEMAIL_TLDNUMERIC		: false;	// TLD can't be all-numeric (http://www.apps.ietf.org/rfc/rfc3696.html#sec-2)

			// Check DNS?
			if ($checkDNS && function_exists('checkdnsrr')) {
				if (!(checkdnsrr($domain, 'A') || checkdnsrr($domain, 'MX'))) {
											return $diagnose ? ISEMAIL_DOMAINNOTFOUND	: false;	// Domain doesn't actually exist
				}
			}
		}

		// Eliminate all other factors, and the one which remains must be the truth.
		// 	(Sherlock Holmes, The Sign of Four)
		return $diagnose ? ISEMAIL_VALID : true;
	}
}
// End of class MyPages_common

/**
 * This class needs a description
 *
 * @package MyPages
 */
interface I_MyPages extends I_MyPages_common {
	// Constants
	const	REQUEST			= 'request',

		// Built-in controllers
		CONTROLLER_ABOUT	= 'about',
		CONTROLLER_CSS		= 'css',
		CONTROLLER_ICON		= 'icon',
		CONTROLLER_FEED		= 'feed',
		CONTROLLER_GET		= 'get',
		CONTROLLER_SET		= 'set',
		CONTROLLER_SOURCE	= 'source',
		CONTROLLER_TRANSFER	= 'transfer',

		RESULT_UNDEFINED	= 0,
		RESULT_SUCCESS		= 1,
		RESULT_UNKNOWNACTION	= 4,
		RESULT_NOACTION		= 5,
		RESULT_NOSESSION	= 6,
		RESULT_NOSESSIONCOOKIES	= 7,
		RESULT_STORAGEERR	= 8,
		RESULT_EMAILERR		= 9,

		// Miscellaneous constants
		DELIMITER_SPACE		= ' ',
		STRING_TRUE		= 'true',
		STRING_FALSE		= 'false';

	// Methods
//	public static /*.MyPages.*/	function getSessionObject();
	public static /*.void.*/	function fatalError(/*.int.*/ $errorCode);
	public static /*.void.*/	function doActions(/*.array[string]string.*/ $actions);
}

/**
 * This class needs a description
 *
 * @package MyPages
 */
class MyPages extends MyPages_common implements I_MyPages {
// ---------------------------------------------------------------------------
// Functions for sending stuff to the browser
// ---------------------------------------------------------------------------
	private static /*.string.*/ function containerHeader() {return self::getPackage() . '-container';}

	private static /*.void.*/ function sendContent(/*.string.*/ $content, $contentType = 'text/html', $filename = '') {
		// Send headers first
		if (!headers_sent()) {
			header('Package: ' . self::getPackage(self::PACKAGE_CASE_CAMEL));
			header("Content-type: $contentType");

			if (strToLower($contentType) === 'application/octet-stream') {
				header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
				header('Content-Transfer-Encoding: binary');
			}
		}

		// Send content
		echo $content;

/* Comment out profiling statements if not needed
		// Send profiling data as a comment
		global $myPages_profile;

		if (count($myPages_profile) > 0) {
			$myPages_profile['response'] = myPages_time();

			if ($contentType === 'text/javascript' || $contentType === 'text/css') {
				$commentStart	= '/' . '*';
				$commentEnd	= '*' . '/';
			} else {
				$commentStart	= '<!--';
				$commentEnd	= '-->';
			}

			echo "\n$commentStart\n";
			$previous = reset($myPages_profile);

			while (list($key, $value) = each($myPages_profile)) {
				$elapsed	= round($value - $previous, 4);
				$previous	= $value;
				echo "$key\t$value\t$elapsed\n";
			}

			echo "$commentEnd\n";
		}
*/
	}

	private static /*.string.*/ function evalTemplate(/*.string.*/ $mypages_html, /*.array[string]string.*/ $mypages_variables) {
		extract($mypages_variables, EXTR_OVERWRITE | EXTR_PREFIX_ALL, self::getPackage());
		unset($mypages_variables);

// Wanna see what template variables you can use?		// debug
// $vars = get_defined_vars();					// debug
// $mypages_content .= self::array_to_HTML($vars);		// debug

		eval('$mypages_html = "' . (string) str_replace('"', '\\"', $mypages_html) . '";');
		return $mypages_html;
	}
/**
 * Returns a per-session object to hold state for the current user's session
 */
//	public static /*.MyPages.*/ function getSessionObject() {
//		if (!array_key_exists(self::getPackage(), $_SESSION)) $_SESSION[self::getPackage()] = new MyPages();
//		return /*.(MyPages).*/ $_SESSION[self::getPackage()];
//	}

	private static /*.string.*/ function getTemplate($filename = '') {
		// Look for index.html in the views folder
		if ($filename === '') $filename = self::findIndexFile('views');

		// Use the specified template or the built-in one
		if (is_file($filename))
			$template = self::getFileContents($filename);
		else
			$template = <<<GENERATED
<!DOCTYPE html>
<html>

<head>
	<link rel="shortcut icon" href="\$mypages_icon" />
	<link href="\$mypages_css" rel="stylesheet" type="text/css" />
	<title>\$mypages_title</title>
</head>

<body>\$mypages_content</body>

</html>

GENERATED;
// Generated code - do not modify in built package

		return $template;
	}

	private static /*.string.*/ function getContent(/*.string.*/ $content, $tv = array(), $sendToBrowser = true) {
		$template	= self::getTemplate();	// To do: find a way to specify the template (part of the whole settings issue)
		$URL		= self::getURL(self::URL_MODE_ALL, self::getPackage() . '.php');

		// Get <meta> tags from content
		$prefix		= self::getPackage(self::PACKAGE_CASE_CAMEL) . '.';
		$prefixLength	= strlen($prefix);
		$meta		= self::meta_to_array($content);

		if (is_array($meta) && array_key_exists('name', $meta)) {
			foreach ($meta['name'] as $key => $value) {
				if (substr($key, 0, $prefixLength) === $prefix) {
					$key = substr($key, $prefixLength);
					if (!isset($tv[$key])) $tv[$key] = $value;
				}
			}
		}

		// Get settings for any variables not yet set
		// To do: the whole settings thing

		// Use defaults for any variables not set any other way
		$key = 'title';		if (!isset($tv[$key])) $tv[$key] = self::getInnerHTML($content, 'title');
		$key = 'content';	if (!isset($tv[$key])) $tv[$key] = self::getInnerHTML($content, 'body');
		$key = 'css';		if (!isset($tv[$key])) $tv[$key] = $URL . '?css';
		$key = 'icon';		if (!isset($tv[$key])) $tv[$key] = $URL . '?icon';

		$html = self::evalTemplate($template, $tv);

		if ($sendToBrowser) {self::sendContent($html); return '';} else return $html;
	}

	private static /*.string.*/ function getView(/*.string.*/ $view, $tv = array(), $sendToBrowser = true) {
		$html = self::getContent(self::getFileContents($view), $tv, false);
		if ($sendToBrowser) {self::sendContent($html); return '';} else return $html;
	}

/**
 * Display an error page
 *
 * @param int errorCode The error number
 * @param string extraHTML additional HTML to include in the output
 */
	public static /*.void.*/ function fatalError(/*.int.*/ $errorCode, $extraHTML = '') {
		header('HTTP', true, $errorCode);
		$viewName	= self::getPackage() . "_$errorCode.html";
		$view		= self::findTarget('pages' . DIRECTORY_SEPARATOR . $viewName); // Look for bespoke error page

		if (empty($view)) {
			$package = self::getPackage(self::PACKAGE_CASE_CAMEL);
			$content = <<<HTML
<title>404 Not Found</title>
<meta name="$package.current_menu_href" content="404" />
<body>I couldn't find this page: $extraHTML</body>
HTML;
			self::getContent($content);
		} else {
			self::getView($view, array('error_details' => $extraHTML));
		}

		exit;
	}

// ---------------------------------------------------------------------------
// Template, CSS & Javascript
// ---------------------------------------------------------------------------
	private static /*.string.*/ function getStyleSheet($filename = '', $sendToBrowser = true) {
		if (is_file($filename)) {
			$css = self::getFileContents($filename);
		} else {
			$css = <<<GENERATED
@charset "utf-8";
* {
	padding:0px;
	margin:0px;
	font-family:"Segoe UI", Tahoma, Geneva, Verdana;
}

body {
	margin:1em;
}

h2, h3, h4, h5, h6 {
	margin:1em 0 0.5em 0;
}

table {
	border-style:none;
	border-collapse:collapse;
}

td {
	padding:3px;
	border:1px solid #FFFFFF;
	vertical-align:top;
	background-color:#CCCCCC;
	font-size:small;
}

hr {
	margin:1em 0 0.5em 0;
}

pre {
	font-family:Consolas, "Courier New", Courier, monospace;
	font-size:small;
}
GENERATED;
// Generated code - do not modify in built package
		}

		if ($sendToBrowser) {self::sendContent($css, 'text/css'); return '';} else return $css;
	}

	private static /*.string.*/ function getJavascript($filename = '', $sendToBrowser = true) {
		if (is_file($filename)) {
			$js = self::getFileContents($filename);
		} else {
			$js = /*:inline js start:*/ '';
			// This code will be modified by the build process
			$filename = self::getPackage() . '.js';
			eval('$js = "' . (string) str_replace('"', '\\"', self::getFileContents($filename)) . '";'); /*:inline js end:*/
		}

		if ($sendToBrowser) {self::sendContent($js, '', 'text/javascript'); return '';} else return $js;
	}

	private static /*.string.*/ function getIcon($filename = '', $sendToBrowser = true) {
		if (is_file($filename)) {
			$icon = self::getFileContents($filename);
		} else {
			$icon = base64_decode('AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANCwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ+QAAAAAAAAAAAAAAAAAAAADQsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP0AAAAAAAAAAAAAAAAAAAAA0LCQ1dCwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJDRAAAAAAAAAAAAAAAAAAAAANCwkDfQsJDc0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJD/0LCQ/9CwkP/QsJDM0LCQLAAAAAAAAAAAAAAAAAAAAAAAAAAA0LCQBdCwkGvQsJDr0LCQ/9CwkP/QsJD/0LCQ/9CwkObQsJBc0LCQAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA0LCQEdCwkOnQsJD/0LCQ/9CwkOTQsJAOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADRr5BL0LCQiNCwkIjRr5BHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANu2kgfPsZC20LCQ79CwkHHQsJF40LCR8M+wkLG/v4AEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADRsI9U0LCQ/9CwkP/QsJD00LCQ99CwkP/QsJD/0K6QTAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAz6+PMNCwkP/TtJb/6NjJ6ufXxu3StJX/0LCQ/82ujykAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AdGvj2nQsJC+7+PX0///////////7N/Sz9CwkMHQsZBs////AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANGyj0LQsJD/0LCQ//Ho3/L//////////+/k2fHQsJD/0LCQ/9Gyj0IAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADPsZBV0LCQ/9CwkP/awqnv+vf07fn28+vZwKbu0LCQ/9CwkP/RsI9UAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA1aqABs+wkKHQsJHZ0LCQs9CwkP/QsJD/0LCQstCwkNfQsZCczJmZBQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANCwkKHQsJD/0LCQ/9CwkKEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADQsJAtz6+QyM+vkMjQsJAtAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwAOsQcADrEHAA6xBwAOsQeAHrEH4H6xB/D+sQfAPrEHwD6xB8A+sQeAHrEHgB6xB4AesQeAHrEH8P6xB/D+sQQ==');	// Generated code - do not modify in built package
		}

		if ($sendToBrowser) {self::sendContent($icon, 'image/x-icon'); return '';} else return $icon;
	}

	private static /*.string.*/ function transferFile($actions, $sendToBrowser = true) {
		$request	= (array_key_exists(self::REQUEST, $actions)) ? $actions[self::REQUEST] : key($actions);
		$slashPos	= strpos($request, self::URL_SEPARATOR);

		if ($slashPos)			$filename = substr($request, $slashPos + 1);
		if (!is_file($filename))	return '';

		$content	= self::getFileContents($filename);

		if ($sendToBrowser) {self::sendContent($content, 'application/octet-stream', $filename); return '';} else return $content;
	}

	private	static /*.string.*/ function getAbout($sendToBrowser = true) {
		$php	= self::getFileContents(self::getPackage() . '.php', 0, NULL, -1, 4096);
		$body	= self::docBlock_to_HTML($php);
		$html	= self::getContent('<title>' . self::getPackage(self::PACKAGE_CASE_CAMEL) . " - About</title><body>$body</body>");

		if ($sendToBrowser) {self::sendContent($html); return '';} else return $html;
	}

	private	static /*.string.*/ function getSourceCode($sendToBrowser = true) {
		$html = (string) highlight_file(__FILE__, true);
		if ($sendToBrowser) {self::sendContent($html); return '';} else return $html;
	}

/**
 * Performs one or more actions
 *
 * To perform more than one action, specify them in the condensed format
 *
 * <pre>
 *     mypages.php?foo1+foo2+foo3=bar1+bar2+bar3
 * </pre>
 *
 * or the extended format
 *
 * <pre>
 *     mypages.php?action=foo1+foo2+foo3&id=bar1+bar2+bar3
 * </pre>
 *
 * By the time it reaches this function they will be in an array with the '+'
 * delimiters replaced with spaces by the magic of PHP. So if you're calling
 * this function with your own parameters you would specify them like this:
 *
 * <pre>
 *     [$actions] => Array
 *        (
 *            [action] => foo1 foo2 foo3
 *            [id] => bar1 bar2 bar3
 *        )
 * </pre>
 *
 * Each space-delimited element in the <kbd>action</kbd> member of the {@link $actions}
 * array will be performed.
 *
 * @param array $actions Same format as {@link http://www.php.net/$_GET $_GET} (which is where it usually comes from)
 */
	public static /*.void.*/ function doActions(/*.array[string]string.*/ $actions) {
		$request	= (array_key_exists(self::REQUEST, $actions)) ? $actions[self::REQUEST] : key($actions);
		$page		= self::findTarget('pages' . DIRECTORY_SEPARATOR . $request);

		if (empty($page)) {
			// Look for a bespoke controller
			$controllerName	= self::strleft($request, self::URL_SEPARATOR, self::STRLEFT_MODE_ALL);
			$controller	= self::findTarget('controllers' . DIRECTORY_SEPARATOR . $request);

			if (empty($controller)) {
				// Call the built-in controller
				switch (strtolower($controllerName)) {
				case self::CONTROLLER_ABOUT:	self::getAbout();		break;
				case self::CONTROLLER_CSS:	self::getStyleSheet();		break;
				case self::CONTROLLER_ICON:	self::getIcon();		break;
				case self::CONTROLLER_TRANSFER:	self::transferFile($actions);	break;
				case self::CONTROLLER_SOURCE:	self::getSourceCode();		break;
				default:
					self::fatalError(404, self::array_to_HTML($actions));
				}
			} else {
				// Call the bespoke controller
//				self::getBespokeController($controller);
				include $controller;
				die;
			}
		} else {
			// Deliver the page
			$extension = pathinfo($page, PATHINFO_EXTENSION);

			switch ($extension) {
			case 'css':	self::getStylesheet($page);	break;
			case 'ico':	self::getIcon($page);		break;
			case 'js':	self::getJavascript($page);	break;
			default:	self::getView($page);		break;
			}
		}
	}
}
// End of class MyPages


// Some code to make this all automagic
//
// If you want more control over how MyPages works then you might need to amend
// or even remove the code below here

// There may already be a session in progress. We will use the existing
// session if possible.
//if ((int) ini_get('session.use_cookies') === 0)
//	MyPages::fatalError(MyPages::RESULT_NOSESSIONCOOKIES);
//else
//	if (!isset($_SESSION) || !is_array($_SESSION)) session_start();

//$myPages = MyPages::getSessionObject();

// Is this script included in another page or is it the HTTP target itself?
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
	// This script has been called directly by the client

	// $_GET is our friend
	if (!is_array($_GET) || 0 === count($_GET)) $_GET[MyPages::REQUEST] = MyPages::CONTROLLER_ABOUT;
	MyPages::doActions(/*.(array[string]string).*/ $_GET);
}
?>