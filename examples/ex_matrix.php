<?php

require_once "Matrix.php";
require_once "MatrixOp.php";

$dat = array(
		array(2,3,4),
		array(5,-1,3),
		array(-2,2,9),
		array(0,3,8)
		);

$dsq = array(
		array(2,3,4),
		array(-2,2,5),
		array(0,3,8)
		);

$m = new Math_Matrix($dat);
list($nr, $nc) = $m->getSize();
echo "Matrix m\n";
echo "Min: ".$m->getMin()." , Max: ".$m->getMax()."\n";
echo "Rows: $nr , Columns: $nc\n";
echo "Element at (0,1) = ".$m->getElement(0,1)."\n";
list($er, $ec) = $m->getMinIndex();
echo "Min element is ($er,$ec) = ".$m->getMin()."\n";
list($er, $ec) = $m->getMaxIndex();
echo "Max element is ($er,$ec) = ".$m->getMax()."\n";
echo "toString()\n".$m->toString();
//echo "Printing table\n".$m->toHTML();

echo "writing m to file 'tmatrix.dat'\n";
Math_MatrixOp::writeToFile($m,"tmatrix.dat");
echo "...reading from file into temp:\n";
$temp = Math_MatrixOp::readFromFile("tmatrix.dat");
echo $temp->toString();

$sum = Math_MatrixOp::add($m, $temp);
echo "m + temp:\n".$sum->toString();

$temp = Math_MatrixOp::scale ($temp, 0.834);
echo "scale temp by 0.834\n".$temp->toString("%6.3f");
$sub = Math_MatrixOp::sub($m, $temp);
echo "m - temp:\n".$sub->toString("%6.3f");

$msq = new Math_Matrix($dsq);
echo "Matrix msq:\n".$msq->toString("%6.0f");
$transp = Math_MatrixOp::transpose($msq);
if (PEAR::isError($transp))
	echo $transp->getMessage()."\n";
else
    echo "transpose(msq):\n".$transp->toString("%6.0f");

$transp = Math_MatrixOp::swapRows($transp,0,1);
echo "swap rows 0<->1\n".$transp->toString("%6.0f");
$transp = Math_MatrixOp::swapCols($transp,0,2);
echo "swap cols 0<->2\n".$transp->toString("%6.0f");
$transp = Math_MatrixOp::swapRowCol($transp,0,1);
echo "swap row<->col 0<->1\n".$transp->toString("%6.0f");

$d = Math_MatrixOp::makeMatrix(2,3,-2);
echo "new matrix d (2 x 3), filled w/ -2\n".$d->toString();

$e = Math_MatrixOp::makeOne(3,2);
echo "new matrix e (3 x 2), filled w/ 1\n".$e->toString();

$u = Math_MatrixOp::makeUnit(3);
echo "new matrix u (unit matrix of size 4)\n".$u->toString();

?>
