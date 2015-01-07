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
 * Ajax POST handler which provide basic functionality to
 * mark the error returned.
 *
 * Usage:
 * $.urlPost('/path/to/url', function(data) {
 *     // do something with data
 * });
 *
 * @author Toha
 */
class PostHandler extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.Dialog.Message');
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $err = $this->trans('Error');

        return <<<EOF
$.extend({
    postErr: null,
    postErrFocus: true,
    formatError: function(error, err_class) {
        var err_class = err_class || 'error_list';
        var message = '';
        $.map($.isArray(error) ? error : new Array(error), function(e) {
            message = message + '<li>' + ($.isArray(e) ? e[1] : e) + '</li>';
        });

        return '<ul class="' + err_class + '">' + message + '</ul>';
    },
    addError: function(el, error, err_class) {
        $($.formatError(error, err_class)).appendTo(el);
    },
    handlePostError: function(data) {
        if ($.isArray(data)) {
            var el = $('#' + data[0]);
            $.addError(el.parent(), data[1]); 
            el.parent().show();
            if ($.postErr === null) {
                $.postErr = el;
                if ($.postErrFocus) {
                    $.postErr.focus();
                }
            }
        } else {
            $.ntdlg.message('dlgerr', '$err', data, true, 'ui-icon-notice');
        }
    },
    handlePostData: function(data, success_cb, error_cb) {
        $.postErr = null;
        var json = typeof(data) === 'object' ? data : $.parseJSON(data);
        if (json.success) {
            if (typeof success_cb == 'function') {
                success_cb(json);
            }
        } else {
            $.map($.isArray(json.error) ? json.error : new Array(json.error), $.handlePostError);
            if (typeof error_cb == 'function') {
                error_cb(json);
            }
        }
    },
    urlPost: function(url, callback) {
        $.post(url, function(data) {
            $.handlePostData(data, callback);
        });
    }
});

EOF;
    }
}