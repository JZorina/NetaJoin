<?php
class City{
	
	function GetCityById($cid)
	{
		global $db;
		$city =  $db->smartQuery(array(
			'sql' => "Select * FROM city where cityid = :cityid",
			'par' => array('cityid'=>$cid),
			'ret' => 'fetch-assoc'
		));
		return $city;
	}
	
	function GetCities()
	{
		global $db;
		$Cities = $db->smartQuery(array(
			'sql' => "Select * FROM city Order By name",
			'par' => array(),
			'ret' => 'all'
		));
		return $Cities;
	}
	
	function AddCities($data)
	{
		global $db;
		foreach($data as $city)
		{
			if(isset($city->cityid))
			{
				$result = $db->smartQuery(array(
					'sql' => "UPDATE `city` 
                              SET `name`=:name, `IsShow`=:IsShow,`ArabicCityName`=:ArabicCityName
                              WHERE `cityid`=:cityid",
					'par' => array(
					    'name'=>$city->name,
                        'IsShow'=>$city->IsShow,
                        'ArabicCityName'=>$city->ArabicCityName,
                        'cityid'=>$city->cityid),
					'ret' => 'result'
				));
			}else
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO city (name,IsShow,ArabicCityName)VALUES(:name,:IsShow,:ArabicCityName)",
					'par' => array('name'=>$city->name, 'IsShow'=>$city->IsShow, 'ArabicCityName'=>$city->ArabicCityName),
					'ret' => 'result'
				));
			}
		}
		
		return $result;
	}





}