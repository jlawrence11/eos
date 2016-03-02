<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use jlawrence\eos\Matrix;


class MatrixTest extends PHPUnit_Framework_TestCase
{
    public function testAssignToString()
    {
        $matrix = new Matrix("[1,0,1;0,1,0;1,0,1]");
        $this->assertEquals("[1,0,1;0,1,0;1,0,1]", $matrix->toString());
    }

    public function testSquareN()
    {
        $matrix = new Matrix("[1,0,1;0,1,0;1,0,1]");
        $this->assertTrue($matrix->isSquare());
        $this->assertEquals(3, $matrix->_getN());
    }

    public function testIdentity()
    {
        $matrix = new Matrix();
        $matrix->createIdentity(3);
        $this->assertEquals("[1,0,0;0,1,0;0,0,1]", $matrix->toString());
    }

    public function testArray()
    {
        $matrix = new Matrix("[0,1,0]");
        $this->assertEquals(array(array('0','1','0')), $matrix->getArray());
    }

    public function testPrettyPrint()
    {
        $toCompare = "|         0.00                     1.00                     0.00             |\n";
        $matrix = new Matrix("[0,1,0]");
        $this->assertEquals($toCompare, $matrix->prettyPrint());
    }

    public function testMatrixAddition()
    {
        $matrixA = new Matrix("[0,1,0;1,0,1;0,1,0]");
        $matrixB = new Matrix("[1,0,1;0,1,0;1,0,1]");
        $matrixC = $matrixA->addMatrix($matrixB);
        $this->assertEquals("[1,1,1;1,1,1;1,1,1]", $matrixC->toString());
    }

    public function testMatrixSubtraction()
    {
        $matrixA = new Matrix("[1,1,1;1,1,1;1,1,1]");
        $matrixB = new Matrix("[1,0,1;0,1,0;1,0,1]");
        $matrixC = $matrixA->subMatrix($matrixB);
        $this->assertEquals("[0,1,0;1,0,1;0,1,0]", $matrixC->toString());
    }

    public function testScalarMultiplication()
    {
        $matrixA = new Matrix("[0,3,0;4,0,5;0,6,0]");
        $matrixB = $matrixA->mpScalar(2);
        $this->assertEquals("[0,6,0;8,0,10;0,12,0]", $matrixB->toString());
    }

    public function testMatrixMultiplication()
    {
        $matrixA = new Matrix("[1,2,3;4,5,6;7,8,9]");
        $matrixB = new Matrix("[1,0,1;0,1,0;1,0,1]");
        $matrixC = $matrixA->mpMatrix($matrixB);
        $this->assertEquals("[4,2,4;10,5,10;16,8,16]", $matrixC->toString());
    }

    public function testDeterminant()
    {
        $matrixA = new Matrix("[1,-3,3;2,3,-1;4,-3,-1]");
        $this->assertEquals(-54, $matrixA->getDeterminant());
    }

    public function testCoFactor()
    {
        $matrixA = new Matrix("[1,-3,3;2,3,-1;4,-3,-1]");
        $this->assertEquals("[-6,-2,-18;-12,-13,-9;-6,7,9]", $matrixA->coFactor(false,false));
    }

    public function testTranspose()
    {
        $matrixA = new Matrix("[1,-3,3;2,3,-1;4,-3,-1]");
        $this->assertEquals("[1,2,4;-3,3,-3;3,-1,-1]", $matrixA->transpose(false,false));
    }

    public function testInverseAdjugate()
    {
        $matrixA = new Matrix("[1,0,1;0,1,0;0,0,1]");
        $this->assertEquals("[1,0,-1;0,1,0;0,0,1]", $matrixA->inverse());
    }

    public function testNonMatrixInput()
    {
        $matrix = new Matrix();
        $this->assertFalse($matrix->_assign("[1,2,3;4,5;6,7,8]"));
        $this->assertFalse($matrix->isValid("[]"));
        $this->assertFalse($matrix->isSquare("[]"));
    }

    public function testMatrixIdentity()
    {
        $matrix = new Matrix();
        $matrixB = $matrix->createIdentity(2, false);
        $this->assertEquals("[1,0;0,1]", $matrixB->toString());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_NOT_SQUARE
     */
    public function testNonSquareN()
    {
        $matrix = new Matrix("[1,2,3;4,5,6]");
        $matrix->_getN();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_NO_MATRIX
     */
    public function testNoMatrixString()
    {
        $matrix = new Matrix();
        $temp = sprintf("%s", $matrix->toString());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_INVALID_INPUT
     */
    public function testMismatchedBrackets()
    {
        $matrix = new Matrix("34'4;5");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_NOT_EQUAL
     */
    public function testWrongAddition()
    {
        $matrixA = new Matrix("[1,2;3,4]");
        $matrixB = new Matrix("[1,2,3;4,5,6;7,8,9]");
        $matrixC = $matrixA->addMatrix($matrixB);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_NOT_EQUAL
     */
    public function testWrongSubtraction()
    {
        $matrixA = new Matrix("[1,2;3,4]");
        $matrixB = new Matrix("[1,2,3;4,5,6;7,8,9]");
        $matrixC = $matrixA->subMatrix($matrixB);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_NOT_SQUARE
     */
    public function testNoSquareDeterminate()
    {
        $matrix = new Matrix("[1,2;3,4;5,6]");
        $matrixC = $matrix->getDeterminant();
    }

    public function testOneByOneDeterminate()
    {
        $matrix = new Matrix("[1]");
        $this->assertEquals(1,$matrix->getDeterminant());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \jlawrence\eos\Matrix::E_NOT_EQUAL
     */
    public function testWrongMatrixMultiplication()
    {
        $matrixA = new Matrix("[1,2;3,4]");
        $matrixB = new Matrix("[1,2,3;4,5,6;7,8,9]");
        $matrixC = $matrixA->mpMatrix($matrixB);
    }

}
