<?php
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jesus M. Castagnetto <jmcastagnetto@php.net>                |
// +----------------------------------------------------------------------+
//
// Matrix definition and manipulation package
//
// $Id$
//
require_once 'Math/Vector.php';

/**
 * Defines a matrix object.
 *
 * A matrix is implemented as an array of arrays such that:
 *
 * <pre>
 * [0][0] [0][1] [0][2] ... [0][M]
 * [1][0] [1][1] [1][2] ... [1][M]
 * ...
 * [N][0] [n][1] [n][2] ... [n][M]
 * </pre>
 *
 * i.e. N rows, M colums
 *
 * Originally this class was part of NumPHP (Numeric PHP package)
 *
 * @author      Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @access      public
 * @version     1.0
 * @package     Math_Matrix
 */
class Math_Matrix {

    // Properties

    /**#@+
     * @access private
     */

    /**
     * Contains the array of arrays defining the matrix
     *
     * @var     array
     * @see     getData()
     */
    var $_data = null;

    /**
     * The number of rows in the matrix
     *
     * @var     integer
     * @see     getSize()
     */
    var $_num_rows = null;

    /**
     * The number of columns in the matrix
     *
     * @var     integer
     * @see     getSize()
     */
    var $_num_cols = null;

    /**
     * A flag indicating if the matrix is square
     * i.e. if $this->_num_cols == $this->_num_rows
     *
     * @var     boolean
     * @see     isSquare()
     */
    var $_square = false;

    /**#@+
     * @access private
     * @var    float
     */
    /**
     * The smallest value of all matrix cells
     *
     * @see     getMin()
     * @see     getMinMax()
     */
    var $_min = null;

    /**
     * The biggest value of all matrix cells
     *
     * @see     getMax()
     * @see     getMinMax()
     */
    var $_max = null;

    /**
     * The Euclidean norm for the matrix: sqrt(sum(e[i][j]^2))
     *
     * @see norm()
     */
    var $_norm = null;

    /**
     * The matrix determinant
     *
     * @see determinant()
     */
    var $_det = null;

    /**
     * Cutoff error used to test for singular or ill-conditioned matrices
     *
     * @see determinant();
     * @see invert()
     */
    var $_epsilon = 1E-18;



    /**#@+
     * @access  public
     */

    /**
     * Constructor for the matrix object
     *
     * @param   array|Math_Matrix   $data a numeric array of arrays of a Math_Matrix object
     * @return  object  Math_Matrix
     * @see     $_data
     * @see     setData()
     */
    function Math_Matrix($data = null) {
        if (!is_null($data)) {
            $this->setData($data);
        }
    }

    /**
     * Validates the data and initializes the internal variables (except for the determinant).
     *
     * The validation is performed by by checking that
     * each row (first dimension in the array of arrays)
     * contains the same number of colums (e.g. arrays of the
     * same size)
     *
     * @param   array   $data array of arrays of numbers or a valid Math_Matrix object
     * @return  boolean
     * @throws InvalidArgumentException
     */
    function setData($data) {
        if (Math_Matrix::isMatrix($data)) {
            if (!$data->isEmpty()) {
                $this->_data = $data->getData();
            } else {
                return $errObj;
            }
        } elseif (is_array($data) || is_array($data[0])) {
            // check that we got a numeric bidimensional array
            // and that all rows are of the same size
            $nc = 0;
            if (!empty($data[0])) {
                $nc = count($data[0]);
            }

            $nr = count($data);
            $eucnorm = 0;
            $tmp = array();
            for ($i=0; $i < $nr; $i++) {
                if (count($data[$i]) != $nc) {
                    throw new InvalidArgumentException('Invalid data, cannot create/modify matrix.'.
                        ' Expecting an array of arrays or an initialized Math_Matrix object');
                }
                for ($j=0; $j < $nc; $j++) {
                    if (!is_numeric($data[$i][$j])) {
                        throw new InvalidArgumentException('Invalid data, cannot create/modify matrix.'.
                            ' Expecting an array of arrays or an initialized Math_Matrix object');
                    }
                    $data[$i][$j] = (float) $data[$i][$j];
                    $tmp[] = $data[$i][$j];
                    $eucnorm += $data[$i][$j] * $data[$i][$j];
                }
            }
            $this->_num_rows = $nr;
            $this->_num_cols = $nc;
            $this->_square = ($nr == $nc);
            $this->_min = !empty($tmp)? min($tmp) : null;
            $this->_max = !empty($tmp)? max($tmp) : null;
            $this->_norm = sqrt($eucnorm);
            $this->_data = $data;
            $this->_det = null; // lazy initialization ;-)
            return true;
        } else {
            throw new InvalidArgumentException('Invalid data, cannot create/modify matrix.'.
                ' Expecting an array of arrays or an initialized Math_Matrix object');
        }
    }

    /**
     * Returns the array of arrays.
     *
     * @return array
     * @throws Math_Matrix_Exception
     */
    function getData () {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return $this->_data;
        }
    }

    /**
     * Sets the threshold to consider a numeric value as zero:
     * if number <= epsilon then number = 0
     *
     * @acess public
     * @param number $epsilon the upper bound value
     * @return boolean
     * @throws InvalidArgumentException
     */
    function setZeroThreshold($epsilon) {
        if (!is_numeric($epsilon)) {
            throw new InvalidArgumentException('Expection a number for threshold, using the old value: '.$this->_epsilon);
        } else {
            $this->_epsilon = $epsilon;
            return true;
        }
    }

    /**
     * Returns the value of the upper bound used to minimize round off errors
     *
     * @return float
     */
    function getZeroThreshold() {
        return $this->_epsilon;
    }

    /**
     * Checks if the matrix has been initialized.
     *
     * @return boolean TRUE on success, FALSE otherwise
     */
    function isEmpty() {
        return ( empty($this->_data) || is_null($this->_data) );
    }


    /**
     * Returns an array with the number of rows and columns in the matrix
     *
     * @return  array
     * @throws Math_Matrix_Exception
     */
    function getSize() {
        if ($this->isEmpty())
            throw new Math_Matrix_Exception('Matrix has not been populated');
        else
            return array($this->_num_rows, $this->_num_cols);
    }

    /**
     * Checks if it is a square matrix (i.e. num rows == num cols)
     *
     * @return boolean TRUE on success, FALSE otherwise
     */
    function isSquare () {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return $this->_square;
        }
    }

    /**
     * Returns the Euclidean norm of the matrix.
     *
     * Euclidean norm = sqrt( sum( e[i][j]^2 ) )
     *
     * @return float
     * @throws Math_Matrix_Exception
     */
    function norm() {
        if (!is_null($this->_norm)) {
            return $this->_norm;
        } else {
            throw new Math_Matrix_Exception('Uninitialized Math_Matrix object');
        }
    }

    /**
     * Returns a new Math_Matrix object with the same data as the current one
     *
     * @return object Math_Matrix
     * @throws Math_Matrix_Exception
     */
    function cloneMatrix() {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return new Math_Matrix($this->_data);
        }
    }


    /**
     * Sets the value of the element at (row,col)
     *
     * @param integer $row
     * @param integer $col
     * @param numeric $value
     * @return boolean
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function setElement($row, $col, $value) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if ($row >= $this->_num_rows && $col >= $this->_num_cols) {
            throw new InvalidArgumentException('Incorrect row and column values');
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Incorrect value, expecting a number');
        }
        $this->_data[$row][$col] = $value;
        return true;
    }

    /**
     * Returns the value of the element at (row,col)
     *
     * @param integer $row
     * @param integer $col
     * @return number
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function getElement($row, $col) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if ($row >= $this->_num_rows && $col >= $this->_num_cols) {
            throw new InvalidArgumentException('Incorrect row and column values');
        }
        return $this->_data[$row][$col];
    }

    /**
     * Returns the row with the given index
     *
     * This method checks that matrix has been initialized and that the
     * row requested is not outside the range of rows.
     *
     * @param integer $row
     * @param optional boolean $asVector whether to return a Math_Vector or a simple array. Default = false.
     * @return array|Math_Vector

     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function getRow ($row, $asVector = false) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if (is_integer($row) && $row >= $this->_num_rows) {
            throw new InvalidArgumentException('Incorrect row value');
        }
        if ($asVector) {
            $classes = get_declared_classes();
            if (!in_array("math_vector", $classes) || !in_array("math_vectorop", $classes)) {
                throw new Math_Matrix_Exception("Classes Math_Vector and Math_VectorOp undefined".
                                    " add \"require_once 'Math/Vector/Vector.php'\" to your script");
            }
            return new Math_Vector($this->_data[$row]);
        } else {
            return $this->_data[$row];
        }
    }

    /**
     * Sets the row with the given index to the array
     *
     * This method checks that the row is less than the size of the matrix
     * rows, and that the array size equals the number of columns in the
     * matrix.
     *
     * @param integer $row index of the row
     * @param array $arr array of numbers
     * @return boolean
     *
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function setRow ($row, $arr) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if ($row >= $this->_num_rows) {
            throw new InvalidArgumentException('Row index out of bounds');
        }
        if (count($arr) != $this->_num_cols) {
            throw new InvalidArgumentException('Incorrect size for matrix row: expecting '.$this->_num_cols
                    .' columns, got '.count($arr).' columns');
        }
        for ($i=0; $i < $this->_num_cols; $i++) {
            if (!is_numeric($arr[$i])) {
                throw new InvalidArgumentException('Incorrect values, expecting numbers');
            }
        }
        $this->_data[$row] = $arr;
        return true;
    }

    /**
     * Returns the column with the given index
     *
     * This method checks that matrix has been initialized and that the
     * column requested is not outside the range of column.
     *
     * @param integer $col
     * @param optional boolean $asVector whether to return a Math_Vector or a simple array. Default = false.
     * @return array|Math_Vector
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function getCol ($col, $asVector=false) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if (is_integer($col) && $col >= $this->_num_cols) {
            throw new InvalidArgumentException('Incorrect column value');
        }
        for ($i=0; $i < $this->_num_rows; $i++) {
            $ret[$i] = $this->getElement($i,$col);
        }
        if ($asVector) {
            $classes = get_declared_classes();
            if (!in_array("math_vector", $classes) || !in_array("math_vectorop", $classes)) {
                throw new Math_Matrix_Exception("Classes Math_Vector and Math_VectorOp undefined".
                                    " add \"require_once 'Math/Vector/Vector.php'\" to your script");
            }
            return new Math_Vector($ret);
        } else {
            return $ret;
        }
    }

    /**
     * Sets the column with the given index to the array
     *
     * This method checks that the column is less than the size of the matrix
     * columns, and that the array size equals the number of rows in the
     * matrix.
     *
     * @param integer $col index of the column
     * @param array $arr array of numbers
     * @return boolean
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function setCol ($col, $arr) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if ($col >= $this->_num_cols) {
            throw new InvalidArgumentException('Incorrect column value');
        }
        if (count($arr) != $this->_num_cols) {
            throw new InvalidArgumentException('Incorrect size for matrix column');
        }
        for ($i=0; $i < $this->_num_rows; $i++) {
            if (!is_numeric($arr[$i])) {
                throw new InvalidArgumentException('Incorrect values, expecting numbers');
            } else {
                $this->setElement($i, $col, $arr[$i]);
            }

        }
        return true;
    }

    /**
     * Swaps the rows with the given indices
     *
     * @param integer $i
     * @param integer $j
     * @return boolean
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function swapRows($i, $j) {
        $r1 = $this->getRow($i);
        $r2 = $this->getRow($j);
        $e = $this->setRow($j, $r1);
        $e = $this->setRow($i, $r2);

        return true;
    }

    /**
     * Swaps the columns with the given indices
     *
     * @param integer $i
     * @param integer $j
     * @return boolean
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function swapCols($i, $j) {
        $r1 = $this->getCol($i);
        $r2 = $this->getCol($j);
        $e = $this->setCol($j, $r1);
        $e = $this->setCol($i, $r2);

        return true;
    }

    /**
     * Swaps a given row with a given column. Only valid for square matrices.
     *
     * @param integer $row index of row
     * @param integer $col index of column
     * @return boolean
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function swapRowCol ($row, $col) {
        if (!$this->isSquare() || !is_int($row) || !is_int($col)) {
            throw new InvalidArgumentException("Parameters must be row and a column indices");
        }
        $c = $this->getCol($col);
        $r = $this->getRow($row);
        $e = $this->setCol($col, $r);
        $e = $this->setRow($row, $c);
        return true;
    }

    /**
     * Returns the minimum value of the elements in the matrix
     *
     * @return number
     * @throws Math_Matrix_Exception
     */
    function getMin () {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return $this->_min;
        }
    }

    /**
     * Returns the maximum value of the elements in the matrix
     *
     * @return number
     * @throws Math_Matrix_Exception
     */
    function getMax () {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return $this->_max;
        }
    }

    /**
     * Gets the position of the first element with the given value
     *
     * @param numeric $val
     * @return array an array of two numbers on success, FALSE if value is not found
     * @throws Math_Matrix_Exception
     */
    function getValueIndex ($val) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        for ($i=0; $i < $this->_num_rows; $i++) {
            for ($j=0; $j < $this->_num_cols; $j++) {
                if ($this->_data[$i][$j] == $val) {
                    return array($i, $j);
                }
            }
        }
        return false;
    }

    /**
     * Gets the position of the element with the minimum value
     *
     * @return array an array of two numbers on success, FALSE if value is not found
     * @see getValueIndex()
     * @throws Math_Matrix_Exception
     */
    function getMinIndex () {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return $this->getValueIndex($this->_min);
        }
    }

    /**
     * Gets the position of the element with the maximum value
     *
     * @return array an array of two numbers on success, FALSE if value is not found
     * @see getValueIndex()
     * @throws Math_Matrix_Exception
     */
    function getMaxIndex () {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        } else {
            return $this->getValueIndex($this->_max);
        }
    }

    /**
     * Transpose the matrix rows and columns
     *
     * @return boolean TRUE on success
     * @throws Math_Matrix_Exception
     */
    function transpose () {
        /* John Pye noted that this operation is defined for
         * any matrix
        if (!$this->isSquare()) {
            throw new Math_Matrix_Exception("Transpose is undefined for non-sqaure matrices");
        }
        */
        list($nr, $nc) = $this->getSize();
        $data = array();
        for ($i=0; $i < $nc; $i++) {
            $col = $this->getCol($i);
            $data[] = $col;
        }
        return $this->setData($data);
    }

    /**
     * Returns the trace of the matrix. Trace = sum(e[i][j]), for all i == j
     *
     * @return number a number on success
     * @throws Math_Matrix_Exception
     */
    function trace() {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if (!$this->isSquare()) {
            throw new Math_Matrix_Exception('Trace undefined for non-square matrices');
        }
        $trace = 0;
        for ($i=0; $i < $this->_num_rows; $i++) {
            $trace += $this->getElement($i, $i);
        }
        return $trace;
    }

    /**
     * Calculates the matrix determinant using Gaussian elimination with partial pivoting.
     *
     * At each step of the pivoting proccess, it checks that the normalized
     * determinant calculated so far is less than 10^-18, trying to detect
     * singular or ill-conditioned matrices
     *
     * @return number a number on success
     * @throws Math_Matrix_Exception
     */
    function determinant() {
        if (!is_null($this->_det) && is_numeric($this->_det)) {
            return $this->_det;
        }
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if (!$this->isSquare()) {
            throw new Math_Matrix_Exception('Determinant undefined for non-square matrices');
        }
        $norm = $this->norm();

        $det = 1.0;
        $sign = 1;
        // work on a copy
        $m = $this->cloneMatrix();
        list($nr, $nc) = $m->getSize();
        for ($r=0; $r<$nr; $r++) {
            // find the maximum element in the column under the current diagonal element
            $ridx = $m->_maxElementIndex($r);

            if ($ridx != $r) {
                $sign = -$sign;
                $e = $m->swapRows($r, $ridx);

            }
            // pivoting element
            $pelement = $m->getElement($r, $r);

            $det *= $pelement;
            // Is this an singular or ill-conditioned matrix?
            // i.e. is the normalized determinant << 1 and -> 0?
            if ((abs($det)/$norm) < $this->_epsilon) {
                throw new Math_Matrix_Exception('Probable singular or ill-conditioned matrix, normalized determinant = '
                        .(abs($det)/$norm));
            }
            if ($pelement == 0) {
                throw new Math_Matrix_Exception('Cannot continue, pivoting element is zero');
            }
            // zero all elements in column below the pivoting element
            for ($i = $r + 1; $i < $nr; $i++) {
                $factor = $m->getElement($i, $r) / $pelement;
                for ($j=$r; $j < $nc; $j++) {
                    $val = $m->getElement($i, $j) - $factor*$m->getElement($r, $j);
                    $e = $m->setElement($i, $j, $val);

                }
            }
            // for debugging
            //echo "COLUMN: $r\n";
            //echo $m->toString()."\n";
        }
        unset($m);
        if ($sign < 0) {
            $det = -$det;
        }
        // save the value
        $this->_det = $det;
        return $det;
    }

    /**
     * Returns the normalized determinant = abs(determinant)/(euclidean norm)
     *
     * @return number a positive number on success
     * @throws Math_Matrix_Exception
     */
    function normalizedDeterminant() {
        $det = $this->determinant();
        $norm = $this->norm();
        if ($norm == 0) {
            throw new Math_Matrix_Exception('Undefined normalized determinant, euclidean norm is zero');
        }
        return abs($det / $norm);
    }

    /**
     * Inverts a matrix using Gauss-Jordan elimination with partial pivoting
     *
     * @return number the value of the matrix determinant on success
     * @see scaleRow()
     * @throws Math_Matrix_Exception
     */
    function invert() {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if (!$this->isSquare()) {
            throw new Math_Matrix_Exception('Determinant undefined for non-square matrices');
        }
        $norm = $this->norm();
        $sign = 1;
        $det = 1.0;
        // work on a copy to be safe
        $m = $this->cloneMatrix();

        list($nr, $nc) = $m->getSize();
        // Unit matrix to use as target
        $q = Math_Matrix::makeUnit($nr);

        for ($i=0; $i<$nr; $i++) {
            $ridx = $m->_maxElementIndex($i);
            if ($i != $ridx) {
                $sign = -$sign;
                $e = $m->swapRows($i, $ridx);

                $e = $q->swapRows($i, $ridx);

            }
            $pelement = $m->getElement($i, $i);

            if ($pelement == 0) {
                throw new Math_Matrix_Exception('Cannot continue inversion, pivoting element is zero');
            }
            $det *= $pelement;
            if ((abs($det)/$norm) < $this->_epsilon) {
                throw new Math_Matrix_Exception('Probable singular or ill-conditioned matrix, normalized determinant = '
                        .(abs($det)/$norm));
            }
            $e = $m->scaleRow($i, 1/$pelement);

            $e = $q->scaleRow($i, 1/$pelement);
            // zero all column elements execpt for the current one
            $tpelement = $m->getElement($i, $i);
            for ($j=0; $j<$nr; $j++) {
                if ($j == $i) {
                    continue;
                }
                $factor = $m->getElement($j, $i) / $tpelement;
                for ($k=0; $k<$nc; $k++) {
                    $vm = $m->getElement($j, $k) - $factor * $m->getElement($i, $k);
                    $vq = $q->getElement($j, $k) - $factor * $q->getElement($i, $k);
                    $m->setElement($j, $k, $vm);
                    $q->setElement($j, $k, $vq);
                }
            }
            // for debugging
            /*
            echo "COLUMN: $i\n";
            echo $m->toString()."\n";
            echo $q->toString()."\n";
            */
        }
        $data = $q->getData();
        /*
        // for debugging
        echo $m->toString()."\n";
        echo $q->toString()."\n";
        */
        unset($m);
        unset($q);
        $e = $this->setData($data);

        if ($sign < 0) {
            $det = -$det;
        }
        $this->_det = $det;
        return $det;
    }

    /**
     * Returns a submatrix from the position (row, col), with nrows and ncols
     *
     * @return object Math_Matrix Math_Matrix on success
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function &getSubMatrix ($row, $col, $nrows, $ncols) {
        if (!is_numeric($row) || !is_numeric($col)
            || !is_numeric($nrows) || !is_numeric($ncols)) {
            throw new InvalidArgumentException('Parameters must be a initial row and column, and number of rows and columns in submatrix');
        }
        list($nr, $nc) = $this->getSize();
        if ($row + $nrows > $nr) {
            throw new Math_Matrix_Exception('Rows in submatrix more than in original matrix');
        }
        if ($col + $ncols > $nc) {
            throw new Math_Matrix_Exception('Columns in submatrix more than in original matrix');
        }
        $data = array();
        for ($i=0; $i < $nrows; $i++) {
            for ($j=0; $j < $ncols; $j++) {
                $data[$i][$j] = $this->getElement($i + $row, $j + $col);
            }
        }
        $obj = new Math_Matrix($data);
        return $obj;
    }


    /**
     * Returns the diagonal of a square matrix as a Math_Vector
     *
     * @return object Math_Vector Math_Vector on success
     * @throws Math_Matrix_Exception
     */
    function &getDiagonal() {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        if (!$this->isSquare()) {
            throw new Math_Matrix_Exception('Cannot get diagonal vector of a non-square matrix');
        }
        list($n,) = $this->getSize();
        $vals = array();
        for ($i=0; $i<$n; $i++) {
            $vals[$i] = $this->getElement($i, $i);
        }
        return new Math_Vector($vals);
    }

    /**
     * Returns a simple string representation of the matrix
     *
     * @param optional string $format a sprintf() format used to print the matrix elements (default = '%6.2f')
     * @return string a string on success
     * @throws Math_Matrix_Exception
     */
    function toString ($format='%6.2f') {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        $out = "";
        for ($i=0; $i < $this->_num_rows; $i++) {
            for ($j=0; $j < $this->_num_cols; $j++) {
                // remove the -0.0 output
                $entry =  $this->_data[$i][$j];
                if (sprintf('%2.1f',$entry) == '-0.0') {
                    $entry = 0;
                }
                $out .= sprintf($format, $entry);
            }
            $out .= "\n";
        }
        return $out;
    }

    function __toString() {
        return $this->toString();
    }

    /**
     * Returns an HTML table representation of the matrix elements
     *
     * @return a string on success
     */
    function toHTML() {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Matrix has not been populated');
        }
        $out = "<table border>\n\t<caption align=\"top\"><b>Matrix</b>";
        $out .= "</caption>\n\t<tr align=\"center\">\n\t\t<th>";
        $out .= $this->_num_rows."x".$this->_num_cols."</th>";
        for ($i=0; $i < $this->_num_cols; $i++) {
            $out .= "<th>".$i."</th>";
        }
        $out .= "\n\t</tr>\n";
        for ($i=0; $i < $this->_num_rows; $i++) {
            $out .= "\t<tr align=\"center\">\n\t\t<th>".$i."</th>";
            for ($j=0; $j < $this->_num_cols; $j++) {
                $out .= "<td bgcolor=\"#ffffdd\">".$this->_data[$i][$j]."</td>";
            }
            $out .= "\n\t</tr>";
        }
        return $out."\n</table>\n";
    }

    // private methods

    /**
     * Returns the index of the row with the maximum value under column of the element e[i][i]
     *
     * @access private
     * @return an integer
     */
    function _maxElementIndex($r) {
        $max = 0;
        $idx = -1;
        list($nr, $nc) = $this->getSize();
        $arr = array();
        for ($i=$r; $i<$nr; $i++) {
            $val = abs($this->_data[$i][$r]);
            if ($val > $max) {
                $max = $val;
                $idx = $i;
            }
        }
        if ($idx == -1) {
            $idx = $r;
        }
        return $idx;
    }


    // Binary operations

    /**#@+
     * @access public
     */

    /**
     * Adds a matrix to this one
     *
     * @param object Math_Matrix $m1
     * @return boolean TRUE on success
     * @see getSize()
     * @see getElement()
     * @see setData()
     */
    function add ($m1) {
        if (!Math_Matrix::isMatrix($m1)) {
            throw new InvalidArgumentException("Parameter must be a Math_Matrix object");
        }
        if ($this->getSize() != $m1->getSize()) {
            throw new InvalidArgumentException("Matrices must have the same dimensions");
        }
        list($nr, $nc) = $m1->getSize();
        $data = array();
        for ($i=0; $i < $nr; $i++) {
            for ($j=0; $j < $nc; $j++) {
                $el1 = $m1->getElement($i,$j);

                $el = $this->getElement($i,$j);

                $data[$i][$j] = $el + $el1;
            }
        }
        if (!empty($data)) {
            return $this->setData($data);
        } else {
           throw new Math_Matrix_Exception('Undefined error');
        }
    }

    /**
     * Substracts a matrix from this one
     *
     * @param object Math_Matrix $m1
     * @return boolean TRUE on success otherwise
     * @see getSize()
     * @see getElement()
     * @see setData()
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function sub (&$m1) {
        if (!Math_Matrix::isMatrix($m1)) {
            throw new InvalidArgumentException("Parameter must be a Math_Matrix object");
        }
        if ($this->getSize() != $m1->getSize()) {
            throw new InvalidArgumentException("Matrices must have the same dimensions");
        }
        list($nr, $nc) = $m1->getSize();
        $data = array();
        for ($i=0; $i < $nr; $i++) {
            for ($j=0; $j < $nc; $j++) {
                $el1 = $m1->getElement($i,$j);

                $el = $this->getElement($i,$j);

                $data[$i][$j] = $el - $el1;
            }
        }
        if (!empty($data)) {
            return $this->setData($data);
        } else {
            throw new Math_Matrix_Exception('Undefined error');
        }
    }

    /**
     * Scales the matrix by a given factor
     *
     * @param numeric $scale the scaling factor
     * @return boolean TRUE on success
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     * @see getSize()
     * @see getElement()
     * @see setData()
     */
    function scale ($scale) {
        if (!is_numeric($scale)) {
            throw new InvalidArgumentException("Parameter must be a number");
        }
        list($nr, $nc) = $this->getSize();
        $data = array();
        for ($i=0; $i < $nr; $i++) {
            for ($j=0; $j < $nc; $j++) {
                $data[$i][$j] = $scale * $this->getElement($i,$j);
            }
        }
        if (!empty($data)) {
            return $this->setData($data);
        } else {
            throw new Math_Matrix_Exception('Undefined error');
        }
    }

    /**
     * Multiplies (scales) a row by the given factor
     *
     * @param integer $row the row index
     * @param numeric $factor the scaling factor
     * @return boolean TRUE on success
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     * @see invert()
     */
    function scaleRow($row, $factor) {
        if ($this->isEmpty()) {
            throw new Math_Matrix_Exception('Uninitialized Math_Matrix object');
        }
        if (!is_integer($row) || !is_numeric($factor)) {
            throw new InvalidArgumentException('Row index must be an integer, and factor a valid number');
        }
        if ($row >= $this->_num_rows) {
            throw new InvalidArgumentException('Row index out of bounds');
        }
        $r = $this->getRow($row);

        $nr = count($r);
        for ($i=0; $i<$nr; $i++) {
            $r[$i] *= $factor;
        }
        return $this->setRow($row, $r);
    }

    /**
     * Multiplies this matrix (A) by another one (B), and stores
     * the result back in A
     *
     * @param object Math_Matrix $m1
     * @return boolean TRUE on success
     * @see getSize()
     * @see getRow()
     * @see getCol()
     * @see setData()
     * @see setZeroThreshold()
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function multiply(&$B) {
        if (!Math_Matrix::isMatrix($B)) {
            throw new InvalidArgumentException('Wrong parameter, expected a Math_Matrix object');
        }
        list($nrA, $ncA) = $this->getSize();
        list($nrB, $ncB) = $B->getSize();
        if ($ncA != $nrB) {
            throw new InvalidArgumentException('Incompatible sizes columns in matrix must be the same as rows in parameter matrix');
        }
        $data = array();
        for ($i=0; $i < $nrA; $i++) {
            $data[$i] = array();
            for ($j=0; $j < $ncB; $j++) {
                $rctot = 0;
                for ($k=0; $k < $ncA; $k++) {
                    $rctot += $this->getElement($i,$k) * $B->getElement($k, $j);
                }
                // take care of some round-off errors
                if (abs($rctot) <= $this->_epsilon) {
                    $rctot = 0.0;
                }
                $data[$i][$j] = $rctot;
            }
        }
        if (!empty($data)) {
            return $this->setData($data);
        } else {
            throw new Math_Matrix_Exception('Undefined error');
        }
    }

    /**
     * Multiplies a vector by this matrix
     *
     * @param object Math_Vector $v1
     * @return object Math_Vector Math_Vector on success
     * @see getSize()
     * @see getRow()
     * @see Math_Vector::get()
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    function &vectorMultiply(&$v1) {
        $validator = new Math_VectorOp();
        if (!$validator->isVector($v1)) {
            throw new InvalidArgumentException("Wrong parameter, a Math_Vector object");
        }
        list($nr, $nc) = $this->getSize();
        $nv = $v1->size();
        if ($nc != $nv) {
            throw new InvalidArgumentException("Incompatible number of columns in matrix ($nc) must ".
                        "be the same as the number of elements ($nv) in the vector");
        }
        $data = array();
        for ($i=0; $i < $nr; $i++) {
            $data[$i] = 0;
            for ($j=0; $j < $nv; $j++) {
                $data[$i] += $this->getElement($i,$j) * $v1->get($j);
            }
        }
        $obj = new Math_Vector($data);
        return $obj;
    }

    // Static operations

    /**@+
     * @static
     * @access public
     */

    /**
     * Create a matrix from a file, using data stored in the given format
     */
    public static function readFromFile ($filename, $format='serialized') {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new Math_Matrix_Exception('File cannot be opened for reading');
        }
        if (filesize($filename) == 0) {
            throw new Math_Matrix_Exception('File is empty');
        }
        if ($format == 'serialized') {
            if (function_exists("file_get_contents")) {
                $objser = file_get_contents($filename);
            } else {
                $objser = implode("",file($filename));
            }
            $obj = unserialize($objser);
            if (Math_Matrix::isMatrix($obj)) {
                return $obj;
            } else {
                throw new Math_Matrix_Exception('File did not contain a Math_Matrix object');
            }
        } else { // assume CSV data
            $data = array();
            $lines = file($filename);
            foreach ($lines as $line) {
                if (preg_match('/^#/', $line)) {
                    continue;
                } else {
                    $data[] = explode(',',trim($line));
                }
            }
            $m = new Math_Matrix();
            $e = $m->setData($data);

            return $m;
        }
    }

    /**
     * Writes matrix object to a file using the given format
     *
     * @param object Math_Matrix $matrix the matrix object to store
     * @param string $filename name of file to contain the matrix data
     * @param optional string $format one of 'serialized' (default) or 'csv'
     * @return boolean TRUE on success
     * @throws InvalidArgumentException
     * @throws Math_Matrix_Exception
     */
    public static function writeToFile($matrix, $filename, $format='serialized') {
        if (!Math_Matrix::isMatrix($matrix)) {
            throw new InvalidArgumentException("Parameter must be a Math_Matrix object");
        }
        if ($matrix->isEmpty()) {
            throw new Math_Matrix_Exception("Math_Matrix object is empty");
        }
        if ($format == 'serialized') {
            $data = serialize($matrix);
        } else {
            $data = '';
            list($nr, $nc) = $matrix->getSize();
            for ($i=0; $i<$nr; $i++) {
                $row = $matrix->getRow($i);

                $data .= implode(',', $row)."\n";
            }
        }
        $fp = fopen($filename, "w");
        if (!$fp) {
            throw new Math_Matrix_Exception("Cannot write matrix to file $filename");
        }
        fwrite($fp, $data);
        fclose($fp);
        return true;
    }

    /**
     * Checks if the object is a Math_Matrix instance
     *
     * @param object Math_Matrix $matrix
     * @return boolean TRUE on success, FALSE otherwise
     */
    public static function isMatrix (&$matrix) {
        if (function_exists("is_a")) {
            return is_object($matrix) && is_a($matrix, "Math_Matrix");
        } else {
            return is_object($matrix) && (strtolower(get_class($matrix)) == "math_matrix");
        }
    }

    /**
     * Returns a Math_Matrix object of size (nrows, ncols) filled with a value
     *
     * @param integer $nrows number of rows in the generated matrix
     * @param integer $ncols number of columns in the generated matrix
     * @param numeric $value the fill value
     * @return object Math_Matrix Math_Matrix instance on success otherwise
     * @throws InvalidArgumentException
     */
    public static function makeMatrix ($nrows, $ncols, $value) {
        if (!is_int($nrows) && is_int($ncols) && !is_numeric($value)) {
            throw new InvalidArgumentException('Number of rows, columns, and a numeric fill value expected');
        }
        for ($i=0; $i<$nrows; $i++) {
            $m[$i] = explode(":",substr(str_repeat($value.":",$ncols),0,-1));
        }

        $obj = new Math_Matrix($m);
        return $obj;

    }

    /**
     * Returns the Math_Matrix object of size (nrows, ncols), filled with the value 1 (one)
     *
     * @param integer $nrows number of rows in the generated matrix
     * @param integer $ncols number of columns in the generated matrix
     * @return object Math_Matrix Math_Matrix instance on success
     * @see Math_Matrix::makeMatrix()
     */
    public static function makeOne ($nrows, $ncols) {
        return Math_Matrix::makeMatrix($nrows, $ncols, 1);
    }

    /**
     * Returns the Math_Matrix object of size (nrows, ncols), filled with the value 0 (zero)
     *
     * @param integer $nrows number of rows in the generated matrix
     * @param integer $ncols number of columns in the generated matrix
     * @return object Math_Matrix Math_Matrix instance on success
     * @see Math_Matrix::makeMatrix()
     */
    public static function makeZero ($nrows, $ncols) {
        return Math_Matrix::makeMatrix($nrows, $ncols, 0);
    }

    /**
     * Returns a square unit Math_Matrix object of the given size
     *
     * A unit matrix is one in which the elements follow the rules:
     *  e[i][j] = 1, if i == j
     *  e[i][j] = 0, if i != j
     * Such a matrix is also called an 'identity matrix'
     *
     * @param integer $size number of rows and columns in the generated matrix
     * @return object Math_Matrix a square unit Math_Matrix instance on success
     * @see Math_Matrix::makeIdentity()
     * @throws InvalidArgumentException
     */
    public static function makeUnit ($size) {
        if (!is_integer($size)) {
            throw new InvalidArgumentException('An integer expected for the size of the Identity matrix');
        }
        for ($i=0; $i<$size; $i++) {
            for ($j=0; $j<$size; $j++) {
                if ($i == $j) {
                    $data[$i][$j] = (float) 1.0;
                } else {
                    $data[$i][$j] = (float) 0.0;
                }
            }
        }

        $obj = new Math_Matrix($data);
        return $obj;
    }

    /**
     * Returns the identity matrix of the given size. An alias of Math_Matrix::makeUnit()
     *
     * @param integer $size number of rows and columns in the generated matrix
     * @return object Math_Matrix a square unit Math_Matrix instance on success
     * @see Math_Matrix::makeUnit()
     */
    public static function makeIdentity($size) {
        return Math_Matrix::makeUnit($size);
    }

    // famous matrices

    /**
     * Returns a Hilbert matrix of the given size: H(i,j) = 1 / (i + j - 1) where {i,j = 1..n}
     *
     * @param integer $size number of rows and columns in the Hilbert matrix
     * @return object Math_Matrix a Hilber matrix on success
     * @throws InvalidArgumentException
     */
    public static function makeHilbert($size) {
        if (!is_integer($size)) {
            throw new InvalidArgumentException('An integer expected for the size of the Hilbert matrix');
        }
        $data = array();
        for ($i=1; $i <= $size; $i++) {
            for ($j=1; $j <= $size; $j++) {
                $data[$i - 1][$j - 1] = 1 / ($i + $j - 1);
            }
        }
        $obj = new Math_Matrix($data);
        return $obj;
    }

    /**
     * Returns a Hankel matrix from a array of size m (C), and (optionally) of
     * an array if size n (R). C will define the first column and R the last
     * row. If R is not defined, C will be used. Also, if the last element of C
     * is not the same to the first element of R, the last element of C is
     * used.
     *
     * H(i,j) = C(i+j-1), i+j-1 <= m
     * H(i,j) = R(i+j-m), otherwise
     * where:
     *   i = 1..m
     *   j = 1..n
     *
     * @param array $c first column of Hankel matrix
     * @param optional array $r last row of Hankel matrix
     * @return object Math_Matrix a Hankel matrix on success
     * @throws InvalidArgumentException
     */
    public static function makeHankel($c, $r=null) {
        if (!is_array($c)) {
            throw new InvalidArgumentException('Expecting an array of values for the first column of the Hankel matrix');
        }

        if (is_null($r)) {
            $r == $c;
        }

        if (!is_array($r)) {
            throw new InvalidArgumentException('Expecting an array of values for the last row of the Hankel matrix');
        }

        $nc = count($c);
        $nr = count($r);

        // make sure that the first element of r is the same as the last element of c
        $r[0] = $c[$nc - 1];

        $data = array();
        for ($i=1; $i <= $nc; $i++) {
            for ($j=1; $j <= $nr; $j++) {
                if (($i + $j - 1) <= $nc) {
                    $val = $c[($i + $j - 1) - 1];
                } else {
                    $val = $r[($i + $j - $nc) - 1];
                }
                $data[($i - 1)][($j - 1)] = $val;
            }
        }
        $obj = new Math_Matrix($data);
        return $obj;

    }


    // methods for solving linear equations

    /**
     * Solves a system of linear equations: Ax = b
     *
     * A system such as:
     * <pre>
     *     a11*x1 + a12*x2 + ... + a1n*xn = b1
     *     a21*x1 + a22*x2 + ... + a2n*xn = b2
     *     ...
     *     ak1*x1 + ak2*x2 + ... + akn*xn = bk
     * </pre>
     * can be rewritten as:
     * <pre>
     *     Ax = b
     * </pre>
     * where:
     * - A is matrix of coefficients (aij, i=1..k, j=1..n),
     * - b a vector of values (bi, i=1..k),
     * - x the vector of unkowns (xi, i=1..n)
     * Using: x = (Ainv)*b
     * where:
     * - Ainv is the inverse of A
     *
     * @param object Math_Matrix $a the matrix of coefficients
     * @param object Math_Vector $b the vector of values
     * @return object Math_Vector a Math_Vector object on succcess
     * @see vectorMultiply()
     */
    public static function solve($a, $b) {
        // check that the vector classes are defined
        if (!Math_Matrix::isMatrix($a) && !Math_VectorOp::isVector($b)) {
            throw new InvalidArgumentException('Incorrect parameters, expecting a Math_Matrix and a Math_Vector');
        }
        $e = $a->invert();

        return $a->vectorMultiply($b);
    }

    /**
     * Solves a system of linear equations: Ax = b, using an iterative error correction algorithm
     *
     * A system such as:
     * <pre>
     *     a11*x1 + a12*x2 + ... + a1n*xn = b1
     *     a21*x1 + a22*x2 + ... + a2n*xn = b2
     *     ...
     *     ak1*x1 + ak2*x2 + ... + akn*xn = bk
     * </pre>
     * can be rewritten as:
     * <pre>
     *     Ax = b
     * </pre>
     * where:
     * - A is matrix of coefficients (aij, i=1..k, j=1..n),
     * - b a vector of values (bi, i=1..k),
     * - x the vector of unkowns (xi, i=1..n)
     * Using: x = (Ainv)*b
     * where:
     * - Ainv is the inverse of A
     *
     * The error correction algorithm uses the approach that if:
     * - xp is the approximate solution
     * - bp the values obtained from pluging xp into the original equation
     * We obtain
     * <pre>
     *     A(x - xp) = (b - bp),
     *     or
     *     A*xadj = (b - bp)
     * </pr>
     * where:
     * - xadj is the adjusted value (= Ainv*(b - bp))
     * therefore, we calculate iteratively new values of x using the estimated
     * xadj and testing to check if we have decreased the error.
     *
     * @param object Math_Matrix $a the matrix of coefficients
     * @param object Math_Vector $b the vector of values
     * @return object Math_Vector a Math_Vector object on succcess
     * @see vectorMultiply()
     * @see invert()
     * @see Math_VectorOp::add()
     * @see Math_VectorOp::substract()
     * @see Math_VectorOp::length()
     */
    public static function solveEC($a, $b) {
        $ainv = $a->cloneMatrix();
        $e = $ainv->invert();

        $x = $ainv->vectorMultiply($b);

        // initial guesses
        $bprime = $a->vectorMultiply($x);

        $operation = new Math_VectorOp();
        $err = $operation->substract($b, $bprime);
        $adj = $ainv->vectorMultiply($err);

        $adjnorm = $adj->length();
        $xnew = $x;

        // compute new solutions and test for accuracy
        // iterate no more than 10 times
        for ($i=0; $i<10; $i++) {
            $xnew = $operation->add($x, $adj);
            $bprime = $a->vectorMultiply($xnew);
            $err = $operation->substract($b, $bprime);
            $newadj = $ainv->vectorMultiply($err);
            $newadjnorm = $newadj->length();
            // did we improve the accuracy?
            if ($newadjnorm < $adjnorm) {
                $adjnorm = $newadjnorm;
                $x = $xnew;
                $adj = $newadj;
            } else { // we did improve the accuracy, so break;
                break;
            }
        }
        return $x;
    }

    /**
     * Convenience static method to multiply matrices and returning the result
     * as a new matrix
     *
     * @param Math_Matrix $m1 a Math_Matrix object
     * @param Math_Matrix $m2 a Math_Matrix object
     * @return object Math_Matrix Math_Matrix instance on success
     * @see multiply()
     * @throws Math_Matrix_Exception
     * @throws InvalidArgumentException
     */
    public static function multiplyMatrices(&$m1, &$m2) {
        $mres = $m1->cloneMatrix();
        $mres->multiply($m2);
        return $mres;
    }
}

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
