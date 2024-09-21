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

/**
 * Image crop operation.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class ImageOpCrop extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS', 'Cropper', 'JQuery.ImageOp']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $title = $this->trans('Image Cropper');
        $no_selection = $this->trans('Please select the crop area first!');
        $apply = $this->trans('Apply');
        $cancel = $this->trans('Cancel');

        return <<<EOF
$.define('imgop.crop', {
    options: {},
    img: null,
    imgname: null,
    selection: null,
    imgop: null,
    next: null,
    dialog: function(img) {
        $.imgop.crop.img = $(img);
        const dlg = $.ntdlg.create('imgcropper', '$title', '', {
            backdrop: 'static',
            open: function() {
                const bd = $.ntdlg.getBody($(this));
                bd.append($(img));
                bd.css({padding: 0});
                new Cropper($(img)[0], $.imgop.crop.options || {});
            },
            buttons: {
                '$apply': {
                    icon: $.ntdlg.BTN_ICON_OK,
                    handler: function() {
                        if (null === $.imgop.crop.selection) {
                            $.ntdlg.message('img-crop-no-selection-msg', '$title', '$no_selection', $.ntdlg.ICON_ALERT);
                            return;
                        }
                        $.ntdlg.close($(this));
                        $.imgop.crop.apply();
                    }
                },
                '$cancel': {
                    icon: $.ntdlg.BTN_ICON_CANCEL,
                    handler: function() {
                        $.ntdlg.close($(this));
                    }
                }
            }
        });
        $.ntdlg.show(dlg);
    },
    apply: function() {
        const self = this;
        self.imgop.data['crop'] = {
            ratio: self.options.aspectRatio ? self.options.aspectRatio : 0,
            selection: self.selection
        };
        if (self.next) {
            self.next();
        }
    },
    doit: function(imgop, imgname, imgurl, params, next) {
        const self = this;
        let offset = 100, options = {
            maxWidth: $(window).width() - offset,
            maxHeight: $(window).height() - (offset * 2)
        };
        self.options = Object.assign({}, params || {}, {
            crop: function(e) {
                self.selection = {
                    x: e.x,
                    y: e.y,
                    w: e.width,
                    h: e.height
                };
            }
        });
        self.imgop = imgop;
        self.next = next;
        self.img = null;
        self.imgname = imgname;
        self.selection = null;
        // load image using Load Image plugin
        loadImage(imgurl, self.dialog, options);
    }
});
EOF;
    }

    public function getInitScript()
    {
        $this
            ->add(
                <<<EOF
$.imgop.addHandler('crop', $.imgop.crop.doit, $.imgop.crop);
EOF
            );
    }
}
