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
use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\JSValue;

/**
 * Include JQuery Spinner assets and provide a helper.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Spinner extends Base
{
    protected function configure()
    {
        $this->setAsset(new Asset('jquery.spinner', [
            Asset::ASSET_JAVASCRIPT => 'js',
            Asset::ASSET_STYLESHEET => 'css',
        ]));
        $this->addDependencies(['JQuery.NS']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $this->useJavascript('jquery.spinner.min');

        $defaults = JSValue::create([])->setIndent(1);
        $el = '$el';
        $target = '$spinning';

        return <<<EOF
$.define('JQuerySpinnerHelper', {
    defaults: $defaults,
    evlockData: 'spinner-changing',
    create(selector, options) {
        const self = this;
        $(selector).spinner(Object.assign({}, self.defaults, options));
        const spinner = $(selector).data('spinner');
        if (spinner.spinning.__proto__.ospin === undefined) {
            spinner.spinning.__proto__.ospin = spinner.spinning.__proto__.spin;
            spinner.spinning.__proto__.spin = function(dir) {
                if (this.$el.prop('readonly')) {
                    return;
                }
                return this.ospin(dir);
            }
        }
        $(selector).on('changed.spinner', function(e) {
            e.preventDefault();
            const d = $(this).data('spinner');
            if (!$(this).data(self.evlockData)) {
                $(this).data(self.evlockData, true);
                d.$target.trigger('change');
            } else {
                $(this).data(self.evlockData, false);
            }
        });
    }
});
EOF;
    }
}
