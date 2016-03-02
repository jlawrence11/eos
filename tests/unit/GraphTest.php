<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use jlawrence\eos\Graph;


class GraphTest extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $this->assertTrue(Graph::init(640, 480));
    }

    public function testGraphImage()
    {
        Graph::graph('x', -10, 10, null, true, true, null, null);
        $this->assertFalse(is_null(Graph::outGD()));
    }
}
