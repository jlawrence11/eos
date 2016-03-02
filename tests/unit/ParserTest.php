<?php

require_once __DIR__.'/../../vendor/autoload.php';

use jlawrence\eos\Parser;
use jlawrence\eos\Trig;

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
        $this->assertEquals(20, Parser::solve('5+((1+2)*4)+3'));
    }

    public function testVariables()
    {
        $this->assertEquals(10, Parser::solve('x*y', [
            'x' => 5,
            'y' => 2,
        ]));

        $this->assertEquals(24, Parser::solve('2(4x)', [
            'x' => 3,
        ]));

        $this->assertEquals(15, Parser::solve('2a+a', [
            'a' => 5,
        ]));
    }

    public function testTrigonometryInDegrees()
    {
        Trig::$DEGREES = true;
        $this->assertEquals(0, Parser::solve('sin(180)'));
        $this->assertEquals(0, Parser::solve('cos(90)'));
        $this->assertEquals(1, Parser::solve('tan(45)'));

        Trig::$DEGREES = false;
    }

    public function testTrigonometry()
    {
        $this->assertEquals(1, Parser::solve('sin(pi/2)'));
        $this->assertEquals(-1, Parser::solve('cos(pi)'));
        $this->assertEquals(1, Parser::solve('tan(pi/4)'));
        $this->assertEquals(1, Parser::solve('csc(pi/2)'));
        $this->assertEquals(-1, Parser::solve('sec(pi)'));
        $this->assertEquals(1, Parser::solve('cot(pi/4)'));
    }

    public function testGamma()
    {
        $this->assertEquals(120, Parser::solve('5!'));
        $this->assertEquals(720, Parser::solve('3!!'));

        $result = Parser::solve('3.5!');

        $this->assertTrue(11.64 > $result && $result > 11.63);
    }

    public function testLogSqrtPowersSummation()
    {
        $this->assertEquals(2, Parser::solve('ln(e^2)'));
        $this->assertEquals(8, Parser::solve('log(sqrt(2^x),2)', 16));
        $this->assertEquals(2, Parser::solve('log(100)'));
        $this->assertEquals(9, Parser::solve('sum(sum(x, 1, 2)x, 1, 2)'));
        $this->assertEquals(100, Parser::solve('sum(100, 2, 3)'));
    }

    public function testModulus()
    {
        $this->assertEquals(2, Parser::solve('5%3'));
        $this->assertEquals(1, Parser::solve('4%3'));
    }

    public function testAbs()
    {
        $this->assertEquals(4, Parser::solve('abs(-4)'));
    }

    public function testSolvePostFix()
    {
        $pf = ['2', '3', '+'];
        $this->assertEquals(5, Parser::solve($pf));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_DIV_ZERO
     */
    public function testDivisionByZero()
    {
        Parser::solve('3/0');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_DIV_ZERO
     */
    public function testModulusByZero()
    {
        Parser::solve('3%0');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NAN
     */
    public function testLnError()
    {
        Parser::solve('ln(0)');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NAN
     */
    public function testLogError()
    {
        Parser::solve('log(0)');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NAN
     */
    public function testSqrtError()
    {
        Parser::solve('sqrt(-4)');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NO_VAR
     */
    public function testNoVariable()
    {
        Parser::solve('4x');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NAN
     */
    public function testNegativeFactorial()
    {
        Parser::solve('-4!');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NO_EQ
     */
    public function testNoEquation()
    {
        Parser::solve('');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NO_SET
     */
    public function testMismatchedParenthesis()
    {
        Parser::solve('(2+(3*4)');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Math::E_NO_SET
     */
    public function testMismatchedBrackets()
    {
        Parser::solve('[2+[3*4]');
    }
}
