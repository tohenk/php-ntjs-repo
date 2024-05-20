Code Javascript Everywhere!
===========================

PHP-NTJS allows to dynamically manage your javascripts, stylesheets, and
scripts so you can focus on your code. You can code your javascript using
PHP class, or write directly in the PHP code, even on template.

JQuery and Bootstrap
--------------------

Support for popular javascript like JQuery, Bootstrap, and FontAwesome.

CDN
---

To speed up your page, CDN can be enabled, PHP-NTJS will automatically do it
for you. Just loads needed CDN information and assets will loaded from CDN.

Minified Output
---------------

On production, you can enable script output compression either by using JSMin
or JShrink. On development, you can add script debug information to easily
locate problematic code.

Integrate With Your Code
------------------------

To integrate PHP-NTJS with your code, you need to enable [Composer](https://getcomposer.org)
support in your project.

* Require `ntlab/ntjs` and install dependencies.

```shell
php composer.phar require ntlab/ntjs
php composer.phar install
```

* Clone the assets somewhere in your public web folder.

```shell
git clone https://github.com/tohenk/ntjs-web-assets /path/to/www/cdn
```

* Create your script backend, which is responsible for collecting assets, it must
  be implements `NTLAB\JS\BackendInterface` or extends `NTLAB\JS\Backend`.
  An example of backend is available [here](https://github.com/tohenk/php-ntjs-demo/blob/master/src/Backend.php).

* Create script dependency resolver, which us responsible for resolving namespace
  when the script referenced. It must be implements `NTLAB\JS\DependencyResolverInterface`.
  An example of resolver is available [here](https://github.com/tohenk/php-ntjs-demo/blob/master/src/Backend.php).

* Optionally, create script compressor which implements `NTLAB\JS\CompressorInterface`.
  An example of compressor is available [here](https://github.com/tohenk/php-ntjs-demo/blob/master/src/Backend.php).

* Connect it together, see [example](https://github.com/tohenk/php-ntjs-demo/blob/master/src/Demo.php).

```php
use NTLAB\JS\Manager;
use NTLAB\JS\Script;

class MyClass
{
    protected $useCDN = true;
    protected $minifyScript = false;
    protected $debugScript = true;

    public function initialize()
    {
        $manager = Manager::getInstance();
        // create backend instance
        $backend = new Backend($this->useCDN);
        // set script backend
        $manager->setBackend($backend);
        // register script resolver, the backend also a resolver
        $manager->addResolver($backend);
        // register script compressor, the backend also a compressor
        if ($this->minifyScript) {
            $manager->setCompressor($backend);
        }
        // set script debug information
        if ($this->debugScript) {
            Script::setDebug(true);
        }
    }
}
```

* Start write your javascript code, see [example](https://github.com/tohenk/php-ntjs-demo/blob/master/src/Script/MyDemo.php).

```php
use NTLAB\JS\Script;

class MyDemoClass
{
    public function something()
    {
        Script::create('JQuery')
            ->add(<<<EOF
alert('Do something');
EOF
            );
    }
}
```

* Add a helper to include stylesheets, javascripts and script to the HTML response,
  see this [example](https://github.com/tohenk/php-ntjs-demo/blob/master/src/Helper.php) and
  this [example](https://github.com/tohenk/php-ntjs-demo/blob/master/view/layout.php).

Live Demo
---------

Live demo is available [here](https://apps.ntlab.id/demo/php-ntjs).
