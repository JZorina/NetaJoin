<?php
/**
 * Created by PhpStorm.
 * User: yulia
 * Date: 3/21/2018
 * Time: 12:24 PM
 */

class NetaCity
{
    function GetNetaCities()
    {
        global $db;
        $Cities = $db->smartQuery(array(
            'sql' => "Select * FROM netacity Order By CityName",
            'par' => array(),
            'ret' => 'all'
        ));
        return $Cities;
    }


}