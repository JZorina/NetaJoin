
<?php
/**
 * Created by PhpStorm.
 * User: yulia
 * Date: 3/21/2018
 * Time: 05:02 PM
 */

class NomineeStatus
{
    function GetStatuses()
    {
        global $db;
        $Statuses = $db->smartQuery(array(
            'sql' => "Select * FROM nomineestatus ORDER BY `nomineestatusid`",
            'par' => array(),
            'ret' => 'all'
        ));
        return $Statuses;
    }

    function AddStatus($data)
    {
        global $db;
        foreach($data as $NomineeStatus)
        {
            if(isset($NomineeStatus->nomineestatusid))
            {

                $result = $db->smartQuery(array(
                    'sql' => "
                  UPDATE `nomineestatus` 
                  SET   `nomineestatus` =:nomineestatus,         
                  WHERE `nomineestatusid`=:nomineestatusid",
                    'par' => array(
                        'nomineestatus'=>$NomineeStatus->nomineestatus,
                        'nomineestatusid'=>$NomineeStatus->nomineestatusid),
                    'ret' => 'result'
                ));
            }else
            {
                $result = $db->smartQuery(array(
                    'sql' => "INSERT INTO nomineestatus (nomineestatus)VALUES(:nomineestatus)",
                    'par' => array('nomineestatus'=>$NomineeStatus->nomineestatus),
                    'ret' => 'result'
                ));
            }
        }
        return $result;
    }

}