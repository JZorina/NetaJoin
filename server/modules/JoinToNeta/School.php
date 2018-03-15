<?php
class School
{

    function GetCityById($cid)
    {
        global $db;
        $city = $db->smartQuery(array(
            'sql' => "Select * FROM city where cityid = :cityid",
            'par' => array('cityid' => $cid),
            'ret' => 'fetch-assoc'
        ));
        return $city;
    }

    function GetSchools()
    {
        global $db;
        $Schools = $db->smartQuery(array(
            'sql' => "Select * FROM school Order By schoolname",
            'par' => array(),
            'ret' => 'all'
        ));
        return $Schools;
    }

    function AddCities($data)
    {
        global $db;
        foreach ($data as $city) {
            if (isset($city->cityid)) {
                $id = $city->cityid;
                $result = $db->smartQuery(array(
                    'sql' => "update city set name= :name, IsShow =:IsShow  where cityid=:id",
                    'par' => array('name' => $city->name, 'IsShow' => $city->IsShow, 'id' => $id),
                    'ret' => 'result'
                ));
            } else {
                $result = $db->smartQuery(array(
                    'sql' => "insert into city (name,IsShow)values(:name,:IsShow)",
                    'par' => array('name' => $city->name, 'IsShow' => $city->IsShow),
                    'ret' => 'result'
                ));
            }
        }

        return $result;
    }
}