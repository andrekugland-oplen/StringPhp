<?php

//  Licensed under MIT License.
// 
//  Copyright (C) 2014 André von Kugland <kugland@gmail.com>
// 
//  Permission is hereby granted, free of charge, to any person obtaining
//  a copy of this software and associated documentation files(the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
// 
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
// 
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
//  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
//  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
//  DEALINGS IN THE SOFTWARE.

mb_internal_encoding('UTF-8');

class S implements Serializable, JsonSerializable
{

    private static $lowerTable = array(
        'ẞ' => 'ß', 'Ĳ' => 'ĳ', 'Ǆ' => 'ǆ', 'ǅ' => 'ǆ',
        'Ǉ' => 'ǉ', 'ǈ' => 'ǉ', 'Ǌ' => 'ǌ', 'ǋ' => 'ǌ',
        'Ǳ' => 'ǳ', 'ǲ' => 'ǳ'
    );

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Constructor and magical methods ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Constructor
    private static $titleTable = array(
        'ß' => 'Ss', 'ĳ' => 'Ĳ', 'ǆ' => 'ǅ', 'ǉ' => 'ǈ',
        'ǌ' => 'ǋ', 'ǳ' => 'ǲ', 'ﬀ' => 'Ff', 'ﬁ' => 'Fi',
        'ﬂ' => 'Fl', 'ﬃ' => 'Ffi', 'ﬄ' => 'Ffl', 'ﬅ' => 'St',
        'ﬆ' => 'St'
    );

    // Converts to string.
    private static $upperTable = array(
        'ß' => 'SS', 'ĳ' => 'Ĳ', 'ǅ' => 'Ǆ', 'ǆ' => 'Ǆ',
        'ǈ' => 'Ǉ', 'ǉ' => 'Ǉ', 'ǋ' => 'Ǌ', 'ǌ' => 'Ǌ',
        'ǲ' => 'Ǳ', 'ǳ' => 'Ǳ', 'ﬀ' => 'FF', 'ﬁ' => 'FI',
        'ﬂ' => 'FL', 'ﬃ' => 'FFI', 'ﬄ' => 'FFL', 'ﬅ' => 'ST',
        'ﬆ' => 'ST'
    );


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Serializable interface ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Serializable::serialize()
    public $s;

    // Serializable::unserialize($serialized)

    function __construct($str = '', $encoding = 'UTF-8')
    {
        $this->s = ($encoding == 'UTF-8')
            ? "$str"
            : mb_convert_encoding($str, 'UTF-8', $encoding);
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ JsonSerializable interface ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // JsonSerializable::JsonSerialize()

    static function fromCharArray(array $array)
    {
        return S('')->join($array);
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Conversions ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Creates an instance of S from a array of chars.

    function join(array $arr)
    {
        return S(implode($this->s, $arr));
    }

    // Creates an instance of S from a given Unicode point.

    static function fromCharCode($code)
    {
        return S(pack('N', $code), 'UCS-4BE');
    }

    // Creates an instance of S from a given list of Unicode points.
    static function fromCharCodeArray(array $list)
    {
        return S(call_user_func_array('pack', array_merge(array('N*'), $list)), 'UCS-4BE');
    }

    // Creates an instance of S from a Json object.
    static function fromJson($json)
    {
        return S(json_decode($json));
    }

    // Converts a number to a string.
    static function fromNumber($number, $base = 10, $zeroPad = 0)
    {
        return S(base_convert("$number", 10, $base))->padLeft($zeroPad, '0')->lower();
    }

    // Converts string to an array of chars.

    function lower()
    {
        return S(mb_convert_case($this->replaceMany(self::$lowerTable), MB_CASE_LOWER));
    }

    // Converts string to an array of Unicode points.

    function replaceMany(array $table, $limit = -1)
    {
        return $this->replaceRegexCallback(
            self::makeRegex(array_keys($table)),
            function ($matches) use ($table) {
                return $table[$matches[0]];
            },
            $limit
        );
    }

    // Encodes a string as a Json object.

    function replaceRegexCallback($regex, $callback, $limit = -1)
    {
        $result = preg_replace_callback($regex, $callback, $this->s, $limit);
        if ($result === null) {
            self::throwRegexError();
        }
        return S($result);
    }

    // Converts a string to a regex that matches it.

    private static function throwRegexError()
    {
        throw new RuntimeException("preg error");
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Basic methods ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Capitalizes the first character of a string.

    private static function makeRegex(array $array)
    {
        // TODO: Test support for Unicode points.
        return '/' . implode('|', array_map(function ($k) {
            return preg_quote($k, '/');
        }, $array)) . '/';
    }

    // Gets the nth character of the string.

    function padLeft($len, $char = ' ')
    {
        $oldlen = $this->length();
        return $oldlen < $len ? S($char)->times($len - $oldlen)->concat($this) : $this;
    }

    // Gets the Unicode point of the nth character of the string.

    function length()
    {
        return mb_strlen($this->s);
    }

    // Compares two strings.

    function concat($str)
    {
        return S($this->s . $str);
    }

    // Concatenates two strings.

    function times($count)
    {
        return S(str_repeat($this->s, $count));
    }

    // Returns true if string contains $substr.

    function __toString()
    {
        return $this->s;
    }

    // Returns a copy of this object.

    function serialize()
    {
        return serialize($this->s);
    }

    // Counts the occurrences of $substr in string.

    function unserialize($serialized)
    {
        $this->s = unserialize($serialized);
    }

    // Returns true if the string ends with a given suffix.

    function JsonSerialize()
    {
        return $this->s;
    }

    // Ensures string starts with prefix.

    function toCharArray()
    {
        $result = $this->splitRegex("/(?<!^)(?!$)/u");
        return $result[0] !== '' ? $result : array();
    }

    // Ensures string ends with suffix.

    function splitRegex($regex, $limit = -1, $noEmpty = false)
    {
        $result = preg_split($regex, $this->s, $limit, $noEmpty ? PREG_SPLIT_NO_EMPTY : 0);
        if ($result === false) {
            self::throwRegexError();
        }
        return $result;
    }

    // Returns true if strings are equal, false otherwise.

    function toCharCodeArray()
    {
        return array_merge(unpack('N*', mb_convert_encoding($this->s, 'UCS-4BE', 'UTF-8')));
    }

    // Compares two strings without case sensitivity.

    function toJson()
    {
        return json_encode($this);
    }

    // Returns true if strings are equal, false otherwise, ignoring case.

    function capitalize()
    {
        return $this->slice(0, 1)->title()->concat($this->slice(1));
    }

    // Finds position of the first occurrence of a substr, or -1 if not found.

    function title()
    {
        return S(mb_convert_case($this->lower()->replaceMany(self::$titleTable)->s, MB_CASE_TITLE));
    }

    // Joins array using string as glue.

    function slice($begin, $size = null)
    {
        return S(mb_substr($this->s, $begin, $size));
    }

    // Finds position of the last occurrence of a substr, or -1 if not found.

    function charCodeAt($n)
    {
        $tmp = unpack('N', mb_convert_encoding($this->charAt($n), 'UCS-4BE', 'UTF-8'));
        return $tmp[1];
    }

    // Returns the substring denoted by n positive left-most characters.

    function charAt($n)
    {
        return mb_substr($this->s, $n, 1);
    }

    // Returns the length of the string object.

    function contains($substr)
    {
        return $this->indexOf($substr) != -1;
    }

    // Splits lines into an array of native strings.

    function indexOf($substr, $start = 0)
    {
        $index = mb_strpos($this->s, $substr, $start);
        return $index !== false ? $index : -1;
    }

    function copy()
    {
        return S($this);
    }

    // Converts a string to lowercase.

    function count($substr)
    {
        return substr_count($this->s, "$substr");
    }

    // Adds a prefix to string.

    function ensureLeft($prefix)
    {
        return $this->startsWith($prefix) ? $this : $this->prefix($prefix);
    }

    // Replaces substrings.

    function startsWith($prefix)
    {
        return substr($this->s, 0, strlen($prefix)) === $prefix;
    }

    // Replaces substrings according to a given table.

    function prefix($str)
    {
        return S($str . $this->s);
    }

    // Returns the substring denoted by n positive right-most characters.

    function ensureRight($suffix)
    {
        return $this->endsWith($suffix) ? $this : $this->concat($suffix);
    }

    // Returns a slice from the string.

    function endsWith($suffix)
    {
        // No need to use mb_ functions.
        return substr($this->s, -strlen("$suffix")) === "$suffix";
    }

    // Returns true if the string starts with prefix.

    function icmp($str)
    {
        // Use lower because there are unicode precomposed characters which
        // don’t have an uppercase equivalent.
        return $this->lower()->cmp(S($str)->lower());
    }

    // Converts a string to an array using a string delimiter.

    function cmp($str)
    {
        return strcmp($this->s, $str);
    }

    // Removes matches of a string.

    function iequals($str)
    {
        // Use lower because there are unicode precomposed characters which
        // don’t have an uppercase equivalent.
        return $this->lower()->equals(S($str)->lower());
    }

    // Returns a string repeated n times.

    function equals($str)
    {
        return $this->s === "$str";
    }

    function lastIndexOf($substr, $end = null)
    {
        $substrlen = mb_strlen($substr);
        $end = $end !== null ? $end : $substrlen;
        $index = mb_strrpos($this->left($end + $substrlen)->s, $substr, 0);
        return $index !== false ? $index : -1;
    }

    // Converts a string to titlecase.

    function left($size)
    {
        return $size >= 0 ? $this->slice(0, $size) : $this->right(-$size);
    }

    // Translates the characters of $set1 into characters of $set2.

    function right($size)
    {
        return $size >= 0 ? $this->slice(-$size) : $this->left(-$size);
    }

    function lines($limit = -1, $noEmpty = false)
    {
        return $this->split("\n", $limit, $noEmpty);
    }

    // Converts a string to uppercase.

    function split($pattern, $limit = -1, $noEmpty = false)
    {
        return $this->splitRegex(S($pattern)->toRegex(), $limit, $noEmpty);
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Test methods ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Tests if string is alphanumeric.

    function toRegex()
    {
        return self::makeRegex(array($this));
    }

    // Tests if string is alphabetic.

    function strip($str, $limit = -1)
    {
        return $this->replace($str, '', $limit);
    }

    // Tests if string is entirely made of ASCII chars.

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
                function () use ($newstr) {
                    return $newstr;
                },
                $limit
            );
        }
    }

    // Tests if string is entirely made of horizontal spaces.

    function translate($set1, $set2)
    {
        if (mb_strlen($set1) == mb_strlen($set2)) {
            $table = array();
            for ($i = 0; $i < mb_strlen($set1); $i++) {
                $table[S($set1)->charAt($i)] = S($set2)->charAt($i);
            }
            return $this->replaceMany($table);
        } else {
            throw new RuntimeException("S::translate(): Lengths of \$set1 and \$set2 differ");
        }
    }

    // Tests if string is entirely made of control characters.

    function upper()
    {
        return S(mb_convert_case($this->replaceMany(self::$upperTable), MB_CASE_UPPER));
    }

    // Tests if string is empty.

    function isAscii()
    {
        return $this->countRegex("/^[[:ascii:]]+$/") === 1;
    }

    // Tests if string can be converted to floating point number.

    function countRegex($regex)
    {
        $matches = $this->matchAll($regex);
        return ($matches === false) ? 0 : count($matches);
    }

    // Tests if string can be converted to integer.

    function matchAll($regex)
    {
        $matches = null;
        if (preg_match_all($regex, $this->s, $matches, PREG_SET_ORDER) !== false) {
            return $matches;
        } else {
            self::throwRegexError();
        }
        return $matches;
    }

    // Tests if string is lowercase.

    function isBlank($unicode = true)
    {
        return $this->countRegex("/^[[:blank:]]+$/" . ($unicode ? 'u' : '')) === 1;
    }

    // Tests if string is a number.

    function isCntrl()
    {
        return $this->countRegex("/^[[:cntrl:]]+$/") === 1;
    }

    // Tests if string is punctuation.

    function isEmpty()
    {
        return $this->s === '';
    }

    // Tests if string is uppercase.

    function isFloat()
    {
        /** @noinspection PhpWrongStringConcatenationInspection */
        return gettype($this->s + 0) === 'double' || $this->isInteger();
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Whitespace functions ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Converts all adjacent whitespace characters to a single space.

    function isInteger()
    {
        /** @noinspection PhpWrongStringConcatenationInspection */
        return gettype($this->s + 0) === 'integer';
    }

    // Converts line breaks to <br />

    function isLower($unicode = true)
    {
        return $this->countRegex("/^[[:lower:]]+$/" . ($unicode ? 'u' : '')) === 1;
    }

    // Center pads the string.

    function isNumber($unicode = true)
    {
        return $this->isAlnum($unicode) && !$this->isAlpha($unicode);
    }

    // Left pads the string.

    function isAlnum($unicode = true)
    {
        return $this->countRegex("/^[[:alnum:]]+$/" . ($unicode ? 'u' : '')) === 1;
    }

    // Right pads the string.

    function isAlpha($unicode = true)
    {
        return $this->countRegex("/^[[:alpha:]]+$/" . ($unicode ? 'u' : '')) === 1;
    }

    // Converts LF to CR LF.

    function isPunct($unicode = true)
    {
        return $this->countRegex("/^[[:punct:]]+$/" . ($unicode ? 'u' : '')) === 1;
    }

    // Converts CR LF to LF.

    function isUpper($unicode = true)
    {
        return $this->countRegex("/^[[:upper:]]+$/" . ($unicode ? 'u' : '')) === 1;
    }

    // Returns the string with leading and trailing whitespace removed.

    function collapseWhitespace()
    {
        return $this->trim()->replaceRegex('/\s+/u', ' ');
    }

    // Returns the string leading and whitespace removed.

    function replaceRegex($regex, $subst, $limit = -1)
    {
        $result = preg_replace($regex, "$subst", $this->s, $limit);
        if ($result === null) {
            self::throwRegexError();
        }
        return S($result);
    }

    // Returns the string with trailing whitespace removed.

    function trim()
    {
        return $this->replaceRegex('/^[\s​]+|[\s​]+$/u', '');
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~[ Regular expression methods ]~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

    // Counts the occurrences of $regex in string.

    function nlToBr($xhtml = true)
    {
        return $this->replaceRegex("/\r?\n/", $xhtml ? "<br>" : "<br />");
    }

    // Creates a regex that matches all given values.

    function padCenter($len, $char = ' ')
    {
        $oldlen = $this->length();
        $padlen = ($len - $oldlen) / 2.0;
        return $oldlen < $len
            ? S($char)->times(ceil($padlen))->concat($this)->concat(S($char)->times(floor($padlen)))
            : $this;
    }

    // Matches a string against $regex.

    function padRight($len, $char = ' ')
    {
        $oldlen = $this->length();
        return $oldlen < $len ? S($char)->times($len - $oldlen)->prefix($this->s) : $this;
    }

    // Matches all occurences of $regex.

    function toDos()
    {
        return $this->replaceRegex("/\r?\n/", "\r\n");
    }

    // Matches all occurences of $regex and calls a callback on each one.

    function toUnix()
    {
        return $this->replace("\r\n", "\n");
    }

    // Replaces substrings using regex.

    function trimLeft()
    {
        return $this->replaceRegex('/^[\s​]+/u', '');
    }

    // Replaces substrings using a regex and a callback.

    function trimRight()
    {
        return $this->replaceRegex('/[\s​]+$/u', '');
    }

    // Convert a string to an array using a regex delimiter.

    function match($regex)
    {
        $matches = null;
        if (!preg_match($regex, $this->s, $matches) !== false) {
            self::throwRegexError();
        }
        return $matches;
    }

    // Removes matches of a regex.

    function matchAllCallback($regex, $callback)
    {
        foreach ($this->matchAll($regex) as $index => $matches) {
            $callback($index, $matches);
        }
    }
    
    // Camelizes the string.
	function camelize() {
		return $this
			->replaceRegexCallback('/([-_])([a-z])/', function ($match) {
				return S($match[2])->upper()->s;
			});
	}
    
	// Underscorizes the string.
	function underscorize() {
		return $this
			->replaceRegex('/([a-z0-9])([A-Z])/', '\1_\2')
			->replaceRegex('/([a-z0-9])-([a-z])/', '\1_\2')
			->upper();
	}
    
	// Capitalizes the first character of a string.
	function capitalize() {
		return $this->slice(0, 1)->title()->concat($this->slice(1));
	}

    // Throw a regex error

    function stripRegex($regex, $limit = -1)
    {
        return $this->replaceRegex($regex, '', $limit);
    }

}

// Create a new StringPhp object.
function S($str, $encoding = 'UTF-8')
{
    return new S($str, $encoding);
}
