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

namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\JSValue;

/**
 * Provides file upload dialog either by statically include it in the document
 * or dynamically loaded from an URL.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class FileUploadDialog extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $url = JSValue::create($this->getConfig('fileupload-dialog-url'));
        $title = $this->trans('File Upload');

        return <<<EOF
$.define('uploaderdlg', {
    selector: '#fileupload',
    el: null,
    dlg: null,
    uploader: null,
    url: $url,
    getEl: function(callback) {
        const self = this;
        const done = function(el) {
            if (el) {
                self.el = el;
            }
            if (typeof callback === 'function') {
                callback(self.el);
            }
        }
        if (self.el) {
            done();
        } else {
            let el = $(self.selector);
            if (!el.length) {
                if (!self.url) {
                    throw new Error('File upload dialog url must be provided!');
                }
                $.get(self.url)
                    .done(function(html) {
                        $(document.body).append(html);
                        el = $(self.selector);
                        if (el.length) {
                            done(el);
                        } else {
                            throw new Error('File upload dialog not available!');
                        }
                    });
            } else {
                done(el);
            }
        }
    },
    show: function(title) {
        const self = this;
        self.getEl(function(el) {
            self.dlg = el.parents('.modal');
            self.dlg.find('.modal-title').text(title || '$title');
            $.ntdlg._create(self.dlg, {
                'hidden.bs.modal': function(e) {
                    self.uploader.target = null;
                }
            });
            $.ntdlg.show(self.dlg);
        });
    },
    close: function() {
        const self = this;
        $.ntdlg.close(self.dlg);
    }
});
EOF;
    }
}
