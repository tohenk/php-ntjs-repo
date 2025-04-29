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

namespace NTLAB\JS\Repo\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Util\Asset;

/**
 * Include JQuery assets.
 *
 * An example how to code JQuery from PHP is shown below:
 *
 * ```php
 * <?php
 *
 * use NTLAB\JS\Script;
 *
 * $script = Script::create('JQuery')
 *     ->includeDependencies(['JQuery.Dialog.Message']) // include dependency
 *     ->add(                                           // create code
 *         <<<EOF
 * $.post('/path/to/url', {data: mydata}, function(json) {
 *     if (json.success) {
 *         $.ntdlg.message('mysuccess', 'Success', 'Your changes has been saved.');
 *     } else {
 *         $.ntdlg.message('myerror', 'Error', 'Your changes can not be saved due to errors.');
 *     }
 * });
 * EOF
 *     );
 * ```
 *
 * To include the code into HTML:
 *
 * ```php
 * <script type="text/javascript">
 * //<![CDATA[
 * <?php echo \NTLAB\JS\Manager::getInstance()->get('jquery')->getContent() ?>
 * //]]>
 * </script>
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class JQuery extends Base
{
    protected function initialize()
    {
        $this->addAsset(Asset::ASSET_JAVASCRIPT, 'jquery.min');
    }

    protected function getRepositoryName()
    {
        return 'jquery';
    }
}
