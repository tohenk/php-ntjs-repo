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
 * A JQuery number formatter helper.
 *
 * @method string call(string $display_el, string $value_el, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class NumberFormat extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.define('nf', {
    format(el) {
        const self = this;
        const f = self.factory();
        f.init(el.data('nf'));
        let v = el.val();
        if (v !== '') {
            if (!el.data('nf_formatted')) {
                v = parseFloat(v);
            }
            el.val(f.format(v));
            el.data('nf_formatted', true);
        }
    },
    apply(el) {
        const self = this;
        const f = self.factory();
        f.init(el.data('nf'));
        const ref = el.data('nf_value');
        const v = f.value(el.val());
        if (v) {
            this.format(el);
        }
        ref.val(v);
        if (ref.is('input[type="hidden"]')) {
            ref.data('nf_skip', true);
            ref.trigger('change');
            ref.data('nf_skip', false);
        }
    },
    factory() {
        const self = this;
        if (self.internal) {
            return self.internal;
        }
        if ($.nfFactory) {
            return $.nfFactory;
        }
        throw new Error('Number formatter factory is required!');
    },
    init(display, value, options) {
        const self = this;
        options = options || {};
        $(display)
            .data('nf', options)
            .data('nf_value', $(value))
            .on('blur', function(e) {
                self.apply($(display));
            })
        ;
        $(value).on('change', function(e) {
            if (!$(this).data('nf_skip')) {
                self.apply($(display).val($(value).val()));
            }
        });
        self.format($(display));
    }
});
EOF;
    }

    /**
     * Call script.
     *
     * @param string $display_el  The display element selector
     * @param string $value_el    The value element selector
     * @param array  $options     Formatting options
     */
    public function doCall($display_el, $value_el, $options)
    {
        $options = JSValue::createInlined($options);
        $this
            ->add(
                <<<EOF
$.nf.init('$display_el', '$value_el', $options);
EOF
            );
    }
}
