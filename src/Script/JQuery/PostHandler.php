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

namespace NTLAB\JS\Repo\Script\JQuery;

use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Ajax POST handler which provide basic functionality to
 * mark the error returned.
 *
 * Usage:
 *
 * ```js
 * $.urlPost('/path/to/url', function(data) {
 *     // do something with data
 * });
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class PostHandler extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.PostErrorHelper']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
Object.assign($, {
    handlePostData(data, errhelper, success_cb, error_cb) {
        $.postErr = null;
        const json = typeof data === 'object' ? data : $.parseJSON(data);
        if (json.success) {
            if (typeof success_cb === 'function') {
                success_cb(json);
            }
        } else {
            if (json.error) {
                const errors = Array.isArray(json.error) ? json.error : [json.error];
                errors.map(errhelper.handleError);
            }
            if (typeof error_cb === 'function') {
                error_cb(json);
            }
        }
    },
    urlPost(url, callback, errhelper) {
        errhelper = errhelper ? errhelper : $.errhelper();
        $.post(url).done(function(data) {
            $.handlePostData(data, errhelper, callback);
        });
    }
});
EOF;
    }
}