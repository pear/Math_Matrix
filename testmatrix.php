<?php

require_once 'Matrix.php';

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
$e = Math_Matrix::writeToFile($m,'testdata.mat','csv');
echo "... Reading from file\n";
$p = Math_Matrix::readFromFile('testdata.mat','csv');
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
$a = Math_Matrix::readFromFile('a.mat','csv');
$b = new Math_Vector(range(1,9));
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
