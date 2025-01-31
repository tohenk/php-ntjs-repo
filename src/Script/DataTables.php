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

namespace NTLAB\JS\Repo\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Util\Asset;

/**
 * Include DataTables assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class DataTables extends Base
{
    public const DATATABLES_NAME = 'DataTables';

    public const UI_BOOTSTRAP = 'bootstrap';
    public const UI_BOOTSTRAP4 = 'bootstrap4';
    public const UI_BOOTSTRAP5 = 'bootstrap5';
    public const UI_FOUNDATION = 'foundation';
    public const UI_JQUERY_UI = 'jqueryui';
    public const UI_SEMANTIC_UI = 'semanticui';

    protected $assetName = null;
    protected $assetAlias = null;
    protected $assetRepository = null;
    protected $useStyle = true;

    protected function configure()
    {
        $this->setupAsset(null);
    }

    protected function setupAsset($name, $type = null, $skipAsset = false, $uniAsset = false)
    {
        $this->assetName = $name ?: static::DATATABLES_NAME;
        $this->assetAlias = lcfirst($this->assetName);
        $this->assetRepository = $this->genRepoName($name);

        // build asset path
        $dir = [static::DATATABLES_NAME];
        if (null !== $type) {
            $dir[] = $type;
        }
        $dir[] = $name ? ($name === strtoupper($name) ? strtolower($name) : $name) : static::DATATABLES_NAME;
        $paths = $uniAsset ? [] : [Asset::ASSET_JAVASCRIPT => 'js', Asset::ASSET_STYLESHEET => 'css'];

        // set asset
        $this->setAsset(new Asset(implode('/', $dir), $paths));
        if ($this->getAsset()->getRepository() !== $this->assetRepository) {
            $this->getAsset()->setAlias($this->assetRepository);
        }

        // register asset
        if (!$skipAsset) {
            $mainAsset = null === $name ? 'dataTables.min' : sprintf('dataTables.%s.min', $this->assetAlias);
            $this->addAsset(Asset::ASSET_JAVASCRIPT, $mainAsset);
            if ($this->useStyle) {
                $styleAsset = sprintf('%s.%s.min', $this->assetAlias, $this->getOption('style', static::UI_BOOTSTRAP5));
                $this->addAsset(Asset::ASSET_JAVASCRIPT, $styleAsset);
                $this->addAsset(Asset::ASSET_STYLESHEET, $styleAsset);
            } else {
                $styleAsset = sprintf('%s.dataTables.min', $this->assetAlias);
                $this->addAsset(Asset::ASSET_STYLESHEET, $styleAsset);
            }
        }
    }

    protected function genRepoName($name)
    {
        return static::DATATABLES_NAME.(null !== $name ? '-'.$name : '');
    }
}
