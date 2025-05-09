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

namespace NTLAB\JS\Repo\Script\JQuery\Dialog;

use NTLAB\JS\Repo\Script\JQuery\UI as Base;
use NTLAB\JS\Repository;

/**
 * JQuery UI wait dialog to show a waiting dialog while in progress.
 *
 * Usage:
 *
 * ```js
 * $.ntdlg.wait('I\'m doing something');
 * // do something here
 * $.ntdlg.wait('I\'m doing another thing');
 * // close wait dialog
 * $.ntdlg.wait();
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Wait extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS', 'JQuery.Overflow');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $message = $this->trans('Loading...');
        $title = $this->trans('Please wait');
        $width = 400;
        $height = 150;

        return <<<EOF
$.define('ntdlg', {
    waitdlg: {
        d: null,
        active: false,
        create() {
            const self = this;
            if (self.d === null) {
                $(document.body).append('<div id="wdialog" title="$title">$message</div>');
                self.d = $('#wdialog').dialog({
                    autoOpen: false,
                    modal: true,
                    width: $width,
                    height: $height,
                    button: [],
                    create() {
                        if ($.ntdlg.hideOverflow) {
                            $.overflow.hide();
                        }
                    },
                    open() {
                        $.ntdlg.waitdlg.active = true;
                    },
                    close() {
                        $.ntdlg.waitdlg.active = false;
                        if ($.ntdlg.hideOverflow) {
                            $.overflow.restore();
                        }
                    }
                });
            }
        },
        show(msg) {
            const self = this;
            if (self.active) {
                self.close();
            }
            self.create();
            if (msg) {
                $(self.d).text(msg);
            }
            $.ntdlg.show(self.d);
        },
        close() {
            const self = this;
            if (self.d) {
                $.ntdlg.close(self.d);
            }
        }
    },
    wait(message) {
        if (message) {
            $.ntdlg.waitdlg.show(message);
        } else {
            $.ntdlg.waitdlg.close();
        }
    }
}, true);
EOF;
    }
}
