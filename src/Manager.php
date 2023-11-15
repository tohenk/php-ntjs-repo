<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2022 Toha <tohenk@yahoo.com>
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
use NTLAB\JS\Util\CDN;
use NTLAB\JS\Util\Escaper;

/**
 * Script manager handle script repository management and provide support
 * such as script backend, script compressor, and script resolver for script
 * itself.
 *
 * @author Toha
 */
class Manager
{
    /**
     * @var \NTLAB\JS\Manager
     */
    protected static $instance = null;

    /**
     * @var \NTLAB\JS\Repository[]
     */
    protected $repositories = [];

    /**
     * @var \NTLAB\JS\BackendInterface
     */
    protected $backend = null;

    /**
     * @var \NTLAB\JS\CompressorInterface
     */
    protected $compressor = null;

    /**
     * @var \NTLAB\JS\DependencyResolverInterface[]
     */
    protected $resolvers = [];

    /**
     * @var \NTLAB\JS\Util\CDN[]
     */
    protected $cdns = [];

    /**
     * Get the registry instance.
     *
     * @return \NTLAB\JS\Manager
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * Get CDN info file.
     *
     * @return string
     */
    public static function getCdnInfoFile()
    {
        return realpath(__DIR__.'/../cdn.json');
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->addResolver(new DependencyResolver());
    }

    /**
     * Set script backend.
     *
     * @param \NTLAB\JS\BackendInterface $backend  Script backend
     * @return \NTLAB\JS\Manager
     */
    public function setBackend(BackendInterface $backend)
    {
        $this->backend = $backend;
        return $this;
    }

    /**
     * Get script backend.
     *
     * @return \NTLAB\JS\BackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Set script compressor.
     *
     * @param \NTLAB\JS\CompressorInterface $compressor  Script compressor
     * @throws \InvalidArgumentException
     * @return \NTLAB\JS\Manager
     */
    public function setCompressor($compressor)
    {
        if (!$compressor instanceof CompressorInterface) {
            throw new \InvalidArgumentException('Compressor must be a CompressorInterface.');
        }
        $this->compressor = $compressor;
        return $this;
    }

    /**
     * Get script compressor.
     *
     * @return \NTLAB\JS\CompressorInterface
     */
    public function getCompressor()
    {
        return $this->compressor;
    }

    /**
     * Register script dependency resolver.
     *
     * @param DependencyResolverInterface $resolver  The resolver
     * @return \NTLAB\JS\Manager
     */
    public function addResolver(DependencyResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
        return $this;
    }

    /**
     * Get all script instances repository.
     *
     * @return \NTLAB\JS\Repository[]
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * Add CDN and create one if it doesn't exist.
     *
     * @param string $repository  Repository id
     * @return \NTLAB\JS\Util\CDN
     */
    public function addCdn($repository)
    {
        if (!isset($this->cdns[$repository])) {
            $this->cdns[$repository] = new CDN($repository);
        }
        return $this->cdns[$repository];
    }

    /**
     * Get CDN object.
     *
     * @param string $repository  Repository id
     * @return \NTLAB\JS\Util\CDN
     */
    public function getCdn($repository)
    {
        if (isset($this->cdns[$repository])) {
            return $this->cdns[$repository];
        }
    }

    /**
     * Parse CDN array.
     *
     * @param array $cdns  CDN definitions.
     * @return \NTLAB\JS\Manager
     */
    public function parseCdn($cdns)
    {
        $providers = [];
        foreach ($cdns as $repository => $parameters) {
            // empty key is CDN providers
            if ($repository === '') {
                $providers = $parameters;
                continue;
            }
            if (isset($parameters['disabled']) && $parameters['disabled']) {
                continue;
            }
            $cdn = $this->addCdn($repository);
            if (isset($parameters['url'])) {
                $cdn->setUrl($parameters['url']);
            } else {
                foreach ($providers as $provider => $url) {
                    if (isset($parameters[$provider])) {
                        $cdn->setPackage($parameters[$provider] ? $parameters[$provider] : $repository);
                        $cdn->setUrl($url);
                        break;
                    }
                }
            }
            if (isset($parameters['version'])) {
                $cdn->setVersion($parameters['version']);
            }
            if (isset($parameters['paths'])) {
                foreach ([Asset::ASSET_JAVASCRIPT, Asset::ASSET_STYLESHEET] as $asset) {
                    if (isset($parameters['paths'][$asset])) {
                        $cdn->setPath($asset, $parameters['paths'][$asset]);
                    }
                }
            }
            foreach ([Asset::ASSET_JAVASCRIPT, Asset::ASSET_STYLESHEET] as $asset) {
                if (isset($parameters[$asset])) {
                    foreach ($parameters[$asset] as $name => $path) {
                        switch ($asset) {
                            case Asset::ASSET_JAVASCRIPT:
                                $cdn->addJs($name, $path);
                                break;
                            case Asset::ASSET_STYLESHEET:
                                $cdn->addCss($name, $path);
                                break;
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get the instance of script repository
     *
     * @param string $name  Script repository name
     * @return \NTLAB\JS\Repository
     */
    public function get($name)
    {
        if (!$name) {
            throw new \InvalidArgumentException('Script name is mandatory');
        }
        if (!isset($this->repositories[$name])) {
            $this->repositories[$name] = new Repository($name);
        }
        return $this->repositories[$name];
    }

    /**
     * Check if script repository instance is exist.
     *
     * @param string $name  The repository name.
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->repositories[$name]) ? true : false;
    }

    /**
     * Get all scripts content.
     *
     * @param boolean $includeTag  Wheter to include javascript tag or not
     * @return string
     */
    public function getScript($includeTag = false)
    {
        $content = null;
        foreach ($this->repositories as $repository) {
            if ($script = $repository->getContent()) {
                if ($content) {
                    $content .= Escaper::getEol();
                }
                $content .= $script;
            }
        }
        if (null !== $content && strlen($content) && $includeTag) {
            $content = $this->scriptTag($content);
        }
        return $content;
    }

    /**
     * Create script tag for javascript.
     *
     * @param string $content  Script content
     * @return string
     */
    public function scriptTag($content)
    {
        return <<<EOF
<script type="text/javascript">
// <![CDATA[
$content
// ]]>
</script>
EOF;
    }

    /**
     * Check if a class is a script.
     *
     * @param string $class  The class
     * @return boolean
     */
    protected function isScript($class)
    {
        if (class_exists($class)) {
            if ($r = new \ReflectionClass($class)) {
                if ($r->isSubclassOf('NTLAB\JS\Script')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Resolve script dependency.
     *
     * @param string $dep  Dependency name
     * @return string
     */
    public function resolveDependency($dep)
    {
        foreach ($this->resolvers as $resolver) {
            if (strlen($class = $resolver->resolve($dep))) {
                foreach ([$class, 'NTLAB\\JS\Script\\'.$class] as $rClass) {
                    if ($this->isScript($rClass)) {
                        return $rClass;
                    }
                }
            }
        }
    }

    /**
     * Compress content using registered compressor.
     *
     * @param string $content  Raw content
     * @return string
     */
    public function compress($content)
    {
        if (strlen($content) && null !== ($compressor = $this->getCompressor())) {
            $content = $compressor->compress($content);
        }
        return $content;
    }
}