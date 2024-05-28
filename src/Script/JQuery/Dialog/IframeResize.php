<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2024 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Script\JQuery\Dialog;

use NTLAB\JS\Script\JQuery\UI as Base;
use NTLAB\JS\Repository;

/**
 * JQuery UI iframe dialog auto-height implementation to resize the
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
    bd: $(document.body),
    pIframe: $(parent.document.body).find('div.ui-dialog-iframe-container iframe'),
    dlg: null,
    oHeight: null,
    resize: function(grow) {
        const self = this;
        let doit = false;
        if (grow) {
            if (self.bd.height() > self.dlg.height() && self.bd.height() <= self.oHeight) {
                doit = true;
            }
        } else {
            if (self.bd.height() < self.dlg.height()) {
                doit = true;
            }
        }
        if (doit) {
            self.dlg.height(self.bd.height());
            self.pIframe.height(self.bd.height());
        }
    },
    init: function() {
        const self = this;
        self.dlg = self.pIframe.parents('div.ui-dialog-content');
        const h = self.dlg.attr('oheight');
        if (h) {
            self.oHeight = h;
        } else {
            self.oHeight = self.dlg.height();
            self.dlg.attr('oheight', self.oHeight);
        }
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
        $this
            ->add(
                <<<EOF
$.dlgresize.init();
EOF
            );
    }
}