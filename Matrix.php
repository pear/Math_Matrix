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

require_once 'PEAR.php';

/**
 * Defines a matrix object, conceptualized as an array of arrays such that:
 *
 * <pre>
 * [0][0] [0][1] [0][2] ... [0][M]<br>
 * [1][0] [1][1] [1][2] ... [1][M]<br>
 * ...<br>
 * [N][0] [n][1] [n][2] ... [n][M]<br>
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
class Math_Matrix {/*{{{*/

	// Properties /*{{{*/
	
    /**
     * Contains the array of arrays defining the matrix
     *
	 * @access	private
     * @var     array
     * @see     getData()
     */
    var $_data = null;

    /**
     * The number of rows in the matrix
     *
	 * @access	private
     * @var     integer
     * @see     getSize()
     */
    var $_num_rows = null;

    /**
     * The number of columns in the matrix
     *
	 * @access	private
     * @var     integer
     * @see     getSize()
     */
    var $_num_cols = null;

    /**
     * The smallest value of all matrix cells
     *
	 * @access	private
     * @var     float
     * @see     getMin()
     * @see     getMinMax()
     */
    var $_min = null;

    /**
     * The biggest value of all matrix cells
     *
	 * @access	private
     * @var     float
     * @see     getMax()
     * @see     getMinMax()
     */
    var $_max = null;

    /**
     * A flag indicating if the matrix is square
     * i.e. if $this->_num_cols == $this->_num_rows
     * 
	 * @access	private
     * @var     boolean
     * @see     isSquare()
     */
    var $_square = false;

    /*}}}*/

    /**
     * Constructor for the matrix object
     * 
     * @access  public
     * @param   array   $data
	 * @return	object	Math_Matrix
     * @see     $_data
     * @see     setData()
     */
    function Math_Matrix ($data=null) {/*{{{*/
		if (!is_null($data))
			$this->setData($data);
    }/*}}}*/
    
    /**
     * Validates the data and initializes all the internal variables.
     *
     * The validation is performed by by checking that
     * each row (first dimension in the array of arrays)
     * contains the same number of colums (e.g. arrays of the
     * same size)
     *
     * @access  public
     * @param   array   $data   array of arrays to create a matrix object
	 * @return	mixed	true on success, a PEAR_Error object otherwise
     *
     * @see     $_data
     * @see     $_num_rows
     * @see     $_num_cols
     * @see     $_square
     * @see     findMinMax()
     */
    function setData($data) {/*{{{*/
		$errorObj = PEAR::raiseError('Invalid data, cannot create/modify matrix');
		if (!is_array($data) || !is_array($data[0]))
			return $errorObj;
		// check that we got a numeric bidimensional array
		// and that all rows are of the same size
		$nc = count($data[0]);
		$nr = count($data);
		for ($i=0; $i < $nr; $i++) {
			if (count($data[$i]) != $nc)
				return $errorObj;
			for ($j=0; $j < $nc; $j++) {
				if (!is_numeric($data[$i][$j]))
					return $errorObj;
				$tmp[] = $data[$i][$j];
			}
		}
		$this->_num_rows = $nr;
		$this->_num_cols = $nc;
		$this->_square = ($nr == $nc);
        $this->_min = min($tmp);
        $this->_max = max($tmp);
		$this->_data = $data;
		return true;
    }/*}}}*/

    function isEmpty() {/*{{{*/
        return ( empty($this->_data) || is_null($this->_data) );
    }/*}}}*/


	/**
	 * Returns the an array with the number of rows and columns
	 * in the matrix
	 *
	 * @access	public
	 * @return	mixed	an array of integers on success, a PEAR_Error object otherwise 
	 */
    function getSize() {
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
		else
			return array($this->_num_rows, $this->_num_cols);
    }

    function setElement($row, $col, $value) {
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        if ($row >= $this->_num_rows && $col >= $this->_num_cols)
            return PEAR::raiseError('Incorrect row and column values');
		if (!is_numeric($value))
            return PEAR::raiseError('Incorrect value, expecting a number');
        $this->_data[$row][$col] = $value;
        return true;
    }

    function getElement($row, $col) {
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        if ($row >= $this->_num_rows && $col >= $this->_num_cols)
            return PEAR::raiseError('Incorrect row and column values');
        return $this->_data[$row][$col];
    }

    function getRow ($row) {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        if ($row >= $this->_num_rows)
            return PEAR::raiseError('Incorrect row value');
        return $this->_data[$row];
    }/*}}}*/

    function getCol ($col) {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        if ($col >= $this->_num_cols)
            return PEAR::raiseError('Incorrect column value');
        for ($i=0; $i < $this->_num_rows; $i++)
            $ret[$i] = $this->getElement($i,$col);
        return $ret;
    }/*}}}*/

    function setRow ($row, $arr) {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        if ($row >= $this->_num_rows)
            return PEAR::raiseError('Incorrect row value');
		if (count($arr) != $this->_num_cols)
            return PEAR::raiseError('Incorrect size for matrix row');
		for ($i=0; $i < $this->_num_cols; $i++)
			if (!is_numeric($arr[$i]))
				return PEAR::raiseError('Incorrect values, expecting numbers');
		$this->_data[$row] = $arr;
        return true;
    }/*}}}*/

    function setCol ($col, $arr) {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        if ($col >= $this->_num_cols)
            return PEAR::raiseError('Incorrect column value');
		if (count($arr) != $this->_num_cols)
            return PEAR::raiseError('Incorrect size for matrix column');
		for ($i=0; $i < $this->_num_rows; $i++) {
			if (!is_numeric($arr[$i])) {
				return PEAR::raiseError('Incorrect values, expecting numbers');
            } else {
                $err = $this->setElement($i, $col, $arr[$i]);
                if (PEAR::isError($err))
                    return $err;
            }
            
        }
        return true;
    }/*}}}*/

    function getData () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
		else
			return $this->_data;
    }/*}}}*/
    
    function getMin () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
		else
            return $this->_min;
    }/*}}}*/
    
    function getMax () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
		else
            return $this->_max;
    }/*}}}*/

    function getMinMax () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
		else
            return array ($this->_min, $this->_max);
    }/*}}}*/
    
    function getValueIndex ($val) {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        for ($i=0; $i < $this->_num_rows; $i++)
            for ($j=0; $j < $this->_num_cols; $j++)
                if ($this->_data[$i][$j] == $val)
                    return array($i, $j);
        return false;
    }/*}}}*/

    function getMinIndex () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        return $this->getValueIndex($this->_min);
    }/*}}}*/

    function getMaxIndex () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        return $this->getValueIndex($this->_max);
    }/*}}}*/

    function getMinMaxIndex () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        else
            return array_merge($this->getMinIndex(), $this->getMaxIndex());
    }/*}}}*/

    function isSquare () {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        else
            return $this->_square;
    }/*}}}*/
    
    function toString ($format="%4s") {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        $out = "";
        for ($i=0; $i < $this->_num_rows; $i++) {
            for ($j=0; $j < $this->_num_cols; $j++)
                $out .= sprintf($format, $this->_data[$i][$j]);
            $out .= "\n";
        }
        return $out;
    }/*}}}*/

    function toHTML() {/*{{{*/
		if ($this->isEmpty())
			return PEAR::raiseError('Matrix has not been populated');
        $out = "<table border>\n\t<caption align=\"top\"><b>Matrix</b>";
        $out .= "</caption>\n\t<tr align=\"center\">\n\t\t<th>";
        $out .= $this->_num_rows."x".$this->_num_cols."</th>";
        for ($i=0; $i < $this->_num_cols; $i++)
            $out .= "<th>".$i."</th>";
        $out .= "\n\t</tr>\n";
        for ($i=0; $i < $this->_num_rows; $i++) {
            $out .= "\t<tr align=\"center\">\n\t\t<th>".$i."</th>";
            for ($j=0; $j < $this->_num_cols; $j++)
                $out .= "<td bgcolor=\"#ffffdd\">".$this->_data[$i][$j]."</td>";
            $out .= "\n\t</tr>";
        }
        return $out."\n</table>\n";
    }/*}}}*/
    
} // end of Math_Matrix class /*}}}*/

// vim: ts=4:sw=4:et:
// vim6: fdl=1:

?>
