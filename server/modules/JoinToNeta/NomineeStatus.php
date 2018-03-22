
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
            'sql' => "Select * FROM nomineestatus ",
            'par' => array(),
            'ret' => 'all'
        ));
        return $Statuses;
    }
}