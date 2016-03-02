<?php
/**
 * matrix.class.php
 *
 * Will set up the defines for error checking as well as provide
 * the Matrix class for include.  As this is made to be modular,
 * only the class (and possibly helper classes) along with their
 * defines will be found in this file.
 * @package Math
 * @subpackage Matrix
 */

namespace jlawrence\eos;

/**
 * Matrix Class
 * 
 * This class will allow you to create and use Matrices
 * as well as providing common Matrix operations.  It 
 * uses PHP5 for OOP and Error Throwing and is commented
 * for the PHPDoc Parser for documentation creation.
 * 
 * @version $Id: matrix.class.php 10 2012-08-06 23:41:36Z jlawrence11 $
 * @author Jon Lawrence <JLawrence11@gmail.com>
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @copyright Copyright �2012, Jon Lawrence
 * @package Math
 * @subpackage Matrix
 */
class Matrix {

    /**
     * Invalid String input type
     */
    const E_INVALID_INPUT = 5001;

    /**
     * Matrix needed to be a square matrix for the operation
     */
    const E_NOT_SQUARE = 5002;

    /**
     * Matrix was undefined
     */
    const E_NO_MATRIX = 5003;

    /**
     * Matrix had varying column lengths
     */
    const E_INVALID_MATRIX = 5004;

    /**
     * Matrix operation required rows/cols to be even, they were not
     */
    const E_NOT_EQUAL = 5005;

    /**
     * Determinate was '0' while preforming another operation
     */
    const E_NO_INVERSE = 5006;

    private $matrix;
    
    /**
     * Construct method
     * 
     * For format of input string, see the see tag below
     *
     * @see Matrix::_assign()
     * @param string $mText Matrix text input
     */
    public function __construct($mText="")
    {
        if ($mText) $this->_assign($mText);
    }
    
    /**
     * Create a matrix based on string input similar to the TI Calculators
     * input string "[1,2,3;4,5,6;7,8,9]" is the equivalent of the matrix:
     * <pre>
     * | 1  2  3 |
     * | 4  5  6 |
     * | 7  8  9 |
     * </pre>
     *
     * @param String $mText The matrix in string format to assign to the current object
     * @return Boolean True if is passes verification after being converted
     * @throws \Exception If the input text is not in a valid format
     */
    public function _assign($mText)
    {
        if(trim($mText)=="")
            return false;
        
        $mText = preg_replace("/\s/", "", $mText);
        if(!preg_match("/^\[(([\-]*[0-9\. ]+[,]{0,1})+[;]{0,1})*\]$/", $mText)) {
            throw new \Exception("'{$mText}' is not a valid input", Matrix::E_INVALID_INPUT);
        }
        $mText = preg_replace("/(\[|\])/", "", $mText);
        
        $rows = explode(";", $mText);
        $i=0;$j=0;
        foreach($rows as $row)
        {
            $cols = explode(",", $row);
            foreach($cols as $value)
            {
                $this->matrix[$i][$j] = $value;
                $j++;
            }
            $i++;
            $j = 0;
        }
        
        return $this->_verify();
    }
    
    /**
     * Private function that will verify all the columns have the same
     * number of items, ensuring it is a valid matrix
     *
     * @access private
     * @param array|bool $mArray
     * @return bool True if it passes, false if not a valid matrix
     */
    private function _verify($mArray = false)
    {
        if(!$mArray) $mArray = $this->matrix;
        $nSet = false;
        
        if(is_array($mArray))
        {
            foreach($mArray as $row)
            {
                $cols = count($row);
                if($nSet===false) {
                    $nSet = $cols;
                }
                if($cols != $nSet) {
                    return false;
                }
            }
        } else {
            return false;
        }
        
        return true;
    }

    /**
     * Is it a valid matrix?
     *
     * Public function to tell the class user whether or not the passed
     * array is valid, if no array is passed, it will tell the user whether
     * the matrix of the current instance is valid. Valid is denoted by all
     * rows have the same number of columns.
     *
     * @param array|bool $mArray Array to be used, if not assigned will default to $this->matrix
     * @return bool True/False depending on if array is a valid matrix
     */
    public function isValid($mArray = false)
    {
        if(!$mArray) $mArray = $this->matrix;
        return $this->_verify($mArray);
    }

    /**
     * Is it a square Matrix?
     *
     * Will determine whether or not the matrix is valid, and if it
     * is, will determine if the matrix is a square matrix (n by n).
     *
     * @param array|bool $mArray Matrix array, if not assigned will use $this->matrix
     * @return bool True/False depending on whether or not the matrix is square
     */
    public function isSquare($mArray = false)
    {
        if(!$mArray) $mArray = $this->matrix;
        if(!$this->_verify($mArray)) {
            return false;
        }
        $rows = count($mArray);
        $cols = count($mArray[0]);
        return ($rows == $cols);
    }
    
    /**
     * Get 'n' from a square (n by n) Matrix
     * 
     * Will check to see if a matrix is square, if so, will return 'n', which
     * is the number of rows==columns in the matrix
     *
     * @param array|bool $mArray Matrix array, uses $this->matrix if not assigned
     * @return int The 'n' of a square matrix, or false if not square
     * @throws \Exception If not a square matrix, throws an exception
     */
    public function _getN($mArray = false)
    {
        if(!$mArray) $mArray = $this->matrix;
        
        if($this->isSquare($mArray)) {
            return count($mArray);
        } else {
            $m = $this->toString($mArray);
            throw new \Exception("'{$m}' is not a square matrix", Matrix::E_NOT_SQUARE);
        }
    }
    
    /**
     * Create an Identity Matrix
     *
     * Creates an Identity Matrix of size 'n'.
     *
     * @link http://en.wikipedia.org/wiki/Identity_matrix
     * @param int $n The rows/cols of identity matrix
     * @param bool $useInternal If true will set $this->matrix
     * @return Matrix|bool Return an identity matrix if $useInternal is false, otherwise 'true'
     */
    public function createIdentity($n, $useInternal = true)
    {
        $mArray = array();
        for($rows=0;$rows<$n;$rows++) {
            for($cols=0;$cols<$n;$cols++) {
                if($rows==$cols) {
                    $mArray[$rows][$cols] = 1;
                } else {
                    $mArray[$rows][$cols] = 0;
                }
            }
        }
        if($useInternal == true) {
            $this->matrix = $mArray;
            return true;
        } else {
            $nMatrix = new Matrix($this->toString($mArray));
            return $nMatrix;
        }
        
    }

    /**
     * Convert current Matrix to string format
     *
     * Convert an array to the string format used by this class.
     *
     * @see Matrix::_assign()
     * @param array|bool $mArray if not assigned will use this instance's matrix.
     * @throws \Exception If matrix is not an array
     * @return string The array broken down in to string format
     */
    public function toString($mArray = false)
    {
        if(!$mArray) $mArray = $this->matrix;
        $rows=array();
        if(is_array($mArray))
        {
            foreach($mArray as $cols){
                $rows[] = implode(",", $cols);
            }
            $retString = sprintf("[%s]", implode($rows, ";"));
            return $retString;
        } else {
            throw new \Exception("No matrix to convert", Matrix::E_NO_MATRIX);
        }
    }
    
    /**
     * Overload PHP's class __toString() method
     * 
     * PHP magic method for "echoing" this object without a specific method called
     * Will use {@link Matrix::toString()} with no parameters for it's return.
     * 
     * @return string Returns the $matrix value in string format
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Get Matrix Array
     *
     * Will return the matrix array of the current instance.
     *
     * @return array The matrix array of the current instance
     */
    public function getArray()
    {
        return $this->matrix;
    }
    
    /**
     * Formatted Matrix output for use in console
     * 
     * Will output the matrix in 'pretty' format, if used with 'echo' and
     * HTML, surround it by the '<<pre>>' and '<</pre>>' tags to display properly
     *
     * @param int $width The width of printing space to use
     * @param array|bool $mArray Matrix array, defaults to $this->matrix
     * @return string "Pretty-Printed" matrix in ASCII format
     */
    public function prettyPrint($width=80, $mArray=false)
    {
        if(!$mArray) $mArray = $this->matrix;
        if(!$this->_verify($mArray)) return false;
        
        $out = "";
        $aCount = count($mArray[0]);
        $space = floor(($width-4)/$aCount);
        $space_2 = floor($space/2);
        
        foreach($mArray as $row)
        {
            $out .= sprintf("| %{$space_2}.2f", $row[0]);
            for($i=1;$i<$aCount;$i++)
            {
                $out .= sprintf("%{$space}.2f", $row[$i]);
            }
            $out .= sprintf("%{$space_2}s |\n", " ");
        }
        
        return $out;
    }
    
    /**
     * Adds two matrices together
     * 
     * Will add the inputted Matrix to the current instance, and return
     * the result as Matrix class.
     *
     * @link http://en.wikipedia.org/wiki/Matrix_addition
     * @param Matrix $nMatrix Matrix class to be added to current instance
     * @return Matrix The result of the addition
     * @throws \Exception $msg of exception explains problem
     */
    public function addMatrix(Matrix $nMatrix)
    {
        if(!$this->_verify() || !$nMatrix->_verify())
            throw new \Exception("Matrices have varying column sizes", Matrix::E_INVALID_MATRIX);

        $matrix1 = $this->getArray();
        $matrix2 = $nMatrix->getArray();
        if((count($matrix1)!=count($matrix2)) || (count($matrix1[0])!=count($matrix2[0])))
        {
            $m1 = $this->toString($matrix1);
            $m2 = $this->toString($matrix2);
            throw new \Exception("The rows and/or columns '{$m1}' and '{$m2}' are not the same", Matrix::E_NOT_EQUAL);
        }
        
        $rArray = array();
        for($row=0;$row<count($matrix1);$row++) {
            for($col=0;$col<count($matrix1[0]);$col++) {
                $rArray[$row][$col] = $matrix1[$row][$col] + $matrix2[$row][$col];
            }
        }
        $rMatrix = new Matrix($this->toString($rArray));
        return $rMatrix;
    }
    
    /**
     * Subtract Matrices
     * 
     * Will subtract the inputted Matrix from the current instance, and return
     * the result as Matrix class.
     *
     * @link http://en.wikipedia.org/wiki/Matrix_subtraction
     * @param Matrix $nMatrix Matrix class to be subtracted from current instance
     * @return Matrix The result of the subtraction
     * @throws \Exception $msg of exception explains problem
     */
    public function subMatrix(Matrix $nMatrix)
    {
        if(!$this->_verify() || !$nMatrix->_verify())
            throw new \Exception("Matrices have varying column sizes", Matrix::E_INVALID_MATRIX);

        $matrix1 = $this->getArray();
        $matrix2 = $nMatrix->getArray();
        if((count($matrix1)!=count($matrix2)) || (count($matrix1[0])!=count($matrix2[0])))
        {
            $m1 = $this->toString($matrix1);
            $m2 = $this->toString($matrix2);
            throw new \Exception("The rows and/or columns '{$m1}' and '{$m2}' are not the same", Matrix::E_NOT_EQUAL);
        }
        
        $rArray = array();
        for($row=0;$row<count($matrix1);$row++) {
            for($col=0;$col<count($matrix1[0]);$col++) {
                $rArray[$row][$col] = $matrix1[$row][$col] - $matrix2[$row][$col];
            }
        }
        $rMatrix = new Matrix($this->toString($rArray));
        return $rMatrix;
    }
    
    /**
     * Multiply current matrix by a scalar value
     * 
     * Multiplies a matrix by a scalar value (int/float/etc) (constant, ie '2')
     *
     * @link http://en.wikipedia.org/wiki/Scalar_multiplication
     * @param float $k The value to multiply the matrix by
     * @return Matrix Returns a new Matrix instance with the result
     * @throws \Exception if the instance matrix is not valid
     */
    public function mpScalar($k)
    {
        //we'll verify a true matrix to ... help the user
        if(!$this->_verify())
            throw new \Exception("Matrix '{$this}' has varying column sizes", Matrix::E_INVALID_MATRIX);

        $cArray = $this->getArray();
        $rArray = array();
        $rows = count($cArray);
        $cols = count($cArray[0]);
        for($i=0;$i<$rows;$i++) {
            for($j=0;$j<$cols;$j++) {
                $rArray[$i][$j] = $cArray[$i][$j] * $k;
            }
        }
        $rMatrix = new Matrix($this->toString($rArray));
        return $rMatrix;
    }
    
    /**
     * Get the Matrix Determinant
     * 
     * Finds the determinant of the square matrix, user should not use
     * the parameter, as that is meant to allow recursive calling
     * of this function from within itself.
     *
     * @link http://en.wikipedia.org/wiki/Matrix_determinant
     * @param array|bool $mArray The array to find a determinate of
     * @return float The Determinate of the square matrix
     * @throws \Exception If matrix is 1,1 or is not square
     */
    public function getDeterminant($mArray = false)
    {
        if(!$mArray) $mArray = $this->matrix;
        //print_r($mArray);
        if(!$this->isSquare($mArray))
            throw new \Exception("'{$this}' is not a square matrix", Matrix::E_NOT_SQUARE);

        $n = $this->_getN($mArray);
        if($n < 1){
            // @codeCoverageIgnoreStart
            // Should never get this far
            throw new \Exception("No Matrix", Matrix::E_NO_MATRIX);
            // @codeCoverageIgnoreEnd
        } elseif ($n == 1) {
            $det = $mArray[0][0];
        } elseif ($n == 2) {
            $det = $mArray[0][0]*$mArray[1][1] - $mArray[1][0]*$mArray[0][1];
        } else {
            $det = 0;
            $nArray = array();
            for($j1=0;$j1<$n;$j1++) {
                for($i=1;$i<$n;$i++) {
                    $j2 = 0;
                    for($j=0;$j<$n;$j++) {
                        if($j==$j1) {
                            continue;
                        }
                        $nArray[$i-1][$j2] = $mArray[$i][$j];
                        $j2++;
                    }
                }
                $det += pow(-1,2+$j1)*$mArray[0][$j1]*$this->getDeterminant($nArray);
            }
        }
        return $det;
    }
    
    /**
     * coFactor Matrix
     * 
     * Will return a Matrix of coFactors for the matrix provided, or an array
     * of the matrix as is the default.
     *
     * @link http://en.wikipedia.org/wiki/Matrix_cofactors
     * @param array|bool $cArray A matrix in array format (or $this->matrix by default)
     * @param bool $asArray When set to true, will return an array, when false a Matrix Object
     * @return Matrix|array A matrix of coFactors for the array provided (or current matrix)
     * @throws \Exception if the matrix is not square
     */
    public function coFactor($cArray=false,$asArray=true)
    {
        if(!$cArray) $cArray = $this->matrix;
        if(!$this->isSquare($cArray))
            throw new \Exception("'{$this}' is not a square matrix", Matrix::E_NOT_SQUARE);

        $n = $this->_getN($cArray);
        $minor = array();
        $rArray = array();
        
        for($j=0;$j<$n;$j++){
            for($i=0;$i<$n;$i++) {
                //Form the adjugate
                $i1 = 0;
                for($ii=0;$ii<$n;$ii++) {
                    if($ii==$i) {
                        continue;
                    }
                    $j1=0;
                    for($jj=0;$jj<$n;$jj++) {
                        if($jj==$j) {
                            continue;
                        }
                        $minor[$i1][$j1] = $cArray[$ii][$jj];
                        $j1++;
                    }
                    $i1++;
                }
                $det = $this->getDeterminant($minor);
                $rArray[$i][$j] = pow(-1,$i+$j+2)*$det;
            }
        }
        if($asArray==false){
            $rMatrix = new Matrix($this->toString($rArray));
            return $rMatrix;
        } else {
            return $rArray;
        }
        
    }
    
    /**
     * Will transpose the current matrix or array provided
     *
     * Transposes the current matrix, or the array provided
     *
     * @link http://en.wikipedia.org/wiki/Matrix_transpose
     * @param array|bool $cArray the array to transpose (defaults to $this->matrix)
     * @param bool $asArray whether to return an array or Matrix object
     * @return array|Matrix Defaults to returning an array of the transposed matrix
     * @throws \Exception if the matrix is not square
     */
    public function transpose($cArray=false,$asArray=true)
    {
        if(!$cArray) $cArray = $this->matrix;
        if(!$this->isSquare($cArray))
            throw new \Exception("'{$this}' is not a square matrix", Matrix::E_NOT_SQUARE);

        $n = $this->_getN();
        $nArray = array();
        for($i=0;$i<$n;$i++) {
            for($j=0;$j<$n;$j++) {
                $nArray[$j][$i] = $cArray[$i][$j];
            }
        }
        if($asArray==true) {
            return $nArray;
        } else {
            $nMatrix = new Matrix($this->toString($nArray));
            return $nMatrix;
        }
    }
    
    /**
     * Adjugate Matrix
     * 
     * Will return the Adjugate matrix of the array provided
     * or the current matrix instance if not provided.
     *
     * @link http://en.wikipedia.org/wiki/Adjugate_matrix
     * @param array|bool $cArray Defaults to $this->matrix if not provided
     * @param bool $asArray Whether to return an array or Matrix object
     * @return array|Matrix Defaults to return the array of the Adjugate matrix
     */
    public function adjugate($cArray=false,$asArray=true)
    {
        if(!$cArray) $cArray = $this->matrix;
        $rArray = $this->transpose($this->coFactor($cArray));
        if($asArray==true)
            return $rArray;

        $rMatrix = new Matrix($this->toString($rArray));
        return $rMatrix;
    }
    
    /**
     * Inverse of current matrix
     * 
     * Will give the inverse of the array provided or the current matrix
     * Matrix returned denoted by A^-1
     *
     * @link http://en.wikipedia.org/wiki/Inverse_matrix
     * @param array|bool $cArray Array to invert (defaults to $this->matrix)
     * @return Matrix By default returns a new instance of Matrix
     * @throws \Exception for any number of reasons that would make the inverse not available
     */
    public function inverse($cArray = false)
    {
        if(!$cArray) $cArray = $this->matrix;

        $det = $this->getDeterminant($cArray);
        if($det == 0)
            throw new \Exception("Determinant of {$this} is 0, No Inverse found", Matrix::E_NO_INVERSE);

        
        $scalar = 1/$det;
        $adj = $this->adjugate($cArray, false);
        $iMatrix = $adj->mpScalar($scalar);
        return $iMatrix;
    }
    
    /**
     * Multiply Matrices
     * 
     * This function will multiply the current matrix with the matrix provided.
     * If current Matrix is denoted by 'A' and the inputted is denoted by 'B',
     * When written, this will return AB.
     *
     * @link http://en.wikipedia.org/wiki/Matrix_multiplication
     * @param Matrix $bMatrix The matrix to multiply with the current
     * @return Matrix The result of multiplication.
     * @throws \Exception $msg explains why operation failed
     */
    public function mpMatrix(Matrix $bMatrix)
    {
        if(!$this->_verify() || !$bMatrix->_verify()) {
            // @codeCoverageIgnoreStart
            // Should never get this far
            $eM1 = $this->toString();
            $eM2 = $bMatrix->toString();
            throw new \Exception("Either '{$eM1}' and/or '{$eM2}' is not a valid Matrix", Matrix::E_INVALID_MATRIX);
            // @codeCoverageIgnoreEnd
        }
        $aArray = $this->matrix;
        $bArray = $bMatrix->getArray();
        
        //The number of columns in A must match the number of rows in B
        if(count($aArray[0]) != count($bArray)) {
            $mA = $this->toString();
            $mB = $bMatrix->toString();
            throw new \Exception("Columns in '{$mA}' don't match Rows of '{$mB}'", Matrix::E_NOT_EQUAL);
        }
        
        $rArray = array();
        
        //Loop through rows of Matrix A
        for($i=0;$i<count($aArray);$i++) {
            //Loop through the columns of Matrix B
            for($j=0;$j<count($bArray[0]);$j++) {
                $value = 0;
                //loop through the rows of Matrix B
                for($k=0;$k<count($bArray);$k++) {
                    $value += $aArray[$i][$k] * $bArray[$k][$j];
                }
                $rArray[$i][$j] = $value;
            }
        }
        $rMatrix = new Matrix($this->toString($rArray));
        return $rMatrix;
    }
}
?>
