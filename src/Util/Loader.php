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
    protected $javascripts = [];

    /**
     * @var array
     */
    protected $stylesheets = [];

    /**
     * Add on demand javascript.
     *
     * @param string $js  The full path of javascript
     * @return \NTLAB\JS\Util\Loader
     */
    public function addJavascript($js)
    {
        if (!in_array($js, $this->javascripts)) {
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
        if (!in_array($css, $this->stylesheets)) {
            $this->stylesheets[] = $css;
        }
        return $this;
    }

    /**
     * Get javascripts.
     *
     * @return array
     */
    public function getJavascripts()
    {
        $manager = Manager::getInstance();
        $js = [];
        foreach ($this->javascripts as $file) {
            $js[] = $manager->getBackend()->asset($file, BackendInterface::ASSET_JS);
        }
        return $js;
    }

    /**
     * Get stylesheets.
     *
     * @return array
     */
    public function getStylesheets()
    {
        $manager = Manager::getInstance();
        $css = [];
        foreach ($this->stylesheets as $file) {
            $css[] = $manager->getBackend()->asset($file, BackendInterface::ASSET_CSS);
        }
        return $css;
    }

    /**
     * Create HTML script tag to autoload required javascripts and
     * stylesheets into HTML.
     *
     * @return string
     */
    public function autoload()
    {
        $manager = Manager::getInstance();
        $js = $this->getJavascripts();
        $css = $this->getStylesheets();
        if (count($js) || count($css)) {
            $assets = JSValue::create(['js' => $js, 'css' => $css]);
            $script = $manager->compress(<<<EOF
if (!document.ntloader) {
    document.ntloader = {
        parent: document.head ? document.head : document.body,
        scriptQueue: [],
        scriptLoaded: [],
        hasAsset: function(parent, tag, path) {
            if (parent) {
                const elems = parent.getElementsByTagName(tag);
                for (let i = 0; i < elems.length; i++) {
                    const el = elems[i];
                    // stylesheet
                    if ('link' === tag) {
                        if (!el.hasAttribute('rel') || 'stylesheet' !== el.getAttribute('rel')) {
                            continue;
                        }
                        if (el.hasAttribute('href') && path == el.getAttribute('href')) {
                            return true;
                        }
                    }
                    // javascript
                    if ('script' === tag) {
                        if (!el.hasAttribute('type') || 'text/javascript' !== el.getAttribute('type')) {
                            continue;
                        }
                        if (el.hasAttribute('src') && path == el.getAttribute('src')) {
                            return true;
                        }
                    }
                }
            }
            return false;
        },
        isAssetExist: function(tag, path) {
            const self = this;
            if (document.head && self.hasAsset(document.head, tag, path)) {
                return true;
            } else if (document.body && self.hasAsset(document.body, tag, path)) {
                return true;
            }
            return false;
        },
        isStylesheetLoaded: function(path) {
            const self = this;
            return self.isAssetExist('link', path);
        },
        queueStylesheet: function(path) {
            const self = this;
            const el = document.createElement('link');
            el.rel = 'stylesheet';
            el.type = 'text/css';
            el.href = path;
            self.parent.appendChild(el);
        },
        loadStylesheets: function(paths) {
            const self = this;
            const items = [];
            for (let i = 0; i < paths.length; i++) {
                if (!self.isStylesheetLoaded(paths[i])) {
                    items.push(paths[i]);
                }
            }
            return items;
        },
        isJavascriptLoaded: function(path) {
            const self = this;
            return self.isAssetExist('script', path);
        },
        queueJavascript: function(path) {
            const self = this;
            const el = document.createElement('script');
            el.type = 'text/javascript';
            el.src = path;
            // http://stackoverflow.com/questions/1293367/how-to-detect-if-javascript-files-are-loaded
            el.onload = function() {
                self.removeQueue(path);
            }
            el.onreadystatechange = function() {
                if (this.readyState === 'complete') {
                    self.removeQueue(path);
                }
            }
            self.parent.appendChild(el);
        },
        removeQueue: function(path) {
            const self = this;
            const idx = self.scriptQueue.indexOf(path);
            if (idx >= 0) {
                self.scriptQueue.splice(idx, 1);
                self.processJavascriptQueue();
            }
        },
        processJavascriptQueue: function() {
            const self = this;
            if (self.scriptQueue.length) {
                self.queueJavascript(self.scriptQueue[0]);
            }
        },
        loadJavascripts: function(paths) {
            const self = this;
            const items = [];
            for (let i = 0; i < paths.length; i++) {
                if (!self.isJavascriptLoaded(paths[i])) {
                    items.push(paths[i]);
                }
            }
            return items;
        },
        isScriptLoaded: function() {
            const self = this;
            return self.scriptQueue.length === 0 ? true : false;
        },
        load: function(assets) {
            const self = this;
            if (assets.css) {
                const css = self.loadStylesheets(assets.css);
                for (let i = 0; i < css.length; i++) {
                    self.queueStylesheet(css[i]);
                }
            }
            if (assets.js) {
                const js = self.loadJavascripts(assets.js);
                for (let i = 0; i < js.length; i++) {
                    if (self.scriptQueue.indexOf(js[i]) < 0) {
                        self.scriptQueue.push(js[i]);
                    }
                }
                if (self.scriptQueue.length) {
                    self.processJavascriptQueue();
                }
            }
        }
    }
}
// load all assets
document.ntloader.load($assets);
EOF
            );
            return $manager->scriptTag($script);
        }
    }
}