<?php

class Math_Matrix3x3 extends Math_Matrix {/*{{{*/

    function Math_Matrix3x3($data = null) {/*{{{*/
        $this->Math_Matrix($data);
    }/*}}}*/

    function setData($data) {/*{{{*/
        if (is_array($data) && count($data) == count($data[0])
            && count($data) == 3)
            parent::setData($data);
        else
            PEAR::raiseError('Invalid data, cannot create/modify 3x3 matrix');
    }/*}}}*/

    function determinant() {/*{{{*/
        if ($this->isEmpty())
            return PEAR::raiseError('Matrix has not been populated');
        else
            return (  $this->geElement(0,0) * $this->getElement(1,1) * $this->getElement(2,2) 
                    - $this->geElement(0,0) * $this->getElement(2,1) * $this->getElement(1,2) 
                    + $this->geElement(1,0) * $this->getElement(2,1) * $this->getElement(0,2) 
                    - $this->geElement(1,0) * $this->getElement(0,1) * $this->getElement(2,2) 
                    + $this->geElement(2,0) * $this->getElement(0,1) * $this->getElement(1,2) 
                    - $this->geElement(2,0) * $this->getElement(1,1) * $this->getElement(0,2) );
    }/*}}}*/
}/*}}}*/

// vim: ts=4:sw=4:et:
// vim6: fdl=1:

?>
