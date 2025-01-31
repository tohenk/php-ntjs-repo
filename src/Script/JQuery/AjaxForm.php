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

/**
 * Provides form handling using Ajax.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class AjaxForm extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.Util']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $yes = $this->trans('Yes');
        $no = $this->trans('No');

        return <<<EOF
$.ajaxform = function(options) {
    $.assert('ntdlg');
    const handler = {
        id: null,
        title: null,
        okay: '$yes',
        cancel: '$no',
        formurl: null,
        posturl: null,
        params: null,
        size: null,
        hideOnOkay: true,
        loaded: null,
        callback: null,
        dlg: null,
        init(data) {
            const self = this;
            Object.assign(self, data);
            $.get(self.formurl, self.params || {})
                .done(function(html) {
                    const buttons = {};
                    if (self.okay) {
                        buttons[self.okay] = {
                            icon: $.ntdlg.BTN_ICON_OK,
                            handler() {
                                const form = $(this).find('form');
                                if (form.length) {
                                    form.submit();
                                } else {
                                    $.ntdlg.close($(this));
                                }
                            }
                        }
                    }
                    if (self.cancel) {
                        buttons[self.cancel] = {
                            icon: $.ntdlg.BTN_ICON_CANCEL,
                            handler() {
                                $.ntdlg.close($(this));
                            }
                        }
                    }
                    const opts = {
                        buttons: buttons,
                        backdrop: 'static',
                        open() {
                            const form = self.dlg.find('form');
                            for (const v of ['.focused', '.form-control']) {
                                const focused = form.find('CLASS:visible:not([readonly])'.replace(/CLASS/, v));
                                if (focused.length) {
                                    focused[0].focus();
                                    break;
                                }
                            }
                        },
                        close() {
                            $.ntdlg.close($(this));
                        }
                    }
                    if (self.size) {
                        opts.size = self.size;
                    }
                    self.dlg = $.ntdlg.create(self.id, self.title, html, opts);
                    const form = self.dlg.find('form');
                    if (form.length) {
                        if (typeof $.formpost !== 'function') {
                            console.error('FormPost is not included!');
                        }
                        $.formpost(form, {
                            progress: false,
                            url: self.posturl
                        });
                        form.on('formsaved', function(e, json) {
                            if (self.hideOnOkay) {
                                $.ntdlg.close(self.dlg);
                            }
                            if (typeof self.callback === 'function') {
                                self.callback(json);
                            }
                        });
                    }
                    if (typeof self.loaded === 'function') {
                        self.loaded(self.dlg);
                    }
                    $.ntdlg.show(self.dlg);
                })
            ;
        },
        show() {
            const self = this;
            if (self.dlg) {
                $.ntdlg.show(self.dlg);
            }
        }
    }
    const data = {};
    const props = ['id', 'title', 'okay', 'cancel', 'formurl', 'posturl', 'params', 'hideOnOkay', 'size', 'loaded', 'callback'];
    $.util.applyProp(props, options || {}, data);
    handler.init(data);

    return handler;
}
EOF;
    }
}
