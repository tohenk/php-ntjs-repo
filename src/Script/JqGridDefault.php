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
use NTLAB\JS\Repository;
use NTLAB\JS\Util\JSValue;

/**
 * Provide Bootstrap Icons integration in JqGrid.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class JqGridDefault extends Base
{
    protected function configure()
    {
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    protected function flattenArray($prefix, $array)
    {
        $result = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                if (count($flatten = $this->flattenArray($prefix.'.'.$k, $v))) {
                    $result = array_merge($result, $flatten);
                }
            } else {
                $result[$prefix.'.'.$k] = $v;
            }
        }

        return $result;
    }

    public function getScript()
    {
        $overrides = [
            'styleUI' => [
                'Bootstrap4' => [
                    'common' => [
                        'hightlight' => 'table-primary',
                    ],
                    'base' => [
                        'headerTable' => 'table table-bordered table-sm',
                        'rowTable' => 'table table-bordered table-sm',
                        'footerTable' => 'table table-bordered table-sm',
                        'pagerTable' => 'table table-sm',
                        'headerBox' => 'px-1 py-2',
                        'rowBox' => 'p-1',
                        'footerBox' => 'p-1',
                    ],
                ]
            ],
            'defaults' => [
                'styleUI' => 'Bootstrap4',
                'iconSet' => 'BootstrapIcons',
                'responsive' => true,
                'padding' => 5,
            ],
        ];
        $icons = JSValue::create([
            'common' => [
                'icon_base' => '',
            ],
            'base' => [
                'icon_first' => 'bi-chevron-double-left',
                'icon_prev' => 'bi-chevron-left',
                'icon_next' => 'bi-chevron-right',
                'icon_end' => 'bi-chevron-double-right',
                'icon_asc' => 'bi-arrow-up',
                'icon_desc' => 'bi-arrow-down',
                'icon_caption_open' => 'bi-chevron-up',
                'icon_caption_close' => 'bi-chevron-down',
            ]
        ]);
        $result = [];
        foreach ($this->flattenArray('$.jgrid', $overrides) as $k => $v) {
            $result[] = sprintf('%s = %s;', $k, JSValue::create($v));
        }
        $script = implode("\n", $result);

        return <<<EOF
$.jgrid.iconSet.BootstrapIcons = $icons;
$script
EOF;
    }
}
