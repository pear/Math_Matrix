<?php

require_once "Matrix.php";
require_once "Matrix3x3.php";
require_once "MatrixOp.php";

$dsq = array(
		array(2,3,4),
		array(-2,2,5),
		array(0,3,8)
		);

$m3 = new Math_Matrix3x3($dsq);
$m3_inv = Math_MatrixOp::invert($m3);
$m3_mult = Math_MatrixOp::multiply($m3, $m3_inv);

echo $m3->toString()."\n";
echo $m3_inv->toString("%6.3f")."\n";
echo $m3_mult->toString("%6.3f")."\n";
print_r($m3_inv);

$m = new Math_Matrix($dsq);
$m_inv = Math_MatrixOp::invert($m);
$m_mult = Math_MatrixOp::multiply($m, $m_inv);

echo $m->toString()."\n";
echo $m_inv->toString("%6.3f")."\n";
echo $m_mult->toString("%6.3f")."\n";
print_r($m_inv);

if ($m_inv === $m3_inv)
	echo "yeah right\n";
else
	echo "YES!\n";

?>
