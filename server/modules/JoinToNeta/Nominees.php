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
                $result = $db->smartQuery(array(
                    'sql' => "INSERT INTO nominee (firstname,lastname,schoolid,email,phone,phoneparents,tz,birthday,netacityid,cityid,classid,hearaboutid,hearaboutother,SchoolOther,CityOther) VALUES (:firstname,:lastname,:schoolid,:email,:phone,:phoneparents,:tz,:birthday,:netacityid,:cityid,:classid,:hearaboutid,:hearaboutother,:SchoolOther,:CityOther)",
                    'par' => array(
                        'firstname'=>$user->firstname,
                        'lastname'=>$user->lastname,
                        'schoolid'=>$user->schoolid,
                        'email'=>$user->email,
                        'phone'=>$user->phone,
                       'phoneparents' =>$user->parentsphone,
                        'tz'=>$user->idnumber,
                        'birthday'=>$user->birthday,
                        'netacityid'=>$user->netacityid,
                        'cityid'=>$user->cityid,
                       'classid' =>$user->classid,
                       'hearaboutid' =>$user->hearaboutid,
                       'hearaboutother' =>$user->hearaboutother,
                       'SchoolOther' =>$user->schoolother,
                       'CityOther' =>$user->cityother
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

    function SearchNominees($search, $sorting, $desc, $page)
    {
        $sortByField='nomineeid';
        //permit only certain ORDER BY values to avoid injection
        in_array($sorting, array(
            'firstname', 'lastname',
            'tz', 'cityname', 'birthday', 'email', 'NetaCityName','SchoolName','hearabout'
        ), true)?$sortByField=$sorting:'';
        $sortingDirection = $desc?"DESC":"ASC";
        global $db;
        //fetch nominees
        $nominees = $db->smartQuery(array(
            'sql' => "
                SELECT nominee.*,class.classname AS 'classname' ,c.name AS 'cityname', IFNULL(g.CityName, nominee.CityOther) AS 'NetaCityName',IFNULL(s.schoolname,nominee.SchoolOther) AS 'SchoolName', hearabout.hearaboutoption AS 'hearabout'
				FROM nominee
				JOIN class ON class.classid=nominee.classid
				JOIN hearabout ON hearabout.hearaboutid=nominee.hearaboutid
				JOIN school as s ON s.schoolid = nominee.schoolid
				JOIN city as c ON c.cityid = nominee.cityid
				JOIN netacity as g ON g.CityId=nominee.netacityid
				WHERE
					  CONCAT(`firstname`,' ',`lastname`,' ',`tz`,' ',IFNULL(`birthday`, ''),' ',hearabout.hearaboutoption,' ',c.name,' ',`email`, ' ', IFNULL(g.CityName, nominee.CityOther),' ',IFNULL(s.schoolname,nominee.SchoolOther)) LIKE :search
			    ORDER BY ".$sortByField." ".$sortingDirection,
            'par' => array('search'=>'%'.$search.'%'),
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