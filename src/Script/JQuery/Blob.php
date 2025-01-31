<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024-2025 Toha <tohenk@yahoo.com>
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
 * Retrieve content from an URL and transform it as blob for download.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Blob extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
$.define('blob', {
    save(url) {
        // https://stackoverflow.com/questions/17657184/using-jquerys-ajax-method-to-retrieve-images-as-a-blob
        $.ajax({
            url: url,
            method: 'GET',
            xhr() {
                let xhr = new XMLHttpRequest();
                xhr.responseType = 'blob';
                return xhr;
            }
        }).done(function(content, status, req) {
            let headers = req.getAllResponseHeaders().trim().split('\\r\\n');
            let filename, mimetype, info;
            headers.forEach(function(header) {
                info = header.split(': ');
                if (info[0] === 'content-disposition') {
                    const matches = info[1].match(/filename="(.*)"/);
                    filename = matches[1];
                }
                if (info[0] === 'content-type') {
                    mimetype = info[1];
                }
                if (filename && mimetype) {
                    return true;
                }
            });
            if (filename && mimetype) {
                let blob = new Blob([content], {type: mimetype});
                let url = window.URL.createObjectURL(blob);
                let a = document.createElement('a');
                document.body.appendChild(a);
                a.style = 'display: none';
                a.href = url;
                a.download = filename;
                a.click();
                window.URL.revokeObjectURL(url);
            }
        });
    }
});
EOF;
    }
}
