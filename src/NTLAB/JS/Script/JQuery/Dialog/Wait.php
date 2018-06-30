<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Script\JQuery\Dialog;

use NTLAB\JS\Script\JQuery\UI as Base;
use NTLAB\JS\Repository;

/**
 * JQuery UI wait dialog to show a waiting dialog while in progress.
 *
 * Usage:
 * $.ntdlg.wait('I\'m doing something');
 * // do something here
 * $.ntdlg.wait('I\'m doing another thing');
 * // close wait dialog
 * $.ntdlg.wait();
 *
 * @author Toha
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
        create: function() {
            if (null === this.d) {
                $(document.body).append('<div id="wdialog" title="$title">$message</div>');
                this.d = $('#wdialog').dialog({
                    autoOpen: false,
                    modal: true,
                    width: $width,
                    height: $height,
                    button: [],
                    create: function() {
                        if ($.ntdlg.hideOverflow) {
                            $.overflow.hide();
                        }
                    },
                    open: function() {
                        $.ntdlg.waitdlg.active = true;
                    },
                    close: function() {
                        $.ntdlg.waitdlg.active = false;
                        if ($.ntdlg.hideOverflow) {
                            $.overflow.restore();
                        }
                    }
                });
            }
        },
        show: function(msg) {
            if (this.active) this.close();
            this.create();
            if (msg) {
                $(this.d).text(msg);
            }
            $.ntdlg.show(this.d);
        },
        close: function() {
            if (this.d) {
                $.ntdlg.close(this.d);
            }
        }
    },
    wait: function(message) {
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