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

namespace NTLAB\JS\Util;

use NTLAB\JS\Manager;
use NTLAB\JS\BackendInterface;

/**
 * Javascript assets loader.
 *
 * @author Toha
 */
class Loader
{
    /**
     * @var array
     */
    protected $javascripts = array();

    /**
     * @var array
     */
    protected $stylesheets = array();

    /**
     * Add on demand javascript.
     *
     * @param string $js  The full path of javascript
     * @return \NTLAB\JS\Util\Loader
     */
    public function addJavascript($js)
    {
        if (! in_array($js, $this->javascripts)) {
            $this->javascripts[] = $js;
        }

        return $this;
    }

    /**
     * Add on demand stylesheet.
     *
     * @param string $css  The full path of stylesheet
     * @return \NTLAB\JS\Util\Loader
     */
    public function addStylesheet($css)
    {
        if (! in_array($css, $this->stylesheets)) {
            $this->stylesheets[] = $css;
        }

        return $this;
    }

    /**
     * Create HTML script tag to autoload required javascripts and
     * stylesheets into HTML.
     *
     * @return string
     */
    public function autoload()
    {
        $js = array();
        foreach ($this->javascripts as $file) {
            $js[] = Manager::getInstance()->getBackend()->asset($file, BackendInterface::ASSET_JS);
        }
        $css = array();
        foreach ($this->stylesheets as $file) {
            $css[] = Manager::getInstance()->getBackend()->asset($file, BackendInterface::ASSET_CSS);
        }
        $assets = Escaper::escape(array('js' => $js, 'css' => $css), null, 1);

        return <<<EOF
<script type="text/javascript">
//<![CDATA[
    assets = $assets;
    p = document.head ? document.head : document.body;
    // load stylesheets
    elems = p.getElementsByTagName('link');
    for (i = 0; i < assets.css.length; i++) {
        css = assets.css[i];
        exist = false;
        for (j = 0; j < elems.length; j++) {
            el = elems[j];
            if (!el.hasAttribute('rel') || 'stylesheet' !== el.getAttribute('rel')) continue;
            if (el.hasAttribute('href') && css == el.getAttribute('href')) {
                exist = true;
                break;
            }
        }
        if (!exist) {
            el = document.createElement('link');
            el.rel = 'stylesheet';
            el.type = 'text/css';
            el.href = css;
            p.appendChild(el);
        }
    }
    // load javascript
    elems = p.getElementsByTagName('script');
    for (i = 0; i < assets.js.length; i++) {
        js = assets.js[i];
        exist = false;
        for (j = 0; j < elems.length; j++) {
            el = elems[j];
            if (!el.hasAttribute('type') || 'text/javascript' !== el.getAttribute('type')) continue;
            if (el.hasAttribute('src') && js == el.getAttribute('src')) {
                exist = true;
                break;
            }
        }
        if (!exist) {
            el = document.createElement('script');
            el.type = 'text/javascript';
            el.src = js;
            p.appendChild(el);
        }
    }
//]]>
</script>
EOF;
    }
}