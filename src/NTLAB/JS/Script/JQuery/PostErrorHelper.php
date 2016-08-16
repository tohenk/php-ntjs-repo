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

namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Handling ajax form submission error.
 *
 * @author Toha
 */
class PostErrorHelper extends Base
{
    protected function configure()
    {
        $this->addDependencies(array('JQuery.Util', 'JQuery.ScrollTo'));
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $err = $this->trans('Error');

        return <<<EOF
$.errhelper = function(container, options) {
    var helper = {
        ERROR_REPLACE: 0,
        ERROR_INPLACE: 1,
        ERROR_ASLIST: 2,
        container: null,
        errorContainer: null,
        defaultError: this.ERROR_ASLIST,
        parentClass: 'error',
        listClass: 'error_list',
        toggleClass: null,
        inplace: null,
        focused: null,
        getError: function(err, fmt, sep) {
            var error = '';
            $.map($.isArray(err) ? err : new Array(err), function(e) {
                if (error.length && sep) {
                    error = error + sep;
                }
                var e = $.isArray(e) ? e.join(': ') : e;
                if (fmt) {
                    error = error + $.util.template(fmt, {error: e});
                } else {
                    error = error + e;
                }
            });

            return error;
        },
        showError: function(el) {
            var self = this;
            el.show();
            if (self.toggleClass) el.removeClass(self.toggleClass);
        },
        addError: function(err, el, errtype) {
            var self = this;
            var errtype = errtype ? errtype : self.ERROR_REPLACE;
            switch (errtype) {
                case self.ERROR_REPLACE:
                    var error = self.getError(err, null, ', ');
                    if (error.length) {
                        el.html(error);
                        self.showError(el);
                    }
                    break;
                case self.ERROR_INPLACE:
                    var error = self.getError(err, null, ', ');
                    if (typeof self.inplace == 'function') {
                        self.inplace(el, error);
                    }
                    break;
                case self.ERROR_ASLIST:
                    var error = self.getError(err, '<li>%error%</li>');
                    var ul = el.find('ul.' + self.listClass);
                    if (ul.length) {
                        ul.append(error);
                    } else {
                        $('<ul class="' + self.listClass + '">' + error + '</ul>').appendTo(el);
                    }
                    self.showError(el);
                    break;
                default:
                    break;
            }
        },
        handleError: function(err) {
            var handled = false;
            // reference self using variable
            if ($.isArray(err)) {
                var el = $('#' + err[0]);
                // check if error element is exist
                if (el.length) {
                    handled = true;
                    helper.addError(err[1], helper.defaultError == helper.ERROR_ASLIST ? el.parent() : el, helper.defaultError);
                    if (helper.parentClass) {
                        el.parent().addClass(helper.parentClass).show();
                    }
                    if (helper.focused == null) {
                        helper.focused = el;
                    }
                } else {
                    var err = err[0] + ': ' + err[1];
                }
            }
            if (!handled) {
                // error message shown in container
                if (helper.errorContainer) {
                    helper.addError(err, helper.errorContainer, helper.ERROR_ASLIST);
                } else {
                    if ($.ntdlg) {
                        $.ntdlg.message('dlgerr', '$err', err, true, $.ntdlg.ICON_ERROR);
                    } else {
                        alert(err);
                    }
                }
            }
        },
        focusError: function() {
            var self = this;
            if (self.focused != null) {
                $.scrollto(self.focused);
                self.focused.focus();
            }
        },
        resetError: function() {
            var self = this;
            self.focused = null;
            if (self.container) {
                if (self.listClass) {
                    self.container.find('.' + self.listClass).remove();
                }
                if (self.parentClass) {
                    self.container.find('.' + self.parentClass).removeClass(self.parentClass);
                }
            }
            if (self.errorContainer) {
                self.errorContainer.hide();
            }
        }
    }
    helper.container = container;
    var options = options ? options : {};
    $.util.applyProp(['errorContainer', 'defaultError', 'parentClass', 'listClass', 'toggleClass', 'inplace'], options, helper);
    if (typeof helper.errorContainer == 'string' && helper.container) {
        helper.errorContainer = helper.container.find(helper.errorContainer);
    }

    return helper;
}
EOF;
    }
}