<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024 Toha <tohenk@yahoo.com>
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
 * Include JqGrid assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class JqGrid extends Base
{
    protected static $style = 'bootstrap4';

    /**
     * Set style.
     *
     * @param string $style
     */
    public static function setStyle($style)
    {
        self::$style = $style;
    }

    /**
     * Get style.
     *
     * @return string
     */
    public static function getStyle()
    {
        return self::$style;
    }

    protected function configure()
    {
        $this->setAsset(new Asset('jqGrid', [Asset::ASSET_JAVASCRIPT => 'js', Asset::ASSET_STYLESHEET => 'css']));
        $this->addAsset(Asset::ASSET_JAVASCRIPT, 'jquery.jqGrid.min');
        $this->addLocaleAsset(Asset::ASSET_JAVASCRIPT, 'i18n/grid.locale-');
        switch (self::$style) {
            case 'bootstrap':
                $this->addAsset(Asset::ASSET_STYLESHEET, 'ui.jqgrid-bootstrap');
                break;
            case 'bootstrap4':
                $this->addAsset(Asset::ASSET_STYLESHEET, 'ui.jqgrid-bootstrap4');
                break;
        }
    }
}
