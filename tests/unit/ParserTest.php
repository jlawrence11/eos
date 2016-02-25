<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use jlawrence\eos\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testAddition()
    {
        $this->assertEquals(2, Parser::solve('1+1'));
    }

    public function testSubtraction()
    {
        $this->assertEquals(3, Parser::solve('9-6'));
    }

    public function testMultiplication()
    {
        $this->assertEquals(10, Parser::solve('5*2'));
    }

    public function testDivision()
    {
        $this->assertEquals(4, Parser::solve('12 / 3'));
    }

    public function testAdditionAndSubtraction()
    {
        $this->assertEquals(5, Parser::solve('3 + 4 - 2'));
    }

    public function testMultiplicationAndDivision()
    {
        $this->assertEquals(8, Parser::solve('16 * 2 / 4'));
    }

    public function testBIDMAS()
    {
        $this->assertEquals(14, Parser::solve('2 + 3 * 4'));
        $this->assertEquals(12, Parser::solve('3 * (7 - 3)'));
        $this->assertEquals(13, Parser::solve('2 + 3 * 5 - 4'));
        $this->assertEquals(13, Parser::solve('2 * 3 – 2 * 5'));
    }
}
