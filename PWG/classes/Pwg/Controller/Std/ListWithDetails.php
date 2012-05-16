<?php

class Pwg_Controller_Std_ListWithDetails extends Pwg_Controller_Std_List {

    protected function fldPrototype(array $overrides = array()) {
        $res = array(
            'class' => 'Pwg_Data_Field',
            'displayParentPath' => '../pnlDetails',
            'dataSourcePath' => '../dsData',
            'prototypesOverride' => array(
                'editor' => array(
                    'containerIsBlock' => true,
                ),
            ),
        );
        if ($overrides) Ac_Util::ms($res, $overrides);
        return $res;
    }

    protected function doOnGetControlPrototypes(array & $prototypes) {
         
        parent::doOnGetControlPrototypes($prototypes);

        unset($prototypes['btnCreate']);
        unset($prototypes['btnOpenDetails']);

        Ac_Util::ms($prototypes, array(

            'pnlLayout' => array(

                'template' => '
                    <table cols="2">
                        <tr>
                        	<td style="padding: 0.5em" colspan="2">
                            	{pnlFilters}  
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5em" colspan="2">
                                {paginator}
                            </td>
                        </tr>
                        <tr>
                			<td style="padding: 0.5em">
                				{tblList}
                			</td>
                			<td style="padding: 0.5em 0.5em 0.5em 0">
                				{pnlDetails}
                			</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5em" colspan="2">
                                {dnNavigator}
                            </td>
                        </tr>
                    </table>
                ',
            ),
         
    	    'pnlDetails' => array(
    	        'displayParentPath' => '../pnlLayout',
            ),
        	
            'dnNavigator' => array(
                'dataSourcePath' => '../dsData', 
                'displayParentPath' => '../pnlLayout',
            	'hasBtnNew' => true,
            	'hasBtnSave' => true,
            	'hasBtnCancel' => true,
            	'hasBtnReload' => true,
            	'deleteConfirmation' => new Pwg_Lang_String('deleteRecordConfirmation'),
            ),

            'lstSort' => array(
                'visible' => false,
                'disabled' => true,
            ),

        ));
    }

}