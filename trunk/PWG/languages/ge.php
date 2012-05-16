<?php


require(dirname(__FILE__).'/en.php');

Ac_Util::ms($lang, array(

    'locale_months_short' => "Jan|Feb|Mär|Apr|Mai|Jun|Jul|Aug|Sep|Okt|Nov|Dez",
    'locale_months_long' => implode("|", array(
    	"Januar", "Februar", "März", "April", "Mai", "Juni", 
	    "Juli", "August", "September", "Oktober", "November", "Dezember"
    )),
	'locale_weekdays_1char' => implode("|", array("S", "M", "D", "M", "D", "F", "S")),
    'locale_weekdays_short' => implode("|", array("Son", "Mon", "Die", "Mitt", "Don", "Fre", "Sam")),
	'locale_weekdays_long' => implode("|", array(
		"Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag")),

));