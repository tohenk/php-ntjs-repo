<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024-2026 Toha <tohenk@yahoo.com>
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
use NTLAB\JS\Util\JSValue;

/**
 * Ajax data loader.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class AjaxData extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.PostHandler']);
    }

    public function getScript()
    {
        $confirm = $this->trans('Confirm');
        $message = $this->trans('Do you really want to delete <code>%me%</code>?');
        $error = $this->trans('Error');
        $ok = $this->trans('OK');
        $spinner = JSValue::create($this->getConfig('spinner-template'));

        return <<<EOF
$.ajaxData = function(el, params) {
    $.assert('ntdlg.confirm', 'ntdlg.iframe');
    const _ajaxData = {
        title: null,
        container: null,
        dataUrl: null,
        addUrl: null,
        browseUrl: null,
        callback: null,
        addValueParam: 'add',
        deleteClicker: 'a.del',
        load() {
            const self = this;
            self.container.html($spinner);
            $.get(self.dataUrl, function(html) {
                const container = self.container.html(html);
                container.find(self.deleteClicker).on('click', function(e) {
                    e.preventDefault();
                    self.delete($(this));
                });
                if (typeof self.callback === 'function') {
                    self.callback.apply(self, [container]);
                }
            });
        },
        showError(message) {
            const self = this;
            $.ntdlg.dialog('ajax_data_dlg', '$error', message, $.ntdlg.ICON_ERROR, {
                '$ok': {
                    icon: $.ntdlg.BTN_ICON_OK,
                    handler() {
                        $.ntdlg.close($(this));
                    }
                }
            });
        },
        add(value) {
            const self = this;
            const params = {[self.addValueParam]: value};
            $.ntdlg.close('ajax_data_browse_dlg');
            $.post(self.addUrl, params, function(data) {
                $.handlePostData(data, $.errhelper(), function() {
                    self.load();
                }, function(json) {
                    if (json.error_msg) {
                        self.showError(json.error_msg);
                    }
                });
            });
        },
        delete(el, message = null) {
            const self = this;
            const url = el.attr('href');
            const title = el.data('title');
            message = message || '$message';
            message = message.replace(/%me%/, title);
            $.ntdlg.confirm('ajax_data_dlg', '$confirm', message, function() {
                $.urlPost(url, function(json) {
                    if (json.success) {
                        self.load();
                    } else if (json.error_msg) {
                        self.showError(json.error_msg);
                    }
                });
            });
        },
        browse(url) {
            const self = this;
            url = url || self.browseUrl;
            $.ntdlg.iframe('ajax_data_browse_dlg', url, {
                title: self.title,
                size: 'lg'
            });
        },
        buttonHandler() {
            this.browse();
        },
        init(el, params) {
            const self = this;
            self.container = $(el);
            if (params.title) {
                self.title = params.title;
            }
            if (params.dataUrl) {
                self.dataUrl = params.dataUrl;
            }
            if (params.addUrl) {
                self.addUrl = params.addUrl;
            }
            if (params.browseUrl) {
                self.browseUrl = params.browseUrl;
            }
            if (params.deleteClicker) {
                self.deleteClicker = params.deleteClicker;
            }
            if (params.width) {
                self.width = params.width;
            }
            if (params.height) {
                self.height = params.height;
            }
            if (params.cb) {
                self.callback = params.cb;
            }
            if (params.button) {
                $(params.button).on('click', function(e) {
                    e.preventDefault();
                    self.buttonHandler();
                });
            }
            self.load();
        }
    }
    _ajaxData.init(el, params);

    return _ajaxData;
}
EOF;
    }
}
