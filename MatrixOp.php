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
// Matrix manipulation class
// 
// $Id$
//


/**
 * Matrix operations class
 * A static class implementing methods to operate on Matrix objects.
 *
 * Originally this class was part of NumPHP (Numeric PHP package)
 * 
 * @author      Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @access      public
 * @version     1.0
 * @package     Math_Matrix
 */
class Math_MatrixOp {/*{{{*/

    function &readFromFile ($filename) {/*{{{*/
        if (!file_exists($filename) || !is_readable($filename))
            return PEAR::raiseError('File cannot be opened for reading');
        if (filesize($filename) == 0)
            return PEAR::raiseError('File is empty');
        if (function_exists("file_get_contents"))
            $objser = file_get_contents($filename);
        else
            $objser = implode("",file($filename));
        $obj = unserialize($objser);
        if (Math_MatrixOp::isMatrix($obj))
            return $obj;
        else
            return PEAR::raiseError('File did not contain a Math_Matrix object');
    }/*}}}*/

    function writeToFile (&$matrix, $filename) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($matrix))
            return PEAR::raiseError("Parameter must be a Math_Matrix object");
        if ($matrix->isEmpty())
            return PEAR::raiseError("Math_Matrix object is empty");
        $objser = serialize($matrix);
        $fp = fopen($filename, "w");
        if (!$fp)
            return PEAR::raiseError("Cannot write to file $filename");
        fwrite($fp, $objser);
        fclose($fp);
        return true;
    }/*}}}*/

    function isMatrix (&$matrix) {/*{{{*/
        if (function_exists("is_a"))
            return is_a($matrix, "Math_Matrix");
        else
            return (get_class($matrix) == "math_matrix");
    }/*}}}*/

    function &makeMatrix ($nrows, $ncols, $value) {/*{{{*/
        for ($i=0; $i<$nrows; $i++)
            $m[$i] = explode(":",substr(str_repeat($value.":",$ncols),0,-1));
        return new Math_Matrix($m);
    }/*}}}*/

    function &makeOne ($nrows, $ncols) {/*{{{*/
        return Math_MatrixOp::makeMatrix ($nrows, $ncols, 1);
    }/*}}}*/

    function &makeZero ($nrows, $ncols) {/*{{{*/
        return Math_MatrixOp::makeMatrix ($nrows, $ncols, 0);
    }/*}}}*/

    function &makeUnit ($size) {/*{{{*/
        for ($i=0; $i<=$size; $i++)
            for ($j=0; $j<=$size; $j++)
                $data[$i][$j] = (int) ($i == $j);
        return new Math_Matrix($data);
    }/*}}}*/

    function &add (&$m1, &$m2) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !Math_MatrixOp::isMatrix($m2))
            return PEAR::raiseError("Parameters must be matrix objects");
        if ($m1->getSize() != $m2->getSize())
            return PEAR::raiseError("Matrices must have the same dimensions");
        list($nr, $nc) = $m1->getSize();
        for ($i=0; $i < $nr; $i++)
            for ($j=0; $j < $nc; $j++)
                $out[$i][$j] = $m1->getElement($i,$j) + $m2->getElement($i,$j);
        return new Math_Matrix($out);
    }/*}}}*/

    function &sub (&$m1, &$m2) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !Math_MatrixOp::isMatrix($m2))
            return PEAR::raiseError("Parameters must be matrix objects");
        if ($m1->getSize() != $m2->getSize())
            return PEAR::raiseError("Matrices must have the same dimensions");
        list($nr, $nc) = $m1->getSize();
        for ($i=0; $i < $nr; $i++)
            for ($j=0; $j < $nc; $j++)
                $out[$i][$j] = $m1->getElement($i,$j) - $m2->getElement($i,$j);
        return new Math_Matrix($out);
    }/*}}}*/

    function &scale (&$m1, $scale) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !is_numeric($scale))
            return PEAR::raiseError("Parameters must be a matrix object and a number");
        list($nr, $nc) = $m1->getSize();
        for ($i=0; $i < $nr; $i++)
            for ($j=0; $j < $nc; $j++)
                $out[$i][$j] = $scale * $m1->getElement($i,$j);
        return new Math_Matrix($out);
    }/*}}}*/

    function &getSubMatrix (&$m1, $tlrow, $tlcol, $nrows, $ncols) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !is_numeric($tlrow) || !is_numeric($tlcol)
            || !is_numeric($nrows) || !is_numeric($ncols))
            return PEAR::raiseError("Parameters must be a matrix object and 4 numbers");
        for ($i=0; $i < $nrows; $i++)
            for ($j=0; $j < $ncols; $j++)
                $data[$i][$j] = $m1->data[$i + $trow][$j + $tcol];
        return new Math_Matrix($data);
    }/*}}}*/

    function &transpose (&$m1) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !$m1->isSquare())
            return PEAR::raiseError("Parameter must be a square matrix object");
        list($nr, $nc) = $m1->getSize();
        for ($i=0; $i < $nc; $i++)
            $data[$i] = $m1->getCol($i);
        return new Math_Matrix($data);
    }/*}}}*/

    function &swapRows ($m1, $row1, $row2) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !is_int($row1) || !is_int($row2))
            return PEAR::raiseError("Parameters must be a matrix and 2 row indices");
        $r1 = $m1->getRow($row1);
        $m1->setRow($row1, $m1->getRow($row2));
        $m1->setRow($row2, $r1);
        return $m1;
    }/*}}}*/

    function &swapCols ($m1, $col1, $col2) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !is_int($col1) || !is_int($col2))
            return PEAR::raiseError("Parameters must be a square matrix and 2 col indices");
        $r1 = $m1->getCol($col1);
        $m1->setCol($col1, $m1->getCol($col2));
        $m1->setCol($col2, $r1);
        return $m1;
    }/*}}}*/

    function &swapRowCol ($m1, $row, $col) {/*{{{*/
        if (!Math_MatrixOp::isMatrix($m1) || !$m1->isSquare() || !is_int($row) || !is_int($col))
            return PEAR::raiseError("Parameters must be a matrix, and a row and a column indices");
        $c = $m1->getCol($col);
        $m1->setCol($col, $m1->getRow($row));
        $m1->setRow($row, $c);
        return $m1;
    }/*}}}*/

} // end of class Math_MatrixOp/*}}}*/

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
?>
