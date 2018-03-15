<?php
date_default_timezone_set ( "Asia/Jerusalem" );

class CheckStudent{
	
	function CheckIn($token, $lessonid, $status){
		global $db;
		global $AppUser;
		$date = date("Y-m-d H:i:s");
		$timestamp = strtotime($date);
		$userid = $AppUser->GetUserIdByToken($token);
		
		if(!isset($userid))
		{
			return (object)array("error" => "token not found");
		}
		$shouldUpdate = $db->smartQuery(array(
				'sql' => "(SELECT COUNT(checkstudentid) AS exist FROM `checkstudent` WHERE studentid=:studentid AND lessonid=:lessonid)",
				'par' => array( 'studentid' => $userid,'lessonid' => $lessonid),
				'ret' => 'fetch-assoc'
		));
		//if there is, update the relevant row
		if($shouldUpdate["exist"]>0)
		{
			$result = $db->smartQuery(array(
					'sql' => "UPDATE `checkstudent` SET `status`=:status, `checkin`=:checkin WHERE studentid=:studentid AND lessonid=:lessonid",
					'par' => array( 'studentid' => $userid,'lessonid' => $lessonid, 'checkin' => $timestamp, 'status'=>$status),
					'ret' => 'result'
			));
		}
		//else, insert a new row
		else {
			$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `checkstudent` (`studentid`,`lessonid`,`checkin`,`status`,`studentlessonstatus`) VALUES ( :studentid,:lessonid, :checkin, :status,'checkin');",
					'par' => array( 'studentid' => $userid,'lessonid' => $lessonid, 'checkin' => $timestamp, 'status'=>$status),
					'ret' => 'result'
			));
		}
		
		if($result==true)
		{
			return $result;
		}else
		{
			return (object)array("error" => "Student not exist");
		}
	}
	
	function CheckOut($type, $token, $lessonid, $FeedbackList){
		global $db;
		global $AppUser;
		$date = date("Y-m-d H:i:s");
		$timestamp = strtotime($date);
		$userid = $AppUser->GetUserIdByToken($token);
		
		if(!isset($userid))
		{
			return (object)array("error" => "Student not exist");
		}
		$result1 = $db->smartQuery(array(
			'sql' => "Update `checkstudent` set `checkout`=:checkout , `studentlessonstatus`='checkout' where `lessonid`=:lessonid AND studentid=:studentid",
			'par' =>array('checkout' => $timestamp, 'lessonid' => $lessonid, 'studentid' => $userid),
			'ret' => 'result1'
		));
		if($result1==true){
			$checkstudentid = $db->smartQuery(array(
				'sql' => "SELECT checkstudentid FROM checkstudent WHERE `lessonid`=:lessonid AND studentid=:studentid;",
				'par' =>array('lessonid' => $lessonid, 'studentid' => $userid),
				'ret' => 'fetch-assoc'
			));
			
			foreach($FeedbackList as $feedback1)
			{
				//TODO: update to a on duplicate key after fixing the questions mechanism
				$questiontype = isset($feedback1->questiontype) ?  $feedback1->questiontype : "";
				//check if feedback from this student on this question for this lesson already exists
				$shouldUpdate = $db->smartQuery(array(
						'sql' => "SELECT  COUNT(feedbackid) AS exist FROM `feedback` WHERE checkstudentid=:checkstudentid AND question=:question AND type=:type",
						'par' => array( 'checkstudentid' => $checkstudentid['checkstudentid'],'question' => $feedback1->question, 'type' => $type,),
						'ret' => 'fetch-assoc'
				));
				//if there is, update the relevant row
				if($shouldUpdate["exist"]>0)
				{
					$result = $db->smartQuery(array(
							'sql' => "UPDATE feedback SET `answer`=:answer WHERE checkstudentid=:checkstudentid AND question=:question AND type=:type",
							'par' => array( 'checkstudentid' => $checkstudentid['checkstudentid'],'question' => $feedback1->question, 'answer' => $feedback1->answer, 'type' => $type,),
							'ret' => 'result'
					));
				}
				//else, insert a new row
				else {
				$result = $db->smartQuery(array(
					'sql' => "
					INSERT INTO `feedback` (`checkstudentid`,`question`,`answer`,`type`,`questiontype`) VALUES (:checkstudentid,:question, :answer, :type, :questiontype);",
					'par' => array( 'checkstudentid' => $checkstudentid['checkstudentid'],'question' => $feedback1->question, 'answer' => $feedback1->answer, 'type' => $type, 'questiontype' => $questiontype),
					'ret' => 'result'
				));
				}
				if($result != true)
				{
					return (object)array("error"=>"error in FeedbackList");
				}
			}
		}
		else
		{
			return (object)array("error"=>"error in checkout");
		}
		return true;

	}
	
	function GetStudentsStatus($courseid, $lessonid){
		global $db;
		global $Course;
		$students = $Course->GetActiveStudentsInCourse($courseid);
		$studentstatus=array();
		foreach($students as $key=>$student)
		{
			$studentstatus[$key]['studentid'] = $student['studentid'];
			$studentstatus[$key]['firstname'] = $student['firstname'];
			$studentstatus[$key]['lastname'] = $student['lastname'];
			$studentstatus[$key]['image'] = $student['image'];
			$status = $db->smartQuery(array(
			'sql' => "SELECT status FROM `checkstudent` WHERE `studentid`=:studentid AND `lessonid`=:lessonid ",
			'par' => array( 'studentid' => $student['studentid'], 'lessonid'=>$lessonid),
			'ret' => 'fetch-assoc'
			));
			if(isset($status['status']) && $status['status'])
			{
				$studentstatus[$key]['status'] = $status['status'];
			}else
			{
				$studentstatus[$key]['status'] = "not checkin";
			}
		}
		return $studentstatus;
	}
	
	
	function UpdateStatus($data){
		$lessonid = $data->lessonid;
		$students = $data->students;
		$token = $data->token;
		global $db;
		global $AppUser;
		
		$UserId = $AppUser->GetUserIdByToken($token);
		$UserType = $AppUser->GetUserTypeById($UserId);
			
		if($UserType=='madrich')
		{
			$result = $db->smartQuery(array(
				'sql' => "UPDATE `lesson` SET `updatestudentstatus`='1' WHERE `lessonid`=:lessonid",
				'par' =>array('lessonid' => $lessonid),
				'ret' => 'result'
			));
		}
		
		$date = date("Y-m-d H:i:s");
		$timestamp = strtotime($date);
		
		foreach($students as $student)
		{
			$checkstudent = $db->smartQuery(array(
			'sql' => "SELECT * FROM `checkstudent` WHERE `studentid`=:studentid AND `lessonid`=:lessonid ",
			'par' => array( 'studentid' => $student->studentid, 'lessonid'=>$lessonid),
			'ret' => 'fetch-assoc'
			));
			
			if(isset($checkstudent['studentid']))
			{
				$result = $db->smartQuery(array(
					'sql' => "UPDATE `checkstudent` SET `status`=:status WHERE `studentid`=:studentid AND `lessonid`=:lessonid",
					'par' =>array('studentid' => $student->studentid, 'status' => $student->status, 'lessonid' => $lessonid),
					'ret' => 'result'
				));	
			}else
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `checkstudent` (`studentid`,`lessonid`,`status`,`checkin`, `studentlessonstatus`) VALUES ( :studentid,:lessonid, :status, :checkin, 'checkin');",
					'par' => array( 'studentid' => $student->studentid, 'lessonid' => $lessonid, 'checkin' => $timestamp, 'status' => $student->status),
					'ret' => 'result'
				));
			}
		}
		return $result;
	}
}