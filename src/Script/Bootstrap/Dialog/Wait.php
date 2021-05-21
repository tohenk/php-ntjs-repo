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

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Bootstrap modal to show a waiting dialog while in progress.
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
        $this->addDependencies('JQuery.NS');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $message = $this->trans('Loading...');
        $title = $this->trans('Please wait');

        return <<<EOF
$.define('ntdlg', {
    waitdlg: {
        id: 'wdialog',
        getDlg: function(create) {
            var self = this;
            var dlg = $('#' + self.id);
            if (dlg.length) {
                self.dlg = dlg;
            } else {
                if (create) {
                    var spinner = $.ntdlg.spinnerTmpl;
                    var content =
                        '<div id="' + self.id + '" class="modal fade" tabindex="-1">' +
                          '<div class="modal-dialog modal-dialog-centered">' +
                            '<div class="modal-content">' +
                              '<div class="modal-header">$title</div>' +
                              '<div class="modal-body">' +
                                '<div class="d-flex">' +
                                  '<div class="flex-shrink-0 icon">' + spinner + '</div>' +
                                  '<div class="flex-grow-1 ms-3">' +
                                    '<div class="msg">$message</div>' +
                                  '</div>' +
                                '</div>' +
                              '</div>' +
                            '</div>' +
                          '</div>' +
                        '</div>';
                    $(document.body).append(content);
                    dlg = $('#' + self.id);
                    dlg.on('shown.bs.modal', function(e) {
                        var dlg = $(this);
                        dlg.addClass('active');
                        if (dlg.hasClass('dismiss')) {
                            setTimeout(function() {
                                $.ntdlg.close(dlg);
                            }, 500);
                        }
                    });
                    dlg.on('hidden.bs.modal', function(e) {
                        var dlg = $(this);
                        dlg.removeClass('active');
                    });
                    $.ntdlg._create(dlg[0], {keyboard: false});
                    self.dlg = dlg;
                }
            }
        },
        isActive: function() {
            var self = this;
            self.getDlg();
            if (self.dlg) {
                return self.dlg.hasClass('show') ? true : false;
            }
        },
        show: function(msg) {
            var self = this;
            self.close();
            self.getDlg(true);
            if (msg) {
                self.dlg.find('.modal-body .msg').html(msg);
            }
            self.dlg.removeClass('dismiss');
            $.ntdlg.show(self.dlg);
        },
        close: function() {
            var self = this;
            self.getDlg();
            if (self.dlg) {
                if (self.dlg.hasClass('active')) {
                    $.ntdlg.close(self.dlg);
                } else {
                    self.dlg.addClass('dismiss');
                }
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