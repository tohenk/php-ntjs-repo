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

namespace NTLAB\JS\Script\Bootstrap;

use NTLAB\JS\Script\Bootstrap as Base;
use NTLAB\JS\Repository;

/**
 * Bootstrap dialog wrapper to create and handling dialog.
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
        $this->addDependencies('JQuery.NS', 'JQuery.Util');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $close = $this->trans('Close');

        return <<<EOF
$.define('ntdlg', {
    ICON_INFO: 'info-sign',
    ICON_ALERT: 'exclamation-sign',
    ICON_ERROR: 'remove-sign',
    ICON_SUCCESS: 'ok',
    ICON_QUESTION: 'question-sign',
    ICON_INPUT: 'pencil',
    dialogTmpl:
        '<div id="%ID%" class="modal fade" tabindex="-1" role="dialog">' +
        '  <div class="%MODAL%" role="document">' +
        '    <div class="modal-content">' +
        '      <div class="modal-header">' +
        '        %CLOSE%' +
        '        <h4 class="modal-title">%TITLE%</h4>' +
        '      </div>' +
        '      <div class="modal-body">%CONTENT%</div>' +
        '      <div class="modal-footer">%BUTTONS%</div>' +
        '    </div>' +
        '  </div>' +
        '</div>',
    iconTmpl:
        '<span class="dialog-icon glyphicon glyphicon-%ICON%"></span>',
    messageTmpl:
        '<div class="row">' +
        '  <div class="col-sm-1">%ICON%</div>' +
        '  <div class="col-sm-10">%MESSAGE%</div>' +
        '</div>',
    buttonTmpl:
        '<button id="%ID%" type="button" class="btn btn-%TYPE%">%CAPTION%</button>',
    closeTmpl:
        '<button type="button" class="close" data-dismiss="modal" aria-label="$close"><span aria-hidden="true">&times;</span></button>',
    create: function(id, title, message, options) {
        var self = this;
        var dlg_id = '#' + id;
        $(dlg_id).remove();
        if ($.ntdlg.moved && typeof $.ntdlg.moved.refs[id] != 'undefined') {
            $('div.' + $.ntdlg.moved.refs[id]).remove();
            delete $.ntdlg.moved.refs[id];
        }
        var modal = typeof options.modal != 'undefined' ? options.modal : true;
        var closable = typeof options.closable != 'undefined' ? options.closable : true;
        var buttons = [];
        var handlers = [];
        var cnt = 0;
        if (options.buttons) {
            $.each(options.buttons, function(k, v) {
                if ($.isArray(v)) {
                    var caption = v.caption ? v.caption : k;
                    var btnType = v.type ? v.type : 'default';
                    var handler = typeof v.handler == 'function' ? v.handler : null;
                } else {
                    var caption = k;
                    var btnType = 0 == cnt ? 'primary' : 'default';
                    var handler = typeof v == 'function' ? v : null;
                }
                var btnid = id + '_btn_' + caption.replace(/\W+/g, "-").toLowerCase();
                buttons.push($.util.template(self.buttonTmpl, {
                    ID: btnid,
                    TYPE: btnType,
                    CAPTION: caption
                }));
                if (typeof handler == 'function') {
                    handlers.push({id: btnid, handler: handler});
                }
                cnt++;
            });
        }
        var content = $.util.template(self.dialogTmpl, {
            ID: id,
            TITLE: title,
            MODAL: 'modal-dialog' + (options.size ? ' modal-' + options.size : ''),
            CLOSE: closable ? self.closeTmpl : '',
            BUTTONS: buttons.join(''),
            CONTENT: message
        });
        $(document.body).append(content);
        var dlg = $(dlg_id);
        // move embedded modal
        var bd = dlg.find('.modal-body');
        var d = bd.find('div.modal');
        if (d.length) {
            if (!$.ntdlg.moved) {
                $.ntdlg.moved = {count: 0, refs: {}}
            }
            $.ntdlg.moved.count++;
            var movedDlg = id + '-moved-' + $.ntdlg.moved.count;
            $.ntdlg.moved.refs[id] = movedDlg;
            d.addClass(movedDlg);
            d.appendTo($(document.body));
        }
        if (buttons.length == 0) {
            dlg.find('.modal-footer').hide();
        }
        $.each(handlers, function(k, v) {
            $('#' + v.id).on('click', function(e) {
                e.preventDefault();
                v.handler.apply(dlg);
            });
        });
        var opts = ['backdrop', 'keyboard', 'show', 'remote'];
        var events = ['show.bs.modal', 'shown.bs.modal', 'hide.bs.modal', 'hidden.bs.modal', 'loaded.bs.modal'];
        var modal_options = {};
        $.util.applyProp(opts, options, modal_options);
        $.util.applyEvent(dlg, events, options);
        // compatibility with JQuery UI dialog
        $.each({open: 'shown.bs.modal', close: 'hidden.bs.modal'}, function(prop, event) {
            if (typeof options[prop] == 'function') {
                dlg.on(event, options[prop]);
            }
        });
        dlg.modal(modal_options);

        return dlg;
    },
    dialog: function(id, title, message, modal, icon, buttons, close_cb) {
        var self = this;
        var modal = modal || true;
        var icon = icon || 'info-sign';
        var buttons = buttons || [];
        var message = $.util.template(self.messageTmpl, {
            ICON: $.util.template(self.iconTmpl, {ICON: icon}),
            MESSAGE: message
        });
        var dlg = self.create(id, title, message, {
            modal: modal,
            'hidden.bs.modal': function(e) {
                e.preventDefault();
                if (typeof close_cb == 'function') {
                    close_cb();
                }
            },
            buttons: buttons
        });
        dlg.modal('show');

        return dlg;
    },
    show: function(dlg) {
        if (dlg && !this.isVisible(dlg)) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            dlg.modal('show');
        }
    },
    close: function(dlg) {
        if (dlg) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            dlg.modal('hide');
        }
    },
    isVisible: function(dlg) {
        if (dlg) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            if (dlg.length) {
                if (dlg.hasClass('modal') && dlg.is(':visible')) {
                    return true;
                }
            }

            return false;
        }
    },
    getBody: function(dlg) {
        if (dlg) {
            if (typeof dlg == 'string') {
                dlg = $('#' + dlg);
            }
            return dlg.find('.modal-body:first');
        }
    }
}, true);
EOF;
    }
}