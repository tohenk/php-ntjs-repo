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
use NTLAB\JS\Util\JSValue;

/**
 * BlueImp gallery helper.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Gallery extends Base
{
    protected function configure()
    {
        $this->addDependencies(['Gallery']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $url = JSValue::create($this->getConfig('gallery-template-url'));

        return <<<EOF
$.ntgallery = function(container, options) {
    options = options || {};
    const items = [];
    const g = $(container ? container : document.body).find('a[data-gallery]');
    g.each(function() {
        items.push(this);
    });
    g.on('click', function(e){
        e.preventDefault();
        if (items.length) {
            options.index = this;
            const gallery = function(el) {
                const setup = options.setup || {
                    controls: true,
                    contain: false,
                    carousel: false
                };
                for (const key in setup) {
                    const klass = 'blueimp-gallery-' + key;
                    if (setup[key]) {
                        if (!el.hasClass(klass)) {
                            el.addClass(klass);
                        }
                    } else {
                        el.removeClass(klass);
                    }
                }
                blueimp.Gallery(items, options)
            }
            const gselector = '.blueimp-gallery';
            let gtmpl = $(gselector);
            if (!gtmpl.length) {
                const url = $url;
                if (!url) {
                    throw new Error('Gallery template url must be provided!');
                }
                $.get(url)
                    .done(function(html) {
                        $(document.body).append(html);
                        gtmpl = $(gselector);
                        if (gtmpl.length) {
                            gallery(gtmpl);
                        } else {
                            throw new Error('Gallery template not available!');
                        }
                    });
            } else {
                gallery(gtmpl);
            }
        }
    });
}
EOF;
    }

    public function getInitScript()
    {
        $this
            ->add(
                <<<EOF
$.ntgallery();
EOF
            );
    }
}
