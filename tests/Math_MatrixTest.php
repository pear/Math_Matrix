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

require_once 'PHPUnit/Framework.php';
require_once 'Math/Matrix.php';

class Math_MatrixTest extends PHPUnit_Framework_TestCase {/*{{{*/
    var $m;

    function setUp() {
        $data = array(
                    array(1.0,2.0,3.0,4.0),
                    array(5.0,6.0,7.0,8.0),
                    array(1.0,4.0,5.0,7.0),
                    array(2.0,3.0,-3.0,4.0)
                );


        $this->m = new Math_Matrix($data);
    }

    function testIsSquare() {
        $this->assertTrue($this->m->isSquare());
    }

    function testGetSize() {
        $this->assertEquals(array(4,4), $this->m->getSize());
    }

    function testIsEmpty() {
        $this->assertFalse($this->m->isEmpty());
    }

    function testCloneMatrix() {
        $q = $this->m->cloneMatrix();
        $this->assertEquals($q, $this->m);
    }

    function testNorm() {
        $this->assertEquals(18.2482875909,$this->m->norm(),'',1E-8);
    }

    function testTrace() {
        $this->assertEquals(16,$this->m->trace(),'',1E-8);
    }

    function testDeterminant() {
        $this->assertEquals(76,$this->m->determinant(),'',1E-8);
    }

    function testNormalizedDeterminant() {
        $this->assertEquals(4.16477434507,$this->m->normalizedDeterminant(),'',1E-8);
    }

    function testGetRow() {
        $row = array(5.0,6.0,7.0,8.0);
        $this->assertEquals($row, $this->m->getRow(1));
    }

    function testGetCol() {
        $col = array(2.0,6.0,4.0,3.0);
        $this->assertEquals($col, $this->m->getCol(1));
    }

    function testGetElement() {
        $this->assertEquals(2.0, $this->m->getElement(0,1));
    }

    function testGetData() {
        $data = array(
                    array(1.0,2.0,3.0,4.0),
                    array(5.0,6.0,7.0,8.0),
                    array(1.0,4.0,5.0,7.0),
                    array(2.0,3.0,-3.0,4.0)
                );
        $this->assertEquals($data, $this->m->getData());
    }

    function testGetMin() {
        $this->assertEquals(-3.0,$this->m->getMin(),'',1E-8);
    }

    function testGetMax() {
        $this->assertEquals(8.0,$this->m->getMax(),'',1E-8);
    }

    function testGetValueIndex() {
        $this->assertEquals(array(1,1),$this->m->getValueIndex(6.0));
    }

    function testGetMinIndex() {
        $this->assertEquals(array(3,2),$this->m->getMinIndex(6.0));
    }

    function testGetMaxIndex() {
        $this->assertEquals(array(1,3),$this->m->getMaxIndex(6.0));
    }

    function testTranspose() {
        $data = array(
                    array(1.0,2.0),
                    array(3.0,4.0)
                );
        $data_transposed = array(
                    array(1.0,3.0),
                    array(2.0,4.0)
                );
        $p = new Math_Matrix($data);
        $p->transpose();
        $this->assertEquals($data_transposed, $p->getData());
    }

    function testInvert() {
        $data = array(
                    array(1.0,2.0,3.0,4.0),
                    array(5.0,6.0,7.0,8.0),
                    array(1.0,4.0,5.0,7.0),
                    array(2.0,3.0,-3.0,4.0)
                );

        $unit = Math_Matrix::makeUnit(4);
        $q = new Math_Matrix($data);
        $p = $q->cloneMatrix();
        $q->invert();
        $p->multiply($q);
        $this->assertEquals($unit->toString(), $p->toString());
    }

    function testMultiply() {
        $Adata = array(
                    array(1,1,2),
                    array(2,3,4)
                );

        $Bdata = array(
                    array(-1,3),
                    array(-3,4),
                    array(-5,2)
                );

        $ABdata = array(
                    array(-14, 11),
                    array(-31, 26)
                );

        $BAdata = array(
                    array(5,8,10),
                    array(5,9,10),
                    array(-1,1,-2)
                );

        $A = new Math_Matrix($Adata);
        $A1 = $A->cloneMatrix();
        $B = new Math_Matrix($Bdata);
        $B1 = $B->cloneMatrix();

        $A1->multiply($B);
        $B1->multiply($A);

        $AB = new Math_Matrix($ABdata);
        $BA = new Math_Matrix($BAdata);

        $this->assertEquals($A1->toString(), $AB->toString());
        $this->assertEquals($B1->toString(), $BA->toString());
    }

    function testGetSubMatrix() {
        $data = array(
                    array(6.0,7.0),
                    array(4.0,5.0),
                );
        $q = $this->m->getSubMatrix(1,1,2,2);
        $this->assertEquals($data, $q->getData()); 
    }

    function testAdd() {
        $data = array(
                    array(2.0,7.0,4.0,6.0),
                    array(7.0,12.0,11.0,11.0),
                    array(4.0,11.0,10.0,4.0),
                    array(6.0,11.0,4.0,8.0)
                );
        $q = $this->m->cloneMatrix();
        $p = $q->cloneMatrix();
        $q->transpose();
        $p->add($q);
        $this->assertEquals($data, $p->getData());
    }

    function testSub() {
        $data = array(
                    array(0.0,-3.0,2.0,2.0),
                    array(3.0,0.0,3.0,5.0),
                    array(-2.0,-3.0,0.0,10.0),
                    array(-2.0,-5.0,-10.0,0.0)
                );
        $q = $this->m->cloneMatrix();
        $p = $q->cloneMatrix();
        $q->transpose();
        $p->sub($q);
        $this->assertEquals($data, $p->getData());
    }

    function testScale() {
        $data = array(
                    array(2.0,4.0,6.0,8.0),
                    array(10.0,12.0,14.0,16.0),
                    array(2.0,8.0,10.0,14.0),
                    array(4.0,6.0,-6.0,8.0)
                );
        $q = $this->m->cloneMatrix();
        $q->scale(2.0);
        $this->assertEquals($data, $q->getData());
    }

    function testScaleRow() {
        $data = array(
                    array(1.0,2.0,3.0,4.0),
                    array(10.0,12.0,14.0,16.0),
                    array(1.0,4.0,5.0,7.0),
                    array(2.0,3.0,-3.0,4.0)
                );
        $q = $this->m->cloneMatrix();
        $q->scaleRow(1,2.0);
        $this->assertEquals($data, $q->getData());
    }

    function testSwapRows() {
        $data = array(
                    array(1.0,2.0,3.0,4.0),
                    array(1.0,4.0,5.0,7.0),
                    array(5.0,6.0,7.0,8.0),
                    array(2.0,3.0,-3.0,4.0)
                );
        $q = $this->m->cloneMatrix();
        $q->swapRows(1,2);
        $this->assertEquals($data, $q->getData());
    }

    function testSwapCols() {
        $data = array(
                    array(1.0,3.0,2.0,4.0),
                    array(5.0,7.0,6.0,8.0),
                    array(1.0,5.0,4.0,7.0),
                    array(2.0,-3.0,3.0,4.0)
                );
        $q = $this->m->cloneMatrix();
        $q->swapCols(1,2);
        $this->assertEquals($data, $q->getData());
    }

    function testSwapRowCol() {
        $data = array(
                    array(1.0,5.0,1.0,2.0),
                    array(2.0,6.0,7.0,8.0),
                    array(3.0,4.0,5.0,7.0),
                    array(4.0,3.0,-3.0,4.0)
                );
        $q = $this->m->cloneMatrix();
        $q->swapRowCol(0,0);
        $this->assertEquals($data, $q->getData());
    }

    function testVectorMultiply() {
        $data = array(53.0,129.0,96.0,13.0);
        $v = new Math_Vector(array(-1,9,8,3));
        $q = $this->m->cloneMatrix();
        $r = $q->vectorMultiply($v);
        $t = $r->getTuple();
        $this->assertEquals($data, $t->data);
    }



    function testWriteToFile() {
        $result = Math_Matrix::writeToFile($this->m, dirname(__FILE__) . '/testdata.mat', 'csv');

        $this->assertTrue($result);
    }

    function testReadFromFile() {
        $p = Math_Matrix::readFromFile(dirname(__FILE__) . '/testdata.mat', 'csv');
        $this->assertEquals($this->m->getData(), $p->getData());
    }

    function testIsMatrix() {
        $this->assertTrue(Math_Matrix::isMatrix($this->m));
    }

    function testMakeMatrix() {
        $data = array (
                    array(3.0,3.0,3.0),
                    array(3.0,3.0,3.0)
                );
        $q = Math_Matrix::makeMatrix(2,3,3.0);
        $this->assertEquals($data, $q->getData());
    }

    function testMakeZero() {
        $data = array (
                    array(0.0,0.0,0.0),
                    array(0.0,0.0,0.0)
                );
        $q = Math_Matrix::makeZero(2,3);
        $this->assertEquals($data, $q->getData());
    }

    function testMakeOne() {
        $data = array (
                    array(1.0,1.0),
                    array(1.0,1.0),
                    array(1.0,1.0)
                );
        $q = Math_Matrix::makeOne(3,2);
        $this->assertEquals($data, $q->getData());
    }

    function testMakeUnit() {
        $data = array (
                    array(1.0,0.0,0.0),
                    array(0.0,1.0,0.0),
                    array(0.0,0.0,1.0)
                );
        $q = Math_Matrix::makeUnit(3);
        $this->assertEquals($data, $q->getData());
    }

    function testMakeHilbert() {
        $data = array (
                    array(1, 1/2, 1/3),
                    array(1/2, 1/3, 1/4),
                    array(1/3, 1/4, 1/5)
                );
        $res = new Math_Matrix($data);
        $hilb = Math_Matrix::makeHilbert(3);
        $this->assertEquals($res->toString(), $hilb->toString());
    }

    function testMakeHankel() {
        $data = array(
                    array(1,2,3,3),
                    array(2,3,3,5),
                    array(3,3,5,7)
                );
        $c = array(1,2,3);
        $r = array(1,3,5,7);
        
        $res = new Math_Matrix($data);
        $hankel = Math_Matrix::makeHankel($c, $r);
        $this->assertEquals($res->toString(), $hankel->toString());
    }

    function testSolve() {
        $adata = array(
            array(-4.0,3.0,-4.0,-1.0),
            array(-2.0,0.0,-5.0,3.0),
            array(-1.0,-1.0,-3.0,-4.0),
            array(-3.0,2.0,4.0,-1.0)
        );
        $bdata = array(-37.0,-20.0,-27.0,7.0);
        $res = array(2.0,-2.0,5.0,3.0);
        $resVector = new Math_Vector($res);
        $a = new Math_Matrix($adata);
        $b = new Math_Vector($bdata);
        $x = Math_Matrix::solve($a, $b);
        $this->assertEquals($resVector->toString(), $x->toString());
    }

    function testSolveEC() {
        $adata = array(
            array(-4.0,3.0,-4.0,-1.0),
            array(-2.0,0.0,-5.0,3.0),
            array(-1.0,-1.0,-3.0,-4.0),
            array(-3.0,2.0,4.0,-1.0)
        );
        $bdata = array(-37.0,-20.0,-27.0,7.0);
        $res = array(2.0,-2.0,5.0,3.0);
        $resVector = new Math_Vector($res);
        $a = new Math_Matrix($adata);
        $b = new Math_Vector($bdata);
        $x = Math_Matrix::solveEC($a, $b);
        $this->assertEquals($resVector->toString(), $x->toString());
    }

}/*}}}*/
