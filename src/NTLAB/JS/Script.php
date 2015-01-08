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
        $this->initialize();
        $this->configure();
    }

    /**
     * Get the script reposiotry instance.
     *
     * @return \NTLAB\JS\Repository
     */
    public function getRepository()
    {
        if (null === ($repoName = $this->getRepositoryName())) {
            throw new \Exception(sprintf('Repository "%s" is not implemented yet.', $repoName));
        }
        $exist = Manager::getInstance()->has($repoName);
        $repo = Manager::getInstance()->get($repoName);
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
        return Manager::getInstance()->getBackend();
    }

    /**
     * Add script depedencies.
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
     * Include script depedencies.
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
     * Get script initialiaztion code.
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
     * Get asset directory.
     *
     * @return string
     */
    public function getAssetDir()
    {
        return $this->getBackend()->getAssetDir($this->getRepositoryName());
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
     * @return string
     */
    protected function url($url)
    {
        return $this->getBackend()->url($url);
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