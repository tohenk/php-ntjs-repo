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

use NTLAB\JS\Util\JSValue;

/**
 * Include Bootstrap StarRating locale assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class StarRatingLocale extends StarRating
{
    protected static $locales = ['ar', 'bn', 'de', 'es', 'fa', 'fr', 'it', 'kk', 'ko', 'pl', 'pt-BR', 'ro', 'ru', 'tr', 'ua', 'zh'];

    protected function configure()
    {
        $this->setupAsset('locale', 'js/locales', true, true);
    }

    public function getScript()
    {
        $locale = $this->getLocale(true);
        if (in_array($locale, static::$locales)) {
            $this->useJavascript($locale);
        } else {
            $langs = JSValue::create([
                'defaultCaption' => $this->trans('{rating} Stars'),
                'starCaptions' => [
                    '0.5' => $this->trans('Half Star'),
                    '1' => $this->trans('One Star'),
                    '1.5' => $this->trans('One & Half Star'),
                    '2' => $this->trans('Two Stars'),
                    '2.5' => $this->trans('Two & Half Stars'),
                    '3' => $this->trans('Three Stars'),
                    '3.5' => $this->trans('Three & Half Stars'),
                    '4' => $this->trans('Four Stars'),
                    '4.5' => $this->trans('Four & Half Stars'),
                    '5' => $this->trans('Five Stars'),
                ],
                'clearButtonTitle' => $this->trans('Clear'),
                'clearCaption' => $this->trans('Not Rated'),
            ]);

            return <<<EOF
$.fn.ratingLocales['$locale'] = $langs;
EOF;
        }
    }
}
