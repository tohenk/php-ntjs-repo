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
 * Provide number formatting internal using JQuery Number Formatter.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class NumberFormatJQueryNumberFormatter extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS', 'NumberFormatter', 'Jshashtable']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.define('nfJQueryNumberFormatter', {
    data: {},
    init(options) {
        const self = this;
        self.data.format = options.format;
        self.data.locale = options.locale;
        self.data.overrideDecSep = options.decimal || null;
        self.data.overrideGroupSep = options.group || null;
    },
    format(v) {
        const self = this;
        return $.formatNumber(v, self.data);
    },
    value(v) {
        const self = this;
        return $.parseNumber(v, self.data);
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
    $.nfFactory = $.nfJQueryNumberFormatter;
}
EOF
                );
        }
    }
}
