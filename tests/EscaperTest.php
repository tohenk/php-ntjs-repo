<?php

namespace NTLAB\JS\Test;

use NTLAB\JS\Util\Escaper;
use NTLAB\JS\Util\JSValue;

class EscaperTest extends BaseTest
{
    public function testEscapeValue()
    {
        $this->assertEquals('null', Escaper::escapeValue(null, true), 'Proper escape value of null');
        $this->assertEquals('true', Escaper::escapeValue(true, true), 'Proper escape value of boolean');
        $this->assertEquals('49', Escaper::escapeValue(49, true), 'Proper escape value of numeric');
        $this->assertEquals("'It''s me'", Escaper::escapeValue('It\'s me', true), 'Proper escape value of string');
    }

    public function testEscape()
    {
        $this->assertEquals('{}', Escaper::escape(array()), 'Proper escape empty array');
        $this->assertEquals("[1, 'test']", Escaper::escape(array(1, 'test'), null, 0, true), 'Proper escape numeric indexed array');
        $this->assertEquals("{me: 'test', you: 100}", Escaper::escape(array('me' => 'test', 'you' => 100), null, 0, true), 'Proper escape indexed array');
    }

    public function testNesting()
    {
        Escaper::setEol("\r\n");
        $this->assertEquals(<<<EOF
{
    test: function(){}
}
EOF
, Escaper::escape(array('test' => new JSValue('function(){}'))), 'Proper escape nested');
    }
}