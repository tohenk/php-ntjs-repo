<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015 Toha <tohenk@yahoo.com>
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
use NTLAB\JS\Util\Escaper;

/**
 * JQuery UI iframe dialog.
 *
 * Usage:
 * $.ntdlg.iframe('/path/to/url', 'my', 'My Iframe Dialog', true, 500, 400);
 *
 * @author Toha
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
            self::$dlg_rand = mt_rand();
        }
    }

    public function getScript()
    {
        return <<<EOF
$.define('ntdlg', {
    iframe: function(url, id, title, modal, w, h, close_cb, overflow) {
        var self = this;
        var overflow = overflow || 'hidden';
        var params = {
            modal: modal ? true : false,
            resizable: false,
            buttons: [],
            create: function(event, ui) {
                $.overflow.hide();
            },
            close: function(event, ui) {
                $.overflow.restore();
            },
            open: function() {
                var d = $(this);
                var h = Math.floor(d.height());
                var w = Math.floor(d.width());
                url += (url.indexOf('?') > -1 ? '&' : '?') + 'height=' + h + '&width=' + w + '&closecb=' + (close_cb ? close_cb : '') + '&_dialog=1';
                d.html('<iframe src="' + url + '" frameborder="0" hspace="0" width="' + w + '" height="' + h + '" style="overflow: ' + overflow + ';"></iframe>');
                d.addClass('ui-dialog-iframe-container');
            }
        };
        // adjust dialog size
        var win = $(window);
        if (typeof(h) == 'number') {
            if (h > win.height()) h = win.height() - 10;
            params.height = h;
        }
        if (typeof(w) == 'number') {
            if (w > win.width()) w = win.width() - 10;
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
        return sprintf('%d_%d', static::$dlg_rand, ++static::$dlg_id);
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
    public function call($title, $content, $url, $options = array())
    {
        $dlg = isset($options['dialog_id']) ? $options['dialog_id'] : $this->getDlgId();
        $height = Escaper::escape(isset($options['height']) ? $options['height'] : 500);
        $width = Escaper::escape(isset($options['width']) ? $options['width'] : 600);
        $modal = isset($options['modal']) ? ($options['modal'] ? 'true' : 'false') : 'true';
        $overflow = isset($options['overflow']) ? $options['overflow'] : 'hidden';
        unset($options['dialog_id'], $options['height'], $options['width'], $options['modal'], $options['overflow']);

        $url = $this->getBackend()->url($url);
        if (isset($options['query_string'])) {
            $url .= (false !== strpos($url, '?') ? '&' : '?').$options['query_string'];
            unset($options['query_string']);
        }

        $this->includeScript();
        $this->useScript(<<<EOF
$.ntdlg.closeIframe$dlg = function() {
    $.ntdlg.close('dlg$dlg');
}

$('#ref-dlg$dlg').click(function(e) {
    e.preventDefault();
    $.ntdlg.iframe($(this).attr('href'), 'dlg$dlg', '$title', $modal, $width, $height, '$.ntdlg.closeIframe$dlg', '$overflow');
});

EOF
, Repository::POSITION_LAST);

        return $this->getBackend()->ctag('a', $content, array_merge(array('href' => $url, 'class' => 'dialog', 'id' => 'ref-dlg'.$dlg), $options));
    }
}