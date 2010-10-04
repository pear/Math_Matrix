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

class Math_Matrix_Static_MethodsTest extends PHPUnit_Framework_TestCase {
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

}

