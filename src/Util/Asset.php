<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2021 Toha <tohenk@yahoo.com>
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

use NTLAB\JS\Manager;

/**
 * Javascript assets helper.
 *
 * @author Toha
 */
class Asset
{
    const ASSET_JAVASCRIPT = 'js';
    const ASSET_STYLESHEET = 'css';
    const ASSET_IMAGE = 'img';
    const ASSET_OTHER = 'other';

    /**
     * @var \NTLAB\JS\Manager
     */
    protected $manager = null;

    /**
     * @var \NTLAB\JS\BackendInterface
     */
    protected $backend = null;

    /**
     * @var string
     */
    protected $repository = null;

    /**
     * @var string
     */
    protected $alias = null;

    /**
     * @var array
     */
    protected $dirs = [];

    /**
     * Constructor.
     *
     * @param string $repository
     * @param array $options
     */
    public function __construct($repository, $options = [])
    {
        $this->manager = Manager::getInstance();
        $this->backend = $this->manager->getBackend();
        $this->repository = $repository;
        foreach ([static::ASSET_JAVASCRIPT, static::ASSET_STYLESHEET, static::ASSET_IMAGE, static::ASSET_OTHER] as $asset) {
            if (isset($options[$asset])) {
                $this->dirs[$asset] = $options[$asset];
            }
        }
    }

    /**
     * Get repository name.
     *
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get asset alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set asset alias.
     *
     * @param string $alias
     * @return \NTLAB\JS\Util\Asset
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Set asset path.
     *
     * @param string $asset  Asset type
     * @param string $dir  Path name
     * @return \NTLAB\JS\Util\Asset
     */
    public function setPath($asset, $dir)
    {
        if (null === $dir) {
            unset($this->dirs[$asset]);
        } else {
            $assets = null === $asset ? [self::ASSET_JAVASCRIPT, self::ASSET_STYLESHEET, self::ASSET_OTHER] : [$asset];
            foreach ($assets as $type) {
                $this->dirs[$type] = $dir;
            }
        }
        return $this;
    }

    /**
     * Generate asset name.
     *
     * @param string $name  Asset name
     * @param string $version  Version
     * @param boolean $minified  Is asset minified
     * @return string
     */
    public function generate($name, $version = null, $minified = null)
    {
        $assetName = $name.($version ? '-'.$version : '').($minified ? '.min' : '');
        return $assetName;
    }

    /**
     * Get asset extension.
     *
     * @param string $asset  Asset type
     * @return string
     */
    public function getExtension($asset)
    {
        switch ($asset) {
            case static::ASSET_JAVASCRIPT:
                return '.js';
            case static::ASSET_STYLESHEET:
                return '.css';
        }
    }

    /**
     * Get the directory name for asset.
     *
     * @param string $asset  Asset type
     * @return string
     */
    public function getDirName($asset)
    {
        if (null !== $asset && isset($this->dirs[$asset])) {
            return $this->dirs[$asset];
        }
    }

    /**
     * Get repository directory.
     *
     * @param string $asset  Asset type
     * @param string $repository  Repository name
     * @return string
     */
    public function getDir($asset = null, $repository = null)
    {
        $dir = $this->backend->getAssetDir(null !== $repository ? $repository : $this->repository);
        if (strlen($dirName = $this->getDirName($asset))) {
            $dir .= '/'.$dirName;
        }
        return $dir;
    }

    /**
     * Fix asset extension.
     *
     * @param string $asset  Asset type
     * @param string $name  Asset name
     * @return string
     */
    protected function fixExtension($asset, $name)
    {
        if (false == strpos($name, '?') && null !== ($extension = $this->getExtension($asset)) && substr($name, -strlen($extension)) != $extension) {
            $name .= $extension;
        }
        return $name;
    }

    /**
     * Check if asset is a local name.
     *
     * @param string $name  Asset name
     * @return bool
     */
    protected function isLocal($name)
    {
        return preg_match('#(^http(s)*\:)*\/\/(.*)#', $name) ? false : true;
    }

    /**
     * Get asset name.
     *
     * @param string $asset  Asset type
     * @param string $name  Asset name
     * @return string
     */
    public function get($asset, $name)
    {
        if ($this->isLocal($name)) {
            // check cdn if exist
            if ($cdn = $this->manager->getCdn($this->alias ? $this->alias : $this->repository)) {
                if ($file = $cdn->get($asset, $name, $this->getDirName($asset))) {
                    return $this->fixExtension($asset, $file);
                }
            }
            $name = $this->backend->generateAsset($this, $name, $asset);
        }
        return $this->fixExtension($asset, $name);
    }
}