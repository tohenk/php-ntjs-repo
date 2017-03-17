<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016 Toha <tohenk@yahoo.com>
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
 * Bootstrap dialog to show a waiting dialog while in progress.
 *
 * Usage:
 * $.wDialog.show('I\'m doing something');
 * // do something here
 * $.wDialog.show('I\'m doing another thing');
 * // do another thing here
 * $.wDialog.close();
 *
 * @author Toha
 */
class Wait extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $message = $this->trans('Loading...');
        $title = $this->trans('Please wait');
        $width = 400;
        $height = 150;

        return <<<EOF
$.define('wDialog', {
    d: null,
    id: 'wdialog',
    active: false,
    create: function() {
        var self = this;
        if (null === self.d) {
            var content =
                '<div id="' + self.id + '" class="modal fade" tabindex="-1" role="dialog">' +
                  '<div class="modal-dialog" role="document">' +
                    '<div class="modal-content">' +
                      '<div class="modal-header">' +
                        '<h4 class="modal-title">$title</h4>' +
                      '</div>' +
                      '<div class="modal-body">$message</div>' +
                    '</div>' +
                  '</div>' +
                '</div>';
            $(document.body).append(content);
            self.d = $('#' + self.id);
            self.d.modal({keyboard: false});
            self.d.on('shown.bs.modal', function(e) {
                self.active = true;
            });
            self.d.on('hidden.bs.modal', function(e) {
                self.active = false;
            });
        }
    },
    show: function(msg) {
        var self = this;
        if (self.active) self.close();
        self.create();
        if (msg) {
            self.d.find('.modal-body').html(msg);
        }
        $.ntdlg.show(self.d);
    },
    close: function() {
        var self = this;
        if (self.d) {
            $.ntdlg.close(self.d);
        }
    }
});
EOF;
    }
}