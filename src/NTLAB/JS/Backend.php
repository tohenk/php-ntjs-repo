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

class Backend implements BackendInterface
{
    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::getConfig()
     */
    public function getConfig($name, $default = null)
    {
        return $default;
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::trans()
     */
    public function trans($text, $vars = array(), $domain = null)
    {
        return $text;
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::url()
     */
    public function url($url, $options = array())
    {
        return $url;
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::asset()
     */
    public function asset($asset, $type = self::ASSET_JS)
    {
        return $asset;
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::addAsset()
     */
    public function addAsset($asset, $type = self::ASSET_JS)
    {
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::getAssetDir()
     */
    public function getAssetDir($repo)
    {
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::tag()
     */
    public function tag($name, $options = array())
    {
    }

    /**
     * (non-PHPdoc)
     * @see \NTLAB\JS\BackendInterface::ctag()
     */
    public function ctag($name, $content, $options = array())
    {
    }
}