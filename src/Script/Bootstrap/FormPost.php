<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2021 Toha <tohenk@yahoo.com>
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

use NTLAB\JS\Script\JQuery\FormPost as Base;
use NTLAB\JS\Util\JSValue;

/**
 * Handling form submission using ajax.
 *
 * Usage:
 * <?php
 * 
 * use NTLAB\JS\Script;
 * 
 * $script = Script::create('Bootstrap.FormPost');
 * $script->call('#myform');
 * ?>
 *
 * @author Toha
 */
class FormPost extends Base
{
    protected function configure()
    {
        parent::configure();
        $this->addDependencies(['Bootstrap.Dialog', 'Bootstrap.Dialog.Wait']);
    }

    protected function getOverrides()
    {
        $ok = $this->trans('OK');

        return [
            'showSuccessMessage' => JSValue::createRaw(<<<EOF
function(title, message, opts) {
            var autoclose = typeof opts.autoClose != 'undefined' ? opts.autoClose : false;
            var withokay = typeof opts.withOkay != 'undefined' ? opts.withOkay : true;
            var buttons = {};
            if (withokay && !autoclose) {
                buttons['$ok'] = {
                    icon: $.ntdlg.BTN_ICON_OK,
                    handler: function() {
                        $.ntdlg.close($(this));
                    }
                }
            }
            var dlg = $.ntdlg.dialog('form_post_success', title, message, $.ntdlg.ICON_SUCCESS, buttons);
            if (autoclose) {
                dlg.on('shown.bs.modal', function() {
                    $.ntdlg.close($(this));
                });
            }
        }
EOF
            ),
            'showErrorMessage' => JSValue::createRaw(<<<EOF
function(title, message, callback) {
            $.ntdlg.dialog('form_post_error', title, message, $.ntdlg.ICON_ERROR, {
                '$ok': {
                    icon: $.ntdlg.BTN_ICON_OK,
                    handler: function() {
                        $.ntdlg.close($(this));
                    }
                }
            }, callback);
        }
EOF
            ),
        ];
    }

    protected function getErrHelperOptions()
    {
        return [
            'errorContainer' => '.alert-danger',
            'errorFormat' => JSValue::createRaw('$.errformat.INPLACE'),
            'parentClass' => null,
            'errClass' => 'is-invalid',
            'toggleClass' => 'd-none',
            'listClass' => 'list-unstyled mb-0',
            'inplace' => JSValue::createRaw(<<<EOF
function(el, error) {
            if (el.hasClass('alert-danger')) {
                el.html(error);
            } else {
                var tt = el;
                var f = function(x, a, p) {
                    var errDisp = x.attr(a);
                    if (errDisp) {
                        var xel = p ? x.parents(errDisp) : x.siblings(errDisp);
                        if (xel.length) {
                            return xel;
                        }
                    }
                }
                var xel = f(tt, 'data-err-display');
                if (!xel) xel = f(tt, 'data-err-display-parent', true);
                if (xel) tt = xel;
                // don't add tooltip on hidden input
                if (tt.is('input[type="hidden"]')) {
                    tt = tt.siblings('input');
                }
                var tooltip = bootstrap.Tooltip.getInstance(tt[0]);
                if (tooltip) {
                    tooltip._config.title = error;
                } else {
                    tooltip = new bootstrap.Tooltip(tt[0], {title: error, placement: 'right'});
                }
                xel = f(el, 'data-err-target');
                el = xel ? xel : tt;
                el.data('err-tt', tt);
            }
            return el;
        }
EOF
            ),
            'onErrReset' => JSValue::createRaw(<<<EOF
function(helper) {
            if (helper.container) {
                helper.container.find('.' + helper.errClass).each(function() {
                    var el = $(this);
                    var tt = el.data('err-tt');
                    if (tt) {
                        var tooltip = bootstrap.Tooltip.getInstance(tt[0]);
                        if (tooltip) {
                            tooltip._config.title = '';
                        }
                    }
                    el.removeClass(helper.errClass);
                });
            }
        }
EOF
            ),
        ];
    }
}