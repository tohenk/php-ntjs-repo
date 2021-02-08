<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2021 Toha <tohenk@yahoo.com>
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
 * Represent javascript value.
 *
 * @author Toha
 */
class JSValue
{
    /**
     * @var string
     */
    protected $value = null;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $inline = null;

    /**
     * @var bool
     */
    protected $raw = null;

    /**
     * @var int
     */
    protected $indent = 0;

    /**
     * Constructor.
     *
     * @param mixed $value  The value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Create javascript value.
     *
     * @param mixed $value
     * @return \NTLAB\JS\Util\JSValue
     */
    public static function create($value = null)
    {
        return new self($value);
    }

    /**
     * Create raw javascript value.
     *
     * @param mixed $value
     * @return \NTLAB\JS\Util\JSValue
     */
    public static function createRaw($value = null)
    {
        return self::create($value)->setRaw(true);
    }

    /**
     * Create inlined javascript value.
     *
     * @param mixed $value
     * @return \NTLAB\JS\Util\JSValue
     */
    public static function createInlined($value = null)
    {
        return self::create($value)->setInline(true);
    }

    /**
     * Set extra data.
     *
     * @param array $value  The value
     * @return \NTLAB\JS\Util\JSValue
     */
    public function setData($value)
    {
        $this->data = $value;
        return $this;
    }

    /**
     * Get extra data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set if value representation is inlined (for array).
     *
     * @param bool $value  The value
     * @return \NTLAB\JS\Util\JSValue
     */
    public function setInline($value)
    {
        $this->inline = $value;
        return $this;
    }

    /**
     * Get if value representation is inlined.
     *
     * @return bool
     */
    public function isInline()
    {
        return $this->inline;
    }

    /**
     * Set if value is raw which will not be escaped.
     *
     * @param bool $value  The value
     * @return \NTLAB\JS\Util\JSValue
     */
    public function setRaw($value)
    {
        $this->raw = $value;
        return $this;
    }

    /**
     * Get if value representation is raw.
     *
     * @return bool
     */
    public function isRaw()
    {
        return $this->raw;
    }

    /**
     * Set the indentation size.
     *
     * @param int $value  The size of indent
     * @return \NTLAB\JS\Util\JSValue
     */
    public function setIndent($value)
    {
        $this->indent = $value;
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
     * Get value representation as string.
     *
     * @return string
     */
    protected function asString()
    {
        $value = $this->value;
        if (!$this->raw) {
            if (is_callable($value)) {
                $value = call_user_func_array($value, $this->data);
            }
            $value = Escaper::escape($value, null, $this->indent, $this->inline);
        }
        return (string) $value;
    }

    public function __toString()
    {
        return $this->asString();
    }
}