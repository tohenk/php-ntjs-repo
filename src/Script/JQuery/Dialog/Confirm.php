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

use NTLAB\JS\Script\JQuery\UI as Base;
use NTLAB\JS\Repository;

/**
 * JQuery UI confirm dialog.
 *
 * Usage:
 *
 * ```js
 * $.ntdlg.confirm('my', 'Confirm', 'Do you want to do something?', $.ntdlg.ICON_QUESTION,
 *     function() {
 *         alert('Okay!');
 *     },
 *     function() {
 *         alert('Nope!');
 *     }
 * );
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Confirm extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS', 'JQuery.Dialog');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $yes = $this->trans('Yes');
        $no = $this->trans('No');

        return <<<EOF
$.define('ntdlg', {
    confirm(id, title, message, icon, cb_yes, cb_no) {
        if (typeof icon === 'function') {
            cb_no = cb_yes;
            cb_yes = icon;
            icon = undefined;
        }
        icon = icon || $.ntdlg.ICON_QUESTION;
        $.ntdlg.dialog(id, title, message, icon, {
            ['$yes']() {
                $.ntdlg.close($(this));
                if (typeof cb_yes === 'function') {
                    cb_yes();
                }
            },
            ['$no']() {
                $.ntdlg.close($(this));
                if (typeof cb_no === 'function') {
                    cb_no();
                }
            }
        });
    }
}, true);
EOF;
    }
}
