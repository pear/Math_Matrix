<?php
/**
 * Example of using the Math_Matrix class
 * @author Jesus M. Castagnetto
 * 
 * $Id$
 */

require_once 'Math/Matrix.php';

$data = array(
            array(1.0,2.0,3.0,4.0),
            array(5.0,6.0,7.0,8.0),
            array(1.0,4.0,5.0,7.0),
            array(2.0,3.0,-3.0,4.0)
        );

$m = new Math_Matrix($data);
//print_r($m);
echo $m->toString()."\n";
$det = $m->determinant();
echo "Determinant = $det\n";
echo "Trace = ".$m->trace()."\n";
echo "Euclidean Norm = ".$m->norm()."\n";
echo "Normalized Determinant = ".$m->normalizedDeterminant()."\n";

echo "\nSubmatrix(1,1,2,2)\n";
$n = $m->getSubMatrix(1,1,2,2);
echo $n->toString()."\n";
$det = $n->determinant();
echo "Determinant = $det\n";
echo "Euclidean Norm = ".$n->norm()."\n";
echo "Normalized Determinant = ".$n->normalizedDeterminant()."\n";

echo "\nWriting original matrix\n";
$e = Math_Matrix::writeToFile($m,'writetest.mat','csv');
echo "... Reading from file\n";
$p = Math_Matrix::readFromFile('writetest.mat','csv');
echo $p->toString()."\n";
$det = $p->determinant();
echo "Determinant = $det\n";
echo "Euclidean Norm = ".$p->norm()."\n";
echo "Normalized Determinant = ".$p->normalizedDeterminant()."\n";

echo "\nInverting matrix\n";
$det = $p->invert();
echo $p->toString()."\n";
echo "Product of matrix and its inverse\n";
$q = $m->clone();
$q->multiply($p);
echo $q->toString('%4.12f')."\n";

echo "\nSolving Ax = b\n";
$a = Math_Matrix::readFromFile('data.mat','csv');
$b = new Math_Vector(range(1,9));
$x = Math_Matrix::solve($a, $b);
echo "\nA\n".$a->toString('%8.4f')."\n";
echo "B ".$b->toString()."\n";
echo "Solution ".$x->toString()."\n";
//print_r($x);
echo "\nSolving with error correction\n";
$x = Math_Matrix::solveEC($a, $b);
echo "EC Solution ".$x->toString()."\n";
//print_r($x);

// Another set of equations

/*
$adata = array(
            array(1,2,3),
            array(2,-1,1),
            array(3,0,-1)
        );

$bdata = array(9,8,3);

// solution: <2, -1, 3>
*/

/*
$adata = array(
            array(1,1,1),
            array(1,-2,2),
            array(1,2,-1)
        );
$bdata = array(0,4,2);
// solution: <4,-2,-2>
*/

$adata = array(
            array(-4,3,-4,-1),
            array(-2,0,-5,3),
            array(-1,-1,-3,-4),
            array(-3,2,4,-1)
        );
$bdata = array(-37,-20,-27,7);
// solution: <2,-2,5,3>

echo "\nSolving another Ax = b\n";
$a = new Math_Matrix($adata);
$b = new Math_Vector($bdata);
$x = Math_Matrix::solve($a, $b);
echo "\nA\n".$a->toString('%8.4f')."\n";
echo "B ".$b->toString()."\n";
echo "Solution ".$x->toString()."\n";
echo "\nSolving with error correction\n";
$x = Math_Matrix::solveEC($a, $b);
echo "EC Solution ".$x->toString()."\n";

/*
echo "\n";
$data = array (
            array(1,0,1),
            array(1,1,1),
            array(1,1,0),
        );
$m = new Math_Matrix($data);
echo $m->toString()."\n";
$det = $m->determinant();
echo "Determinant = $det\n";
echo "Euclidean Norm = ".$m->norm()."\n";
echo "Normalized Determinant = ".$m->normalizedDeterminant()."\n";

echo "\n";
$m = Math_Matrix::makeIdentity(6);
echo $m->toString()."\n";
$det = $m->determinant();
echo "Determinant = $det\n";
echo "Euclidean Norm = ".$m->norm()."\n";
echo "Normalized Determinant = ".$m->normalizedDeterminant()."\n";
*/
?>
