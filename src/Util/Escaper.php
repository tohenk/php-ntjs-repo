<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2024 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace NTLAB\JS\Util;

/**
 * Javascript escaper escape PHP value into javascript.
 *
 * @author Toha
 */
class Escaper
{
    /**
     * @var string
     */
    protected static $eol = "\n";

    /**
     * Get EOL delimeter.
     *
     * @return string
     */
    public static function getEol()
    {
        return static::$eol;
    }

    /**
     * Set EOL delimeter.
     *
     * @param string $eol  Delimeter
     */
    public static function setEol($eol)
    {
        static::$eol = $eol;
    }

    /**
     * Escape mixed php value into javascript value.
     *
     * @param mixed $value  Value to be escaped as javascript
     * @param string $key  Array key
     * @param int $indent  Identation size   
     * @param boolean $inline  Inline format
     * @return string
     */
    public static function escape($value, $key = null, $indent = 0, $inline = null)
    {
        if (is_array($value)) {
            if (empty($value)) {
                $value = '{}';
            } else {
                // if all arrays keys is numeric then use [] otherwise use {}
                $numKeys = true;
                $values = [];
                foreach ($value as $k => $v) {
                    $result = '';
                    if (!is_int($k) || !$numKeys) {
                        $numKeys = false;
                        $akey = $k;
                        // quote key if contain special characters
                        if (preg_match('/[\.\+\-\[\]\/\s]/', $akey)) {
                            $akey = '\''.$akey.'\'';
                        }
                        $result = $akey.': ';
                    }
                    // treat string started with 'function(' as raw
                    if (is_string($v) && 'function(' === substr($v, 0, 9)) {
                        // add indentation
                        $v = self::implodeAndPad(explode(self::getEol(), $v), 1, null);
                        $v = JSValue::createRaw(trim($v));
                    }
                    $result .= self::escape($v, $k, $indent + 1, $v instanceof JSValue && null !== $v->isInline() ? $v->isInline() : $inline);
                    $values[] = $result;
                }
                // implode all array values
                $value = $inline ? implode(', ', $values) : self::implodeAndPad($values, $indent + 1);
                $value = sprintf($numKeys ? '[%s]' : '{%s}', $value);
            }
        } else {
            if ($value instanceof JSValue) {
                $value->setIndent($indent);
            } else if (null !== $key && !is_object($value)) {
                $value = self::escapeValue($value, self::escapeExcept($key, $value));
            } else {
                $value = self::escapeValue($value, true);
            }
        }
        return $value;
    }

    /**
     * Escape PHP value as javascript value.
     *
     * @param mixed $value  The value to be escaped
     * @param bool $escape  Flag for escaping a value
     * @return mixed Escaped value
     */
    public static function escapeValue($value, $escape)
    {
        if (null === $value) {
            $value = 'null';
        } else if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } else if (is_numeric($value)) {
            $value = (string) $value;
        } else if ($escape) {
            $value = '\''.strtr($value, ['\'' => '\\\'', "\r" => '\\r', "\n" => '\\n', "\t" => '\\t', "\b" => '\\b', "\f" => '\\f']).'\'';
        }
        return $value;
    }

    /**
     * Check if pair of key and value are should be escaped.
     *
     * @param string $key  The array key
     * @param string $value  The array valaue
     * @return boolean
     */
    public static function escapeExcept($key, $value)
    {
        if (is_int($value) || is_bool($value) || is_array($value) || is_object($value)) {
            return false;
        }
        return true;
    }

    /**
     * Implode the array and add left padding if necessary.
     *
     * @param array $array  The array to be imploded
     * @param int $size  Padding size
     * @param string $delim  Delimeter for implode
     * @return string The imploded array as string
     */
    public static function implodeAndPad($array, $size, $delim = ',')
    {
        $pad = self::padLeft($size);
        return static::$eol.$pad.implode($delim.static::$eol.$pad, $array).static::$eol.self::padLeft($size - 1);
    }

    /**
     * Create the left padding of spaces.
     *
     * @param int $size  The size of padding multiplied by 4
     * @return string
     */
    public static function padLeft($size = 0)
    {
        return str_repeat(' ', $size * 4);
    }
}