<?php

class Math_Matrix2x2 extends Math_Matrix {/*{{{*/

    function Math_Matrix2x2($data = null) {/*{{{*/
        $this->Math_Matrix($data);
    }/*}}}*/

    function setData($data) {/*{{{*/
        if (is_array($data) && count($data) == count($data[0])
            && count($data) == 2)
            parent::setData($data);
        else
            PEAR::raiseError('Invalid data, cannot create/modify 2x2 matrix');
    }/*}}}*/

    function determinant() {/*{{{*/
        if ($this->isEmpty())
            return PEAR::raiseError('Matrix has not been populated');
        else
            return ( $this->getElement(0,0) * $this->getElement(1,1) -
                     $this->getElement(1,0) * $this->getElement(0,1) );
    }/*}}}*/

}/*}}}*/

// vim: ts=4:sw=4:et:
// vim6: fdl=1:

?>
