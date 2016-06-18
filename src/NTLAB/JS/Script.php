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

namespace NTLAB\JS;

use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\Escaper;

/**
 * A base class to write javascript code easily in PHP.
 *
 * Using object approach to write javascript code to create well
 * maintained code and automatic dependency inclusion.
 * 
 * @author Toha
 */
abstract class Script
{
    /**
     * @var string
     */
    protected $position = Repository::POSITION_LAST;

    /**
     * @var array
     */
    protected $dependencies = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected static $included = array();

    /**
     * @var array
     */
    protected static $maps = array();

    /**
     * @var \NTLAB\JS\Util\Asset
     */
    protected $_asset = null;

    /**
     * Create script object.
     *
     * @param string $name  The script name
     * @throws \InvalidArgumentException
     * @return \NTLAB\JS\Script
     */
    public static function create($name)
    {
        if (!isset(static::$maps[$name])) {
            if (null == $class = Manager::getInstance()->resolveDependency($name)) {
                throw new \RuntimeException(sprintf('Can\'t resolve script "%s".', $name));
            }
            static::$maps[$name] = array('class' => $class);
        }
        if (!isset(static::$maps[$name]['obj'])) {
            $class = static::$maps[$name]['class'];
            static::$maps[$name]['obj'] = new $class();
        }

        return static::$maps[$name]['obj'];
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_asset = new Asset($this->getRepositoryName());
        $this->initialize();
        $this->configure();
    }

    /**
     * Get the script manager instance.
     *
     * @return \NTLAB\JS\Manager
     */
    public function getManager()
    {
        return Manager::getInstance();
    }

    /**
     * Get the script repository instance.
     *
     * @return \NTLAB\JS\Repository
     */
    public function getRepository()
    {
        if (null === ($repoName = $this->getRepositoryName())) {
            throw new \Exception(sprintf('Repository "%s" is not implemented yet.', $repoName));
        }
        $exist = $this->getManager()->has($repoName);
        $repo = $this->getManager()->get($repoName);
        if (!$exist) {
            $this->initRepository($repo);
        }

        return $repo;
    }

    /**
     * Get script id.
     *
     * @return string
     */
    protected function getRepositoryName()
    {
    }

    /**
     * Initialize script repository.
     *
     * @param \NTLAB\JS\Repository $repo  The script repository name
     */
    protected function initRepository(Repository $repo)
    {
    }

    /**
     * Do initialization.
     */
    protected function initialize()
    {
    }

    /**
     * Do configuration.
     */
    protected function configure()
    {
    }

    /**
     * Get script backend.
     *
     * @return \NTLAB\JS\BackendInterface
     */
    protected function getBackend()
    {
        return $this->getManager()->getBackend();
    }

    /**
     * Add script dependencies.
     *
     * @param array $dependencies  The dependencies
     * @return \NTLAB\JS\Script
     */
    protected function addDependencies()
    {
        foreach (func_get_args() as $deps) {
            $this->dependencies = array_merge($this->dependencies, is_array($deps) ? $deps : array($deps));
        }

        return $this;
    }

    /**
     * Set script position.
     *
     * @param string $position  The position
     * @return \NTLAB\JS\Script
     */
    protected function setPosition($position = Repository::POSITION_LAST)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Check if script is already included.
     *
     * @return boolean
     */
    protected function isIncluded($class = null)
    {
        return array_key_exists(null === $class ? get_class($this) : $class, static::$included);
    }

    /**
     * Mark script as already included.
     *
     * @return \NTLAB\JS\Script
     */
    protected function markAsIncluded()
    {
        static::$included[get_class($this)] = $this;

        return $this;
    }

    /**
     * Include script dependencies.
     *
     * @param array $dependencies  The dependencies to load
     * @return \NTLAB\JS\Script
     */
    protected function includeDepedencies($dependencies = null)
    {
        $dependencies = null === $dependencies ? $this->dependencies : $dependencies;
        foreach ((array) $dependencies as $class) {
            $this->create($class)
              ->includeScript();
        }

        return $this;
    }

    /**
     * Include script content if its not already included.
     *
     * @return \NTLAB\JS\Script
     */
    public function includeScript()
    {
        if (!$this->isIncluded()) {
            $this->markAsIncluded();
            $this->includeDepedencies();
            $this->buildScript();
        }

        return $this;
    }

    /**
     * Build script.
     *
     * @return \NTLAB\JS\Script
     */
    protected function buildScript()
    {
        if ($script = $this->getScript()) {
            $this->useScript($script);
        }
        $this->getInitScript();

        return $this;
    }

    /**
     * Use script.
     *
     * @param string $script  The script to include
     * @param string $position  Script position
     * @return \NTLAB\JS\Script
     */
    protected function useScript($script, $position = null)
    {
        $this->getRepository()->add($script, null === $position ? $this->position : $position);

        return $this;
    }

    /**
     * Get script content.
     *
     * @return string
     */
    abstract public function getScript();

    /**
     * Get script initialization code.
     */
    public function getInitScript()
    {
    }

    /**
     * Add script option.
     *
     * @param string $name  The option name
     * @param mixed $value  The option value
     * @return \NTLAB\JS\Script
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Get script option.
     *
     * @param string $name  The option name
     * @param mixed $default  The default value
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * Get asset helper.
     *
     * @return \NTLAB\JS\Util\Asset
     */
    public function getAsset()
    {
        return $this->_asset;
    }

    /**
     * Add javascript.
     *
     * @param string $js  Javascript to include
     * @return \NTLAB\JS\Script
     */
    public function addJavascript($js)
    {
        $this->getBackend()->addAsset($js, BackendInterface::ASSET_JS);

        return $this;
    }

    /**
     * Add stylesheet.
     *
     * @param string $css  Stylesheet to include
     * @return \NTLAB\JS\Script
     */
    public function addStylesheet($css)
    {
        $this->getBackend()->addAsset($css, BackendInterface::ASSET_CSS);

        return $this;
    }

    /**
     * Include a jquery javascript.  The javascript name accepted with the following
     * format:
     * jquery-%name%-%version%.%minified%.js
     *
     * @param string $name  The javascript name, e.q. ui
     * @param string $version  The javascript version
     * @param bool $minified  The minified version used
     * @param \NTLAB\JS\Util\Asset $asset  Asset helper
     * @return \NTLAB\JS\Script\JQuery
     */
    public function useJavascript($name, $asset = null)
    {
        $asset = null != $asset ? $asset : $this->getAsset();
        $this->addJavascript($asset->get(Asset::ASSET_JAVASCRIPT, $name));

        return $this;
    }

    /**
     * Include locale javascript.
     *
     * @param string $name     Javascript name
     * @param string $culture  The user culture
     * @param string $default  Default culture
     * @param \NTLAB\JS\Util\Asset $asset  Asset helper
     * @return \NTLAB\JS\Script
     */
    public function useLocaleJavascript($name, $culture = null, $default = 'en', $asset = null)
    {
        $asset = null != $asset ? $asset : $this->getAsset();
        $default = null !== $default ? $default : 'en';
        $baseDir = $this->getBackend()->getConfig('web-dir').DIRECTORY_SEPARATOR.$asset->getDir();
        $culture = null === $culture ? $this->getBackend()->getConfig('default-culture') : $culture;
        // change culture delimeter from _ to -
        $culture = str_replace('_', '-', $culture);
        $locales = array($culture);
        if (false!== ($p = strpos($culture, '-'))) {
            $locales[] = substr($culture, 0, $p);
        }
        if (!in_array($default, $locales)) {
            $locales[] = $default;
        }
        // check file
        foreach ($locales as $locale) {
            $localeJs = $name.$locale.$asset->getExtension(Asset::ASSET_JAVASCRIPT);
            $realJs = $baseDir;
            if ($dirName = $asset->getDirName(Asset::ASSET_JAVASCRIPT)) {
                $realJs .= DIRECTORY_SEPARATOR.$dirName;
            }
            $realJs .= DIRECTORY_SEPARATOR.$localeJs;
            if (is_readable($realJs)) {
                $this->useJavascript($localeJs, $asset);
                break;
            }
        }

        return $this;
    }

    /**
     * Include a jquery stylesheet.
     *
     * @param string $name  The stylesheet name, e.q. ui
     * @param \NTLAB\JS\Util\Asset $asset  Asset helper
     * @return \NTLAB\JS\Script
     */
    public function useStylesheet($name, $asset = null)
    {
        $asset = null != $asset ? $asset : $this->getAsset();
        $this->addStylesheet($asset->get(Asset::ASSET_STYLESHEET, $name));

        return $this;
    }

    /**
     * Add script dependency.
     *
     * @param string $dependencies  The dependency script name
     * @return \NTLAB\JS\Script
     */
    public function includeDependency($dependencies)
    {
        $this->addDependencies(is_array($dependencies) ? $dependencies : array($dependencies));

        return $this;
    }

    /**
     * Translate text.
     *
     * @param string $text  Text to translate
     * @param array $vars  Text variables
     * @param string $domain  Text domain
     * @return string
     */
    protected function trans($text, $vars = array(), $domain = null)
    {
        return $this->getBackend()->trans($text, $vars, $domain);
    }

    /**
     * Translate URL.
     *
     * @param string $url  Raw url
     * @param array $options  URL options
     * @return string
     */
    protected function url($url, $options = array())
    {
        return $this->getBackend()->url($url, $options);
    }

    /**
     * Indent lines of code.
     *
     * @param string $lines  The code
     * @param int $size  Indentation size
     * @return string
     */
    public static function indentLines($lines, $size = 4)
    {
        $result = array();
        $pad = str_repeat(' ', $size);
        foreach (explode(Escaper::getEol(), $lines) as $line) {
            if (strlen($line)) {
                $line = $pad.$line;
            }
            $result[] = $line;
        }

        return implode(Escaper::getEol(), $result);
    }
}