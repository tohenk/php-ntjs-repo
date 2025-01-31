<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024-2025 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Repo\Script\Bootstrap;

use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Util\Asset;

/**
 * Include Bootstrap StarRating assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class StarRating extends Base
{
    public const ASSET_NAME = 'bootstrap-star-rating';

    protected function configure()
    {
        $this->setupAsset(null);
    }

    protected function setupAsset($name, $type = null, $skipAsset = false, $uniAsset = false)
    {
        // set asset
        $names = [];
        if (null !== $type) {
            $names[] = $type;
        }
        if ($name) {
            $names[] = $name;
        }
        $paths = [];
        if (count($names) || !$uniAsset) {
            foreach ([Asset::ASSET_JAVASCRIPT, Asset::ASSET_STYLESHEET] as $asset) {
                if ($uniAsset) {
                    $paths[$asset] = implode('/', $names);
                } else {
                    $paths[$asset] = implode('/', array_merge($names, [$asset === Asset::ASSET_JAVASCRIPT ? 'js' : 'css']));
                }
            }
        }
        $this->setAsset(new Asset(static::ASSET_NAME, $paths));
        // register asset
        if (!$skipAsset) {
            $asset = null === $type ? 'star-rating.min' : 'theme.min';
            $this->addAsset(Asset::ASSET_JAVASCRIPT, $asset);
            $this->addAsset(Asset::ASSET_STYLESHEET, $asset);
        }
    }
}
