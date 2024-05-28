<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2024 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS;

use NTLAB\JS\Util\Asset;

interface BackendInterface
{
    public const ASSET_JS = 1;
    public const ASSET_CSS = 2;
    public const ASSET_OTHER = 3;
    public const ASSET_PRIORITY_DEFAULT = 0;
    public const ASSET_PRIORITY_FIRST = 1;

    /**
     * Get various script configuration.
     *
     * @param string $name  Configuration name
     * @param mixed $default  Default value
     * @return mixed
     */
    public function getConfig($name, $default = null);

    /**
     * Get default script repository name used as a fallback
     * repository name for script which is not define a repository.
     *
     * @return string
     */
    public function getDefaultRepository();

    /**
     * Translate text for internationalization.
     *
     * @param string $text  Text to translate
     * @param array $vars  Variable substitusions
     * @param string $domain  Text domain
     * @return string
     */
    public function trans($text, $vars = [], $domain = null);

    /**
     * Perform URL translation or transform.
     *
     * @param string $url  Raw URL
     * @param array $options  URL options
     * @return string
     */
    public function url($url, $options = []);

    /**
     * Perform asset translation for javascript or stylesheet.
     *
     * @param string $asset  Asset name
     * @param int $type  Asset type
     * @return string
     */
    public function asset($asset, $type = self::ASSET_JS);

    /**
     * Add script assets.
     *
     * @param string $asset  Asset name
     * @param int $type  Asset type
     * @param int $priority  Asset priority
     * @param array $attributes  Extra attributes
     */
    public function addAsset($asset, $type = self::ASSET_JS, $priority = self::ASSET_PRIORITY_DEFAULT, $attributes = null);

    /**
     * Remove asset.
     *
     * @param string $asset  Asset name
     * @param int $type  Asset type
     */
    public function removeAsset($asset, $type = self::ASSET_JS);

    /**
     * Get local asset directory for specified repository.
     *
     * @param string $repo  Repository name
     * @return string
     */
    public function getAssetDir($repo);

    /**
     * Generate full asset path.
     *
     * @param Asset $asset  Asset repository
     * @param string $name  Asset name
     * @param int $type  Asset type
     * @return string
     */
    public function generateAsset(Asset $asset, $name, $type = self::ASSET_JS);

    /**
     * Create HTML tag.
     *
     * @param string $name  Tag name
     * @param array $options  Tag options and attributes
     * @return string
     */
    public function tag($name, $options = []);

    /**
     * Create HTML tag with content.
     *
     * @param string $name  Tag name
     * @param string $content  Tag content
     * @param array $options  Tag options and attributes
     * @return string
     */
    public function ctag($name, $content, $options = []);
}