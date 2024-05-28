<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\JSValue;

/**
 * A JQuery typeahead (auto complete).
 *
 * @method string call(string $el, array $options = [], array $datasource = [])
 * @author Toha <tohenk@yahoo.com>
 */
class Typeahead extends Base
{
    protected function configure()
    {
        $this->setAsset(new Asset('typeahead.js'));
        $this->addDependencies(['JQuery.NS']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $this->useJavascript('typeahead.bundle.min');

        return <<<EOF
$.actypeahead = function(el, options, source) {
    options = options ? options : {};
    $.extend(options, {
        classNames: {
            menu: 'dropdown-menu col-xs-12 tt-menu',
            dataset: 'col-xs-12 tt-dataset'
        }
    });
    el.typeahead(options, source);
}
EOF
        ;
    }

    /**
     * Setup bloodhound datasource.
     *
     * @param string $url  Remote url
     * @param array $options  Typeahead source options
     * @return array
     */
    public function setupDatasource($url, $options = [])
    {
        $url .= (false !== strpos($url, '?') ? '&' : '?').'term=%Q';
        $source = JSValue::create([
            'queryTokenizer' => JSValue::createRaw('Bloodhound.tokenizers.whitespace'),
            'datumTokenizer' => JSValue::createRaw('Bloodhound.tokenizers.whitespace'),
            'remote' => [
                'url' => $url,
                'wildcard' => '%Q',
            ],
        ], null, 1);

        return array_merge([
            'source' => JSValue::createRaw("new Bloodhound($source)"),
            'display' => <<<EOF
function(data) {
    if (typeof data === 'string') {
        return data;
    }
    if (typeof data === 'object') {
        return data.label;
    }
    return data;
}
EOF,
        ], $options);
    }

    /**
     * Call script.
     *
     * @param string $el      The element selector
     * @param array $options  The autocomplete options
     * @param array $datasource  The autocomplete datasource
     */
    public function doCall($el, $options = [], $datasource = [])
    {
        $defaults = [
            'minLength' => 1,
            'highlight' => true,
        ];
        $options = JSValue::create(array_merge($defaults, (array) $options));
        $datasource = JSValue::create((array) $datasource);
        $this
            ->add(
                <<<EOF
$.actypeahead($('$el'), $options, $datasource);
EOF
            );
    }
}
