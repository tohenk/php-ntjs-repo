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

namespace NTLAB\JS\Repo\Script\JQuery;

use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Provide number formatting internal using NumeralJs.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class NumberFormatNumeralJs extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS', 'NumeralJs']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.define('nfNumeralJs', {
    fmt: '0,0',
    init(options) {
        const self = this;
        if (!self.initialized) {
            self.initialized = true;
            if (options.format) {
                self.fmt = options.format;
            }
            numeral.register('locale', options.locale, {
                delimiters: {
                    thousands: options.group,
                    decimal: options.decimal
                },
                abbreviations: {
                    thousand: 'k',
                    million: 'm',
                    billion: 'b',
                    trillion: 't'
                },
                ordinal(number) {
                    return '.';
                },
                currency: {
                    symbol: '*'
                }
            });
            numeral.locale(options.locale);
        }
    },
    format(v) {
        const self = this;
        const n = numeral(v);
        return n.format(self.fmt);
    },
    value(v) {
        const self = this;
        const n = numeral(v);
        return n.value();
    }
});
EOF;
    }

    public function getInitScript()
    {
        if ($this->getOption('register', true)) {
            $this
                ->useScript(
                    <<<EOF
if (!$.nfFactory) {
    $.nfFactory = $.nfNumeralJs;
}
EOF
                );
        }
    }
}
