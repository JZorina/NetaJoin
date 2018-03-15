<?php
class permissions{
	
	function IsPermission($token, $lessonid, $courseid,$type)
	{
		global $AppUser;
		if(isset($token)&&$token!=""){
			$user = $AppUser->getLoggedInUser($token);
			if(!is_object($user)&&isset($user['userid']))
			{
				if($user['isAdmin'])
					return true;
			}else
			{
				return (object)array("error" => "user not found"); 
			}
		}
		else{
			return (object)array("error" => "user not found");
		}
		return true;
	}
	
	function CheckMadrichLesson($madrichid, $lessonid)
	{
		global $db;
		$exist = $db->smartQuery(array(
			'sql' => "Select l.lessonid FROM `lesson` as l join course as c on c.courseid=l.courseid  Where l.lessonid=:lessonid and c.madrichid=:madrichid",
			'par' => array( 'lessonid' => $lessonid, 'madrichid' => $madrichid),
			'ret' => 'fetch-assoc'
		));
		
		if(isset($exist['lessonid']))
		{
			return true;
		}else
		{
			return false;
		}
	}
	
	function CheckMadrichCourse($madrichid, $courseid)
	{
		$mySubStaff = getAccessibleStaff($madrichid);
		array_push($mySubStaff, $madrichid);
		global $db;
		$params = array("courseid"=>$courseid);
		$sql = "
			SELECT c.courseid
			FROM course AS c
			WHERE c.madrichid IN (";
		foreach ($mySubStaff AS $index=>$staffid)
		{
			$sql.=":staffid".$index;
			//add a comma to seperate values, unless working on the last value
			$sql.=($index<count($mySubStaff)-1)?",":"";
			//add coresponding parameter to the array
			$params['staffid'.$index]=$staffid;
		}
		$sql.=")
			AND c.courseid=:courseid";
		//fetch courses
		$exist = $db->smartQuery(array(
			'sql' => $sql,
			'par' => $params,
			'ret' => 'fetch-assoc'
		));
		if(isset($exist['courseid']))
		{
			return true;
		}else
		{
			return false;
		}	
	}
	
	function CheckStudentLesson($studentid, $lessonid)
	{
		global $db;
		$exist = $db->smartQuery(array(
			'sql' => "Select l.lessonid FROM `lesson` as l join course as c on c.courseid=l.courseid join student_course as s_c on s_c.courseid=c.courseid  Where l.lessonid=:lessonid and s_c.studentid=:studentid AND s_c.statusincourse <> 3",
			'par' => array( 'lessonid' => $lessonid, 'studentid' => $studentid),
			'ret' => 'fetch-assoc'
		));
		
		if(isset($exist['lessonid']))
		{
			return true;
		}else
		{
			return false;
		}
	}
	
	function CheckStudentCourse($studentid, $courseid)
	{
		global $db;
		$exist = $db->smartQuery(array(
			'sql' => "Select * FROM student_course as s_c where s_c.studentid=:studentid and s_c.courseid=:courseid AND s_c.statusincourse <> 3",
			'par' => array( 'courseid' => $courseid, 'studentid' => $studentid),
			'ret' => 'fetch-assoc'
		));
		
		if(isset($exist['courseid']))
		{
			return true;
		}else
		{
			return false;
		}
	}
	
}