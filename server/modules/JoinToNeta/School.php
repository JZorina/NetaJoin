<?php
class School
{

    function GetSchoolsByNetaCityId($ncid)
    {
        global $db;
        $schools = $db->smartQuery(array(
            'sql' => "Select * FROM school where CityId = :cityid",
            'par' => array('cityid' => $ncid),
            'ret' => 'all'
        ));
        return $schools;
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