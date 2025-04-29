<?php

/*
 * The MIT License
 *
 * Copyright (c) 2015-2025 Toha <tohenk@yahoo.com>
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

namespace NTLAB\JS\Repo\Test;

use PHPUnit\Framework\TestCase;
use NTLAB\JS\Manager;
use NTLAB\JS\Backend;
use NTLAB\JS\Script;
use NTLAB\JS\Util\Escaper;

class ScriptTest extends TestCase
{
    protected function setUp(): void
    {
        Manager::getInstance()
            ->setBackend(new Backend());
        Escaper::setEol("\r\n");
    }

    public function testScript()
    {
        $script = Script::create('JQuery');
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
