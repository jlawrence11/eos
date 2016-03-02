<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use jlawrence\eos\Stack;

class StackTest extends PHPUnit_Framework_TestCase
{
    public function testClearGetStack()
    {
        $stack = new Stack();
        $stack->push("1");
        $stack->push("2");
        $this->assertEquals(array("1", "2"), $stack->getStack());
        $stack->clear();
        $this->assertFalse($stack->getStack());
    }
}
