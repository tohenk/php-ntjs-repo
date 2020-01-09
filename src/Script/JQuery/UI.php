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
namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Util\Asset;

/**
 * JQuery UI base class for script that is depends on JQuery UI.
 *
 * JQuery UI support theming which can be set by returning configuration
 * named 'jquery-ui-theme' from within backend handler.
 *
 * @author Toha
 */
class UI extends Base
{
    protected function initialize()
    {
        parent::initialize();

        $theme = $this->getOption('theme', $this->getTheme());
        $this->addAsset(Asset::ASSET_JAVASCRIPT, 'jquery-ui');
        $this->addAsset(Asset::ASSET_STYLESHEET, $theme.'/jquery-ui');
    }

    /**
     * Get JQuery UI theme.
     *
     * @param string $default  The default theme
     * @return string
     */
    public function getTheme($default = 'ui-lightness')
    {
        return $this->getBackend()->getConfig('jquery-ui-theme', $default);
    }

    protected static function createInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }
}