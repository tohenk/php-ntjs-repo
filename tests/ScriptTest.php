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

namespace NTLAB\JS\Test;

use NTLAB\JS\DependencyResolver;
use NTLAB\JS\Manager;
use NTLAB\JS\Backend;
use NTLAB\JS\Script;
use NTLAB\JS\Test\Script\TestScript;

class ScriptTest extends BaseTest
{
    protected $script;

    protected function setUp(): void
    {
        Manager::getInstance()
            ->setBackend(new Backend())
            ->addResolver(new DependencyResolver('NTLAB\JS\Test\Script'));
    }

    public function testCreate()
    {
        $this->assertEquals(TestScript::class, get_class(Script::create('TestScript')), 'Resolver can resolve script name to class name');
    }

    public function testCall()
    {
        $script = Script::create('TestScript');
        $script
            ->add('do_something()')
            ->call('a message');
        $this->assertEquals(<<<EOF
do_something();
// a message
EOF
        , $script->getRepository()->getContent(), 'Script call() should include script doCall()');
    }

    public function testScript()
    {

        $script = Script::create('JQuery');
        $script->getRepository()
            ->setWrapper(<<<EOF
(function($) {%s})(jQuery);
EOF
            )
            ->setWrapSize(1)
        ;
        $script->add('$.test();');
        $this->assertTrue(Manager::getInstance()->has('jquery'), 'Repository properly initialized when script added');
        $this->assertEquals(<<<EOF
(function($) {
    $.test();
})(jQuery);
EOF
        , $script->getRepository()->getContent(), 'Script properly added');
    }
}