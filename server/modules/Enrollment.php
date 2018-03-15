<?php
class Enrollment{
	function GetEnrolledUsersIds($courseid)
	{
		global $db;
		$userids = $db->smartQuery(array(
		'sql' => "SELECT userid FROM `enrollment` WHERE courseid=:courseid",
		'par' => array('courseid' => $courseid),
		'ret' => 'all'
		));
		return array_column($userids, "userid");
	}
	/**
	 * Gets a list of search perimeters, and returns a list of staff according to said perimeters, filtered by
	 * whether or not the user who makes the request is authorized to view them,
	 * along side the number of pages filled by the full results set
	 * @param String $search - the search term to use
	 * @param String $sorting - based on which field to sort the results
	 * @param boolean $desc - whether to order the results in a descending order
	 * @param int $userstatus - which user status to filter by
	 * @param int $page - which page of the results to return
	 * @return results[]:
	 * {
	 * "students":
	 * [{"studentid","studentinfo"}],
	 * pages:208
	 * }
	 */
	function SearchUsersToEnroll($courseid, $search, $sorting, $desc, $page)
	{
		global $db;
		//fetch unenrolled students
		$unenrolled = $db->smartQuery(array(
				'sql' =>
				"SELECT u.userid, CONCAT(u.firstname, ' ', u.lastname) AS userinfo
				FROM `user_profile` AS u
				WHERE
					CONCAT(u.firstname, ' ', u.lastname) LIKE :search
					AND u.status = 1
					AND u.userid NOT IN(
						SELECT userid
						FROM enrollment
						WHERE courseid=:courseid
					)",
				'par' => array('courseid'=>$courseid, 'search'=>'%'.$search.'%'),
				'ret' => 'all'
		));
		return cutPage($unenrolled, 'users', $page);
	}
	function GetCourseEnrollmentProfiles($courseid, $roleid, $page, $search)
	{
		global $db;
		//fetch enrolled users
		$enrolled = $db->smartQuery(array(
				'sql' => "
				SELECT
					u.userid, u.firstname, u.lastname, u.firstnameinarabic, u.lastnameinarabic, u.tznumber, u.phone, u.birthday, user.email, u.address,
					u.genderid, u.religionid, u.cityid, city.name AS cityname,
					e.status
				FROM
				`user_profile` AS u
				JOIN `user` AS user ON user.userid = u.userid
				JOIN enrollment AS e ON e.userid = u.userid
				LEFT JOIN `city` AS city ON city.cityid = u.cityid
				WHERE
					e.courseid = :courseid
					AND e.enrollmentroleid = :roleid
					AND CONCAT(u.`firstname`,' ',u.`lastname`,' ',u.`firstnameinarabic`,' ',u.`lastnameinarabic`,' ',u.`tznumber`,' ',IFNULL(u.`phone`,''),' ',IFNULL(u.`birthday`, ''),' ',user.`email`) LIKE :search
					AND u.status=1
				ORDER BY u.firstname",
			'par' => array('courseid' => $courseid, 'search' => '%'.$search.'%', 'roleid' => $roleid),
			'ret' => 'all'
		));
		return cutPage($enrolled, 'enrolled', $page);
	}
	function EnrollUsers($userids, $courseid, $roleid)
	{
		$defaultStatus=1;
		$time = date("Y-m-d H:i:s");
		$existingStudents = $this -> GetEnrolledUsersIds($courseid);
		global $db;
		$params = array('courseid' => $courseid, 'time' => $time, 'status'=>$defaultStatus, 'roleid'=>$roleid);
		$sql = "INSERT INTO enrollment (`status`,`courseid`,`userid`, `laststatuschange`, enrollmentroleid) VALUES ";
		$newEnrollmentCount = 0;
		foreach ($userids AS $index=>$sid)
		{
			//don't insert a student who is already enrolled
			if(!in_array($sid, $existingStudents, true))
			{
				$newEnrollmentCount++;
				$sql.="(:status, :courseid, :userid".$index.", :time, :roleid)";
				//add a comma to seperate values, unless working on the last value
				$sql.=($index<count($userids)-1)?",":"";
				//add coresponding parameter to the array
				$params['userid'.$index]=$sid;
			}
		}
		if($newEnrollmentCount==0)
		{
			return (object)array("error"=>"the users are already enrolled");
		}
		//enroll users
		$users = $db->smartQuery(array(
				'sql' => $sql,
				'par' => $params,
				'ret' => 'result'
		));
		return $users;
	}
}