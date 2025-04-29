<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2025 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Repo\Script\JQuery\Dialog;

use NTLAB\JS\Script\JQuery\UI as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\JSValue;

/**
 * JQuery UI iframe dialog.
 *
 * Usage:
 *
 * ```js
 * $.ntdlg.iframe('/path/to/url', 'my', 'My Iframe Dialog', true, 500, 400);
 * ```
 *
 * @method string call(string $title, string $content, string $url, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class Iframe extends Base
{
    /**
     * @var int
     */
    protected static $dlg_rand = null;

    /**
     * @var int
     */
    protected static $dlg_id = 0;

    protected function configure()
    {
        $this->addDependencies('JQuery.NS', 'JQuery.Dialog');
        $this->setPosition(Repository::POSITION_FIRST);
        if (null === self::$dlg_rand) {
            self::$dlg_rand = $this->getConfig('random-dlg-id', mt_rand());
        }
    }

    public function getScript()
    {
        return <<<EOF
$.define('ntdlg', {
    iframe(id, url, options) {
        const self = this;
        options = options || {};
        const title = options.title || '';
        let w = options.w || 600;
        let h = options.h || 500;
        const overflow = options.overflow || 'hidden';
        const close_cb = options.close_cb || null;
        const params = {
            resizable: false,
            buttons: [],
            create(event, ui) {
                $.overflow.hide();
            },
            close(event, ui) {
                $.overflow.restore();
            },
            open() {
                const d = $(this);
                const h = Math.floor(d.height());
                const w = Math.floor(d.width());
                url += (url.indexOf('?') > -1 ? '&' : '?') + 'height=' + h + '&width=' + w + '&closecb=' + (close_cb ? close_cb : '') + '&_dialog=1';
                d.html('<iframe src="' + url + '" frameborder="0" hspace="0" width="' + w + '" height="' + h + '" style="overflow: ' + overflow + ';"></iframe>');
                d.addClass('ui-dialog-iframe-container');
            }
        };
        // adjust dialog size
        const win = $(window);
        if (typeof h === 'number') {
            if (h > win.height()) {
                h = win.height() - 10;
            }
            params.height = h;
        }
        if (typeof w === 'number') {
            if (w > win.width()) {
                w = win.width() - 10;
            }
            params.width = w;
        }
        $.ntdlg.create(id, title, '', params);
    }
}, true);
EOF;
    }

    /**
     * Get the next dialog id.
     *
     * @return string
     */
    protected function getDlgId()
    {
        return sprintf('%s_%d', static::$dlg_rand, ++static::$dlg_id);
    }

    /**
     * Create an iframe dialog.
     *
     * @param string $title  The dialog title
     * @param string $content  The dialog link content
     * @param string $url  The dialog url
     * @param array $options  The dialog options
     * @return string
     */
    public function doCall($title, $content, $url, $options = [])
    {
        $dlg = isset($options['dialog_id']) ? $options['dialog_id'] : $this->getDlgId();
        $height = isset($options['height']) ? $options['height'] : 500;
        $width = isset($options['width']) ? $options['width'] : 600;
        $iframeOptions = ['title' => $title, 'h' => $height, 'w' => $width];
        if (isset($options['overflow'])) {
            $iframeOptions['overflow'] = $options['overflow'];
        }
        $iframeOptions['close_cb'] = '$.ntdlg.closeIframe'.$dlg;
        $iframeOptions = JSValue::create($iframeOptions)->setIndent(1);
        $this
            ->add(
                <<<EOF
$.ntdlg.closeIframe$dlg = function() {
    $.ntdlg.close('dlg$dlg');
}
$('#ref-dlg$dlg').on('click', function(e) {
    e.preventDefault();
    $.ntdlg.iframe('dlg$dlg', $(this).attr('href'), $iframeOptions);
});
EOF
            );
        $url = $this->getBackend()->url($url);
        if (isset($options['query_string'])) {
            $url .= (false !== strpos($url, '?') ? '&' : '?').$options['query_string'];
        }
        unset($options['dialog_id'], $options['height'], $options['width'], $options['overflow'], $options['query_string']);

        return $this->getBackend()->ctag('a', $content, array_merge(['href' => $url, 'class' => 'dialog', 'id' => 'ref-dlg'.$dlg], $options));
    }
}
