<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Script\Bootstrap;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\JSValue;

/**
 * Include Bootstrap Typeahead assets.
 *
 * @method string call(string $el, string $url, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class Typeahead extends Base
{
    protected function configure()
    {
        $this->setAsset(new Asset('bootstrap-typeahead'));
        $this->addDependencies(['JQuery.NS', 'JQuery.AjaxHelper']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $this->useJavascript('bootstrap3-typeahead.min');
        $defaults = [
            'fitToElement' => true,
            'minLength' => 1,
            'displayText' => <<<EOF
function(data) {
            if (typeof data === 'string') {
                return data;
            }
            if (typeof data === 'object') {
                return data.label;
            }
            return data;
        }
EOF,
        ];
        $defaults = JSValue::create($defaults)->setIndent(2);

        return <<<EOF
$.bstypeahead = function(el, url, options) {
    if (typeof url === 'object' && url.constructor.name === 'Object') {
        options = url;
        url = undefined;
    }
    if (!url) {
        const dataUrl = el.data('url');
        if (dataUrl) {
            url = dataUrl;
        }
    }
    const typeahead = {
        url: url,
        el: null,
        xhr: null,
        options: $defaults,
        render: null,
        delay: 1000,
        init: function(options) {
            const self = this;
            Object.assign(self.options, options);
            if (self.options.source === undefined) {
                self.options.source = function(query, process) {
                    self.query = query;
                    const sourceLoader = function() {
                        const params = typeof self.options.buildQuery === 'function' ? self.options.buildQuery(self.query) : {term: self.query};
                        self.xhr.load(self.url, params, function(data) {
                            process(data);
                        });
                        if (self.delay > 0) {
                            self.tmo = null;
                        }
                    }
                    if (self.delay > 0) {
                        if (self.tmo) {
                            clearTimeout(self.tmo);
                        }
                        self.tmo = setTimeout(sourceLoader, self.delay);
                    } else {
                        sourceLoader();
                    }
                }
            }
            // proxy functions
            ['render', 'matcher'].forEach(fn => {
                if (typeof self.options[fn] === 'function') {
                    self[fn] = self.options[fn];
                    delete self.options[fn];
                }
            });
            self.el.typeahead(self.options);
            const o = self.el.data('typeahead');
            if (self.matcher) {
                o.oMatcher = o.matcher;
                o.matcher = self.matcher;
            }
            if (self.render) {
                o.oRender = o.render;
                o.render = function(items) {
                    this.oRender.call(this, items);
                    self.render.call(this);
                    return this;
                }
            }
        }
    }
    typeahead.el = typeof el === 'string' ? $(el) : el;
    typeahead.xhr = $.ajaxhelper(typeahead.el);
    typeahead.init(options || {});
    typeahead.el.data('_typeahead', typeahead);

    return typeahead;
}
// apply bootstrap style
if (!$.fn.typeahead.defaults._applied) {
    $.fn.typeahead.defaults._applied = true;
    if ($.fn.typeahead.defaults.item) {
        $.fn.typeahead.defaults.item = $.fn.typeahead.defaults.item.replace('dropdown-item', 'dropdown-item text-wrap');
    }
}
EOF;
    }

    /**
     * Call script.
     *
     * @param string $el      The element selector
     * @param string $url     Datypeahead source url
     * @param array $options  The autocomplete options
     */
    public function doCall($el, $url, $options = [])
    {
        if (!isset($options['items'])) {
            $options['items'] = 25;
        }
        $options = JSValue::create($options);
        if ($url) {
            if (!$url instanceof JSValue) {
                $url = JSValue::create($url);
            }
            $this
                ->add(
                    <<<EOF
$.bstypeahead($('$el'), $url, $options);
EOF
                );
        } else {
            $this
                ->add(
                    <<<EOF
$.bstypeahead($('$el'), $options);
EOF
                );
        }
    }
}
