<?php

/* -*- tab-width: 2 -*- */

/*
* Licensed under MIT License.
*
* Copyright (C) 2013 André von Kugland <kugland@gmail.com>
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

//require_once "underscore.php";

mb_internal_encoding('UTF-8');

class S implements Serializable
/*, JsonSerializable */
{
	public $s; // Value of the string.

	// -------------------------------------[ Constructor ]------------------------------------- //

	/**
	* Constructor.
	*/
	function __construct($str, $encoding = 'UTF-8')
	{
		if ($encoding == 'UTF-8') {
			$this->s = "$str";
		} else {
			$this->s = mb_convert_encoding($str, 'UTF-8', $encoding);
		}
	}

	// ------------------------------------[ Serialization ]------------------------------------ //

	/**
	* Implementation of JsonSerializable::JsonSerialize().
	*/
	public function JsonSerialize()
	{
		return $this->s;
	}

	/**
	* Implementation of Serializable::serialize().
	*/
	public function serialize()
	{
		return serialize($this->s);
	}

	/**
	* Implementation of Serializable::unserialize($serialized).
	*/
	public function unserialize($serialized)
	{
		$this->s = unserialize($serialized);
	}

	// ----------------------------------[ Other constructors ]--------------------------------- //

	/**
	* Creates an instance of S from a array of chars.
	*
	* Examples:
	*   S::fromCharArray(['道','德','經'])->s === '道德經'
	*/
	static function fromCharArray(array $array)
	{
		return S('')->join($array);
	}

	/**
	* Creates an instance of S from a given Unicode point.
	*
	* Examples:
	*   S::fromCharCode(0x9053)->s === '道'
	*/
	static function fromCharCode($code)
	{
		return S(pack('N', $code), 'UCS-4BE');
	}

	/**
	* Creates an instance of S from a given list of Unicode points.
	*
	* Examples:
	*   S::fromCharCodeArray(array(0x9053, 0x5fb7, 0x7d93))->s === '道德經'
	*/
	static function fromCharCodeArray(array $list)
	{
		return S(call_user_func_array('pack',	array_merge(array('N*'), $list)), 'UCS-4BE');
	}

	/**
	* Creates an instance of S from a Json object.
	*
	* Examples:
	*   S::fromJson('"áéíóú"')->s === 'áéíóú'
	*/
	static function fromJson($json)
	{
		return S(json_decode($json));
	}

	/**
	* Converts a number to a string.
	*
	* Examples:
	*   S::fromNumber(0x0abc, 16, 4)->s === '0abc'
	*/
	static function fromNumber($number, $base=10, $zeroPad=0)
	{
		return S(base_convert("$number", 10, $base))->padLeft($zeroPad, '0')->lower();
	}

	// ==================================[ Conversion functions ]==================================

	/**
	* Returns string value.
	*
	* Examples:
	*   (string) S('a') === 'a'
	*/
	public function __toString()
	{
		return $this->s;
	}

	/**
	* Encodes a string as a Json object.
	*
	* Examples:
	*    S('abc')->toJson() === '"abc"'
	*/
	function toJson()
	{
		return json_encode($this);
	}

	/**
	* Converts a string to a regex that matches it.
	*
	* Examples:
	*   S('ab/cd')->toRegex() === "/ab\\/cd/"
	*/
	function toRegex()
	{
		return self::makeRegex(array($this));
	}

	// =================================[ Basic string functions ]=================================

	/**
	* Capitalizes the first character of a string.
	*
	* Examples:
	*   S('διονύσιος ὁ ἀρεοπαγίτες')->capitalize()->s === 'Διονύσιος ὁ ἀρεοπαγίτες'
	*/
	function capitalize()
	{
		return $this->slice(0, 1)->title()->concat($this->slice(1));
	}

	/**
	* Gets the nth character of the string.
	*
	* Examples:
	*   S('Достоевский')->charAt(10) === 'й'
	*/
	function charAt($n)
	{
		return mb_substr($this->s, $n, 1);
	}

	/**
	* Gets the Unicode point of the nth character of the string.
	*
	* Examples:
	*   S('Достоевский')->charCodeAt(10) === 0x0439
	*/
	function charCodeAt($n)
	{
		$tmp = unpack('N', mb_convert_encoding($this->charAt($n), 'UCS-4BE', 'UTF-8'));
		return $tmp[1];
	}

	/**
	* Compares two strings.
	*
	* Examples:
	*   S('001')->cmp('000')  > 0
	*   S('001')->cmp('001') == 0
	*   S('001')->cmp('002')  < 0
	*/
	function cmp($str)
	{
		return strcmp($this->s, $str);
	}

	/**
	* Concatenates two strings.
	*
	* Example:
	*   S('a')->concat('b')->s === 'ab'
	*/
	function concat($str)
	{
		return S($this->s . $str);
	}

	/**
	* Returns true if string contains $substr.
	*
	* Example:
	*   S('abcd')->contains('bc') === true
	*   S('abcd')->contains('ef') === false
	*/
	function contains($substr)
	{
		return $this->indexOf($substr) != -1;
	}

	/**
	* Returns a copy of this object.
	*
	* Examples:
	*   S('a')->copy()->s === 'a'
	*/
	function copy()
	{
		return S($this);
	}

	/**
	* Counts the occurrences of $substr in string.
	*
	* Examples:
	*   S('a,b,c,d')->count(',') === 3
	*/
	function count($substr)
	{
		return substr_count($this->s, "$substr");
	}

	/**
	* Returns true if the string ends with a given suffix.
	*
	* Examples:
	*   S('abcd')->endsWith('cd') === true
	*   S('abcd')->endsWith('bc') === false
	*/
	function endsWith($suffix)
	{
		// No need to use mb_ functions.
		return substr($this->s, -strlen("$suffix")) === "$suffix";
	}

	/**
	* Returns true if strings are equal, false otherwise.
	*
	* Examples:
	*   S('abcd')->equals('abcd') === true
	*   S('abcd')->equals('cd') === false
	*/
	function equals($str)
	{
		return $this->s === "$str";
	}

	/**
	* Compares two strings without case sensitivity.
	*
	* Examples:
	*   S('abcd')->icmp('ACDE')  < 0
	*   S('abcd')->icmp('ABCD') == 0
	*   S('acde')->icmp('ABCD')  > 0
	*/
	function icmp($str)
	{
		// Use lower because there are unicode precomposed characters which
		// don’t have an uppercase equivalent.
		return $this->lower()->cmp(S($str)->lower());
	}

	/**
	* Returns true if strings are equal, false otherwise, ignoring case.
	*
	* Examples:
	*   S('ÁéÍóÚ')->iequals('áéíóú') === true
	*/
	function iequals($str)
	{
		// Use lower because there are unicode precomposed characters which
		// don’t have an uppercase equivalent.
		return $this->lower()->equals(S($str)->lower());
	}

	/**
	* Finds position of the first occurrence of a substr, or -1 if not found.
	*
	* Examples:
	*   S('ÁéÍóÚ')->indexOf('é') === 1
	*   S('ÁéÍóÚ')->indexOf('z') === -1
	*/
	function indexOf($substr, $start = 0)
	{
		$index = mb_strpos($this->s, $substr, $start);
		return $index !== false ? $index : -1;
	}

	/**
	* Joins array using string as glue.
	*
	* Examples:
	*   S(',')->join(array('1', '2', '3'))->s === "1,2,3"
	*/
	function join(array $arr)
	{
		return S(implode($this->s, $arr));
	}

	/**
	* Finds position of the last occurrence of a substr, or -1 if not found.
	*
	* Examples:
	*   S('ÁéÍóÚ')->lastIndexOf('é') === 1
	*   S('ÁéÍóÚ')->lastIndexOf('z') === -1
	*/
	function lastIndexOf($substr, $end = null)
	{
		$substrlen = mb_strlen($substr);
		$end = $end !== null ? $end : $substrlen;
		$index = mb_strrpos($this->left($end + $substrlen)->s, $substr, 0);
		return $index !== false ? $index : -1;
	}

	/**
	* Returns the substring denoted by n positive left-most characters.
	*
	* Examples:
	*   S('aabb')->left( 2)->s === 'aa'
	*   S('aabb')->left(-2)->s === 'bb'
	*/
	function left($size)
	{
		return $size >= 0
			? $this->slice(0, $size)
			: $this->right(-$size);
	}

	/**
	* Returns the length of the string object.
	*
	* Examples:
	*   S('道德經')->length() === 3
	*/
	function length()
	{
		return mb_strlen($this->s);
	}

	/**
	* Splits lines into an array of native strings.
	*
	* Examples:
	*   S("a\nb\nc\n\nd")->lines() === array('a', 'b', 'c', '', 'd')
	*   S("a\nb\nc\n\nd")->lines(2) === array('a', "b\nc\n\nd")
	*   S("a\nb\nc\n\nd")->lines(-1, true) === array('a', 'b', 'c', 'd')
	*/
	function lines($limit = -1, $noEmpty = false)
	{
		return $this->split("\n", $limit, $noEmpty);
	}

	/**
	* Converts a string to lowercase.
	*
	* Examples:
	*   S('ǊAM')->lower()->s === 'ǌam'
	*/
	function lower()
	{
		return S(mb_convert_case($this->replaceMany(array(
			'ẞ' => 'ß', 'Ǳ' => 'ǳ', 'ǲ' => 'ǳ', 'Ǆ' => 'ǆ',
			'ǅ' => 'ǆ', 'Ĳ' => 'ĳ', 'Ǉ' => 'ǉ', 'ǈ' => 'ǉ',
			'Ǌ' => 'ǌ', 'ǋ' => 'ǌ'
		)), MB_CASE_LOWER));
	}

	/**
	* Adds a prefix to string.
	*
	* Examples:
	*   S('a')->prefix('_')->s === '_a'
	*/
	function prefix($str)
	{
		return S($str . $this->s);
	}

	/**
	* Replaces substrings.
	*
	* Examples:
	*   S('aaa')->replace('a', 'b')->s === 'bbb'
	*   S('aaa')->replace('a', 'b', 2)->s === 'bba'
	*/
	function replace($oldstr, $newstr, $limit = -1)
	{
		if ($limit == -1) {
			// If $limit == -1, then do a simple and fast str_replace.
			return S(str_replace($oldstr, $newstr, $this->s));
		} else {
			// I used this rather fancy way to replace a string because
			// 1) str_replace doesn't have a limit parameter, and
			// 2) preg_replace would have problems with \1, \2 &c.
			return $this->replaceRegexCallback(
				S($oldstr)->toRegex(),
				function ($matches) use ($newstr) { return $newstr; },
				$limit
			);
		}
	}

	/**
	* Replaces substrings according to a given table.
	*
	* Examples:
	*   S('ab')->replaceMany(array('a' => 'b', 'b' => 'a'))->s === 'ba'
	*/
	function replaceMany(array $table, $limit = -1)
	{
		return $this->replaceRegexCallback(
			S::makeRegex(array_keys($table)),
			function ($matches) use ($table) { return $table[$matches[0]]; },
			$limit
		);
	}

	/**
	* Returns the substring denoted by n positive right-most characters.
	*
	* Examples:
	*   S('aabb')->right( 2)->s === 'bb'
	*   S('aabb')->right(-2)->s === 'aa'
	*/
	function right($size)
	{
		return $size >= 0
			? $this->slice(-$size)
			: $this->left(-$size);
	}

	/**
	* Returns a slice from the string.
	*
	* Examples:
	*   S('aabb')->slice(0, 2)->s === 'aa'
	*   S('aabb')->slice(-3, 2)->s === 'ab'
	*   S('aabb')->slice(1, 2)->s === 'ab'
	*/
	function slice($begin, $size = null)
	{
		return S(mb_substr($this->s, $begin, $size));
	}

	/**
	* Returns true if the string starts with prefix.
	*
	* Examples:
	*   S('aabb')->startsWith('aa') === true
	*   S('aabb')->startsWith('bb') === false
	*/
	function startsWith($prefix)
	{
		return substr($this->s, 0, strlen($prefix)) === $prefix;
	}

	/**
	* Converts a string to an array using a string delimiter.
	*
	* Examples:
	*   S('a,b,c')->split(',') === array('a', 'b', 'c')
	*   S('a,b,c,')->split(',') === array('a', 'b', 'c', '')
	*   S('a,b,c,')->split(',', -1, true) === array('a', 'b', 'c')
	*/
	function split($pattern, $limit = -1, $noEmpty = false)
	{
		return $this->splitRegex(S($pattern)->toRegex(), $limit, $noEmpty);
	}

	/**
	* Returns a string repeated n times.
	*
	* Examples:
	*   S('a')->times(4)->s === 'aaaa'
	*/
	function times($count)
	{
		return S(str_repeat($this->s, $count));
	}

	/**
	* Converts a string to titlecase.
	*
	* Examples:
	*   S('ǌam')->title()->s === 'ǋam'
	*   S('ßam')->title()->s === 'Ssam'
	*/
	function title()
	{
		return S(mb_convert_case($this->upper()->lower()->replaceMany(array(
			'ǳ' => 'ǲ', 'ǆ' => 'ǅ', 'ĳ' => 'Ĳ', 'ǉ' => 'ǈ', 'ǌ' => 'ǋ'
		))->s, MB_CASE_TITLE));
	}

	/**
	* Translates the characters of $set1 into characters of $set2.
	*
	* Examples:
	*   S('ebg13 vf vafrpher')->translate('abefghprv', 'norstucei')->s === 'rot13 is insecure'
	*/
	function translate($set1, $set2)
	{
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

	/**
	* Converts a string to uppercase.
	*
	* Examples:
	*   S('ǌam')->upper()->s === 'ǊAM'
	*/
	function upper()
	{
		return S(mb_convert_case($this->replaceMany(array(
			'ß' => 'SS', 'ĳ' => 'Ĳ', 'ǅ' => 'Ǆ', 'ǆ' => 'Ǆ',
			'ǈ' => 'Ǉ', 'ǉ' => 'Ǉ', 'ǋ' => 'Ǌ', 'ǌ' => 'Ǌ',
			'ǲ' => 'Ǳ', 'ǳ' => 'Ǳ', 'ﬀ' => 'FF', 'ﬁ' => 'FI',
			'ﬂ' => 'FL', 'ﬃ' => 'FFI', 'ﬄ' => 'FFL', 'ﬅ' => 'ST',
			'ﬆ' => 'ST'
		)), MB_CASE_UPPER));
	}

	// =====================================[ Test functions ]=====================================

	/**
	* Tests if string is alphanumeric.
	*/
	function isAlnum($unicode = true)
	{
		return $this->matches("/^[[:alnum:]]+$/".($unicode ? 'u' : ''));
	}

	/**
	* Tests if string is alphabetic.
	*/
	function isAlpha($unicode = true)
	{
		return $this->matches("/^[[:alpha:]]+$/".($unicode ? 'u' : ''));
	}

	/**
	* Tests if string is entirely made of ASCII chars.
	*/
	function isAscii()
	{
		return $this->matches("/^[[:ascii:]]+$/");
	}

	/**
	* Tests if string is entirely made of horizontal spaces.
	*/
	function isBlank($unicode = true)
	{
		return $this->matches($unicode ? '/^[\p{Zs}|\t]+$/u' : '/^[[:blank:]]+$/');
	}

	/**
	* Tests if string is entirely made of control characters.
	*/
	function isCntrl()
	{
		return $this->matches("/^[[:cntrl:]]+$/");
	}

	/**
	* Tests if string is empty.
	*/
	function isEmpty()
	{
		return $this->s === '';
	}

	/**
	* Tests if string can be converted to floating point number.
	*/
	function isFloat()
	{
		return gettype($this->s + 0) === 'double';
	}

	/**
	* Tests if string can be converted to integer.
	*/
	function isInteger()
	{
		return gettype($this->s + 0) === 'integer';
	}

	/**
	* Tests if string is a number.
	*/
	function isNumber()
	{
		return $this->isInt() || $this->isFloat();
	}

	/**
	* Tests if string is lowercase.
	*/
	function isLower($unicode = true)
	{
		return $this->matches("/^[[:lower:]]+$/" . $unicode ? 'u' : '');
	}

	/**
	* Tests if string is punctuation.
	*/
	function isPunct($unicode = true)
	{
		return $this->matches("/^[[:punct:]]+$/" . $unicode ? 'u' : '');
	}

	/**
	* Tests if string is punctuation.
	*/
	function isUpper($unicode = true)
	{
		return $this->matches("/^[[:upper:]]+$/" . $unicode ? 'u' : '');
	}

	// ==================================[ Whitespace functions ]==================================

	/**
	* Converts all adjacent whitespace characters to a single space.
	*
	* Examples:
	*   S('a  b  c')->collapseWhitespace()->s === 'a b c'
	*   S("a \xc2\xa0 \t b \xc2\xa0 \t c")->collapseWhitespace()->s === 'a b c'
	*/
	function collapseWhitespace()
	{
		return $this->trim()->replaceRegex('/\s+/u', ' ');
	}

	/**
	* Center pads the string.
	*
	* Examples:
	*   S('abc')->padCenter(5)->s === ' abc '
	*/
	function padCenter($len, $char = ' ')
	{
		$oldlen = $this->length();
		$padlen = ($len - $oldlen) / 2.0;
		return $oldlen < $len
			? S($char)
					->times(ceil($padlen))
					->concat($this)
					->concat(S($char)->times(floor($padlen)))
			: $this;
	}

	/**
	* Left pads the string.
	*
	* Examples:
	*   S('abc')->padLeft(5)->s === '  abc'
	*/
	function padLeft($len, $char = ' ')
	{
		$oldlen = $this->length();
		return $oldlen < $len
			? S($char)->times($len - $oldlen)->concat($this)
			: $this;
	}

	/**
	* Right pads the string.
	*
	* Examples:
	*   S('abc')->padRight(5)->s === 'abc  '
	*/
	function padRight($len, $char = ' ')
	{
		$oldlen = $this->length();
		return $oldlen < $len
			? S($char)->times($len - $oldlen)->prefix($this->s)
			: $this;
	}

	/**
	* Converts LF to CR LF.
	*
	* Examples:
	*   S("a\nb")->toDos()->s === "a\r\nb"
	*   S("a\r\nb")->toDos()->s === "a\r\nb"
	*/
	function toDos()
	{
		return $this->replaceRegex("/\r?\n/", "\r\n");
	}

	/**
	* Converts CR LF to LF.
	*
	* Examples:
	*   S("a\r\nb")->toUnix()->s === "a\nb"
	*/
	function toUnix()
	{
		return $this->replace("\r\n", "\n");
	}

	/**
	* Returns the string with leading and trailing whitespace removed.
	*
	* Examples:
	*   S("\t\xc2\xa0 ab \xc2\xa0\t")->trim()->s === "ab"
	*   S("\t\xc2\xa0 \xc2\xa0\t")->trim()->s === ""
	*/
	function trim()
	{
		return $this->replaceRegex('/^[\s​]+|[\s​]+$/u', '');
	}

	/**
	* Returns the string leading and whitespace removed.
	*
	* Examples:
	*   S("\t\xc2\xa0 ab \xc2\xa0\t")->trimLeft()->s === "ab \xc2\xa0\t"
	*   S("\t\xc2\xa0 \xc2\xa0\t")->trimRight()->s === ''
	*/
	function trimLeft()
	{
		return $this->replaceRegex('/^[\s​]+/u', '');
	}

	/**
	* Returns the string with trailing whitespace removed.
	*
	* Examples:
	*   S("\t\xc2\xa0  ab \xc2\xa0\t")->trimRight()->s === "\t\xc2\xa0  ab"
	*   S("\t\xc2\xa0 \xc2\xa0\t")->trimRight()->s === ''
	*/
	function trimRight()
	{
		return $this->replaceRegex('/[\s​]+$/u', '');
	}

	// ==============================[ Regular expression functions ]==============================

	/**
	* Counts the occurrences of $regex in string.
	*
	* Examples:
	*   S('a,b;c:d')->countRegex('/[,;:]/') === 3
	*/
	function countRegex($regex)
	{
		$matches = $this->matchAll($regex);
		return ($matches === false) ? 0 : count($matches);
	}

	/**
	* Matches a string against $regex.
	*/
	function match($regex)
	{
		$matches = array();
		if (preg_match($regex, $this->s, $matches) !== false) {
			return $matches;
		} else {
			throw new RuntimeException('S::match(): preg_match error');
		}
	}

	/**
	* Matches all occurences of $regex.
	*/
	function matchAll($regex)
	{
		$matches = null;
		if (preg_match_all($regex, $this->s, $matches, PREG_SET_ORDER) !== false) {
			return $matches;
		} else {
			throw new RuntimeException('S::matchAll(): preg_match_all error');
		}
		return $matches;
	}

	/**
	* Matches all occurences of $regex and calls a callback on each one.
	*/
	function matchAllCallback($regex, $callback)
	{
		foreach ($this->matchAll($regex) as $index => $matches) {
			$callback($index, $matches);
		}
	}

	/**
	* Returns true if string matches $regex.
	*/
	function matches($regex)
	{
		$result = preg_match($regex, $this->s);
		if ($result !== false) {
			return $result == 1;
		} else {
			throw new RuntimeException('S::matches(): preg_match error');
		}
	}

	/**
	* Replaces substrings using regex.
	*/
	function replaceRegex($regex, $subst, $limit = -1)
	{
		$result = preg_replace($regex, "$subst", $this->s, $limit);
		if ($result !== null) {
			return S($result);
		} else {
			throw new RuntimeException('preg_replace error');
		}
	}

	/**
	* Replaces substrings using a regex and a callback.
	*/
	function replaceRegexCallback($regex, $callback, $limit = -1)
	{
		$result = preg_replace_callback($regex, $callback, $this->s, $limit);
		if ($result !== null) {
			return S($result);
		} else {
			throw new RuntimeException('preg_replace_callback error');
		}
	}

	/**
	* Convert a string to an array using a regex delimiter.
	*/
	function splitRegex($regex, $limit = -1, $noEmpty = false)
	{
		$result = preg_split($regex, $this->s, $limit, $noEmpty ? PREG_SPLIT_NO_EMPTY : 0);
		if ($result !== false) {
			return $result;
		} else {
			throw new RuntimeException('preg_replace error');
		}
	}

	// ====================================[ Misc formatting ]=====================================

	/**
	* Camel cases a string.
	*
	* Examples:
	*   S('André von Kugland')->camelize()->s === 'andréVonKugland';
	*   S('max-width')->camelize()->s === 'maxWidth';
	*   S('MAX_WIDTH')->camelize()->s === 'maxWidth';
	*   S('CamelCase')->camelize()->s === 'camelcase';
	*/
	function camelize()
	{
		return $this->lower()->replaceRegexCallback(
			'/[-_\s]+(\w)/u',
			function ($matches) {
				return S($matches[1])->upper()->s;
			}
		);
	}

	/**
	* Dasherizes a string.
	*
	* Examples:
	*   S('André von Kugland')->dasherize()->s === 'andré-von-kugland';
	*   S('max-width')->dasherize()->s === 'max-width';
	*   S('MAX_WIDTH')->dasherize()->s === 'max-width';
	*   S('CamelCase')->dasherize()->s === 'camel-case';
	*/
	function dasherize()
	{
		return $this
			->replaceRegex('/(.)(\p{Lu})/u', '\1-\2')
			->replaceRegex('/[-_\s]+/', '-')
			->replaceRegex('/-{2,}/', '-')
			->lower();
	}

	/**
	* Underscores a string.
	*
	* Examples:
	*   S('André von Kugland')->underscore()->s === 'ANDRÉ_VON_KUGLAND';
	*   S('CamelCase')->underscore()->s === 'CAMEL_CASE';
	*/
	function underscore()
	{
		return $this
			->replaceRegex('/(.)(\p{Lu})/u', '\1_\2')
			->replaceRegex('/\s+/', '_')
			->replaceRegex('/_{2,}/', '_')
			->upper();
	}

	// =====================================[ i18n functions ]=====================================

	/**
	* Compose characters with combining diacritics as precomposed characters.
	*
	* Example:
	*   S::fromCharCodeArray(array(0x0061, 0x0306))->composeUnicode()->charCodeAt(0) === 0x0103
	*/
	function composeUnicode()
	{
		if (function_exists("normalizer_normalize")) {
			return S(normalizer_normalize($this->s, Normalizer::FORM_KC));
		} else {
			throw new RuntimeException('S::composeUnicode(): Needs intl extension');
		}
	}

	/**
	* Decompose precomposed characters as characters with combining diacritics.
	*
	* Example:
	*   S('ă')->decomposeUnicode()->charCodeAt(1) === 0x0306
	*/
	function decomposeUnicode()
	{
		if (function_exists("normalizer_normalize")) {
			return S(normalizer_normalize($this->s, Normalizer::FORM_KD));
		} else {
			throw new RuntimeException('S::decomposeUnicode(): Needs intl extension');
		}
	}

	/**
	* Removes all diacritics from the input.
	*
	* Examples:
	*   S('ação')->removeDiacritics()->s === 'acao'
	*   S('Διονύσιος ὁ Ἀρεοπαγίτες')->removeDiacritics()->s === 'Διονυσιος ο Αρεοπαγιτες'
	*/
	function removeDiacritics()
	{
		return $this->decomposeUnicode()->replaceRegex('/\p{Mn}/u', '')->composeUnicode();
	}

	/**
	* Returns the width of the string.
	*
	* Examples:
	*   S('道德經')->width() === 6
	*   S('dàodéjīng')->width() === 9
	*/
	function width()
	{
		return mb_strwidth($this->s);
	}


	// -----------------------------------[ Misc formatting ]----------------------------------- //

	private static $transliterator = null;
	private static $transliteratorId = 'Any-Latin';

	static function setTransliteratorId($id)
	{
		if (self::$transliteratorId != $id)
			self::$transliterator = null;
		self::$transliteratorId = $id;
	}

	static function getTransliteratorId($id)
	{
		return self::$transliteratorId;
	}

	/**
	* Converts string to a string made entirely of lowercase letters, numbers and dashes.
	*
	* Examples:
	*   S('Διονύσιος ὁ Ἀρεοπαγίτης')->slugify(true)->s === 'dionysios-ho-areopagites'
	*/
	function slugify($transliterate = false)
	{
		if ($transliterate === true) {
			if (function_exists("transliterator_create")) {
				if (self::$transliterator === null)
					self::$transliterator = Transliterator::create(self::$transliteratorId);
			} else {
				throw new RuntimeException("S::slugify(): Needs intl extension for transliteration");
			}
		}
		$sObj = !$transliterate
			? $this
			: S(self::$transliterator->transliterate($this->s));
		return $sObj
			->lower()
			->removeDiacritics()
			->replaceRegex('/\p{P}/u', ' ')
			->replaceRegex('/[^a-z ]/u', '')
			->collapseWhitespace()
			->replaceRegex('/\s/', '-');
	}

	/**
	* Truncates the string, accounting for word placement and character count.
	*/
	function truncate($length, $ellipsis = '...')
	{
		return ($this->length() > $length)
			? $this
					->slice(0, $length + 1)
					->replaceRegex('/\s*\w*$/u', '')
					->concat($ellipsis)
			: $this;
	}

	/**
	* Creates a regex that matches all given values.
	*/
	private static function makeRegex(array $array)
	{
		// TODO: Test support for Unicode points.
		return '/'.implode(
				'|',
				array_map(
					function ($k) { return preg_quote($k, '/'); },
					$array
				)
			).'/';
	}

} /* End of class S. */

/**
* Create a new StringPhp object.
*/
function S($str, $encoding = 'UTF-8')
{
	return new S($str, $encoding);
}

