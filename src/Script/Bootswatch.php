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

/**
 * Include Bootswatch assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Bootswatch extends Base
{
    protected static $themes = [
        'cerulean' => 'Cerulean',
        'cosmo' => 'Cosmo',
        'cyborg' => 'Cyborg',
        'darkly' => 'Darkly',
        'flatly' => 'Flatly',
        'journal' => 'Journal',
        'litera' => 'Litera',
        'lumen' => 'Lumen',
        'lux' => 'Lux',
        'materia' => 'Materia',
        'minty' => 'Minty',
        'morph' => 'Morph',
        'pulse' => 'Pulse',
        'quartz' => 'Quartz',
        'sandstone' => 'Sandstone',
        'simplex' => 'Simplex',
        'sketchy' => 'Sketchy',
        'solar' => 'Solar',
        'slate' => 'Slate',
        'spacelab' => 'Spacelab',
        'superhero' => 'Superhero',
        'united' => 'United',
        'vapor' => 'Vapor',
        'yeti' => 'Yeti',
        'zephyr' => 'Zephyr',
    ];

    protected function configure()
    {
        $this->setAsset(new Asset('bootswatch'));
        $this->addDependencies(['Bootstrap']);
        // remove bootstrap default css
        $bs = static::create('Bootstrap');
        $bs->removeAsset(Asset::ASSET_STYLESHEET, 'bootstrap.min');
    }

    public function getScript()
    {
        $theme = $this->getOption('theme', $this->getConfig('bootswatch-theme', 'cerulean'));
        if (count($themes = array_keys(static::$themes))) {
            if (!in_array($theme, $themes)) {
                $theme = $themes[0];
            }
        }
        $this->useStylesheet($theme.'/bootstrap.min');
    }

    /**
     * Get Bootswatch themes.
     *
     * @return array
     */
    public static function getThemes()
    {
        return static::$themes;
    }
}
