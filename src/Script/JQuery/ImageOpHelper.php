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

use NTLAB\JS\Script;
use NTLAB\JS\Repo\Script\JQuery as Base;
use NTLAB\JS\Util\JSValue;

/**
 * Provides all image operation.
 *
 * @method string call(string $selector, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class ImageOpHelper extends Base
{
    protected function configure()
    {
        $this->addDependencies(['JQuery.FileUpload', 'JQuery.ImageOp', 'JQuery.ImageOpCrop', 'JQuery.ImageOpOrientation',
            'JQuery.ImageOpResize', 'JQuery.ImageOpRotate']);
    }

    /**
     * Call script.
     *
     * @param string $selector  The uploader element selector
     * @param array $options    The image operation parameters
     */
    public function doCall($selector, $options = [])
    {
        $params = ['ops' => []];
        // orientation
        if (isset($options['orientation'])) {
            $params['ops']['orientation'] = ['auto' => true];
        }
        // crop
        if (isset($options['crop'])) {
            // this option handled by javascript
            if (isset($options['crop_ratio'])) {
                $params['ops']['crop'] = ['aspectRatio' => $options['crop_ratio']];
            } else {
                $params['ops']['crop'] = [];
            }
        }
        // resize
        if (isset($options['resize'])) {
            if (isset($options['pixel_size'])) {
                $params['ops']['resize'] = ['pixel_size' => $options['pixel_size']];
            } elseif (isset($options['resize_width']) && isset($options['resize_height'])) {
                $params['ops']['resize'] = ['width' => $options['resize_width'], 'height' => $options['resize_height']];
            }
            if (isset($params['ops']['resize'])) {
                if (isset($options['resize_enlarge'])) {
                    $params['ops']['resize']['enlarge'] = true;
                }
                if (isset($options['resize_reduce'])) {
                    $params['ops']['resize']['reduce'] = true;
                }
            }
        }
        // image types
        if (isset($options['image_types'])) {
            $imageTypes = [];
            foreach ((array) $options['image_types'] as $k => $v) {
                if (!is_string($k)) {
                    list($mime_type, $mime_subtype) = explode('/', $v);
                    $k = $v;
                    $v = strtoupper($mime_subtype);
                }
                $imageTypes[$k] = $v;
            }
            $params['images'] = $imageTypes;
        }
        $params = JSValue::create($params)->setIndent(2);
        $this
            ->add(
                <<<EOF
$('$selector').uploader({
    select(filename, data) {
        $.imgop.handleUpload({name: filename, type: data.type}, $params);
    }
}).on('click', function(e) {
    e.preventDefault();
    $.uploader.target = $(this);
    $(this).uploader('show');
});
EOF
            );
        // clean image ops parameters
        unset(
            $options['orientation'],
            $options['crop'], $options['crop_ratio'],
            $options['resize'], $options['pixel_size'], $options['resize_height'], $options['resize_width'], $options['resize_enlarge'], $options['resize_reduce'],
            $options['image_types']
        );
        /** @var \NTLAB\JS\Repo\Script\JQuery\ImageOpSave $img_save */
        $img_save = Script::create('JQuery.ImageOpSave');
        $img_save
            ->call($selector, $options);
    }
}
