<?php

/*
 * The MIT License
 *
 * Copyright (c) 2017 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Util\Asset;

/**
 * Popper.js javascript code repository for PHP.
 *
 * @author Toha
 */
class Popper extends Base
{
    const UMD = 'umd';
    const ESM = 'esm';
    const ESNext = '';

    /**
     * @var string
     */
    protected $repository = 'popper.js';

    protected function configure()
    {
        switch ($this->getOption('version', self::UMD)) {
            case static::UMD:
                $this->getAsset()->setPath(Asset::ASSET_JAVASCRIPT, static::UMD);
                break;
            case static::ESM:
                $this->getAsset()->setPath(Asset::ASSET_JAVASCRIPT, static::ESM);
                break;
            case static::ESNext:
                $this->getAsset()->setPath(Asset::ASSET_JAVASCRIPT, static::ESNext);
                break;
        }
        $this->addAsset(Asset::ASSET_JAVASCRIPT, 'popper.min');
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Script::getRepositoryName()
     */
    protected function getRepositoryName()
    {
        return $this->repository;
    }
}