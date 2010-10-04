<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Math_Matrix_AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Math_MatrixTest.php';

class Math_Matrix_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PEAR - Math_Matrix');

        $suite->addTestSuite('Math_MatrixTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Math_Matrix_AllTests::main') {
    Math_Matrix_AllTests::main();
}
