<?php

/*
* Licensed under MIT License.
*
* Copyright (C) 2014 André von Kugland <kugland@gmail.com>
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files(the "Software"),
* to deal in the Software without restriction, including without limitation
* the rights to use, copy, modify, merge, publish, distribute, sublicense,
* and/or sell copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
* DEALINGS IN THE SOFTWARE.
*/

mb_internal_encoding('UTF-8');

class StringPhp implements JsonSerializable, Serializable {

	public $s;

	// -----------------------------[ Constructor and magic methods ]----------------------------- //

	// Constructor
	function __construct($str = '', $encoding = 'UTF-8') {
		$this->s = ($encoding == 'UTF-8')
			? "$str"
			: mb_convert_encoding($str, 'UTF-8', $encoding);
	}

	// Converts to string.
	function __toString() {
		return $this->s;
	}


	// -------------------------------[ JsonSerializable interface ]------------------------------ //

	// JsonSerializable::JsonSerialize()
	function JsonSerialize() {
		return $this->s;
	}


	// ---------------------------------[ Serializable interface ]-------------------------------- //

	// Serializable::serialize()
	function serialize() {
		return serialize($this->s);
	}

	// Serializable::unserialize($serialized)
	function unserialize($serialized) {
		$this->s = unserialize($serialized);
	}


	// --------------------------------------[ Conversions ]-------------------------------------- //

	// Creates an instance of S from a array of chars.
	static function fromCharArray(array $array) {
		return S('')->join($array);
	}

	// Creates an instance of S from a given Unicode point.
	static function fromCharCode($code) {
		return S(pack('N', $code), 'UCS-4BE');
	}

	// Creates an instance of S from a given list of Unicode points.
	static function fromCharCodeArray(array $list) {
		return S(call_user_func_array('pack',	array_merge(['N*'], $list)), 'UCS-4BE');
	}

	// Creates an instance of S from a Json object.
	static function fromJson($json) {
		return S(json_decode($json));
	}

	// Converts a number to a string.
	static function fromNumber($number, $base=10, $zeroPad=0) {
		return S(base_convert("$number", 10, $base))->padLeft($zeroPad, '0')->lower();
	}

	// Converts string to an array of chars.
	function toCharArray() {
		$result = $this->splitRegex("/(?<!^)(?!$)/u");
		return $result[0] !== ''
			? $result
			: array();
	}

	// Converts string to an array of Unicode points.
	function toCharCodeArray() {
		return array_merge(unpack('N*', mb_convert_encoding($this->s, 'UCS-4BE', 'UTF-8')));
	}

	// Encodes a string as a Json object.
	function toJson() {
		return json_encode($this);
	}

	// Converts a string to a regex that matches it.
	function toRegex() {
		return self::makeRegex(array($this));
	}


	// -------------------------------------[ Basic methods ]------------------------------------- //

	// Capitalizes the first character of a string.
	function capitalize() {
		return $this->slice(0, 1)->title()->concat($this->slice(1));
	}

	// Gets the nth character of the string.
	function charAt($n) {
		return mb_substr($this->s, $n, 1);
	}

	// Gets the Unicode point of the nth character of the string.
	function charCodeAt($n) {
		return unpack('N', mb_convert_encoding($this->charAt($n), 'UCS-4BE', 'UTF-8'))[1];
	}

	// Compares two strings.
	function cmp($str) {
		return strcmp($this->s, $str);
	}

	// Concatenates two strings.
	function concat($str) {
		return S($this->s . $str);
	}

	// Returns true if string contains $substr.
	function contains($substr) {
		return $this->indexOf($substr) != -1;
	}

	// Returns a copy of this object.
	function copy() {
		return S($this);
	}

	// Counts the occurrences of $substr in string.
	function count($substr) {
		return substr_count($this->s, "$substr");
	}

	// Returns true if the string ends with a given suffix.
	function endsWith($suffix) {
		// No need to use mb_ functions.
		return substr($this->s, -strlen("$suffix")) === "$suffix";
	}

	// Ensures string starts with prefix.
	function ensureLeft($prefix) {
		return $this->startsWith($prefix) ? $this : $this->prefix($prefix);
	}

	// Ensures string ends with suffix.
	function ensureRight($suffix) {
		return $this->endsWith($suffix) ? $this : $this->concat($suffix);
	}

	// Returns true if strings are equal, false otherwise.
	function equals($str) {
		return $this->s === "$str";
	}

	// Compares two strings without case sensitivity.
	function icmp($str) {
		// Use lower because there are unicode precomposed characters which
		// don’t have an uppercase equivalent.
		return $this->lower()->cmp(S($str)->lower());
	}

	// Returns true if strings are equal, false otherwise, ignoring case.
	function iequals($str) {
		// Use lower because there are unicode precomposed characters which
		// don’t have an uppercase equivalent.
		return $this->lower()->equals(S($str)->lower());
	}

	// Finds position of the first occurrence of a substr, or -1 if not found.
	function indexOf($substr, $start = 0) {
		$index = mb_strpos($this->s, $substr, $start);
		return $index !== false ? $index : -1;
	}

	// Joins array using string as glue.
	function join(array $arr) {
		return S(implode($this->s, $arr));
	}

	// Finds position of the last occurrence of a substr, or -1 if not found.
	function lastIndexOf($substr, $end = null) {
		$substrlen = mb_strlen($substr);
		$end = $end !== null ? $end : $substrlen;
		$index = mb_strrpos($this->left($end + $substrlen)->s, $substr, 0);
		return $index !== false ? $index : -1;
	}

	// Returns the substring denoted by n positive left-most characters.
	function left($size) {
		return $size >= 0 ? $this->slice(0, $size) : $this->right(-$size);
	}

	// Returns the length of the string object.
	function length() {
		return mb_strlen($this->s);
	}

	// Splits lines into an array of native strings.
	function lines($limit = -1, $noEmpty = false) {
		return $this->split("\n", $limit, $noEmpty);
	}

	private static $lowerTable = array(
		"\304\262" => "\304\263", "\307\204" => "\307\206",
		"\307\205" => "\307\206", "\307\207" => "\307\211",
		"\307\210" => "\307\211", "\307\212" => "\307\214",
		"\307\213" => "\307\214", "\307\261" => "\307\263",
		"\307\262" => "\307\263", "\341\272\236" => "\303\237"
	);

	// Converts a string to lowercase.
	function lower() {
		return S(mb_convert_case($this->replaceMany(self::$lowerTable), MB_CASE_LOWER));
	}

	// Adds a prefix to string.
	function prefix($str) {
		return S($str . $this->s);
	}

	// Replaces substrings.
	function replace($oldstr, $newstr, $limit = -1) {
		if ($limit == -1) {
			// If $limit == -1, then do a simple and fast str_replace.
			return S(str_replace($oldstr, $newstr, $this->s));
		} else {
			// I used this rather fancy way to replace a string because
			// 1) str_replace doesn't have a limit parameter, and
			// 2) preg_replace would have problems with \1, \2 &c.
			return $this->replaceRegexCallback(
				S($oldstr)->toRegex(),
				function ($matches) use ($newstr) {
					return $newstr;
				},
				$limit
			);
		}
	}

	// Replaces substrings according to a given table.
	function replaceMany(array $table, $limit = -1) {
		return $this->replaceRegexCallback(
			self::makeRegex(array_keys($table)),
			function ($matches) use ($table) {
				return $table[$matches[0]];
			},
			$limit
		);
	}

	// Returns the substring denoted by n positive right-most characters.
	function right($size) {
		return $size >= 0 ? $this->slice(-$size) : $this->left(-$size);
	}

	// Returns a slice from the string.
	function slice($begin, $size = null) {
		return S(mb_substr($this->s, $begin, $size));
	}

	// Returns true if the string starts with prefix.
	function startsWith($prefix) {
		return substr($this->s, 0, strlen($prefix)) === $prefix;
	}

	// Converts a string to an array using a string delimiter.
	function split($pattern, $limit = -1, $noEmpty = false) {
		return $this->splitRegex(S($pattern)->toRegex(), $limit, $noEmpty);
	}

	// Removes matches of a string.
	function strip($str, $limit = -1) {
		return $this->replace($str, '', $limit);
	}

	// Returns a string repeated n times.
	function times($count) {
		return S(str_repeat($this->s, $count));
	}

	private static $titleTable = array(
		"\307\263" => "\307\262", "\307\206" => "\307\205",
		"\304\263" => "\304\262", "\307\211" => "\307\210",
		"\307\214" => "\307\213"
	);

	// Converts a string to titlecase.
	function title() {
		return S(mb_convert_case($this->upper()->lower()->replaceMany(self::$titleTable)->s, MB_CASE_TITLE));
	}

	// Translates the characters of $set1 into characters of $set2.
	function translate($set1, $set2) {
		if (mb_strlen($set1) == mb_strlen($set2)) {
			$table = array();
			for ($i = 0; $i < mb_strlen($set1); $i++) {
				$table[S($set1)->charAt($i)] = S($set2)->charAt($i);
			}
			return $this->replaceMany($table);
		} else {
			throw new RuntimeException('S::translate(): Lengths of $set1 and $set2 differ');
		}
	}

	private static $upperTable = array(
		"\303\237" => "\123\123", "\304\263" => "\304\262", "\307\205" => "\307\204",
		"\307\206" => "\307\204", "\307\210" => "\307\207", "\307\211" => "\307\207",
		"\307\213" => "\307\212", "\307\214" => "\307\212", "\307\262" => "\307\261",
		"\307\263" => "\307\261", "\357\254\200" => "\106\106",
		"\357\254\201" => "\106\111", "\357\254\202" => "\106\114",
		"\357\254\203" => "\106\106\111", "\357\254\204" => "\106\106\114",
		"\357\254\205" => "\123\124", "\357\254\206" => "\123\124"
	);

	// Converts a string to uppercase.
	function upper() {
		return S(mb_convert_case($this->replaceMany(self::$upperTable), MB_CASE_UPPER));
	}


	// --------------------------------------[ Test methods ]------------------------------------- //

	// Tests if string is alphanumeric.
	function isAlnum($unicode = true) {
		return $this->countRegex("/^[[:alnum:]]+$/".($unicode ? 'u' : '')) === 1;
	}

	// Tests if string is alphabetic.
	function isAlpha($unicode = true) {
		return $this->countRegex("/^[[:alpha:]]+$/".($unicode ? 'u' : '')) === 1;
	}

	// Tests if string is entirely made of ASCII chars.
	function isAscii() {
		return $this->countRegex("/^[[:ascii:]]+$/") === 1;
	}

	// Tests if string is entirely made of horizontal spaces.
	function isBlank($unicode = true) {
		return $this->countRegex("/^[[:blank:]]+$/".($unicode ? 'u' : '')) === 1;
	}

	// Tests if string is entirely made of control characters.
	function isCntrl() {
		return $this->countRegex("/^[[:cntrl:]]+$/") === 1;
	}

	// Tests if string is empty.
	function isEmpty() {
		return $this->s === '';
	}

	// Tests if string can be converted to floating point number.
	function isFloat() {
		return gettype($this->s + 0) === 'double' || $this->isInteger();
	}

	// Tests if string can be converted to integer.
	function isInteger() {
		return gettype($this->s + 0) === 'integer';
	}

	// Tests if string is lowercase.
	function isLower($unicode = true) {
		return $this->countRegex("/^[[:lower:]]+$/".($unicode ? 'u' : '')) === 1;
	}

	// Tests if string is a number.
	function isNumber($unicode = true) {
		return $this->isAlnum($unicode) && !$this->isAlpha($unicode);
	}

	// Tests if string is punctuation.
	function isPunct($unicode = true) {
		//echo $this->countRegex("/^[[:punct:]]+$/".($unicode ? 'u' : ''));
		return $this->countRegex("/^[[:punct:]]+$/".($unicode ? 'u' : '')) === 1;
	}

	// Tests if string is uppercase.
	function isUpper($unicode = true) {
		return $this->countRegex("/^[[:upper:]]+$/".($unicode ? 'u' : '')) === 1;
	}


	// ----------------------------------[ Whitespace functions ]--------------------------------- //

	// Converts all adjacent whitespace characters to a single space.
	function collapseWhitespace() {
		return $this->trim()->replaceRegex('/\s+/u', ' ');
	}

	// Converts line breaks to <br />
	function nlToBr($xhtml = true) {
		return $this->replaceRegex("/\r?\n/", $xhtml ? "<br>" : "<br />");
	}

	// Center pads the string.
	function padCenter($len, $char = ' ') {
		$oldlen = $this->length();
		$padlen = ($len - $oldlen) / 2.0;
		return $oldlen < $len
			? S($char)->times(ceil($padlen))->concat($this)->concat(S($char)->times(floor($padlen)))
			: $this;
	}

	// Left pads the string.
	function padLeft($len, $char = ' ') {
		$oldlen = $this->length();
		return $oldlen < $len ? S($char)->times($len - $oldlen)->concat($this) : $this;
	}

	// Right pads the string.
	function padRight($len, $char = ' ') {
		$oldlen = $this->length();
		return $oldlen < $len ? S($char)->times($len - $oldlen)->prefix($this->s) : $this;
	}

	// Converts LF to CR LF.
	function toDos() {
		return $this->replaceRegex("/\r?\n/", "\r\n");
	}

	// Converts CR LF to LF.
	function toUnix() {
		return $this->replace("\r\n", "\n");
	}

	// Returns the string with leading and trailing whitespace removed.
	function trim() {
		return $this->replaceRegex('/^[\s​]+|[\s​]+$/u', '');
	}

	// Returns the string leading and whitespace removed.
	function trimLeft() {
		return $this->replaceRegex('/^[\s​]+/u', '');
	}

	// Returns the string with trailing whitespace removed.
	function trimRight() {
		return $this->replaceRegex('/[\s​]+$/u', '');
	}


	// -------------------------------[ Regular expression methods ]------------------------------ //

	// Counts the occurrences of $regex in string.
	function countRegex($regex) {
		$matches = $this->matchAll($regex);
		return ($matches === false) ? 0 : count($matches);
	}

	// Creates a regex that matches all given values.
	private static function makeRegex(array $array) {
		// TODO: Test support for Unicode points.
		return '/'.implode('|', array_map(function ($k) { return preg_quote($k, '/'); }, $array)).'/';
	}

	// Matches a string against $regex.
	function match($regex) {
		$matches = [];
		if (preg_match($regex, $this->s, $matches) !== false) {
			return $matches;
		} else {
			self::throwRegexError();
		}
	}

	// Matches all occurences of $regex.
	function matchAll($regex) {
		$matches = null;
		if (preg_match_all($regex, $this->s, $matches, PREG_SET_ORDER) !== false) {
			return $matches;
		} else {
			self::throwRegexError();
		}
		return $matches;
	}

	// Matches all occurences of $regex and calls a callback on each one.
	function matchAllCallback($regex, $callback) {
		foreach ($this->matchAll($regex) as $index => $matches) {
			$callback($index, $matches);
		}
	}

	// Replaces substrings using regex.
	function replaceRegex($regex, $subst, $limit = -1) {
		$result = preg_replace($regex, "$subst", $this->s, $limit);
		if ($result !== null) {
			return S($result);
		} else {
			self::throwRegexError();
		}
	}

	// Replaces substrings using a regex and a callback.
	function replaceRegexCallback($regex, $callback, $limit = -1) {
		$result = preg_replace_callback($regex, $callback, $this->s, $limit);
		if ($result !== null) {
			return S($result);
		} else {
			self::throwRegexError();
		}
	}

	// Convert a string to an array using a regex delimiter.
	function splitRegex($regex, $limit = -1, $noEmpty = false) {
		$result = preg_split($regex, $this->s, $limit, $noEmpty ? PREG_SPLIT_NO_EMPTY : 0);
		if ($result !== false) {
			return $result;
		} else {
			self::throwRegexError();
		}
	}

	// Removes matches of a regex.
	function stripRegex($regex, $limit = -1) {
		return $this->replaceRegex($regex, '', $limit);
	}

	// Throw a regex error
	private static function throwRegexError() {
		throw new RuntimeException("preg error");
	}

}

// Create a new StringPhp object.
function S($str, $encoding = 'UTF-8') {
	return new StringPhp($str, $encoding);
}
