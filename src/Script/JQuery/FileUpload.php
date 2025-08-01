<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024-2025 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Repo\Script\JQuery;

use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\JSValue;

/**
 * Handle file upload using BlueImp File Upload.
 *
 * @method string call(string $selector, string $clicker, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class FileUpload extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS', 'JQuery.Util', 'Templates', 'LoadImage', 'CanvasToBlob',
            'FileUpload', 'JQuery.FileUploadDialog', 'JQuery.Gallery']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    protected function getChunkSize()
    {
        // to disable chunked upload, set to 0
        if ('auto' === ($chunkSize = $this->getConfig('fileupload-chunk-size', 'auto'))) {
            $chunkSize = min([$this->getConfigBytes(ini_get('upload_max_filesize')), 100 * 1024]);
        } else {
            $chunkSize = $this->getConfigBytes($chunkSize);
        }

        return $chunkSize;
    }

    /**
     * Get byte size from shortened notation.
     *
     * @param string $val
     * @return int
     */
    public function getConfigBytes($val)
    {
        $suffix = strtolower(substr(trim($val), -1));
        $value = (int) $val;
        switch ($suffix) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $this->fixIntegerOverflow($value);
    }

    /**
     * Fix for overflowing signed 32 bit integers, works for sizes up to 2^32-1 bytes (4 GiB - 1).
     *
     * @param int $size
     * @return int
     */
    public function fixIntegerOverflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }

        return $size;
    }

    public function getScript()
    {
        $chunkSize = $this->getChunkSize();
        $confirm = $this->trans('Confirm');
        $delete = $this->trans('Are you sure want to delete <code>%FILE%</code>?');
        $delete_all = $this->trans('Are you sure want to delete all files?');
        $not_allowed = $this->trans('Filetype not allowed');

        return <<<EOF
$.define('uploader', {
    el: null,
    target: null,
    mimeTypes: [],
    mimeError: null,
    addEvent: 'fileuploadadd',
    completeEvent: 'fileuploadcompleted',
    startEvent: 'fileuploadstarted',
    finishEvent: 'fileuploadfinished',
    fileuploadOptions: {
        maxChunkSize: $chunkSize,
        sequentialUploads: true
    },
    init(cb) {
        const self = this;
        if ($.uploaderdlg) {
            $.uploaderdlg.uploader = self;
            $.uploaderdlg.getEl(function(el) {
                self.el = el;
                if (!self.el.data('uploader-initialized')) {
                    self.el.data('uploader-initialized', true);
                    self.el.fileupload(self.fileuploadOptions);
                    self.bindHandler();
                }
                self.clear();
                self.list();
                if (typeof cb === 'function') {
                    cb();
                }
            });
        }
    },
    bindHandler() {
        const self = this;
        self.el
            .on(self.startEvent, function(e, data) {
                self.setProgress(true);
            })
            .on(self.addEvent, function(e, data) {
                if (data && data.files) {
                    self.setEmpty(false);
                    for (const file of data.files) {
                        const ftype = file.type || 'application/octet-stream';
                        if (self.mimeTypes.length && self.mimeTypes.indexOf(ftype) < 0) {
                            const error = self.mimeError || '$not_allowed';
                            file.error = error.replace(/%mime_type%/, ftype);
                        }
                    }
                }
            })
            .on(self.completeEvent, function(e, data) {
                if (!self.hasPendingUpload() && data && data.files) {
                    let i = 0;
                    for (const file of data.files) {
                        const filename = data.result.files[i++].name;
                        self.el.queue(function(next) {
                            self.select(filename, file);
                            next();
                        });
                    }
                }
            })
            .on(self.finishEvent, function(e, data) {
                if (!self.hasPendingUpload()) {
                    self.setProgress(false);
                }
                self.applyHandlers();
            })
        ;
        self.el.find('.delete-all').on('click', function(e) {
            e.preventDefault();
            const files = self.el.find('.template-download .delete');
            if (files.length) {
                $.ntdlg.confirm('uploader-confirm-dlg', '$confirm', '$delete_all', function() {
                    $.each(files, function() {
                        $(this)
                            .data('force-delete', true)
                            .trigger('click');
                    });
                });
            }
        });
    },
    applyHandlers() {
        const self = this;
        self.el.find('.template-download .select')
            .on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const err = btn.data('err');
                if (0 === err.length) {
                    const data = {
                        name: btn.data('file'),
                        type: btn.data('type')
                    };
                    self.select(data.name, data);
                }
            })
        ;
        // enable gallery
        $.ntgallery(self.el);
    },
    clear() {
        const self = this;
        self.el.find('.template-download').remove();
        self.setProgress(self.hasPendingUpload());
    },
    list() {
        const self = this;
        self.el.each(function() {
            const that = this;
            self.setLoading(!self.hasPendingUpload());
            self.setEmpty(false);
            $.get(this.action, {mime: self.mimeTypes})
                .done(function(json) {
                    self.setLoading(false);
                    if (!json || (Array.isArray(json.files) && !json.files.length)) {
                        self.setEmpty(true);
                    }
                    if (json) {
                        const e = $.Event('click');
                        self.el.fileupload('option', 'done').call(that, e, {result: json});
                    }
                })
            ;
        });
    },
    hasPendingUpload() {
        const self = this;
        return self.el.find('.template-upload').length ? true : false;
    },
    setProgress(state) {
        const self = this;
        if (state) {
            self.el.find('.fileupload-progress').removeClass('d-none');
        } else {
            self.el.find('.fileupload-progress').addClass('d-none');
        }
    },
    setLoading(state) {
        const self = this;
        if (state) {
            self.el.find('.files .loading').removeClass('d-none');
        } else {
            self.el.find('.files .loading').addClass('d-none');
        }
    },
    setEmpty(state) {
        const self = this;
        if (state) {
            self.el.find('.files .empty').removeClass('d-none');
        } else {
            self.el.find('.files .empty').addClass('d-none');
        }
    },
    show(options) {
        const self = this;
        options = options || {};
        self.mimeTypes = options.mimeTypes !== undefined ? (Array.isArray(options.mimeTypes) ? options.mimeTypes : [options.mimeTypes]) : [];
        self.mimeError = options.mimeError !== undefined ? options.mimeError : null;
        self.init(function() {
            if (typeof options.select === 'function') {
                self.el.data('fileuploadselect', options.select);
            } else {
                self.el.data('fileuploadselect', null);
            }
            $.uploaderdlg.show(options.title);
        });
    },
    close() {
        if ($.uploaderdlg) {
            $.uploaderdlg.close();
        }
    },
    select(filename, file) {
        const self = this;
        const f = self.el.data('fileuploadselect');
        if (typeof f === 'function') {
            f(filename, file);
        }
    }
});
$.fn.uploader = function(options) {
    this.each(function() {
        switch (options) {
            case 'show':
                $.uploader.show($(this).data('uploader'));
                break;
            case 'close':
                $.uploader.close();
                break;
            default:
                if (options === undefined) {
                    return $(this).data('uploader');
                }
                $(this).data('uploader', options);
                break;
        }
    });
    return this;
}
if ($.blueimp.fileupload.prototype.__deleteHandler === undefined) {
    $.assert('ntdlg.confirm');
    $.blueimp.fileupload.prototype.__deleteHandler = $.blueimp.fileupload.prototype._deleteHandler;
    $.blueimp.fileupload.prototype._deleteHandler = function(e) {
        e.preventDefault();
        const self = this;
        const file = $(e.currentTarget).data('file');
        const handler = function() {
            $.blueimp.fileupload.prototype.__deleteHandler.call(self, e);
        }
        if ($(e.target).data('force-delete')) {
            handler();
        } else {
            $.ntdlg.confirm('uploader-confirm-dlg', '$confirm', '$delete'.replace(/%FILE%/, file), function() {
                handler();
            });
        }
    }
}
EOF;
    }

    /**
     * Call script.
     *
     * @param string $selector  The upload element selector
     * @param string $clicker   The upload clicker element selector
     * @param array $options    The upload options
     */
    public function doCall($selector, $clicker, $options = [])
    {
        $params = [];
        if (isset($options['mime_types'])) {
            $params['mimeTypes'] = $options['mime_types'];
        }
        if (isset($options['mime_error'])) {
            $params['mimeError'] = $this->trans($options['mime_error']);
        }
        if (!isset($options['onselect'])) {
            $params['select'] = <<<EOF
function(filename, data) {
    $('$selector').val(filename).uploader('close');
}
EOF;
        }
        $params = JSValue::create($params)->setIndent(1);
        $this
            ->add(
                <<<EOF
$('$selector').uploader($params);
$clicker.on('click', function(e) {
    e.preventDefault();
    $.uploader.target = $('$selector');
    $('$selector').uploader('show');
});
EOF
            );
    }
}
