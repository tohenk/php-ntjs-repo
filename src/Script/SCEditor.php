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

namespace NTLAB\JS\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\JSValue;

/**
 * Include SCEditor assets.
 *
 * @method string call(string $id, array $options)
 * @author Toha <tohenk@yahoo.com>
 */
class SCEditor extends Base
{
    protected function configure()
    {
        $this->setAsset(new Asset('sceditor'));
        $this->addAsset(Asset::ASSET_JAVASCRIPT, sprintf('minified/jquery.sceditor.%s.min', $this->getOption('type', 'bbcode')));
        $this->addAsset(Asset::ASSET_STYLESHEET, sprintf('minified/themes/%s.min', $this->getOption('theme', 'default')));
        $this->addLocaleAsset(Asset::ASSET_JAVASCRIPT, 'languages/');
    }

    /**
     * Call the script.
     *
     * @param string $id  Element id
     * @param array $options  Script options
     */
    public function doCall($id, $options)
    {
        $options = JSValue::create($options);
        $this
            ->add(
                <<<EOF
$('#$id').sceditor($options);
EOF
            );
    }
}
