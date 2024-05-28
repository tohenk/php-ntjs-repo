<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2024 Toha <tohenk@yahoo.com>
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
 * Javascript CDN helper.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class CDN
{
    /**
     * @var string
     */
    protected $repository = null;

    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $package = null;

    /**
     * @var string
     */
    protected $version = null;

    /**
     * @var array
     */
    protected $paths = null;

    /**
     * @var array
     */
    protected $js = [];

    /**
     * @var array
     */
    protected $css = [];

    /**
     * @var string[]
     */
    protected $tags = ['%', '<>'];

    /**
     * Constructor.
     *
     * @param string $repository  Repository id
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set cdn url.
     *
     * @param string $url
     * @return \NTLAB\JS\Util\CDN
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get cdn url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set cdn package.
     *
     * @param string $version
     * @return \NTLAB\JS\Util\CDN
     */
    public function setPackage($package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * Get cdn package.
     *
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Set cdn version.
     *
     * @param string $version
     * @return \NTLAB\JS\Util\CDN
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get cdn version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set cdn asset path, valid assets are js and css.
     *
     * @param string $asset
     * @param string $path
     * @return \NTLAB\JS\Util\CDN
     */
    public function setPath($asset, $path)
    {
        $this->paths[$asset] = $path;
        return $this;
    }

    /**
     * Get cdn asset path.
     *
     * @param string $asset
     * @return string
     */
    public function getPath($asset)
    {
        if (isset($this->paths[$asset])) {
            return $this->paths[$asset];
        }
    }

    /**
     * Add javascript file mapping.
     *
     * @param string $name
     * @param string $path
     * @return \NTLAB\JS\Util\CDN
     */
    public function addJs($name, $path)
    {
        $this->js[$name] = $path;
        return $this;
    }

    /**
     * Get javascript file mapping.
     *
     * @param string $name
     * @return string
     */
    public function getJs($name)
    {
        if (isset($this->js[$name])) {
            return $this->js[$name];
        }
    }

    /**
     * Add stylesheet file mapping.
     *
     * @param string $name
     * @param string $path
     * @return \NTLAB\JS\Util\CDN
     */
    public function addCss($name, $path)
    {
        $this->css[$name] = $path;
        return $this;
    }

    /**
     * Get stylesheet file mapping.
     *
     * @param string $name
     * @return string
     */
    public function getCss($name)
    {
        if (isset($this->css[$name])) {
            return $this->css[$name];
        }
    }

    /**
     * Replace tag in url.
     *
     * @param string $str Url to replace
     * @param string $tag Tag to replace
     * @param string $value Replacement
     * @return string
     */
    protected function replaceTag($str, $tag, $value)
    {
        foreach ($this->tags as $marker) {
            $tagged = $marker[0].$tag.(strlen($marker) > 1 ? $marker[1] : $marker[0]);
            if (false !== strpos($str, $tagged)) {
                $str = str_replace($tagged.(!strlen((string) $value) ? '/' : ''), (string) $value, $str);
                break;
            }
        }
        return $str;
    }

    /**
     * Replace package.
     *
     * @param string $str
     * @return string
     */
    protected function replacePackage($str)
    {
        return $this->replaceTag($str, 'PKG', $this->package);
    }

    /**
     * Replace version.
     *
     * @param string $str
     * @return string
     */
    protected function replaceVersion($str)
    {
        return $this->replaceTag($str, 'VER', $this->version);
    }

    /**
     * Replace asset path.
     *
     * @param string $asset
     * @param string $str
     * @param string $default
     * @return string
     */
    protected function replacePath($asset, $str, $default = null)
    {
        $type = isset($this->paths[$asset]) ? $this->paths[$asset] : $default;
        return $this->replaceTag($str, 'TYPE', $type);
    }

    /**
     * Replace version.
     *
     * @param string $name
     * @param string $str
     * @return string
     */
    protected function replaceName($name, $str)
    {
        return $this->replaceTag($str, 'NAME', $name);
    }

    /**
     * Get cdn asset name.
     *
     * @param string $asset
     * @param string $name
     * @param string $path
     * @return string
     */
    public function get($asset, $name, $path)
    {
        $cdn = null;
        $file = null;
        switch ($asset) {
            case Asset::ASSET_JAVASCRIPT:
                $file = empty($this->js) ? $name : $this->getJs($name);
                break;
            case Asset::ASSET_STYLESHEET:
                $file = empty($this->css) ? $name : $this->getCss($name);
                break;
        }
        if ($file) {
            $file = $this->replaceVersion($file);
            $cdn = $this->getUrl();
            $cdn = $this->replacePackage($cdn);
            $cdn = $this->replacePath($asset, $cdn, $path);
            $cdn = $this->replaceVersion($cdn);
            $cdn = $this->replaceName($file, $cdn);
        }
        return $cdn;
    }
}