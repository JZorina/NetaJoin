<?php
/**
 * Created by PhpStorm.
 * User: yulia
 * Date: 3/21/2018
 * Time: 02:49 PM
 */

class HearAboutUs
{
    function GetHearAboutUsOptions()
    {
        global $db;
        $HearAboutUs = $db->smartQuery(array(
            'sql' => "Select * FROM hearabout Order By hearaboutoption",
            'par' => array(),
            'ret' => 'all'
        ));
        return $HearAboutUs;
    }
}
