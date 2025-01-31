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

namespace NTLAB\JS\Repo\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Util\Asset;

/**
 * Include Highcharts assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Highcharts extends Base
{
    protected $modules = [];

    protected function configure()
    {
        $this->setAsset(new Asset('highcharts'));
    }

    /**
     * Include highcharts module javascript.
     *
     * @param string $module  Module javascript
     * @return \NTLAB\JS\Script\Highcharts
     */
    public function useModule($module)
    {
        $modules = is_array($module) ? $module : [$module];
        foreach ($modules as $module) {
            if (!in_array($module, $this->modules)) {
                $this->modules[] = $module;
            }
        }

        return $this;
    }

    public function getScript()
    {
        $this->useJavascript('highcharts');
        if ($this->getOption('more')) {
            $this->useJavascript('highcharts-more');
        }
        if ($this->getOption('3d')) {
            $this->useJavascript('highcharts-3d');
        }
        if ($theme = $this->getOption('theme')) {
            $this->useJavascript(sprintf('themes/%s', $theme));
        }
        if ($this->getOption('accessibility', true)) {
            $module = 'accessibility';
            if (!in_array($module, $this->modules)) {
                array_unshift($this->modules, $module);
            }
        }
        foreach ($this->modules as $module) {
            $this->useJavascript(sprintf('modules/%s', $module));
        }
    }
}
