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
    function AddHearAbout($data)
    {
        global $db;
        foreach($data as $HearAboutUs)
        {
            if(isset($HearAboutUs->hearaboutid))
            {

                $result = $db->smartQuery(array(
                    'sql' => "
                  UPDATE `hearabout` 
                  SET   `hearaboutoption` =:hearaboutoption, 
                        `ArabicHearAbout` =:ArabicHearAbout
                  WHERE `hearaboutid`=:hearaboutid",
                    'par' => array(
                        'hearaboutoption'=>$HearAboutUs->hearaboutoption,
                        'ArabicHearAbout'=>$HearAboutUs->ArabicHearAbout,
                        'hearaboutid'=>$HearAboutUs->hearaboutid),
                    'ret' => 'result'
                ));
            }else
            {
                $result = $db->smartQuery(array(
                    'sql' => "INSERT INTO hearabout (hearaboutoption,ArabicHearAbout)VALUES(:hearaboutoption,:ArabicHearAbout)",
                    'par' => array('hearaboutoption'=>$HearAboutUs->hearaboutoption,'ArabicHearAbout'=>$HearAboutUs->ArabicHearAbout),
                    'ret' => 'result'
                ));
            }
        }
        return $result;
    }
}
