<?php

class Pwg_Yui_Calendar extends Pwg_Base implements Pwg_I_Calendar {
	
	protected $selectedValue = false;
	
	protected $minDate = false;
	
	protected $maxDate = false;
	
	protected $navigator = false;
	
	protected $pages = 1;
	
	protected $dateClasses = array();
	
    protected $pageDate = false;
    
    protected $phpDateFormat = 'Y-m-d';
    
    protected $zeroDate = '0000-00-00';
    
    protected $phpSelectedValue = false;
	
	protected $multiple = false;

	protected $closeButton = false;

    protected $visible = true;

    protected $hideOnClose = true;
    
    protected $hasNextPrevButtons = true;
    
    protected $isLocalized = true;
	
    protected $showHeader = true;
    
	function hasContainer() {
		return true; 
	}
    
	protected function doGetAssetLibs() {
		return array_merge(parent::doGetAssetLibs(), array(
            '{AE}/util.js',
			'core.js',
			'widgets.js',
			'widgets/yui/calendar.js',
			
			'{YUI}/yahoo/yahoo.js',
			'{YUI}/event/event.js',
			'{YUI}/dom/dom.js',
			'{YUI}/calendar/calendar.js',
			'{YUI}/calendar/assets/skins/sam/calendar.css',
		));
	}
	
	protected function doListPassthroughParams() {
		return array_merge(parent::doListPassthroughParams(), array(
			'multiple',
			'selectedValue',
			'minDate',
			'maxDate',
			'navigator',
		    'showHeader',
			'pages',
			'pageDate',
			'dateClasses',
			'closeButton',
			'visible',
			'hideOnClose',
		    'hasNextPrevButtons',
		    'localizationData',  
		));
	}
	
    function setMultiple($multiple) {
        if ($multiple !== ($oldMultiple = $this->multiple)) {
            $this->multiple = $multiple;
            $this->sendMessage(__FUNCTION__, array($multiple));
        }
    }

    function getMultiple() {
        return $this->multiple;
    }
    
    function setDateClasses($dateClasses) {
        if ($dateClasses !== ($oldDateClasses = $this->dateClasses)) {
            $this->dateClasses = $dateClasses;
            $this->sendMessage(__FUNCTION__, array($this->jsGetDateClasses()));
        }
    }

    function getDateClasses() {
        return $this->dateClasses;
    }
    
    function isZeroDate($date) {
        return Ae_Util::date($date) === 0; 
    }
    
    protected function toFormat($dateOrDates, $format) {
    	if (is_array($dateOrDates)) {
    		$res = array();
    		foreach ($dateOrDates as $k => $v) {
    			$v = $this->toFormat($v, $format);
    			if (($v !== false) && !(is_array($v) && !count($v))) $res[$k] = $v;
    		} 
    	} else {
    	    Pwg_Conversation::log("Selected dates: ", $dateOrDates);
    		if (is_null($dateOrDates) || ($dateOrDates === false)) $res = $dateOrDates;
    		elseif ($this->isZeroDate($dateOrDates)) {
    		    $res = ($format === $this->phpDateFormat)? $this->zeroDate : '';
    		} else {
    		    $res = Ae_Util::date($dateOrDates, $format);
    		}
    	}
    	return $res;
    }
    
    protected function jsGetDateClasses() {
    	$res = array();
    	if (is_array($this->dateClasses)) {
    		foreach($this->dateClasses as $date => $classes) if (is_array($classes) && $classes || strlen($classes)) {
    			$d = Ae_Util::date($date, 'n/j/Y');
    			if (strlen($d)) {
    				$res[$d] = $classes;
    			}
    		}
    	}
    	return $res;
    }
    
    function setSelectedValue($selectedValue) {
        if ($selectedValue !== ($oldSelectedValue = $this->selectedValue)) {
            $this->selectedValue = $selectedValue;
            if ($this->multiple && !is_array($this->selectedValue)) {
            	if (is_null($this->selectedValue) || $this->selectedValue === false) $this->selectedValue = array();
            	else $this->selectedValue = array($this->selectedValue);
            } elseif (!$this->multiple && is_array($this->selectedValue)) {
            	$this->selectedValue = array_slice($this->selectedValue, 0, 1);
	        	if (count($this->selectedValue)) $this->selectedValue = $this->selectedValue[0];
	        		else $this->selectedValue = false;
            }
            if (!$this->multiple && $this->selectedValue && !strlen($this->pageDate)) {
                $this->setPageDate($this->pageDate, true);
            }
            $this->phpSelectedValue = false;
            $this->sendMessage(__FUNCTION__, array($this->jsGetSelectedValue()), 1);
        }
    }

    function getSelectedValue() {
    	if ($this->phpSelectedValue === false) {
	    	$this->phpSelectedValue = $this->toFormat($this->selectedValue, $this->phpDateFormat);
    	}
    	return $this->phpSelectedValue;
    }
    
    function jsGetSelectedValue() {
    	return $this->toFormat($this->selectedValue, 'n/j/Y');
    }
    
    function triggerFrontendSelectedValue($selectedValue = false) {
    	$oldSelectedValue = $this->getSelectedValue();
        $this->lockMessages();
    	$this->setSelectedValue($selectedValue);
    	$this->unlockMessages();
    	$newSelectedValue = $this->getSelectedValue();
        //Pwg_Conversation::log("{$this} changed event ", $oldSelectedValue, $newSelectedValue);
    	if ($newSelectedValue != $oldSelectedValue) {
    	    //Pwg_Conversation::log("{$this} triggering event ", $newSelectedValue);
    	    $this->triggerEvent(self::evtSelectedValueChange, array('selectedValue' => $newSelectedValue));
    	}
    	
    }
    
    function setMinDate($minDate) {
        if ($minDate !== ($oldMinDate = $this->minDate)) {
            $this->minDate = $minDate;
            $this->sendMessage(__FUNCTION__, array($this->jsGetMinDate()));
        }
    }

    function getMinDate() {
        return $this->minDate;
    }
    
    function jsGetMinDate() {
    	return $this->toFormat($this->minDate, 'n/j/Y');
    }

    function setMaxDate($maxDate) {
        if ($maxDate !== ($oldMaxDate = $this->maxDate)) {
            $this->maxDate = $maxDate;
            $this->sendMessage(__FUNCTION__, array($this->jsGetMaxDate()));
        }
    }

    function getMaxDate() {
        return $this->maxDate;
    }
    
    function jsGetMaxDate() {
    	return $this->toFormat($this->maxDate, 'n/j/Y');
    }
    
    protected function setNavigator($navigator) {
        $this->navigator = $navigator;
    }

    function getNavigator() {
        return $this->navigator;
    }

    protected function setPages($pages) {
        $this->pages = $pages;
    }

    function getPages() {
        return $this->pages;
    }

    function setPageDate($pageDate, $force = false) {
        if ($force || ($pageDate !== ($oldPageDate = $this->pageDate))) {
            $this->pageDate = $pageDate;
            $this->sendMessage(__FUNCTION__, array($this->jsGetPageDate()), 1);
            $this->triggerEvent(self::evtPageDateChange, array('pageDate' => $this->pageDate));
        }
    }

    function getPageDate() {
    	$res = false;
        if (!strlen($this->pageDate)) {
    	    if (!$this->multiple && strlen($this->selectedValue) && !$this->isZeroDate($this->selectedValue)) {
    	        $strPageDate = Ae_Util::date($this->selectedValue, 'Y-m').'-01';
    	    }  else {
    	        $strPageDate = date('Y-m').'-01';
    	    } 
    	} else {
    	    $strPageDate = $this->pageDate;
    	}
    	$res = Ae_Util::date($strPageDate, $this->phpDateFormat);
    	return $res;
    }
    
    function jsGetPageDate() {
    	//Pwg_Conversation::log("PageDate to set" , $this->pageDate, Ae_Util::date($this->pageDate, 'Y-m-d'), Ae_Util::date($this->pageDate, 'n/Y'));
    	if (!strlen($this->pageDate)) {
    	    if (!$this->multiple && strlen($this->selectedValue) && !$this->isZeroDate($this->selectedValue)) $res = Ae_Util::date($this->selectedValue, 'n/Y'); 
    	        else $res = date('n/Y'); 
    	} else {
    	    $res = Ae_Util::date($this->pageDate, 'n/Y');
    	}
    	//Pwg_Conversation::log("jsPageDate is ", $res);
    	return $res;
    }
    
    function triggerFrontendPageDate($pageDate) {
    	if (preg_match("#([0-9]{1,2})/([0-9]{1,4})#", $pageDate, $matches)) {
    		$this->pageDate = Ae_Util::date("{$matches[2]}-{$matches[1]}-01", $this->phpDateFormat);
    		Pwg_Conversation::log($pageDate, $this->pageDate);
    		$this->triggerEvent(self::evtPageDateChange, array('pageDate' => $this->pageDate));
    	}
    }
    
	function setPhpDateFormat($phpDateFormat) {
        $this->phpDateFormat = $phpDateFormat;
        $this->phpSelectedValue = false;
    }

    function getPhpDateFormat() {
        return $this->phpDateFormat;
    }

    protected function setCloseButton($closeButton) {
        $this->closeButton = $closeButton;
    }

    function getCloseButton() {
        return $this->closeButton;
    }

    function setVisible($visible) {
        $visible = (bool) $visible;
        if ($visible !== ($oldVisible = $this->visible)) {
            $this->visible = $visible;
            $this->sendMessage(__FUNCTION__, array($visible));
        }
    }

    function getVisible() {
        return $this->visible;
    }
    
    function triggerFrontendVisible($visible) {
    	if (!$visible) {
	    	$allowClose = true;
	    	$hideOnClose = $this->hideOnClose;
	    	if ($hideOnClose) {
	    		$this->visible = false;
	    		$this->triggerEvent(self::evtClose, array('allowClose' => & $allowClose));
	    		if (!$allowClose) $this->setVisible(true);
	    	} else {
	    		$this->setVisible(false);
	    		$this->triggerEvent(self::evtClose, array('allowClose' => & $allowClose));
	    		if (!$allowClose) $this->setVisible(true);
	    	}
    	} else {
    		$this->visible = true;
    	}
    }

    function setHideOnClose($hideOnClose) {
    	if ($hideOnClose !== ($oldHideOnClose = $this->hideOnClose)) {
            $this->hideOnClose = $hideOnClose;
            $this->sendMessage(__FUNCTION__, array($this->hideOnClose));
        }
    }

    function getHideOnClose() {
        return $this->hideOnClose;
    }
    
    protected function setHasNextPrevButtons($hasNextPrevButtons) {
        $this->hasNextPrevButtons = $hasNextPrevButtons;
    }

    function getHasNextPrevButtons() {
        return $this->hasNextPrevButtons;
    }

    protected function setIsLocalized($isLocalized) {
        $this->isLocalized = $isLocalized;
    }

    function getIsLocalized() {
        return $this->isLocalized;
    }

	function jsGetLocalizationData() {
	    if ($this->isLocalized) {
	        $defaults = array(
	            'MONTHS_SHORT' => explode("|", new Pwg_Lang_String('locale_months_short', 
	            	"Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec")),
	        	'MONTHS_LONG' => explode("|", new Pwg_Lang_String('locale_months_long', 
	            	implode("|", array("January", "February", "March", "April", "May", "June", 
	            	"July", "August", "September", "October", "November", "December")))),
	            'WEEKDAYS_1CHAR' => explode("|", new Pwg_Lang_String('locale_weekdays_1char', 
	            	implode("|", array("S", "M", "T", "W", "T", "F", "S")))),
	            'WEEKDAYS_SHORT' => explode("|", new Pwg_Lang_String('locale_weekdays_short', 
	            	implode("|", array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa")))),
	            'WEEKDAYS_LONG' => explode("|", new Pwg_Lang_String('locale_weekdays_long', 
	            	implode("|", array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")))),
	            'START_WEEKDAY' => (int) (string) new Pwg_Lang_String('locale_start_weekday', '0'),
	        );
	        if (is_array($this->isLocalized)) $res = array_merge($defaults, $this->isLocalized);
	            else $res = $defaults;
	    } else $res = false;
	    return $res;
	}

    protected function setShowHeader($showHeader) {
        $this->showHeader = $showHeader;
    }

    function getShowHeader() {
        return $this->showHeader;
    }	
	
}