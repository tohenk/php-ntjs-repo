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
    createTemplate(height, ratio) {
        return `<cropper-canvas background style="min-height:\${height}px;">
                <cropper-image rotatable scalable skewable translatable></cropper-image>
                <cropper-shade hidden></cropper-shade>
                <cropper-handle action="select" plain></cropper-handle>
                <cropper-selection aspect-ratio="\${ratio}" initial-coverage="1.0" keyboard movable precise resizable>
                    <cropper-grid role="grid" bordered covered></cropper-grid>
                    <cropper-crosshair centered></cropper-crosshair>
                    <cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>
                    <cropper-handle action="n-resize"></cropper-handle>
                    <cropper-handle action="e-resize"></cropper-handle>
                    <cropper-handle action="s-resize"></cropper-handle>
                    <cropper-handle action="w-resize"></cropper-handle>
                    <cropper-handle action="ne-resize"></cropper-handle>
                    <cropper-handle action="nw-resize"></cropper-handle>
                    <cropper-handle action="se-resize"></cropper-handle>
                    <cropper-handle action="sw-resize"></cropper-handle>
                </cropper-selection>
            </cropper-canvas>`;
    },
    inSelection(check, against) {
        return (
            check.x >= against.x
            && check.y >= against.y
            && (check.x + check.width) <= (against.x + against.width)
            && (check.y + check.height) <= (against.y + against.height)
        );
    },
    update(selection) {
        const self = this;
        const canvasRect = self.instance.getCropperCanvas()
            .getBoundingClientRect();
        const imageRect = self.instance.getCropperImage()
            .getBoundingClientRect();
        const r = {
            x: imageRect.left - canvasRect.left,
            y: imageRect.top - canvasRect.top,
            width: imageRect.width,
            height: imageRect.height
        }
        self.cropdata.width = r.width;
        self.cropdata.height = r.height;
        self.cropdata.selection = null;
        if (selection && self.inSelection(selection, r)) {
            self.cropdata.selection = {
                x: selection.x - r.x,
                y: selection.y - r.y,
                w: selection.width,
                h: selection.height,
            }
        }
    },
    cropper(img, options) {
        const self = this;
        const h = Math.min(img.naturalHeight, Math.floor(window.innerHeight * 0.6));
        self.instance = new Cropper.default(img, {
            template: self.createTemplate(h, options.aspectRatio || '')
        });
        const cropperImage = self.instance.getCropperImage();
        cropperImage.\$ready(im => {
            const cropperSelection = self.instance.getCropperSelection();
            cropperSelection.addEventListener('change', function(e) {
                self.update(e.detail);
            });
            self.update(cropperSelection);
        });
    },
    dialog(img) {
        const self = this;
        self.img = $(img);
        const dlg = $.ntdlg.create('imgcropper', '$title', '', {
            backdrop: 'static',
            size: 'lg',
            open() {
                const bd = $.ntdlg.getBody($(this));
                bd.append(self.img);
                bd.css({padding: 0});
                self.cropper(img, self.options);
            },
            buttons: {
                '$apply': {
                    icon: $.ntdlg.BTN_ICON_OK,
                    handler() {
                        if (null === self.cropdata.selection) {
                            $.ntdlg.message('img-crop-no-selection-msg', '$title', '$no_selection', $.ntdlg.ICON_ALERT);
                            return;
                        }
                        $.ntdlg.close($(this));
                        self.apply();
                    }
                },
                '$cancel': {
                    icon: $.ntdlg.BTN_ICON_CANCEL,
                    handler() {
                        $.ntdlg.close($(this));
                    }
                }
            }
        });
        $.ntdlg.show(dlg);
    },
    apply() {
        const self = this;
        if (self.cropdata.selection) {
            self.imgop.data['crop'] = self.cropdata;
        }
        if (self.next) {
            self.next();
        }
    },
    doit(imgop, imgname, imgurl, params, next) {
        const self = this;
        const offset = 100, options = {
            maxWidth: $(window).width() - offset,
            maxHeight: $(window).height() - (offset * 2)
        }
        self.options = Object.assign({}, params || {});
        self.imgop = imgop;
        self.next = next;
        self.img = null;
        self.imgname = imgname;
        self.selection = null;
        self.cropdata = {
            ratio: self.options.aspectRatio ? self.options.aspectRatio : 0,
            selection: self.selection
        }
        // load image using Load Image plugin
        loadImage(imgurl, img => self.dialog(img), options);
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
