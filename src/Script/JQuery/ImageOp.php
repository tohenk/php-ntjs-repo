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
 * Provides image manipulation such as cropping, orientation, and others.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class ImageOp extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS', 'JQuery.Util']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $url = JSValue::create($this->getConfig('image-op-url'));
        $image_type = $this->trans('Only image of %IMAGE_TYPES% allowed!');

        return <<<EOF
$.define('imgop', {
    el: null,
    handlers: {},
    imgname: null,
    imgver: null,
    imgurl: null,
    imageTypes: [],
    operations: [],
    url: $url,
    data: {},
    init() {
        $.assert('ntdlg.message');
        if (null === this.el) {
            this.el = $('<div id="img-op-queue" style="display: none;"></div>').appendTo(document.body);
        }
        this.monitorQueue(false);
        this.el.clearQueue();
    },
    process(imgname, imgurl) {
        const self = this;
        let cnt = 0;
        self.imgname = imgname;
        self.imgver = null;
        self.imgurl = null;
        self.init();
        self.data = {file: imgname};
        for (const op of self.operations) {
            if (self.handlers[op.name]) {
                const handler = self.handlers[op.name];
                if (typeof handler.callback === 'function') {
                    cnt++;
                    self.el.queue(function(next) {
                        handler.callback.apply(handler.context, [self, imgname, imgurl, op.params, next]);
                    });
                }
            }
        }
        if (cnt) {
            self.monitorQueue(true);
        } else {
            self.apply();
        }
    },
    apply() {
        const self = this;
        if (Object.keys(self.data).length > 1) {
            if (!self.url) {
                throw new Error('Image op url is missing!');
            }
            $.ajax({
                url: self.url,
                type: 'POST',
                dataType: 'json',
                data: self.data
            }).done(function(json) {
                if (json.success) {
                    if (json.imgname) {
                        self.imgname = json.imgname;
                    }
                    if (json.imgver) {
                        self.imgver = json.imgver;
                    }
                    if (json.image) {
                        self.imgurl = json.image;
                    }
                    self.saveImage();
                }
                if (json.error) {
                    $.ntdlg.message('img-op-error-msg', self.data.file, json.error, $.ntdlg.ICON_ERROR);
                }
            });
        } else {
            self.saveImage();
        }
    },
    saveImage() {
        this.init();
        if ($.uploader.target) {
            $.uploader.target.trigger('imagesaved', [this]);
        } else {
            this.el.trigger('imagesaved', [this]);
        }
    },
    checkImageType(image_name, image_type) {
        let types, allowed = false;
        for (const type in this.imageTypes) {
            if (types) {
                types += ', ' + this.imageTypes[type];
            } else {
                types = this.imageTypes[type];
            }
            if (image_type === type && !allowed) {
              allowed = true;
            }
        }
        if (!types) {
            allowed = true;
        }
        if (!allowed) {
            $.ntdlg.message('img-op-error-msg', image_name, '$image_type'.replace(/%IMAGE_TYPES%/, types), $.ntdlg.ICON_ERROR);
        }
        return allowed;
    },
    handleUpload(data, options) {
        const self = this;
        if (options) {
            self.applyOptions(options);
        }
        if (!self.checkImageType(data.name, data.type)) {
            return;
        }
        const selector = '.template-download .preview a[title="' + data.name + '"]';
        $.uploader.el.find(selector).each(function() {
            const url = $(this).attr('href');
            self.process(data.name, url);
        });
    },
    applyOptions(options) {
        const self = this;
        options = options || {};
        if (options.ops) {
            self.operations = [];
            for (const op of Object.keys(options.ops)) {
                self.addOperation(op, options.ops[op]);
            }
        }
        if (options.images) {
           self.addImageTypes(options.images);
        }
    },
    addHandler(handler, func, ctx) {
        this.handlers[handler] = {name: handler, callback: func, context: ctx || this};
    },
    addOperation(name, data) {
        this.operations.push({name: name, params: data});
    },
    monitorQueue(active) {
        const self = this;
        if (active) {
            self.pid = setTimeout(function() {
                const queue = $.queue(self.el[0]);
                if (queue.length) {
                    self.monitorQueue(true);
                } else {
                    self.pid = null;
                    self.apply();
                }
            }, 1000);
        } else {
            if (self.pid) {
                clearTimeout(self.pid);
            }
        }
    },
    addImageTypes(types) {
        this.imageTypes = types || {};
    },
    onsaved(handler) {
        this.init();
        this.el.on('imagesaved', handler);
    }
});
EOF;
    }
}
