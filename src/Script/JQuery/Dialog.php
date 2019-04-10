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

namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery\UI as Base;
use NTLAB\JS\Repository;

/**
 * JQuery UI dialog wrapper to create and handling dialog.
 *
 * Usage:
 * $.ntdlg.dialog('mydlg', 'A Dialog', 'This is a dialog', {
 *     buttons: {
 *         'OK': function() {
 *             $(this).dialog('close');
 *         }
 *     }
 * });
 *
 * @author Toha
 */
class Dialog extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS', 'JQuery.Overflow', 'JQuery.Util');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $width = $this->getOption('width', 400);

        return <<<EOF
$.define('ntdlg', {
    ICON_INFO: 'info',
    ICON_ALERT: 'alert',
    ICON_ERROR: 'circle-close',
    ICON_SUCCESS: 'circle-check',
    ICON_QUESTION: 'help',
    ICON_INPUT: 'pencil',
    hideOverflow: null,
    minWidth: $width,
    dialogTmpl: '<div id="%ID%" class="ntdlg-container" title="%TITLE%">%CONTENT%</div>',
    iconTmpl: '<span class="ui-icon ui-icon-%ICON%"></span>',
    messageTmpl: '<div class="msg-container"><div class="msg-icon" style="float:left;margin:0 10px 0 0;padding:0 10px 0 10px;">%ICON%</div><div class="msg-content" style="margin-left: 50px;">%MESSAGE%</div></div>',
    create: function(id, title, message, params) {
        var self = this;
        var dlg_id = '#' + id;
        var content = $.util.template(self.dialogTmpl, {
            ID: id,
            TITLE: title,
            CONTENT: message
        });
        $(dlg_id).remove();
        $(document.body).append(content);
        $(dlg_id).dialog(params);
    },
    dialog: function(id, title, message, modal, icon, buttons, close_cb) {
        var self = this;
        var modal = modal || true;
        var icon = icon || $.ntdlg.ICON_INFO;
        var buttons = buttons || [];
        var message = $.util.template(self.messageTmpl, {
            ICON: $.util.template(self.iconTmpl, {ICON: icon}),
            MESSAGE: message
        });
        self.create(id, title, message, {
            modal: modal,
            width: 'auto',
            create: function() {
                if ($.ntdlg.hideOverflow) {
                    $.overflow.hide();
                }
            },
            open: function() {
                var dlg = $(this);
                if ($.ntdlg.minWidth && dlg.width() < $.ntdlg.minWidth) {
                    dlg.dialog('option', 'width', $.ntdlg.minWidth);
                }
            },
            close: function() {
                if ($.ntdlg.hideOverflow) {
                    $.overflow.restore();
                }
                if (typeof close_cb == 'function') {
                    close_cb();
                }
            },
            buttons: buttons
        });
    },
    show: function(dlg) {
        if (dlg && !this.isVisible(dlg)) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            dlg.dialog('open');
        }
    },
    close: function(dlg) {
        if (dlg) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            dlg.dialog('close');
        }
    },
    isVisible: function(dlg) {
        if (dlg) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            return dlg.dialog('isOpen');
        }
    },
    getBody: function(dlg) {
        if (dlg) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            return dlg;
        }
    }
}, true);
EOF;
    }
}