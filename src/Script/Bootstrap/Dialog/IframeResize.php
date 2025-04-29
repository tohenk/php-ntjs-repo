<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2025 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Repo\Script\Bootstrap\Dialog;

use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Bootstrap iframe dialog auto-height implementation to resize the
 * parent dialog height according to its frame content height.
 *
 * Simply include this script into the iframe content (not the dialog).
 *
 * @author Toha <tohenk@yahoo.com>
 */
class IframeResize extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS');
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.define('dlgresize', {
    pIframe: null,
    resize(grow) {
        const self = this;
        const dlg = parent.$(self.pIframe.parents('div.modal.show'));
        if (!dlg.length) {
            return;
        }
        let maxheight = dlg.height() - 60;
        let mc = dlg.find('.modal-content');
        let header = mc.find('.modal-header');
        if (header.length) {
            maxheight -= header.outerHeight(true);
        }
        let footer = mc.find('.modal-footer');
        if (footer.length) {
            maxheight -= footer.outerHeight(true);
        }
        let bd = mc.find('.modal-body');
        if (bd.length) {
            maxheight -= (bd.outerHeight(true) - bd.height());
        }
        let isIframe = self.pIframe[0].nodeName === 'IFRAME';
        bd = isIframe ? $(document.body) : mc.find('.modal-body');
        let h;
        if (grow || grow == undefined) {
            if (isIframe) {
                h = Math.min(maxheight, bd.height());
            } else if (bd.height() > maxheight) {
                h = maxheight;
            }
        }
        if (!grow) {
            if (isIframe && bd.height() < maxheight) h = bd.height();
        }
        if (h) {
            if (!isIframe && bd.height() > h) {
                self.pIframe.css({'max-height': h, 'overflow-y': 'auto'});
            }
            self.pIframe.css({'height': h});
        }
    },
    init() {
        // bootstrap modal always resized to its content
        const self = this;
        self.pIframe = $(parent.document.body).find('div.ui-dialog-iframe-container iframe');
        if (!self.pIframe.length) {
            self.pIframe = $('div.modal.show div.ui-dialog-iframe-container:last');
        }
        self.resize();
        $.formErrorHandler = function() {
            self.resize(true);
        }
    }
});
EOF;
    }

    public function getInitScript()
    {
        $this
            ->add(
                <<<EOF
$.dlgresize.init();
EOF
            );
    }
}
