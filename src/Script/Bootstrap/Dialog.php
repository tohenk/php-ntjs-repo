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
        $this->addDependencies('Bootstrap', 'JQuery.NS', 'JQuery.Util', 'FontAwesome');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        $close = $this->trans('Close');

        return <<<EOF
$.define('ntdlg', {
    ICON_INFO: 'fas fa-info-circle text-info',
    ICON_ALERT: 'fas fa-exclamation-circle text-warning',
    ICON_ERROR: 'fas fa-times-circle text-danger',
    ICON_SUCCESS: 'fas fa-check-circle text-success',
    ICON_QUESTION: 'fas fa-question-circle text-primary',
    ICON_INPUT: 'fas fa-edit text-primary',
    BTN_ICON_OK: 'fas fa-check',
    BTN_ICON_CANCEL: 'fas fa-ban',
    BTN_ICON_CLOSE: 'fas fa-times',
    dialogTmpl:
        '<div id="%ID%" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="%ID%-title">' +
          '<div class="%MODAL%" role="document">' +
            '<div class="modal-content">' +
              '<div class="modal-header">' +
                '<h5 id="%ID%-title" class="modal-title">%TITLE%</h5>' +
                '%CLOSE%' +
              '</div>' +
              '<div class="modal-body">%CONTENT%</div>' +
              '<div class="modal-footer">%BUTTONS%</div>' +
            '</div>' +
          '</div>' +
        '</div>',
    iconTmpl:
        '<span class="dialog-icon %ICON% fa-fw fa-2x"></span>',
    messageTmpl:
        '<div class="media d-flex align-items-center">' +
          '<div class="p-2 mr-1">%ICON%</div>' +
          '<div class="media-body">%MESSAGE%</div>' +
        '</div>',
    buttonClass:
        'btn btn-outline-%TYPE%',
    buttonIconTmpl:
        '<span class="%ICON%"></span> %CAPTION%',
    buttonTmpl:
        '<button id="%ID%" type="button" class="%BTNCLASS%">%CAPTION%</button>',
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
        var closable = typeof options.closable != 'undefined' ? options.closable : true;
        var buttons = [];
        var handlers = [];
        var cnt = 0;
        if (options.buttons) {
            $.each(options.buttons, function(k, v) {
                var caption, btnType, btnIcon, handler;
                if ($.isArray(v) || $.isPlainObject(v)) {
                    caption = v.caption ? v.caption : k;
                    btnType = v.type ? v.type : (0 == cnt ? 'primary' : 'secondary');
                    if (v.icon) btnIcon = v.icon;
                    handler = typeof v.handler == 'function' ? v.handler : null;
                } else {
                    caption = k;
                    btnType = 0 == cnt ? 'primary' : 'secondary';
                    handler = typeof v == 'function' ? v : null;
                }
                var btnid = id + '_btn_' + caption.replace(/\W+/g, "-").toLowerCase();
                var btnclass = $.util.template(self.buttonClass, {TYPE: btnType});
                if (btnIcon) {
                    caption = $.util.template(self.buttonIconTmpl, {CAPTION: caption, ICON: btnIcon});
                }
                buttons.push($.util.template(self.buttonTmpl, {
                    ID: btnid,
                    BTNCLASS: btnclass,
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
    dialog: function(id, title, message, icon, buttons, close_cb) {
        var self = this;
        var icon = icon || self.ICON_INFO;
        var buttons = buttons || [];
        var message = $.util.template(self.messageTmpl, {
            ICON: $.util.template(self.iconTmpl, {ICON: icon}),
            MESSAGE: message
        });
        var dlg = self.create(id, title, message, {
            'shown.bs.modal': function(e) {
                e.preventDefault();
                var focused = dlg.find('input.focused');
                if (focused.length) {
                    focused.focus();
                }
            },
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
    },
    fixModal: function() {
        // https://stackoverflow.com/questions/19305821/multiple-modals-overlay
        // fix z-index
        if (typeof $.fn.modal.Constructor.prototype.__showElement == 'undefined') {
            $.fn.modal.Constructor.prototype.__showElement = $.fn.modal.Constructor.prototype._showElement;
            $.fn.modal.Constructor.prototype._showElement = function(relatedTarget) {
                this.__showElement(relatedTarget);
                var cIdx = zIdx = parseInt($(this._element).css('z-index'));
                if ($.ntdlg.zIndex) {
                    zIdx = Math.max(zIdx, $.ntdlg.zIndex);
                }
                var modalCount = $('.modal:visible').length;
                if (modalCount > 1 || zIdx > cIdx) {
                    zIdx += 10 * (modalCount - 1);
                    $(this._element).css('z-index', zIdx);
                    $(this._backdrop).css('z-index', zIdx - 1);
                }
            }
        }
        // re-add modal-open class if there're still opened modal
        if (typeof $.fn.modal.Constructor.prototype.__resetAdjustments == 'undefined') {
            $.fn.modal.Constructor.prototype.__resetAdjustments = $.fn.modal.Constructor.prototype._resetAdjustments;
            $.fn.modal.Constructor.prototype._resetAdjustments = function() {
                this.__resetAdjustments();
                if ($('.modal:visible').length > 0) {
                    $(document.body).addClass('modal-open');
                }
            }
        }
    }
}, true);
EOF;
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Script::getInitScript()
     */
    public function getInitScript()
    {
        $this->add(<<<EOF
$.ntdlg.fixModal();
EOF);
    }
}