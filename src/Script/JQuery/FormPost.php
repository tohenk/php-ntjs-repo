<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2022 Toha <tohenk@yahoo.com>
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
use NTLAB\JS\Util\JSValue;

/**
 * Handling form submission using ajax.
 *
 * Usage:
 * <?php
 * 
 * use NTLAB\JS\Script;
 * 
 * $script = Script::create('JQuery.FormPost');
 * $script->call('#myform');
 * ?>
 *
 * @author Toha
 */
class FormPost extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.PostHandler', 'JQuery.Util']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    protected function getOverrides()
    {
      return [];
    }

    protected function getErrHelperOptions()
    {
        return [];
    }

    public function getScript()
    {
        $title = $this->trans('Information');
        $error = $this->trans('Error');
        $ok = $this->trans('OK');
        $message = $this->trans('Please wait while your data being saved.');
        $overrides = JSValue::create($this->getOverrides())->setIndent(1);
        $erroptions = JSValue::create($this->getErrHelperOptions())->setIndent(1);

        return <<<EOF
$.formpost = function(form, options) {
    var fp = {
        errhelper: null,
        message: '$message',
        xhr: false,
        progress: true,
        url: null,
        paramName: null,
        onsubmit: null,
        onfail: null,
        onerror: null,
        onalways: null,
        onconfirm: null,
        hasRequired: function(form) {
            var self = this;
            var status = false;
            if (self.errhelper.requiredSelector) {
                form.find(self.errhelper.requiredSelector).each(function() {
                    var el = $(this);
                    if ((el.is('input') || el.is('select') || el.is('textarea')) && el.is(':visible') && !el.is(':disabled')) {
                        var value = el.val();
                        if (!value) {
                            status = true;
                            self.errhelper.focused = el;
                            self.errhelper.focusError();
                            return false;
                        }
                    }
                });
            }
            return status;
        },
        formPost: function(form, url, success_cb, error_cb) {
            form.trigger('formpost');
            if (fp.paramName) {
                var params = form.data('submit-params');
                params = typeof params == 'object' ? params : {};
                params[fp.paramName] = form.serialize();
            } else {
                var params = form.serializeArray();
            }
            var xtra = form.data('submit');
            if ($.isArray(xtra) && xtra.length) {
                for (var i = 0; i < xtra.length; i++) {
                    params.push(xtra[i]);
                }
            }
            fp.errhelper.resetError();
            form.trigger('formrequest');
            if (fp.xhr) {
                var request = $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    xhrFields: {
                        'withCredentials': true
                    }
                });
            } else {
                var request = $.post(url, params);
            }
            request.done(function(data) {
                $.handlePostData(data, fp.errhelper, function(data) {
                    if (typeof success_cb == 'function') {
                        success_cb(data);
                    }
                }, function(data) {
                    if (typeof error_cb == 'function') {
                        error_cb(data);
                    }
                    if (typeof fp.onerror == 'function') {
                        fp.onerror(data);
                    }
                });
            }).fail(function() {
                if (typeof fp.onfail == 'function') {
                    fp.onfail();
                }
            }).always(function() {
                if (typeof fp.onalways == 'function') {
                    fp.onalways();
                }
            });
        },
        bind: function(form) {
            var self = this;
            var submitclicker = function(e) {
                e.preventDefault();
                var submitter = $(this);
                var xtra = [];
                if (submitter.attr('name')) {
                    xtra.push({name: submitter.attr('name'), value: submitter.val()});
                }
                form.data('submit', xtra).submit();
            }
            form.find('[type=submit]').on('click', submitclicker);
            var doit = function() {
                if (self.hasRequired(form) || (typeof self.onsubmit == 'function' && !self.onsubmit(form))) {
                    return false;
                }
                var url = self.url || form.attr('action');
                if (self.progress) {
                    $.ntdlg.wait(self.message);
                }
                self.formPost(form, url, function(json) {
                    form.trigger('formsaved', [json]);
                    if (json.notice) {
                        self.showSuccessMessage('$title', json.notice, {
                            withOkay: !json.redir,
                            autoClose: typeof $.fpRedir == 'function'
                        });
                    }
                    if (json.redir) {
                        if (typeof $.fpRedir == 'function') {
                            $.fpRedir(json.redir);
                        } else {
                            window.location.href = json.redir;
                        }
                    }
                }, function(json) {
                    if (typeof self.onalways == 'function') {
                        self.onalways();
                    }
                    var f = function() {
                        self.errhelper.focusError();
                        form.trigger('formerror', [json]);
                        if (typeof $.formErrorHandler == 'function') {
                            $.formErrorHandler(form);
                        }
                    }
                    // handle global error
                    if (json.global || json.error_msg) {
                        if (json.global && json.global.length) {
                            if (self.errhelper.errorContainer) {
                                self.errhelper.addError(json.global, self.errhelper.errorContainer, self.errhelper.ERROR_ASLIST);
                            } else {
                                // concate error as part of error mesage
                            }
                        }
                        if (json.error_msg) {
                            self.showErrorMessage('$error', json.error_msg, f);
                        } else {
                            f();
                        }
                    } else {
                        f();
                    }
                });
            }
            form.on('submit', function(e) {
                e.preventDefault();
                if (typeof self.onconfirm == 'function') {
                    self.onconfirm(form, doit);
                } else {
                    doit();
                }
            });
        },
        showSuccessMessage: function(title, message, opts) {
            var autoclose = typeof opts.autoClose != 'undefined' ? opts.autoClose : false;
            var withokay = typeof opts.withOkay != 'undefined' ? opts.withOkay : true;
            var buttons = {};
            if (withokay && !autoclose) {
                buttons['$ok'] = function() {
                    $.ntdlg.close($(this));
                }
            }
            var dlg = $.ntdlg.dialog('form_post_success', title, message, $.ntdlg.ICON_SUCCESS, buttons);
            if (autoclose) {
                dlg.on('dialogopen', function() {
                    $.ntdlg.close($(this));
                });
            }
        },
        showErrorMessage: function(title, message, callback) {
            $.ntdlg.dialog('form_post_error', title, message, $.ntdlg.ICON_ERROR, {
                '$ok': function() {
                    $.ntdlg.close($(this));
                }
            }, callback);
        }
    }
    $.extend(fp, $overrides);
    var props = ['message', 'progress', 'xhr', 'url', 'paramName', 'onsubmit', 'onconfirm'];
    $.util.applyProp(props, options, fp, true);
    fp.bind(form);
    fp.errhelper = $.errhelper(form, $erroptions);
    fp.onalways = function() {
        if (fp.progress) {
            $.ntdlg.wait();
        }
    }
    return fp;
}
EOF;
    }

    /**
     * Call script.
     *
     * @param string $selector  The element selector
     * @param string $message  The form post message
     * @return \NTLAB\JS\Script\JQuery\FormPost
     */
    public function call($selector, $message = null)
    {
        $this->includeScript();
        $options = [];
        if (null !== $message) {
            $options['message'] = $this->trans($message);
        }
        $options = JSValue::create($options);
        $this->add(<<<EOF
$.formpost($('$selector'), $options);
EOF
        );
        return $this;
    }
}