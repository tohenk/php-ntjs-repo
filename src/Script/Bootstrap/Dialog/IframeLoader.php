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
 * Iframe loader helper.
 *
 * @author Toha
 */
class IframeLoader extends Base
{
    protected function configure()
    {
      $this->addDependencies('JQuery.NS');
      $this->setPosition(Repository::POSITION_FIRST);
    }

    public function getScript()
    {
        return <<<EOF
$.define('ntdlg', {
    iframeLoader: function(dlg, options) {
        var self = this;
        var bd = dlg.find('.modal-body');
        if (bd.hasClass('ui-dialog-iframe-container')) {
            options.ajax = bd[0].nodeName == 'IFRAME' ? false : true;
        } else {
            bd.addClass('ui-dialog-iframe-container');
        }
        if (options.ajax) {
            bd.css({'max-height': '', 'height': ''});
            bd.html('<span class="fas fa-sync-alt fa-spin fa-fw"></span>');
            $.get(options.url).then(function(html) {
                bd.html(html);
            });
        } else {
            var height = dlg.height();
            var header = dlg.find('.modal-header');
            if (header.length) height -= (2 * header.outerHeight(true));
            bd.html('<iframe src="' + options.url + '" frameborder="0" hspace="0" width="100%" height="' + height + '" style="overflow: ' + overflow + ';"></iframe>');
        }
    }
}, true);
EOF;
    }
}