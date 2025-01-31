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
use NTLAB\JS\BackendInterface;

/**
 * Include Google Fonts assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class GoogleFonts extends Base
{
    public const FONT_CSS_V1 = 1;
    public const FONT_CSS_V2 = 2;

    protected static $version = self::FONT_CSS_V2;
    protected static $preconnect = null;

    /**
     * Parse font family specs.
     *
     * @param string $family
     * @return string[]
     */
    protected function parseFontFamily($family)
    {
        $fonts = [];
        $family = is_array($family) ? $family : [$family];
        foreach ($family as $fam) {
            $specs = [];
            if (false !== ($p = strpos($fam, ':'))) {
                $weights = explode(',', substr($fam, $p + 1));
                $fam = substr($fam, 0, $p);
                $italic = false;
                if (count($weights) && $weights[0] === 'italic') {
                    $italic = true;
                    array_shift($weights);
                }
                switch (static::$version) {
                    case static::FONT_CSS_V1:
                        foreach ($weights as $weight) {
                            $specs[] = $weight;
                            if ($italic) {
                                $specs[] = $weight.'i';
                            }
                        }
                        break;
                    case static::FONT_CSS_V2:
                        if ($italic) {
                            $specs[] = 'ital';
                        }
                        $w = [];
                        foreach ([0, 1] as $index) {
                            $w = array_merge($w, array_map(function ($a) use ($index) {
                                return implode(',', [$index, $a]);
                            }, $weights));
                            if ($index === 0 && !$italic) {
                                break;
                            }
                        }
                        if (count($w)) {
                            $specs[] = sprintf('wght@%s', implode(';', $w));
                        }
                        break;
                }
            }
            $fam = urlencode($fam);
            if (count($specs)) {
                $fam .= ':'.implode(',', $specs);
            }
            $fonts[] = $fam;
        }

        return $fonts;
    }

    /**
     * Use google fonts icon family.
     *
     * @param string $family  Icon family
     * @return \NTLAB\JS\Script\GoogleFonts
     */
    public function useIcon($family)
    {
        $this->useStylesheet(sprintf('https://fonts.googleapis.com/icon?family=%s', $family));

        return $this;
    }

    /**
     * Use google fonts font family.
     *
     * @param string $family  Font family
     * @return \NTLAB\JS\Script\GoogleFonts
     */
    public function useFont($family)
    {
        $font = null;
        $fonts = $this->parseFontFamily($family);
        switch (static::$version) {
            case static::FONT_CSS_V1:
                $font = sprintf('https://fonts.googleapis.com/css?family=%s', implode('|', $fonts));
                break;
            case static::FONT_CSS_V2:
                $font = implode('&', array_map(function ($a) {
                    return sprintf('family=%s', $a);
                }, $fonts)).'&display=swap';
                $font = sprintf('https://fonts.googleapis.com/css2?%s', $font);
                if (null === static::$preconnect) {
                    static::$preconnect = true;
                    $this->useStylesheet('https://fonts.googleapis.com', null, BackendInterface::ASSET_PRIORITY_FIRST, ['rel' => 'preconnect', 'type' => null]);
                    $this->useStylesheet('https://fonts.gstatic.com', null, BackendInterface::ASSET_PRIORITY_FIRST, ['rel' => 'preconnect', 'type' => null, 'crossorigin' => true]);
                }
                break;
        }
        if ($font) {
            $this->useStylesheet($font);
        }

        return $this;
    }
}
