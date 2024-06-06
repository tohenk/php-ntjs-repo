<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2024 Toha <tohenk@yahoo.com>
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
 *
 * ```js
 * $.define('my', {
 *     test: function() {
 *         alert('Test');
 *     }
 * });
 *
 * // call it
 * $.my.test();
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
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
    'use strict';
    $.namespace = {
        factory: function(ns, create = false) {
            let res = true, o = $;
            const props = ns.split('.');
            for (const prop of props) {
                if (!create && !o[prop]) {
                    res = false;
                    break;
                }
                o[prop] = o[prop] || {};
                o = o[prop];
            }
            return [res, o];
        },
        create: function(ns) {
            return this.factory(ns, true)[1];
        },
        has: function(ns) {
            return this.factory(ns, false)[0];
        },
        assert: function(ns) {
            if (!this.has(ns)) {
                throw new Error(`Namespace \${ns} is not defined!`);
            }
        },
        define: function(ns, o, e) {
            if (!e && $.namespace.has(ns)) {
                return;
            }
            $.extend($.namespace.create(ns), o);
        }
    }
    $.define = $.namespace.define.bind($.namespace);
    $.assert = $.namespace.assert.bind($.namespace);
}
EOF;
    }
}