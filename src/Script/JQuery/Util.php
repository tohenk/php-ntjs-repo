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
 * Common utility for javascript.
 *
 * Usage:
 *
 * ```js
 * $.util.dump($('#me'));
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Util extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        return <<<EOF
$.define('util', {
    template(tmpl, replaces) {
        for (const n in replaces) {
            const re = new RegExp('%' + n + '%', 'g');
            tmpl = tmpl.replace(re, replaces[n]);
        }
        return tmpl;
    },
    copyProp(prop, src, dest, remove) {
        if (src[prop] !== undefined) {
            dest[prop] = src[prop];
            if (remove) {
                delete src[prop];
            }
        }
    },
    applyProp(props, src, dest, remove) {
        const self = this;
        if (src && dest) {
            if (typeof props === 'object') {
                if (Array.isArray(props)) {
                    for (let i = 0; i < props.length; i++) {
                        const prop = props[i];
                        self.copyProp(prop, src, dest, remove);
                    }
                } else {
                    for (const prop in props) {
                        self.copyProp(prop, src, dest, remove);
                    }
                }
            }
        }
    },
    bindEvent(el, event, handlers) {
        if (typeof handlers[event] === 'function') {
            el.on(event, handlers[event]);
        }
    },
    applyEvent(el, events, handlers) {
        const self = this;
        if (typeof events === 'object') {
            if (Array.isArray(events)) {
                for (let i = 0; i < events.length; i++) {
                    const event = events[i];
                    self.bindEvent(el, event, handlers);
                }
            } else {
                for (const event in events) {
                    self.bindEvent(el, event, handlers);
                }
            }
        }
    },
    dump(o, p) {
        if (typeof o === 'object') {
            for (const a in o) {
                $.util.dump(o[a], (p !== undefined ? p + '.' : '') + a);
            }
        } else {
            alert((p !== undefined ? p + ' = ' : '') + o);
        }
    }
});
EOF;
    }
}
