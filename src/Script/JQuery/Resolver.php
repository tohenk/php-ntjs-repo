<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2025 Toha <tohenk@yahoo.com>
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
 * Javascript namespace resolver provides function to resolve object namespace
 * or execute given namespace. Resolver can give advantage to pass callback
 * to other window.
 *
 * Usage:
 *
 * ```js
 * $.define('my', {
 *     doit() {
 *         alert('I\'m doing it.');
 *     }
 * });
 *
 * $.resolver.exec('$.my.doit');
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Resolver extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        return <<<EOF
$.define('resolver', {
    resolve(n, o) {
        let i, l;
        o = o || parent || window;
        n = n.split('.');
        for (i = 0, l = n.length; i < l; i++) {
            o = o[n[i]];
            if (!o) {
                break;
            }
        }
        return o;
    },
    exec(cb) {
        if (typeof cb === 'function') {
            return cb.apply(cb, Array.prototype.slice.call(arguments, 1));
        }
        if (typeof cb === 'string') {
            scope = cb.replace(/\.\w+$/, '');
            scope = scope ? this.resolve(scope) : 0;
            cb = this.resolve(cb);
            if (cb) {
                return cb.apply(scope || this, Array.prototype.slice.call(arguments, 1));
            }
        }
    }
});
EOF;
    }
}