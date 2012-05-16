<?php

class Pwg_Yui_Calendar_PopupWithTime extends Pwg_Yui_Calendar_Popup {

    const evtValueWithTimeChange = 'valueWithTimeChange';
    
//    function __construct(array $options = array()) {
//    }    

    /**
    * @var Pwg_Text
    */
    public $timeEdit = false;
    
    /**
     * @return Pwg_Text
     */
    function getTimeEdit() {
    	return $this->getControl('timeEdit');
    }
    
	protected function doOnGetControlPrototypes(& $prototypes) {
	    $prototypes = array('edit' => array(), 'timeEdit' => array());
	    parent::doOnGetControlPrototypes($prototypes);
	    Ac_Util::ms($prototypes, array(
	        'timeEdit' => array(
	            'class' => 'Pwg_Text',
	            'size' => 10,
	            'containerIsBlock' => false,
	            'dummyCaption' => new Pwg_Lang_String('calendar_popup_dummy_time'),
	        ),
	    ));
	}
	
    function setTime($time) {
        $this->passthroughSet('timeEdit', 'text', $time);
    }

    function getTime() {
        return $this->passthroughGet('timeEdit', 'text');
    }

    function setValueWithTime($valueWithTime) {
        $date = '';
        $time = '';
        if (is_array($valueWithTime)) {
            $date = array();
            foreach ($valueWithTime as $val) {
                $dt = preg_split("/\\w+/", $val, 2);
                $date = $dt[0];
                if (count($date) > 1) $time = $dt[1];
            }
        } else {
            $dt = preg_split("/[ ]+/", $valueWithTime, 2);
            $date = $dt[0];
            if (count($dt) > 1) $time = $dt[1];
        }
        $this->setSelectedValue($date);
        $this->setTime($time);
    }
    
    function getValueWithTime() {
        $d = $this->getSelectedValue();
        $t = $this->getTime();
        if (strlen($t)) {
            if (is_array($d)) {
                foreach (array_keys($d) as $i) $d[$i] .= ' '.$t;
            } else {
                $d .= ' '.$t;
            }
        }
        return $d;
    }
    
    
    protected function doAfterControlsCreated() {
        parent::doAfterControlsCreated();
        $this->observe(self::evtSelectedValueChange , $this, 'triggerValueWithTimeChange');
    }
    
    function triggerValueWithTimeChange() {
        $this->triggerEvent(self::evtValueWithTimeChange, array('valueWithTime' => $this->getValueWithTime()));
    }
    
    function handleTimeEditChange() {
        $this->triggerValueWithTimeChange();
    }    
    
}