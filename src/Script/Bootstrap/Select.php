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

namespace NTLAB\JS\Repo\Script\Bootstrap;

use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\Asset;

/**
 * Include Bootstrap Select assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Select extends Base
{
    protected function configure()
    {
        $this->setAsset(new Asset('bootstrap-select', [Asset::ASSET_JAVASCRIPT => 'js', Asset::ASSET_STYLESHEET => 'css']));
        $this->addDependencies(['JQuery.NS', 'Bootstrap']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $this->useJavascript('bootstrap-select.min');
        $this->useLocaleJavascript('i18n/defaults-%s.min', null, 'en');
        $this->useStylesheet('bootstrap-select.min');

        $loading = $this->trans('Loading&hellip;');

        return <<<EOF
// override defaults to match Bootstrap style
$.fn.selectpicker.Constructor.DEFAULTS.styleBase = 'form-control';
$.fn.selectpicker.Constructor.DEFAULTS.style = '';
$.define('bootstrapSelectHelper', {
    bsClass: 'selectpicker',
    loadingClass: 'bs-loading',
    dropdown: '.dropdown-toggle',
    dropdownMenu: '.dropdown-menu',
    spinner: '<div class="spinner-border spinner-border-sm text-secondary float-end" role="status"><span class="visually-hidden">$loading</span></div>',
    spinnerClass: 'spinner-border',
    fixHeight(el, strategy) {
        let maxHeight = el.css('max-height');
        let minHeight = el.css('min-height');
        switch (strategy) {
        case 'top-start':
        case 'bottom-start':
            if (parseFloat(minHeight) > parseFloat(maxHeight)) {
                const sub = strategy === 'bottom-start' ? 40 : 0;
                if (sub > 0) {
                    maxHeight = (parseFloat(maxHeight) - sub) + 'px';
                    el.css('max-height', maxHeight);
                }
                el.css('min-height', maxHeight);
            }
            break;
        }
    },
    applyHandler(selector, handlers) {
        const self = this;
        selector.each(function() {
            const el = $(this);
            const states = el.data('bs-select-handlers') ? el.data('bs-select-handlers') : [];
            Object.keys(handlers).forEach(function(event) {
                if (states.indexOf(event) < 0) {
                    states.push(event);
                    el.on(event, handlers[event]);
                }
            });
            el.data('bs-select-handlers', states);
        });
    },
    apply() {
        const self = this;
        self.applyHandler($('.' + self.bsClass), {
            ['loaded.bs.select']() {
                $(this).data('bs.select.loaded', true);
                if ($(this).hasClass(self.loadingClass)) {
                    const dropdown = $(this).siblings(self.dropdown);
                    const spinner = dropdown.find('.' + self.spinnerClass);
                    if (!spinner.length) {
                        dropdown.append(self.spinner);
                    }
                    // hack for (min|max)-height
                    dropdown.off('shown.bs.dropdown').on('shown.bs.dropdown', function() {
                        const menu = $(this).siblings(self.dropdownMenu);
                        const s = menu.data('this');
                        if (s.dropdown._popper) {
                            const strategy = s.dropdown._popper.state.options.placement;
                            self.fixHeight(menu, strategy);
                            self.fixHeight(menu.find('div.inner'), strategy);
                        }
                    });
                }
            },
            ['rendered.bs.select']() {
                if ($(this).attr('readonly') || $(this).attr('disabled')) {
                    $(this).siblings(self.dropdown)
                        .prop('disabled', true);
                    ;
                }
            },
            optionLoading() {
                $(this).addClass(self.loadingClass);
                if ($(this).data('bs.select.loaded')) {
                    $(this).trigger('loaded.bs.select');
                }
            },
            optionLoaded() {
                $(this).removeClass(self.loadingClass);
                const dropdown = $(this).siblings(self.dropdown);
                let spinner = dropdown.find('.' + self.spinnerClass);
                if (!spinner.length) {
                    spinner = dropdown.find('svg.' + self.spinnerClass);
                }
                spinner.remove();
                $(this).selectpicker('refresh');
            },
            change() {
                $(this).selectpicker('refresh');
            },
            refresh() {
                $(this).selectpicker('refresh');
            }
        });
    },
    observeState() {
        const self = this;
        const observer = new MutationObserver(function(mutationsList, observer) {
            for (const mutation of mutationsList) {
                if (mutation.attributeName == 'disabled') {
                    const el = $(mutation.target);
                    const disabled = el.is(':disabled');
                    const dd = el.siblings(self.dropdown);
                    if (disabled) {
                        dd.addClass('disabled');
                    } else {
                        dd.removeClass('disabled');
                    }
                    dd.prop('disabled', disabled);
                }
            }
        });
        for (const node of document.getElementsByClassName(self.bsClass)) {
            observer.observe(node, {attributes: true});
        }
    },
    init() {
        const self = this;
        self.apply();
        self.observeState();
        if (document.ntloader) {
            $(document).find('.' + self.bsClass).selectpicker();
        }
    }
});
// apply patch for 1.14.0-beta3
if ($.fn.selectpicker.Constructor.VERSION === '1.14.0-beta3' && $.fn.selectpicker.Constructor.prototype.__buildData === undefined) {
    console.log('=== Applying bootstrap-select 1.14.0-beta3 patch ===');
    $.fn.selectpicker.Constructor.prototype.__buildData = $.fn.selectpicker.Constructor.prototype.buildData;
    $.fn.selectpicker.Constructor.prototype.buildData = function(data, type) {
        const retval = this.__buildData(data, type);
        // remove previous select options
        if (type === 'data' && retval.length && this.selectpicker.main.data && this.selectpicker.main.data.length > retval.length) {
            this.selectpicker.main.data.splice(0, this.selectpicker.main.data.length - retval.length);
        }
        return retval;
    }
}
EOF;
    }
}
