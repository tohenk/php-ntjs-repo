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
use NTLAB\JS\Repository;

/**
 * JQuery namespace helper, to avoid javascript function redefine.
 *
 * Usage:
 * $.define('my', {
 *     test: function() {
 *         alert('Test');
 *     }
 * });
 *
 * // call it
 * $.my.test();
 *
 * @author Toha
 */
class NS extends Base
{
    protected function configure()
    {
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        // http://stackoverflow.com/questions/527089/is-it-possible-to-create-a-namespace-in-jquery
        return <<<EOF
if (!$.define) {
    $.namespace = {
        create: function(ns) {
            o = $;
            p = ns.split('.');
            for (i = 0; i < p.length; i++) {
                o[p[i]] = o[p[i]] || {};
                o = o[p[i]];
            }

            return o;
        },
        has: function(ns) {
            o = $;
            p = ns.split('.');
            for (i = 0; i < p.length; i++) {
                if (!o[p[i]]) {
                    return false;
                }
                o = o[p[i]];
            }

            return true;
        },
        define: function(ns, o, e) {
            if (!e && $.namespace.has(ns)) return;
            $.extend($.namespace.create(ns), o);
        }
    }

    $.define = $.namespace.define;
}

EOF;
    }
}