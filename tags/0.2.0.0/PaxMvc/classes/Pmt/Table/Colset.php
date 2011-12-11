<?php

class Pmt_Table_Colset extends Pmt_Composite_Display {

    protected $allowedDisplayChildrenClass = 'Pmt_Table_Column';
    
    protected $allowedChildrenClass = 'Pmt_Table_Column';
    
    /**
     * @return Pmt_Table
     */
    function getTable() {
        return $this->getParent();
    }
    
    function hasJsObject() {
        return false;
    }
    
    function hasContainer() {
        return false;
    }
    
    /**
     * @param string $id
     * @return Pmt_Table_Column
     */
    function getControl($id) {
        return parent::getControl($id);
    }
    
}

?>