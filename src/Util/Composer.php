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

namespace NTLAB\JS\Util;

use ReflectionClass;

class Composer
{
    /**
     * @var array
     */
    protected static $packages = null;

    /**
     * Get available formatters.
     *
     * @return array
     */
    public function getPackages()
    {
        if (null === static::$packages) {
            static::$packages = [];
            $vendorDir = null;
            if ($composer = $this->getComposer()) {
                $r = new ReflectionClass($composer);
                $composerDir = dirname($r->getFileName());
                $vendorDir = dirname($composerDir);
            } else {
                $vendorDir = realpath(__DIR__.'/../../../..');
            }
            if (is_dir($vendorDir)) {
                $pattern = implode(DIRECTORY_SEPARATOR, [$vendorDir, '*', '*', 'composer.json']);
                foreach (glob($pattern) as $filename) {
                    $this->addPackage($filename);
                }
            }
            // add self composer.json
            $this->addPackage(realpath(__DIR__.'/../../composer.json'));
        }

        return static::$packages;
    }

    /**
     * Add a composer package.
     *
     * @param string $filename
     */
    protected function addPackage($filename)
    {
        if (!in_array($filename, static::$packages)) {
            static::$packages[$filename] = json_decode(file_get_contents($filename), true);
        }
    }

    /**
     * Get Composer autoloader instance.
     *
     * @return \Composer\Autoload\ClassLoader
     */
    protected function getComposer()
    {
        if ($autoloaders = spl_autoload_functions()) {
            foreach ($autoloaders as $autoload) {
                if (is_array($autoload)) {
                    $class = $autoload[0];
                    if ('Composer\Autoload\ClassLoader' === get_class($class)) {
                        return $class;
                    }
                }
            }
        }
    }
}
