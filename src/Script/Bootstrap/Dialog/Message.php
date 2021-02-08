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

namespace NTLAB\JS\Script\Bootstrap\Dialog;

use NTLAB\JS\Script\Bootstrap as Base;
use NTLAB\JS\Repository;

/**
 * JQuery UI message dialog.
 *
 * Usage:
 * $.ntdlg.message('my', 'Message', 'This is an example');
 *
 * @author Toha
 */
class Message extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS', 'Bootstrap.Dialog');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $ok = $this->trans('OK');

        return <<<EOF
$.define('ntdlg', {
    message: function(id, title, message, icon) {
        $.ntdlg.dialog(id, title, message, icon, {
            '$ok': {
                icon: $.ntdlg.BTN_ICON_OK,
                handler: function() {
                    $.ntdlg.close($(this));
                }
            }
        });
    }
}, true);
EOF;
    }
}