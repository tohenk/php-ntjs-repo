<?php

/*
 * The MIT License
 *
 * Copyright (c) 2016-2024 Toha <tohenk@yahoo.com>
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

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Redirection helper to allow an Ajax Iframe to reload.
 *
 * @author Toha
 */
class IframeRedir extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS', 'Bootstrap.Dialog.IframeLoader');
        $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        return <<<EOF
$.define('ntdlg', {
    iframeRedir: function(url) {
        let pIframe = $(parent.document.body).find('div.ui-dialog-iframe-container iframe');
        if (!pIframe.length) {
            pIframe = $('div.modal.show div.ui-dialog-iframe-container:last');
        }
        if (!pIframe.length || pIframe[0].nodeName == 'IFRAME') {
            window.location.href = url;
        } else {
            $.ntdlg.iframeLoader(pIframe.parents('div.modal.show'), {url: url});
        }
    }
}, true);
EOF;
    }
}