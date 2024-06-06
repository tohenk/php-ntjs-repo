<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024 Toha <tohenk@yahoo.com>
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

/**
 * Apply delete confirmation to element using attribute `data-delete-confirm`
 * and do post request with its `href`.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class DeleteConfirm extends Base
{
    public function getScript()
    {
        $title = $this->trans('Confirm');
        $information = $this->trans('Information');
        $error = $this->trans('Error');
        $ok = $this->trans('OK');

        return <<<EOF
if ($.confirmDelete === undefined) {
    $.assert('ntdlg.confirm');
    $.confirmDelete = function(container) {
        $(container ? container : document.body).find('[data-delete-confirm]').on('click', function(e) {
            e.preventDefault();
            const el = $(this);
            const message = el.data('delete-confirm');
            $.ntdlg.confirm('data-delete-confirm-dlg', '$title', message, $.ntdlg.ICON_QUESTION,
                function() {
                    $.ntdlg.close($(this));
                    $.post(el.attr('href'))
                        .done(function(json) {
                            if (json.success) {
                                const f = function() {
                                    if (json.redir) {
                                        window.location.href = json.redir;
                                    } else {
                                        if (el.data('delete-container')) {
                                            $(el.data('delete-container')).trigger('reload');
                                        } else {
                                            window.location.reload();
                                        }
                                    }
                                }
                                if (json.message) {
                                    $.ntdlg.dialog('data-delete-confirm-dlg', '$information', json.message, $.ntdlg.ICON_SUCCESS, {
                                        '$ok': {
                                            icon: $.ntdlg.BTN_ICON_OK,
                                            handler: function() {
                                                $.ntdlg.close($(this));
                                            }
                                        },
                                    }, function() {
                                        f();
                                    });
                                } else {
                                    f();
                                }
                            } else {
                                if (json.message) {
                                    $.ntdlg.message('data-delete-confirm-dlg', '$error', json.message, $.ntdlg.ICON_ERROR);
                                }
                            }
                        });
                },
                function() {
                    $.ntdlg.close($(this));
                }
            );
        });
    }
}
EOF;
    }
}
