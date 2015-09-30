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
    protected $repositories = array();

    /**
     * @var \NTLAB\JS\BackendInterface
     */
    protected $backend = null;

    /**
     * @var \NTLAB\JS\Compressor
     */
    protected $compressor = null;

    /**
     * @var \NTLAB\JS\DependencyResolverInterface[]
     */
    protected $resolvers = array();

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
     * @param \NTLAB\JS\Compressor $compressor  Script compressor
     * @throws \InvalidArgumentException
     * @return \NTLAB\JS\Manager
     */
    public function setCompressor($compressor)
    {
        $this->compressor = $compressor;
        if (null !== $this->compressor && !$this->compressor instanceof Compressor) {
            throw new \InvalidArgumentException('Compressor must be a sub class of \NTLAB\JS\Compressor.');
        }

        return $this;
    }

    /**
     * Get script compressor.
     *
     * @return \NTLAB\JS\Compressor
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
        if (strlen($content) && $includeTag) {
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
        return sprintf(<<<EOF
<script type="text/javascript">
//<![CDATA[
%s
//]]>
</script>
EOF
, $content);
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
                foreach (array($class, 'NTLAB\\JS\Script\\'.$class) as $rClass) {
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