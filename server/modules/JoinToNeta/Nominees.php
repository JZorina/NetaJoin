<?php
/**
 * Created by PhpStorm.
 * User: yulia
 * Date: 3/21/2018
 * Time: 03:02 PM
 */

class Nominees
{
	function AddNominee($user)
	{
		global $db;
        $createdate = date("Y-m-d H:i:s");
		$result = $db->smartQuery(array(
			'sql' => "INSERT INTO nominee(
                firstname,lastname,schoolid,neighborhood,email,phone,phoneparents,birthday,netacityid,cityid,classid,hearaboutid,hearaboutother,SchoolOther,CityOther,genderid, RegistrationDate,firstnameinarabic,lastnameinarabic   
                ) VALUES (
                :firstname,:lastname,:schoolid,:neighborhood,:email,:phone,:phoneparents,:birthday,:netacityid,:cityid,:classid,:hearaboutid,:hearaboutother,:SchoolOther,:CityOther,:genderid, :RegistrationDate,:firstnameinarabic,:lastnameinarabic)",
			'par' => array(
				'firstname'=>$user->firstname,
				'lastname'=>$user->lastname,
				'schoolid'=>$user->schoolid,
			    'neighborhood'=>$user->neighborhood,
				'email'=>$user->email,
				'phone'=>$user->phone,
				'phoneparents' =>$user->parentsphone,
				'birthday'=>$user->birthday,
				'netacityid'=>$user->netacityid,
				'cityid'=>$user->cityid,
				'classid' =>$user->classid,
				'hearaboutid' =>$user->hearaboutid,
				'hearaboutother' =>$user->hearaboutother,
				'SchoolOther' =>$user->schoolother,
				'CityOther' =>$user->cityother,
                'genderid'=>$user->genderid,
                'RegistrationDate'=>$createdate,
                'firstnameinarabic'=>$user->firstnameinarabic,
                'lastnameinarabic'=>$user->lastnameinarabic
			),
			'ret' => 'result'
		));
		return $result;
	}

	function GetNominees()
	{
		global $db;
		$Nominees = $db->smartQuery(array(
			'sql' => "Select * FROM nominee Order By netacityid",
			'par' => array(),
			'ret' => 'all'
		));
		return $Nominees;
	}

	function SearchNominees($search, $sorting, $desc, $page,$netacityfilter, $statusfilter)
	{
		$sortByField='nomineeid';
		//permit only certain ORDER BY values to avoid injection
		in_array($sorting, array(
			'firstname', 'lastname',
			 'CityName', 'birthday', 'email', 'netacityname','SchoolName','hearabout'
		), true)?$sortByField=$sorting:'';
		$sortingDirection = $desc?"DESC":"ASC";
		global $db;
		//fetch nominees
		$nominees = $db->smartQuery(array(
			'sql' => "
				SELECT nominee.*,gender.name AS 'gender' ,class.classname AS 'classname' ,n.CityName AS 'netacityname', IFNULL(city.name, nominee.CityOther) AS 'CityName', IFNULL(s.schoolname, nominee.SchoolOther) AS 'SchoolName', IFNULL(hearabout.hearaboutoption, nominee.hearaboutother) AS 'hearabout', nominee.phoneparents  AS 'Parentphone'
				FROM nominee
				LEFT JOIN gender ON gender.genderid=nominee.genderid
				LEFT JOIN class ON class.classid=nominee.classid
				LEFT JOIN hearabout ON hearabout.hearaboutid=nominee.hearaboutid
				LEFT JOIN school as s ON s.schoolid = nominee.schoolid
				LEFT JOIN city  ON city.cityid = nominee.cityid
				LEFT JOIN netacity  AS n ON n.CityId=nominee.netacityid
				WHERE
					  CONCAT(`firstname`,' ',`lastname`,' ',n.CityName,' ',`email`, ' ',IFNULL(hearabout.hearaboutoption, nominee.hearaboutother), ' ',  IFNULL(city.name, nominee.CityOther),' ',IFNULL(s.schoolname,nominee.SchoolOther) ) LIKE :search 
					  AND (nominee.netacityid =:netacityfilter OR :netacityfilter IS NULL)
					  AND (nominee.nomineestatusid=:statusfilter OR :statusfilter IS NULL)
				ORDER BY ".$sortByField." ".$sortingDirection,
			'par' => array('search'=>'%'.$search.'%', 'netacityfilter'=>$netacityfilter,'statusfilter'=>$statusfilter),
			'ret' => 'all'
		));
		return cutPage($nominees, 'nominees', $page);
	}

	function UpdateNomineeStatus($NomineeId, $StatusId)
	{
		global $db;
		$result=$db->smartQuery(array(
			'sql' => "UPDATE `nominee` SET `nomineestatusid`=:nomineestatusid WHERE `nomineeid`=:nomineeid",
			'par' => array('nomineestatusid' => $StatusId, 'nomineeid' => $NomineeId),
			'ret' => 'result'
		));
		return true;
	}
}