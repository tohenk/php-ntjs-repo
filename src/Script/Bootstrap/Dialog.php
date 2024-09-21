<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2024 Toha <tohenk@yahoo.com>
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

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\JSValue;

/**
 * Bootstrap modal wrapper to create and handle dialog.
 *
 * Usage:
 *
 * ```js
 * $.ntdlg.dialog('mydlg', 'A Dialog', 'This is a dialog', {
 *     buttons: {
 *         'OK': function() {
 *             $(this).dialog('close');
 *         }
 *     }
 * });
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Dialog extends Base
{
    const ICON_BOOTSTRAP = 'Bootstrap';
    const ICON_FONTAWESOME = 'FontAwesome';

    protected function configure()
    {
        $this->addDependencies('Bootstrap', 'JQuery.NS', 'JQuery.Util');
        $this->setPosition(Repository::POSITION_FIRST);
        switch ($this->getIconSet()) {
            case static::ICON_BOOTSTRAP:
                $this->addDependencies('BootstrapIcons');
                break;
            case static::ICON_FONTAWESOME:
                $this->addDependencies('FontAwesome');
                break;
        }
    }

    protected function getIconSet()
    {
        return $this->getOption('icon-set', static::ICON_BOOTSTRAP);
    }

    protected function getIcons()
    {
        $loading = $this->trans('Loading...');
        switch ($this->getIconSet()) {
            case static::ICON_BOOTSTRAP:
                return [
                    'ICON_INFO' => 'bi-info-circle text-info fs-1',
                    'ICON_ALERT' =>'bi-exclamation-circle text-warning fs-1',
                    'ICON_ERROR' => 'bi-x-circle text-danger fs-1',
                    'ICON_SUCCESS' => 'bi-check-circle text-success fs-1',
                    'ICON_QUESTION' => 'bi-question-circle text-primary fs-1',
                    'ICON_INPUT' => 'bi-pencil-square text-primary fs-1',
                    'BTN_ICON_OK' => 'bi-check-lg',
                    'BTN_ICON_CANCEL' => 'bi-x-lg',
                    'BTN_ICON_CLOSE' => 'bi-x-lg',
                    'spinnerTmpl' => '<div class="spinner-border text-secondary" role="status"><span class="visually-hidden">'.$loading.'</span></div>',
                ];
            case static::ICON_FONTAWESOME:
                return [
                    'ICON_INFO' => 'fas fa-info-circle text-info',
                    'ICON_ALERT' =>'fas fa-exclamation-circle text-warning',
                    'ICON_ERROR' => 'fas fa-times-circle text-danger',
                    'ICON_SUCCESS' => 'fas fa-check-circle text-success',
                    'ICON_QUESTION' => 'fas fa-question-circle text-primary',
                    'ICON_INPUT' => 'fas fa-edit text-primary',
                    'BTN_ICON_OK' => 'fas fa-check',
                    'BTN_ICON_CANCEL' => 'fas fa-ban',
                    'BTN_ICON_CLOSE' => 'fas fa-times',
                    'iconTmpl' => '<span class="dialog-icon %ICON% fa-fw fa-2x"></span>',
                    'spinnerTmpl' => '<span class="fas fa-circle-notch fa-spin fa-fw fa-2x"></span>',
                ];
        }
    }

    public function getScript()
    {
        $icons = JSValue::create($this->getIcons())->setIndent(2);
        $close = $this->trans('Close');

        return <<<EOF
$.define('ntdlg', {
    ICON_INFO: null,
    ICON_ALERT: null,
    ICON_ERROR: null,
    ICON_SUCCESS: null,
    ICON_QUESTION: null,
    ICON_INPUT: null,
    BTN_ICON_OK: null,
    BTN_ICON_CANCEL: null,
    BTN_ICON_CLOSE: null,
    dialogTmpl:
        '<div id="%ID%" class="modal fade" tabindex="-1" aria-labelledby="%ID%-title">' +
          '<div class="%MODAL%">' +
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
        '<span class="dialog-icon %ICON%"></span>',
    messageTmpl:
        '<div class="d-flex flex-row">' +
          '<div class="flex-shrink-0 p-2">%ICON%</div>' +
          '<div class="flex-grow-1 ms-3 align-self-center">%MESSAGE%</div>' +
        '</div>',
    buttonClass:
        'btn btn-outline-%TYPE%',
    buttonIconTmpl:
        '<span class="%ICON%"></span> %CAPTION%',
    buttonTmpl:
        '<button id="%ID%" type="button" class="%BTNCLASS%">%CAPTION%</button>',
    closeTmpl:
        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="$close"></button>',
    create: function(id, title, message, options) {
        const self = this;
        const dlg_id = '#' + id;
        $(dlg_id).remove();
        if ($.ntdlg.moved && $.ntdlg.moved.refs[id] !== undefined) {
            $('div.' + $.ntdlg.moved.refs[id]).remove();
            delete $.ntdlg.moved.refs[id];
        }
        const closable = options.closable !== undefined ? options.closable : true;
        const buttons = [];
        const handlers = [];
        let cnt = 0;
        if (options.buttons) {
            $.each(options.buttons, function(k, v) {
                let caption, btnType, btnIcon, handler;
                if (Array.isArray(v) || $.isPlainObject(v)) {
                    caption = v.caption ? v.caption : k;
                    btnType = v.type ? v.type : (0 === cnt ? 'primary' : 'secondary');
                    if (v.icon) {
                        btnIcon = v.icon;
                    }
                    handler = typeof v.handler === 'function' ? v.handler : null;
                } else {
                    caption = k;
                    btnType = 0 === cnt ? 'primary' : 'secondary';
                    handler = typeof v === 'function' ? v : null;
                }
                let btnid = id + '_btn_' + caption.replace(/\W+/g, "-").toLowerCase();
                let btnclass = $.util.template(self.buttonClass, {TYPE: btnType});
                if (btnIcon) {
                    caption = $.util.template(self.buttonIconTmpl, {CAPTION: caption, ICON: btnIcon});
                }
                buttons.push($.util.template(self.buttonTmpl, {
                    ID: btnid,
                    BTNCLASS: btnclass,
                    CAPTION: caption
                }));
                if (typeof handler === 'function') {
                    handlers.push({id: btnid, handler: handler});
                }
                cnt++;
            });
        }
        const m = ['modal-dialog', 'modal-dialog-centered'];
        if (options.size) {
            m.push('modal-' + options.size);
        }
        const content = $.util.template(self.dialogTmpl, {
            ID: id,
            TITLE: title,
            MODAL: m.join(' '),
            CLOSE: closable ? self.closeTmpl : '',
            BUTTONS: buttons.join(''),
            CONTENT: message
        });
        $(document.body).append(content);
        const dlg = $(dlg_id);
        // move embedded modal
        const bd = dlg.find('.modal-body');
        const d = bd.find('div.modal');
        if (d.length) {
            if (!$.ntdlg.moved) {
                $.ntdlg.moved = {count: 0, refs: {}}
            }
            $.ntdlg.moved.count++;
            const movedDlg = id + '-moved-' + $.ntdlg.moved.count;
            $.ntdlg.moved.refs[id] = movedDlg;
            d.addClass(movedDlg);
            d.appendTo($(document.body));
        }
        if (buttons.length === 0) {
            dlg.find('.modal-footer').hide();
        }
        $.each(handlers, function(k, v) {
            $('#' + v.id).on('click', function(e) {
                e.preventDefault();
                v.handler.apply(dlg);
            });
        });
        const modal_options = {};
        $.util.applyProp(['backdrop', 'keyboard', 'show', 'remote'], options, modal_options);
        $.util.applyEvent(dlg, ['show.bs.modal', 'shown.bs.modal', 'hide.bs.modal', 'hidden.bs.modal', 'loaded.bs.modal'], options);
        // compatibility with JQuery UI dialog
        $.each({open: 'shown.bs.modal', close: 'hidden.bs.modal'}, function(prop, event) {
            if (typeof options[prop] === 'function') {
                dlg.on(event, options[prop]);
            }
        });
        self._create(dlg[0], modal_options);
        return dlg;
    },
    dialog: function(id, title, message, icon, buttons, close_cb) {
        const self = this;
        icon = icon || self.ICON_INFO;
        buttons = buttons || [];
        message = $.util.template(self.messageTmpl, {
            ICON: $.util.template(self.iconTmpl, {ICON: icon}),
            MESSAGE: message
        });
        const dlg = self.create(id, title, message, {
            'shown.bs.modal': function(e) {
                e.preventDefault();
                let focused = dlg.find('input.focused');
                if (focused.length) {
                    focused.focus();
                }
            },
            'hidden.bs.modal': function(e) {
                e.preventDefault();
                if (typeof close_cb === 'function') {
                    close_cb();
                }
            },
            buttons: buttons
        });
        $.ntdlg.show(dlg);
        return dlg;
    },
    show: function(dlg) {
        const self = this;
        if (dlg && !this.isVisible(dlg)) {
            if (typeof dlg === 'string') {
                dlg = $('#' + dlg);
            }
            let d = self._get(dlg[0]);
            if (!d) {
                d = self._create(dlg[0]);
            }
            if (d) d.show();
        }
    },
    close: function(dlg) {
        const self = this;
        if (dlg) {
            if (typeof dlg === 'string') {
                dlg = $('#' + dlg);
            }
            const d = self._get(dlg[0]);
            if (d) d.hide();
        }
    },
    isVisible: function(dlg) {
        if (dlg) {
            if (typeof dlg === 'string') {
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
            if (typeof dlg === 'string') {
                dlg = $('#' + dlg);
            }
            return dlg.find('.modal-body:first');
        }
    },
    _create: function(el, options) {
        return new bootstrap.Modal(el, options || {});
    },
    _get: function(el) {
        return bootstrap.Modal.getInstance(el);
    },
    init: function() {
        // icon set
        Object.assign(this, $icons);
        // https://stackoverflow.com/questions/19305821/multiple-modals-overlay
        // fix z-index
        const p = bootstrap.Modal.prototype;
        if (p.__showElement === undefined) {
            p.__showElement = p._showElement;
            p._showElement = function(relatedTarget) {
                this.__showElement(relatedTarget);
                let cIdx = zIdx = parseInt($(this._element).css('z-index'));
                if ($.ntdlg.zIndex) {
                    zIdx = Math.max(zIdx, $.ntdlg.zIndex);
                }
                const modalCount = $('.modal:visible').length;
                if (modalCount > 1 || zIdx > cIdx) {
                    zIdx += 10 * (modalCount - 1);
                    $(this._element).css('z-index', zIdx);
                    $(this._backdrop).css('z-index', zIdx - 1);
                }
            }
        }
        // re-add modal-open class if there're still opened modal
        if (p.__resetAdjustments === undefined) {
            p.__resetAdjustments = p._resetAdjustments;
            p._resetAdjustments = function() {
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
        $this
            ->add(
                <<<EOF
$.ntdlg.init();
EOF
            );
    }
}