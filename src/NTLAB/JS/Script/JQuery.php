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

namespace NTLAB\JS\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\Asset;

/**
 * JQuery javascript code repository for PHP.
 *
 * To code JQuery from PHP, shown below:
 *
 * <?php
 *
 * use NTLAB\JS\Script;
 *
 * $jq = Script::create('JQuery');
 * // include dependency
 * $jq->includeDependencies(array('JQuery.Dialog.Message'));
 * // create code
 * $jq->add(<<<EOF
 * $.post('/path/to/url', { data: mydata }, function(json) {
 *     if (json.success) {
 *         $.ntdlg.message('mysuccess', 'Success', 'Your changes has been saved.', true);
 *     } else {
 *         $.ntdlg.message('myerror', 'Error', 'Your changes can not be saved due to errors.', true);
 *     }
 * });
 * 
 * EOF
 * );
 *
 * ?>
 *
 * To include the code into HTML:
 *
 * <script type="text/javascript">
 * //<![CDATA[
 * <?php echo \NTLAB\JS\Manager::getInstance()->get('jquery')->getContent() ?>
 * //]]>
 * </script>
 *
 * @author Toha
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

    protected function initRepository(Repository $repo)
    {
        $repo
            ->setWrapper(<<<EOF
(function($) {
    (function loader(f) {
        if (document.ntloader && !document.ntloader.isScriptLoaded()) {
            setTimeout(function() {
                loader(f);
            }, 100);
        } else {
            f($);
        }
    })(function($) {%s});
})(jQuery);
EOF
            )
            ->setWrapSize(2)
        ;
    }

    /**
     * Create singleton instance.
     *
     * @return \NTLAB\JS\Script\JQuery
     */
    protected static function createInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Add script.
     *
     * @param string $script  The javascript
     * @param string $position  The position script will be added
     */
    public static function add($script, $position = Repository::POSITION_LAST)
    {
        self::createInstance()->useScript($script, $position);
    }
}