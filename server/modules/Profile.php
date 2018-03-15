<?php
class Profile{
	
	function GetProfile()
	{
		global $db;
		global $myid;
		
		$staff = $db->smartQuery(array(
			'sql' => "Select * FROM `staff` Where staffid=:staffid",
			'par' => array('staffid'=>$myid),
			'ret' => 'fetch-assoc'
		));
		
		if(isset($staff)){
				if(isset($staff['superstaffid']) && $staff['superstaffid']!="" && $staff['superstaffid']!='0')
				{
					$superstaff = $db->smartQuery(array(
						'sql' => "Select * FROM `staff` Where staffid=:superstaffid",
						'par' => array('superstaffid'=>$staff['superstaffid']),
						'ret' => 'fetch-assoc'
					));
					$staff['superstaff'] = $superstaff['firstname'].' '.$superstaff['lastname'];
				}
				
				if(isset($staff['genderid']) && $staff['genderid']!="")
				{
					$gender = $db->smartQuery(array(
								'sql' => "Select * FROM `gender` Where genderid = :genderid",
								'par' => array('genderid'=>$staff['genderid']),
								'ret' => 'fetch-assoc'));
								
					$staff['gendername'] = $gender['name'];		
								
				}
				if(isset($staff['religionid']) && $staff['religionid']!="")
				{
					$religion = $db->smartQuery(array(
								'sql' => "Select * FROM `religion` Where religionid = :religionid",
								'par' => array('religionid'=>$staff['religionid']),
								'ret' => 'fetch-assoc'));
								
					$staff['religionname'] = $religion['name'];
				}
				/*$password = $db->smartQuery(array(
								'sql' => "Select password FROM `appuser` Where appuserid = :appuserid",
								'par' => array('appuserid'=>$staff['staffid']),
								'ret' => 'fetch-assoc'));
								
					$staff['password'] = $password['password'];*/
					$staff['password'] = '***';
					
				$languagesid = $db->smartQuery(array(
				'sql' => "Select languageid FROM staff_language where staffid=:staffid",
				'par' => array('staffid'=>$staff['staffid']),
				'ret' => 'all'
				));
				if(isset($languagesid) && count($languagesid)>0)
				{
					$lang = "";
					foreach($languagesid as $languageid)
					{
						$language = $db->smartQuery(array(
						'sql' => "Select name FROM language where languageid=:languageid",
						'par' => array('languageid'=>$languageid['languageid']),
						'ret' => 'fetch-assoc'
						));
					$staff['languages'][] = $languageid['languageid'];
					$lang = $lang . $language['name'] . ', ';
					
					}
					$lang = rtrim($lang, ", "); 
					$staff['languagesname'] = $lang;
				}
			
		}
		return $staff;
	}
	
	function GetMyStaffs()
	{
		global $mySubStaff;
		global $Staff;
		
		$staffs = array();
		foreach($mySubStaff as $MyStaff)
		{
			$staffs[] = $Staff->GetStaffById($MyStaff);
		}
		
		return $staffs;
	}
	
	
}