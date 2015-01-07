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

namespace NTLAB\JS;

use NTLAB\JS\Util\Escaper;

class Repository
{
    const POSITION_FIRST = 'first';
    const POSITION_MIDDLE = 'middle';
    const POSITION_LAST = 'last';

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $wrapper = null;

    /**
     * @var bool
     */
    protected $useWrapper = true;

    /**
     * 
     * @var bool
     */
    protected $included = false;

    /**
     * @var array
     */
    protected $scripts = array();

    /**
     * Constructor.
     *
     * @param string $name  The javascript repository name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of this script.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the wrapper for this script.
     *
     * @param string $text  Script wrapper formatter used for sprintf
     * @return \NTLAB\JS\Repository
     */
    public function setWrapper($text)
    {
        if ($text != $this->wrapper) {
            $this->wrapper = $text;
        }

        return $this;
    }

    /**
     * Enable the use of script wrapper.
     *
     * @return \NTLAB\JS\Repository
     */
    public function enableWrapper()
    {
        $this->useWrapper = true;

        return $this;
    }

    /**
     * Disable the use of script wrapper.
     *
     * @return \NTLAB\JS\Repository
     */
    public function disableWrapper()
    {
        $this->useWrapper = true;

        return $this;
    }

    /**
     * Get script inclusion state.
     *
     * @return bool
     */
    public function isIncluded()
    {
        return $this->included;
    }

    /**
     * Clear the script.
     *
     * @return \NTLAB\JS\Repository
     */
    public function clear()
    {
        $this->scripts = array();
        $this->included = false;

        return $this;
    }

    /**
     * Add new script text to the existing script.
     *
     * @param string $text  Script text
     * @param string $position  where to put new script, default to Repository::POSITION_LAST
     * @return \NTLAB\JS\Repository
     */
    public function add($text, $position = self::POSITION_LAST)
    {
        if ($text) {
            if (!('}' == substr(rtrim($text), - 1) || ';' == substr(rtrim($text), - 1))) {
                $text = rtrim($text).";".Escaper::getEol();
            }
            $text .= Escaper::getEol();
            // adjust position
            if (!in_array($position, array(static::POSITION_FIRST, static::POSITION_MIDDLE, static::POSITION_LAST))) {
                $position = static::POSITION_LAST;
            }
            // insert the script by position
            if (!isset($this->scripts[$position])) {
                $this->scripts[$position] = array();
            }
            $this->scripts[$position][] = $text;
        }

        return $this;
    }

    /**
     * Get script repository content. If the repository has already
     * included then null content returned.
     *
     * @return string
     */
    public function getContent()
    {
        if (!$this->included) {
            $this->included = true;
            if (strlen($content = $this->__toString())) {
                if (null !== ($compressor = Manager::getInstance()->getCompressor())) {
                    $content = $compressor->compress($content);
                }
            }

            return $content;
        }
    }

    public function __toString()
    {
        $script = null;
        foreach (array(static::POSITION_FIRST, static::POSITION_MIDDLE, static::POSITION_LAST) as $position) {
            if (isset($this->scripts[$position])) {
                $script .= implode('', $this->scripts[$position]);
            }
        }
        if (strlen($script)) {
            if ($this->useWrapper && false != strpos($this->wrapper, '%s')) {
                $script = sprintf($this->wrapper, Escaper::implodeAndPad(explode(Escaper::getEol(), rtrim($script)), 1, null));
            }
        }

        return $script;
    }
}
