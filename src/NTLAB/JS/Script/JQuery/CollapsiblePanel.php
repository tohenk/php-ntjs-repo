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

namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Create a collapsible panel.
 *
 * Usage:
 * <div id="mypanel" class="task-container ui-widget ui-widget-content ui-corner-all">
 * <div class="task-header ui-widget-header ui-corner-all">My Title</div>
 * <div class="task-content">My Content</div>
 * </div>
 *
 * $.panel.collapse('#mypanel');
 *
 * @author Toha
 */
class CollapsiblePanel extends Base
{
    protected function configure()
    {
        $this->addDependencies('JQuery.NS');
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.define('panel', {
    collapse: function(id) {
        $(id + ' .task-header').prepend('<span class="ui-icon ui-icon-triangle-1-n"></span>');
        $(id + ' .ui-icon').click(function(eventObject) {
            $(this)
                .toggleClass('ui-icon-triangle-1-s')
                .toggleClass('ui-icon-triangle-1-n')
            $(this).parents('.task-container')
                .find('.task-collapsible')
                    .toggle();
        });
    }
});

EOF;
    }

    /**
     * Call script.
     *
     * @param string $id  The element id
     * @return \NTLAB\JS\Script\JQuery\CollapsiblePanel
     */
    public function call($id)
    {
        $this->includeScript();
        $this->useScript(<<<EOF
$.panel.collapse('$id');

EOF
, Repository::POSITION_LAST);

        return $this;
    }
}