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
    bd: $(document.body),
    pIframe: $(parent.document.body).find('div.ui-dialog-iframe-container iframe'),
    oHeight: null,
    resize: function(grow) {
        var self = this;
        var w = $(parent.window);
        var dlg = self.pIframe.parents('.modal-dialog');
        var maxheight = w.height();
        var h = null;
        if (grow) {
            if (self.bd.height() > self.pIframe.height()) {
                if (self.bd.height() <= maxheight) {
                    h = self.bd.height();
                } else {
                    h = maxheight;
                }
            }
        } else {
            if (self.bd.height() < maxheight) {
                h = self.bd.height();
            }
        }
        if (h != null) {
            self.pIframe.height(h);
        }
    },
    init: function() {
        // bootstrap modal always resized to its content
        var self = this;
        self.resize(false);
        self.resize(true);
        $.formErrorHandler = function() {
            self.resize(true);
        }
    }
});
EOF
;
    }

    public function getInitScript()
    {
        $this->useScript(<<<EOF
$.dlgresize.init();
EOF
, Repository::POSITION_LAST);
    }
}