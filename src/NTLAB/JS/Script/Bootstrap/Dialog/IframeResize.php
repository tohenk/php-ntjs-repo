<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Script\Bootstrap\Dialog;

use NTLAB\JS\Script\Bootstrap as Base;
use NTLAB\JS\Repository;

/**
 * JQuery iframe dialog auto-height implementation to resize the
 * parent dialog height according to its frame content height.
 *
 * Simply include this script into the iframe content (not the dialog).
 *
 * @author Toha
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
    resize: function(grow) {
        var self = this;
        var dlg = parent.$(self.pIframe.parents('div.modal.show'));
        if (!dlg.length) return;
        var maxheight = dlg.height() - 60;
        var mc = dlg.find('.modal-content');
        var header = mc.find('.modal-header');
        if (header.length) maxheight -= header.outerHeight(true);
        var footer = mc.find('.modal-footer');
        if (footer.length) maxheight -= footer.outerHeight(true);
        var bd = mc.find('.modal-body');
        if (bd.length) maxheight -= (bd.outerHeight(true) - bd.height());
        var isIframe = self.pIframe[0].nodeName == 'IFRAME';
        var bd = isIframe ? $(document.body) : mc.find('.modal-body');
        var h;
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
    init: function() {
        // bootstrap modal always resized to its content
        var self = this;
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
        $this->useScript(<<<EOF
$.dlgresize.init();
EOF
, Repository::POSITION_LAST);
    }
}