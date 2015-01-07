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

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;

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
        $this->addDependencies('JQuery.PostHandler');
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $this->useJavascript('jquery.form');

        return <<<EOF
$.extend({
    formFailHandler: null,
    formAlwaysHandler: null,
    formErrorHandler: null,
    formResetError: function(form) {
        form.find('ul.error_list').remove();
        form.find('.error').removeClass('error');
        form.find('.form-error').hide();
    },
    formPost: function(form, url, success_cb, error_cb) {
        form.trigger('formPost');
        var params = form.formToArray();
        var xtra = form.data('submit');
        if ($.isArray(xtra) && xtra.length) {
            for (var i = 0; i < xtra.length; i++) {
                params.push(xtra[i]);
            }
        } 
        $.formResetError(form);
        $.post(url, params, function(data) {
            $.handlePostData(data, function(data) {
                if (typeof(success_cb) == 'function') {
                    success_cb(data);
                }
            }, function(data) {
                if (typeof(error_cb) == 'function') {
                    error_cb(data);
                }
                if (typeof($.formErrorHandler) == 'function') {
                    $.formErrorHandler(data);
                }
            });
        }).fail(function() {
            if (typeof($.formFailHandler) == 'function') {
                $.formFailHandler();
            }
        }).always(function() {
            if (typeof($.formAlwaysHandler) == 'function') {
                $.formAlwaysHandler();
            }
        });
    }
});

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
        $this->includeDepedencies(array('JQuery.Dialog', 'JQuery.Dialog.Wait'));

        if (null === $message) {
            $message = $this->trans('Please wait while your data being saved.');
        }
        $title = $this->trans('Information');
        $ok = $this->trans('OK');

        $this->useScript(<<<EOF
$('$selector').submit(function(e) {
    var form = $(this);
    var url = form.attr('action');
    $.formAlwaysHandler = function() {
        $.wDialog.close();
    }
    var errFocus = function() {
        if ($.postErr) {
            $.postErr.focus();
        }
    }
    $.postErrFocus = false;
    $.ntdlg.hideOverflow = true;
    $.wDialog.show('$message');
    $.formPost(form, url, function(json) {
        if (json.notice) {
            $.ntdlg.dialog('form_post_success', '$title', json.notice, false, 'ui-icon-check');
        }
        if (json.redir) {
            window.location.href = json.redir;
        }
    }, function(json) {
        if (json.error_msg) {
            var err = json.error_msg;
            if (json.global) {
                err = err + $.formatError(json.global);
            }
            $.ntdlg.dialog('form_post_error', '$title', err, true, 'ui-icon-circle-close', {
                '$ok': function() {
                    $(this).dialog('close');
                    errFocus();
                }
            });
        } else {
            errFocus();
        }
    });

    return false;
}).find('input[type=submit]').click(function(e) {
    var submitter = $(this);
    var xtra = [];
    if (submitter.attr('name')) {
        xtra.push({name: submitter.attr('name'), value: submitter.val()});
    }
    $('$selector').data('submit', xtra).submit();

    return false;
});

EOF
, Repository::POSITION_LAST);

        return $this;
    }
}