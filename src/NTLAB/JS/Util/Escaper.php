<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015 Toha <tohenk@yahoo.com>
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
     * @var int
     */
    protected $indent = 0;

    /**
     * @var string
     */
    protected $content = null;

    /**
     * @var string
     */
    protected $callback = null;

    /**
     * @var array
     */
    protected $args = array();

    /**
     * Constructor.
     *
     * @param string $content  The script content
     * @param int $indent  The identation size
     */
    public function __construct($content = null, $indent = null)
    {
        $this->content = $content;
        if (null !== $indent) {
            $this->setIndent($indent);
        }
    }

    /**
     * Set the indentation size.
     *
     * @param int $indent  The size of indent
     * @return \NTLAB\JS\Util\Escaper
     */
    public function setIndent($indent)
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * Get the indentation size.
     *
     * @return int The size of indent
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set the callback.
     *
     * @param array $callback  The callback
     * @return \NTLAB\JS\Util\Escaper
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set the callback arguments.
     *
     * @param array $args  The arguments
     * @return \NTLAB\JS\Util\Escaper
     */
    public function setArgs($args = array())
    {
        $this->args = $args;

        return $this;
    }

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
                $values = array();
                foreach ($value as $k => $v) {
                    $result = '';
                    if (!is_numeric($k)) {
                        $numKeys = false;
                        $akey = $k;
                        // quote key if contain special characters
                        if (preg_match('/[\.\+\-\[\]\/\s]/', $akey)) {
                            $akey = '\''.$akey.'\'';
                        }
                        $result = $akey.': ';
                    }
                    $result .= self::escape($v, $k, $indent + 1, $inline);
                    $values[] = $result;
                }
                // implode all array values
                $value = $inline ? implode(', ', $values) : self::implodeAndPad($values, $indent + 1);
                $value = sprintf($numKeys ? '[%s]' : '{%s}', $value);
            }
        } else {
            if ($value instanceof self) {
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
            $value = '\''.str_replace('\'', '\'\'', $value).'\'';
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

    public function __toString()
    {
        if (null !== $this->content) {
            return $this->content;
        }
        if (is_callable($this->callback)) {
            return call_user_func_array($this->callback, $this->args);
        }
    }
}