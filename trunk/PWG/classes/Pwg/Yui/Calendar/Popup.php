<?php

class Pwg_Yui_Calendar_Popup extends Pwg_Controller_Aggregate implements Pwg_I_Calendar {

	protected $allowPassthroughEvents = true;
	
	protected $preConfig = array(
		'panel' => array(
			'hideOnClose' => true,
			'visible' => false,
			'label' => '** label will be set in constructor **',
		),
		'calendar' => array(
			'selectedValue' => false,
			'multiple' => false,
			'minDate' => false,
			'maxDate' => false,
			'dateClasses' => array(),
			'pageDate' => false,
			'phpDateFormat' => 'Y-m-d',
			'autoSize' => true,
		),
	);
	
    protected $yearPrevHtml = '&laquo;';

    protected $yearNextHtml = '&raquo;';
	
	protected $containerIsBlock = false;
    
	/**
	 * Regular expression to check input into the text box; only if the text in the field matches
	 * this expression, calendar value will be updated. (FALSE to ignore)
	 * 
	 * @var string
	 */
    protected $maskToUpdate = false;

	/**
	 * @var Pwg_Text
	 */
	protected $edit = false;
	
	/**
	 * @var Pwg_Button
	 */
	protected $button = false;
	
	/**
	 * @var Pwg_Yui_Panel
	 */
	protected $panel = false;
	
	/**
	 * @var Pwg_Yui_Calendar
	 */
	protected $calendar = false;
	
	/**
	 * @var Pwg_Label
	 */
	protected $lblYear = false;
	
	/**
	 * @var Pwg_List
	 */
	protected $lstMonths = false;
	
	protected $label = false;
	
	protected $closeOnSelect = false;
    
    protected $withYearMonthSelector = true;
    
    protected $pageYear = false;
    
    protected $pageMonth = false;
	
    function __construct(array $options = array()) {
    	$this->preConfig['panel']['header'] = new Pwg_Lang_String('calendar_popup_default_label');
    	parent::__construct($options);
    }
	
	/**
	 * @return Pwg_Button
	 */
	function getButton() {
		return $this->getControl('button');
	}
	
	/**
	 * @return Pwg_Yui_Panel
	 */
	function getPanel() {
		return $this->getControl('panel');
	}
	
	/**
	 * @var Pwg_Calendar
	 */
	function getCalendar() {
		return $this->getControl('calendar');
	}
	
	protected function doOnGetControlPrototypes(& $prototypes) {
	    
		$months = explode("|", new Pwg_Lang_String('locale_months_long'));
		$options = array();
		
		for ($i = 1; $i <= 12; $i++) $options[] = array(
		    'value' => $i,
		    'label' => $months[$i - 1],
		);
	    
	    Ae_Util::ms($prototypes, array(
			'edit' => array(
				'class' => 'Pwg_Text',
				'containerIsBlock' => false,
				'size' => 10,
			),  
			'button' => array(
				'class' => 'Pwg_Button',
				'label' => new Pwg_Lang_String('calendar_popup_default_button'),
				'containerIsBlock' => false,
			),
			'panel' => array(
				'class' => 'Pwg_Yui_Panel',
				'context' => array(new Pwg_Control_Path('../button'), 'tl', 'bl'),
				'closeOnOutsideClick' => true,
				'draggable' => false,
				'autoSize' => false,
			),
			'pnlPanelContent' => array(
			    'class' => 'Pwg_Panel',
			    'displayParentPath' => '../panel',
			    'template' => ('<div class="calheader">{lblYear} {lstMonths}</div>{calendar}'),
			),			
			'calendar' => array(
				'class' => 'Pwg_Yui_Calendar',
				'displayParentPath' => '../pnlPanelContent',
				'closeButton' => false,
			),
			'lblYear' => array(
			    'class' => 'Pwg_Label',
			    'displayParentPath' => '../pnlPanelContent',
			    'containerIsBlock' => false,
			),
			'lstMonths' => array(
			    'class' => 'Pwg_List',
			    'options' => $options,
				'displayParentPath' => '../pnlPanelContent',
				'containerIsBlock' => false,
			),
			'lblDummy' => array(
				'displayParentPath' => '../panel',
				'html' => "<br style='clear:both' />"
			),
		));
		
		Ae_Util::ms($prototypes, $this->preConfig);
		if (($val = $this->getSelectedValue()) !== false) {
			$prototypes['edit']['text'] = is_array($val)? implode(" ", $val) : $val;
		}
		
		if ($this->withYearMonthSelector) {
		    $prototypes['calendar']['showHeader'] = false;
		} else { 
		    unset($prototypes['lblYear']);
		    unset($prototypes['lstMonths']);
		    $prototypes['pnlPanelContent']['template'] = '{calendar}';
		}
		
	}
	
	protected function passthroughSet($control, $name, $value) {
		if ($this->controlsCreated) {
			$s = 'set'.ucfirst($name);
			$this->$control->$s($value);
		} else $this->preConfig[$control][$name] = $value;
	}
	
	protected function passthroughGet($control, $name) {
		if ($this->controlsCreated) {
			$g = 'get'.ucfirst($name);
			$res = $this->$control->$g();
		} else $res = $this->preConfig[$control][$name];
		return $res;
	}
	
	// +-------------------------------- Pwg_I_Calendar implementation ---------------------------------+
	
    function setMultiple($multiple) {
        $this->passthroughSet('calendar', 'multiple', $multiple);
    }

    function getMultiple() {
        return $this->passthroughGet('calendar', 'multiple');
    }

    function setDateClasses($dateClasses) {
        $this->passthroughSet('calendar', 'dateClasses', $dateClasses);
    }

    function getDateClasses() {
        return $this->passthroughGet('calendar', 'dateClasses');
    }

    function setSelectedValue($selectedValue) {
        $this->passthroughSet('calendar', 'selectedValue', $selectedValue);
        $v = is_array($selectedValue)? implode(' ', $selectedValue) : $selectedValue;
        $this->passthroughSet('edit', 'text', $selectedValue);
        $this->applyPageDate();
    }

    function getSelectedValue() {
        return $this->passthroughGet('calendar', 'selectedValue');
    }

    function setMinDate($minDate) {
        $this->passthroughSet('calendar', 'minDate', $minDate);
    }

    function getMinDate() {
        return $this->passthroughGet('calendar', 'minDate');
    }

    function setMaxDate($maxDate) {
        $this->passthroughSet('calendar', 'maxDate', $maxDate);
    }

    function getMaxDate() {
        return $this->passthroughGet('calendar', 'maxDate');
    }

    function setPageDate($pageDate, $force = false) {
        $this->passthroughSet('calendar', 'pageDate', $pageDate);
    }

    function getPageDate() {
        $res = $this->passthroughGet('calendar', 'pageDate');
        $this->applyPageDate();
        return $res;
    }

    function setPhpDateFormat($phpDateFormat) {
        $this->passthroughSet('calendar', 'phpDateFormat', $phpDateFormat);
    }

    function getPhpDateFormat() {
        return $this->passthroughGet('calendar', 'phpDateFormat');
    }	
    
    // ------------------------ end of Pwg_I_Calendar implementation ---------------------------
	
	function setLabel($label) {
		$this->passthroughSet('panel', 'header', $label);
	} 
	
	function getLabel() {
		$this->passthroughGet('panel', 'header');
	}
	
	function setCalendarVisible($visible) {
		$this->passthroughSet('panel', 'visible', $visible);
		$this->panel->setFocused(true);
	} 
	
	function getCalendarVisible() {
		$this->passthroughGet('panel', 'visible');
	}
	
	function setHideOnClose($hideOnClose) {
		$this->passthroughSet('panel', 'hideOnClose', $hideOnClose);
	} 
	
	function getCalendarHideOnClose() {
		$this->passthroughGet('panel', 'hideOnClose');
	}
	
	function handleCalendarSelectedValueChange($control, $eventType, array $params) {
		$close = $this->closeOnSelect;
		$params['close'] = & $close;
		$sv = $params['selectedValue'];
		if ($this->edit) {
			if ($sv === false) $t = '';
			elseif (is_array($sv)) $t = implode(' ', $sv);
			else $t = $sv;
			$this->edit->setText($t);
		}
		$res = $this->triggerEvent(self::evtSelectedValueChange, $params);
		if ($close) $this->panel->setVisible(false);
		return $res;
	} 
	
	function handleCalendarPageDateChange($control, $eventType, array $params) {
		$this->applyPageDate();
	    return $this->triggerEvent(self::evtPageDateChange, $params);
	}
	
	function handlePanelClose($control, $eventType, array $params) {
		return $this->triggerEvent(self::evtClose, $params);
	}

    function handleButtonClick() {
    	$this->panel->setVisible(!$this->panel->getVisible());
    	if ($this->panel->getVisible()) {
    	    $this->panel->setContext($this->panel->getContext());
    	    $this->panel->setFocused(false);
    	    $this->panel->setFocused(true);
    	}
    }
    
    function setCloseOnSelect($closeOnSelect) {
        $this->closeOnSelect = $closeOnSelect;
    }

    function getCloseOnSelect() {
        return $this->closeOnSelect;
    }    
    
    function handleEditChange() {
    	$vals = preg_split("/\s+/", $txt = $this->edit->getText());
    	$sv = array();
    	$maskOk = true;
    	foreach ($vals as $val) {
    		if (strlen($this->maskToUpdate)) {
    		    if (strlen(trim($val)) && !preg_match($this->maskToUpdate, $val)) {
    		        $maskOk = false;
    		        break;
    		    }
    		}
    		if (($d = Ae_Util::date($val)) !== false) $sv[] = $d;
    	}
    	if ($maskOk) {
        	if (!$this->calendar->getMultiple()) $sv = count($sv)? $sv[0] : false;
        	$empty = $sv === null || $sv === false;
        	if (strlen($txt) && !$empty || !strlen($txt) && $empty) {
        	   $oldSelectedValue = $this->calendar->getSelectedValue();
        	   $this->calendar->setSelectedValue($sv);     	
        	   $newSelectedValue = $this->calendar->getSelectedValue();
        	   if ($oldSelectedValue != $newSelectedValue) {
        	       $this->triggerEvent(self::evtSelectedValueChange, array('selectedValue' => $newSelectedValue));
        	   }
        	}
    	}
    }

    protected function setWithYearMonthSelector($withYearMonthSelector) {
        $this->withYearMonthSelector = $withYearMonthSelector;
    }

    function getWithYearMonthSelector() {
        return $this->withYearMonthSelector;
    }

    protected function doAfterControlsCreated() {
        parent::doAfterControlsCreated();
        Pwg_Conversation::log("PageDate of {$this->calendar} is ", $this->calendar->getPageDate());
        $this->applyPageDate();
    }
    
    protected function applyPageDate() {
        if ($this->calendar && $this->lblYear && $this->lstMonths) {
            $pd = $this->calendar->getPageDate();
            if (Ae_Util::date($pd) !== false) {
                $year = Ae_Util::date($pd, 'Y');
                $month = (int) Ae_Util::date($pd, 'm');
                $this->lblYear->setHtml("<a class='calnavleft' href='##prev'>{$this->yearPrevHtml}</a> {$year} <a class='calnavright' href='##next'>{$this->yearNextHtml}</a>");
                $this->lstMonths->setSelectedValue($month);
            }
        }
    }
    
    function handleLblYearClick($lbl, $eventType, $params) {
        if (isset($params['href'])) {
            $year = Ae_Util::date($this->calendar->getPageDate(), 'Y');
            if ($year !== false) {
                $year = (int) $year;
                if ($params['href'] == '##prev') {
                    $year--;
                } elseif ($params['href'] == '##next') {
                    $year++;
                }
            }
            $newPageDate = $year.Ae_Util::date($this->calendar->getPageDate(), '-m-').'-01';
            $this->calendar->setPageDate($newPageDate);
            $this->applyPageDate();
        }
    }
    
    function handleLstMonthsSelectionChange() {
        $v = $this->lstMonths->getSelectedValue();
        Pwg_Conversation::log("Selected value is ", $v);
        $month = Ae_Util::date($this->calendar->getPageDate(), 'm');
        if ($month !== false) {
            $newMonth = ''.$v;
            if (strlen($v) < 2) $v = '0'.$v;
            $year = Ae_Util::date($this->calendar->getPageDate(), 'Y');
            $newPageDate = $year.'-'.$newMonth.'-01';
            $this->calendar->setPageDate($newPageDate);
        }
    }

    protected function setYearPrevHtml($yearPrevHtml) {
        $this->yearPrevHtml = $yearPrevHtml;
    }

    function getYearPrevHtml() {
        return $this->yearPrevHtml;
    }

    protected function setYearNextHtml($yearNextHtml) {
        $this->yearNextHtml = $yearNextHtml;
    }

    function getYearNextHtml() {
        return $this->yearNextHtml;
    }

    function setMaskToUpdate($maskToUpdate) {
        $this->maskToUpdate = $maskToUpdate;
    }

    function getMaskToUpdate() {
        return $this->maskToUpdate;
    }    
	
}