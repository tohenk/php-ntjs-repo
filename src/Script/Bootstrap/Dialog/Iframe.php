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
use NTLAB\JS\Util\JSValue;

/**
 * JQuery UI iframe dialog.
 *
 * Usage:
 * $.ntdlg.iframe('my', '/path/to/url', {
 *     title: 'My Iframe Dialog',
 *     modal: true,
 *     width: 500,
 *     height: 400
 * });
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
        $this->addDependencies('JQuery.NS', 'JQuery.Util', 'Bootstrap.Dialog', 'Bootstrap.Dialog.IframeLoader');
        $this->setPosition(Repository::POSITION_FIRST);
        if (null === self::$dlg_rand) {
            self::$dlg_rand = mt_rand();
        }
    }

    public function getScript()
    {
        return <<<EOF
$.define('ntdlg', {
    iframe: function(id, url, options) {
        var self = this;
        var options = options || {};
        var title = options.title || '';
        var modal = options.modal || true;
        var overflow = options.overflow || 'hidden';
        var close_cb = options.close_cb || null;
        var ajax = options.ajax || true;
        url += (url.indexOf('?') > -1 ? '&' : '?') + 'closecb=' + (close_cb ? close_cb : '') + '&_dialog=1';
        var params = {
            modal: modal ? true : false,
            buttons: [],
            'shown.bs.modal': function() {
                $.ntdlg.iframeLoader($(this), {ajax: ajax, url: url});
            }
        }
        var opts = ['size', 'closable', 'backdrop', 'keyboard', 'show', 'remote'];
        $.util.applyProp(opts, options, params);
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
        $modal = isset($options['modal']) ? ($options['modal'] ? true : false) : true;
        $overflow = isset($options['overflow']) ? $options['overflow'] : 'hidden';
        $size = isset($options['size']) ? $options['size'] : null;
        $clicker_class = isset($options['clicker_class']) ? $options['clicker_class'] : null;
        unset($options['dialog_id'], $options['modal'], $options['overflow'], $options['size'],
            $options['clicker_class'], $options['height'], $options['width']);

        $url = $this->getBackend()->url($url);
        if (isset($options['query_string'])) {
            $url .= (false !== strpos($url, '?') ? '&' : '?').$options['query_string'];
            unset($options['query_string']);
        }

        $iframeOptions = JSValue::create(array(
            'title'     => $title,
            'modal'     => $modal,
            'overflow'  => $overflow,
            'size'      => $size,
            'close_cb'  => '$.ntdlg.closeIframe'.$dlg,
        ))->setIndent(1);

        $this->includeScript();
        $this->add(<<<EOF
$.ntdlg.closeIframe$dlg = function() {
    $.ntdlg.close('dlg$dlg');
}
$('#ref-dlg$dlg').click(function(e) {
    e.preventDefault();
    $.ntdlg.iframe('dlg$dlg', $(this).attr('href'), $iframeOptions);
});
EOF
        );

        return $this->getBackend()->ctag('a', $content, array_merge(array('href' => $url, 'class' => 'dialog'.(null !== $clicker_class ? ' '.$clicker_class : ''), 'id' => 'ref-dlg'.$dlg), $options));
    }
}