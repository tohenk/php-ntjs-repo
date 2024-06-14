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
use NTLAB\JS\Repository;
use NTLAB\JS\Util\JSValue;

/**
 * Image save operation.
 *
 * @method string call(string $selector, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class ImageOpSave extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.Util', 'JQuery.ImageOp']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.imgop.saveimg = function(options) {
    $.assert('ntdlg.message');
    const _saveimg = {
        url: null,
        el: null,
        img: null,
        autoclose: true,
        delete: false,
        callback: null,
        reloadImage: function(src) {
            const self = this;
            if (self.img) {
                const img = $(self.img);
                img.attr('src', (src ? src : img.attr('src')) + '?_x=' + Date.now());
            }
        },
        saveImg: function(imgop) {
            const self = this;
            $.ajax({
                url: self.url,
                type: 'POST',
                dataType: 'json',
                data: {
                    file: imgop.imgname,
                    version: imgop.imgver ? imgop.imgver : ''
                }
            }).done(function(json) {
                if (json.success) {
                    self.reloadImage();
                    if (self.autoclose) {
                        $.uploader.close();
                    }
                    if (self.delete) {
                        $.uploader.delete(imgop.imgname);
                    }
                    if (typeof self.callback === 'function') {
                        self.callback(json, imgop.imgname);
                    }
                }
                if (json.error) {
                    $.ntdlg.message('img-op-save-error-msg', imgop.imgname, json.error, $.ntdlg.ICON_ERROR);
                }
            });
        },
        save: function(imgop) {
            const self = this;
            if (self.el) {
                if (typeof self.el === 'string') {
                    $(self.el).val(imgop.imgname);
                }
                if (typeof self.el === 'object') {
                    if (self.el.file) {
                        $(self.el.file).val(imgop.imgname);
                    }
                    if (self.el.version) {
                        $(self.el.version).val(imgop.imgver ? imgop.imgver : '');
                    }
                }
                if (!self.url && self.img && imgop.imgurl) {
                    self.reloadImage(imgop.imgurl);
                }
            }
            if (self.url) {
                self.saveImg(imgop);
            }
        },
        init: function(options) {
            const self = this;
            $.util.applyProp(['url', 'el', 'img', 'delete', 'callback'], options, self);
            $.imgop.onsaved(function(e, imgop) {
                self.save(imgop);
            });
            return this;
        }
    }
    return _saveimg.init(options || {});
}
$.fn.saveimg = function(options) {
    this.each(function() {
        const imgsave = options || $(this).data('saveimg-opts');
        if (imgsave && typeof imgsave.save === 'function') {
            if (!imgsave.url && $(this).data('save-url')) {
                imgsave.url = $(this).data('save-url');
            }
            $(this).on('imagesaved', function(e, imgop) {
                imgsave.save(imgop);
            });
        }
    });

    return this;
}
EOF;
    }

    /**
     * Call script.
     *
     * @param string $selector  The element selector to apply for imagesaved event
     * @param array $options    Options
     */
    public function doCall($selector, $options = [])
    {
        $options = JSValue::create($options)->setIndent(1);
        $this
            ->add(
                <<<EOF
$('$selector')
    .data('saveimg-opts', $.imgop.saveimg($options))
    .saveimg();
EOF
            );
    }
}
