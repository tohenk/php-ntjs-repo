<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2022 Toha <tohenk@yahoo.com>
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
use NTLAB\JS\Repository;

/**
 * Ajax request helper.
 *
 * @author Toha
 *
 */
class AjaxHelper extends Base
{
    protected function configure()
    {
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.ajaxhelper = function(el) {
    var helper = {
        el: null,
        dataKey: '_acxhr',
        load: function(url, params, callback) {
            var self = this;
            if (typeof params === 'function') {
                var callback = params;
                var params = {};
            }
            var oxhr = self.el.data(self.dataKey);
            if (oxhr && 'pending' === oxhr.state()) {
                oxhr.abort();
            }
            self.el.trigger('xhrstart');
            var xhr = $.ajax({
                url: url,
                dataType: 'json',
                data: params
            }).done(function(data) {
                callback(data);
            }).always(function() {
                self.el.trigger('xhrend');
            });
            self.el.data(self.dataKey, xhr);
        }
    }
    if (typeof el == 'string') {
        helper.el = $(el);
    } else {
        helper.el = el;
    }
    return helper;
}
EOF;
    }
}