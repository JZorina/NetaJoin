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

    function AddNetaCities($data)
    {
        global $db;
        foreach ($data as $netacity) {
            if (isset($netacity->CityId)) {
                $result = $db->smartQuery(array(
                    'sql' => "UPDATE `netacity` 
                              SET `CityName`=:CityName,`ArabicCityName`=:ArabicCityName
                              WHERE `CityId`=:CityId",
                    'par' => array(
                        'CityName' => $netacity->CityName,
                        'ArabicCityName' => $netacity->ArabicCityName,
                        'CityId' => $netacity->CityId),
                    'ret' => 'result'
                ));
            } else {
                $result = $db->smartQuery(array(
                    'sql' => "INSERT INTO netacity (CityName,ArabicCityName)VALUES(:CityName,:ArabicCityName)",
                    'par' => array('CityName' => $netacity->CityName, 'ArabicCityName' => $netacity->ArabicCityName),
                    'ret' => 'result'
                ));
            }
        }

        return $result;
    }
}


