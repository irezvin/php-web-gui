<?php

class Pwg_Table_Colset extends Pwg_Composite_Display {

    protected $allowedDisplayChildrenClass = 'Pwg_Table_Column';
    
    protected $allowedChildrenClass = 'Pwg_Table_Column';
    
    protected $allowPassthroughEvents = true;
    
    /**
     * @return Pwg_Table
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
     * @return Pwg_Table_Column
     */
    function getControl($id) {
        return parent::getControl($id);
    }
    
}

?>