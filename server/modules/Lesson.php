<?php
date_default_timezone_set ( "Asia/Jerusalem" );

class Lesson{
	
	function GetLessonsOfCourse($courseid)
	{
		global $db;
		$lessons = $db->smartQuery(array(
			'sql' => "select lessonid,name,checkin from lesson where courseid=:courseid AND checkout<>''",// and not status='close'
			'par' => array('courseid' => $courseid),
			'ret' => 'all'
		));
		return $lessons;
	}
	
	function AddLesson($courseid,$name,$beginningdate,$comments,$notification, $type,$subjects)
	{
		global $db;
		global $Course;
		$status = "notactive";
		
		$lesson = $db->smartQuery(array(
			'sql' => "select * from lesson where courseid=:courseid and not status='close'",
			'par' => array('courseid' => $courseid),
			'ret' => 'all'
		));
		if(isset($lesson) && count($lesson)>0)
		{
			$result = $db->smartQuery(array(
			'sql' => "update lesson set name=:name, comments=:comments, beginningdate=:beginningdate, isnotification=:notification where courseid=:courseid and not status=:status",
			'par' => array( 'courseid' => $courseid, 'name' => $name, 'comments' => $comments, 'beginningdate' => $beginningdate, 'notification' => $notification,'status'=>'close'),
			'ret' => 'result'
			));
			//select lessonid from lesson where courseid=:courseid and not status=:status;
			$id = $db->smartQuery(array(
			'sql' => "select lessonid from lesson where courseid=:courseid and not status=:status",
			'par' => array( 'courseid' => $courseid,'status'=>'close'),
			'ret' => 'all'
			));
		}
		else
		{
			$createdate = date("Y-m-d H:i:s");
			$result = $db->smartQuery(array(
				'sql' => "INSERT INTO `lesson` (`courseid`,`name`,`comments`,`beginningdate`,`isnotification`,`type`,`status`,`createdate`) VALUES ( :courseid, :name, :comments, :beginningdate,:notification, :type, :status, :createdate);",
				'par' => array( 'courseid' => $courseid, 'name' => $name, 'comments' => $comments, 'beginningdate' => $beginningdate, 'notification' => $notification, 'type' => $type, 'status' => $status, 'createdate' => $createdate),
				'ret' => 'result'
			));
		}
		if($result==true)
		{
			$lid=$db->getLastInsertId();
			if($lid==0)
			{
				$lid = $id[0]['lessonid'];
			}
			$this->UpdateSubjectLesson($lid,$subjects,null);
			if($notification)
				$Course->sendNotificationToStudentOnMeetingCreation($courseid,$lid,$beginningdate, false);
			return (object)array("lessonid" => $lid);
		}else
		{
			return false;
		}
		
	}
	
	function UpdateLesson($lessonid,$courseid,$name,$beginningdate,$comments,$notification, $type,$subjects)
	{
		global $db;
		global $Course;
		$result = $db->smartQuery(array(
		'sql' => "update lesson set courseid=:courseid, name=:name, comments=:comments, beginningdate=:beginningdate, isnotification=:notification where lessonid=:lessonid ;",
			'par' => array( 'lessonid' => $lessonid, 'courseid' => $courseid, 'name' => $name, 'comments' => $comments, 'beginningdate' => $beginningdate, 'notification' => $notification),
			'ret' => 'result'
		));
		
		if($result==true)
		{
			$this->UpdateSubjectLesson($lessonid,$subjects,null);
			if($notification)
			{
				$Course->sendNotificationToStudentOnMeetingCreation($courseid,$lessonid,$beginningdate, true);
			}
			return (object)array("lessonid" => $lessonid);
		}else
		{
			return false;
		}
		
	}
	
	
	function UpdateSubjectLesson($lessonid,$subjects,$token)
	{
		global $db;
		global $AppUser;
		if(isset($token))
		{
			$UserId = $AppUser->GetUserIdByToken($token);
			$UserType = $AppUser->GetUserTypeById($UserId);
				
			if($UserType=='madrich')
			{
				$result = $db->smartQuery(array(
					'sql' => "Update `lesson` set `updatesubjectlesson`='1' where `lessonid`=:lessonid",
					'par' =>array('lessonid' => $lessonid),
					'ret' => 'result'
				));
			}
		}
		
		foreach($subjects as $subject)
		{
			if(isset($subject->isChecked))
			{
				$isChecked = $subject->isChecked;
			}else
			{
				$isChecked = false;
			}
			$result = $db->smartQuery(array(
				'sql' => "INSERT INTO `subjectstaught` (`subjectid`,`lessonid`,`subject`,`subjectinarabic`,`isChecked`) VALUES ( :subjectid, :lessonid, :subject,:subjectinarabic, :isChecked)
				ON DUPLICATE KEY 
				UPDATE subjectid=:subjectid,lessonid=:lessonid,subject=:subject,subjectinarabic=:subjectinarabic,isChecked=:isChecked
				;",
				'par' => array( 'subjectid' => $subject->subjectid, 'lessonid' => $lessonid, 'subject' => $subject->subject,'subjectinarabic' => $subject->subjectinarabic, 'isChecked' => $isChecked),
				'ret' => 'result'
			));
			if(isset($subject->subsubjects))
			{
				foreach($subject->subsubjects as $sub)
				{
					if(isset($sub->isChecked))
					{
						$isChecked = $sub->isChecked;
					}else
					{
						$isChecked = false;
					}
			
					$result = $db->smartQuery(array(
						'sql' => "INSERT INTO `subsubjecttaught` (`subjectid`,`lessonid`,`subsubjectid`,`subsubject`,`subsubjectinarabic`,`isChecked`) VALUES ( :subjectid,:lessonid, :subsubjectid, :subsubject,:subsubjectinarabic, :isChecked)
						ON DUPLICATE KEY 
						UPDATE subjectid=:subjectid,lessonid=:lessonid,subsubjectid=:subsubjectid,subsubject=:subsubject,subsubjectinarabic=:subsubjectinarabic,isChecked=:isChecked
						;",
						'par' => array( 'subjectid' => $subject->subjectid,'lessonid' => $lessonid, 'subsubjectid' => $sub->subsubjectid, 'subsubject' => $sub->subsubject,'subsubjectinarabic' => $sub->subsubjectinarabic, 'isChecked' => $isChecked),
						'ret' => 'result'
					));
				}
			
			}
		}
		return true;
	}
	
	function GetNumberOfLessonsByCourseId($courseid,$type)
	{
		global $db;
		$lessons = $db->smartQuery(array(
			'sql' => "Select count(*) as count FROM `lesson` Where `courseid`=:courseid AND type=:type",
			'par' => array( 'courseid' => $courseid,'type' => $type ),
			'ret' => 'fetch-assoc'
		));
		return (object)array("LessonCount"=>$lessons['count']);
	}
	
	function CheckIn($lessonid){
		global $Course;
		global $db;
		$status = "active";
		$courseid=$this -> getCourseIdByLessonId($lessonid);
		$date = date("Y-m-d H:i:s");
		$timestamp = strtotime($date);
		$CheckinExist =  $db->smartQuery(array(
			'sql' => "Select * FROM `lesson` Where `lessonid`=:lessonid AND checkout='' AND checkin!=''",
			'par' => array( 'lessonid' => $lessonid ),
			'ret' => 'fetch-assoc'
		));
		if(isset($CheckinExist) && $CheckinExist!="")
		{
			return (object)array("error" => "madrich was already checkin -> lessonid = ".$CheckinExist['lessonid']);
		}
		$result = $db->smartQuery(array(
			'sql' => "update `lesson` set checkin=:checkin, status=:status where lessonid=:lessonid",
			'par' => array( 'lessonid' => $lessonid, 'checkin' => $timestamp, 'status' => $status),
			'ret' => 'result'
		));
		$Course->sendNotificationToStudentOnMeetingActivation($courseid,$lessonid);
		return $result;
	}
	
	function CheckOut($lessonid){
		$date = date("Y-m-d H:i:s");
		$timestamp = strtotime($date);
		global $CheckStudent;
		global $db;
		
		$lesson =  $db->smartQuery(array(
			'sql' => "SELECT checkin,checkout,courseid FROM `lesson` Where `lessonid`=:lessonid",
			'par' => array( 'lessonid' => $lessonid ),
			'ret' => 'fetch-assoc'
		));
		
		if(!isset($lesson) || $lesson=="")
		{
			return (object)array("error" => "lesson did not exist");
		}
		if($lesson['checkin']=="")
		{
			return (object)array("error" => "Madrich did not checkin");
		}
		if($lesson['checkout']!="")
		{
			return (object)array("error" => "Madrich was already checkout");
		}
		$result = $db->smartQuery(array(
			'sql' => "UPDATE `lesson` SET `checkout`=:checkout,`status`='close' WHERE `lessonid`=:lessonid",
			'par' =>array('checkout' => $timestamp, 'lessonid' => $lessonid),
			'ret' => 'result'
		));
		if($result==true)
		{
			$course =  $db->smartQuery(array(
				'sql' => "Select madrichid,projectid FROM `course` Where `courseid`=:courseid",
				'par' => array( 'courseid' => $lesson['courseid'] ),
				'ret' => 'fetch-assoc'
			));
			
			$madrichid = $course['madrichid'];
			$courseid = $lesson['courseid'];
			$endtimestamp = $timestamp;
			$enddate = date('Y-m-d', $endtimestamp);
			
			if(isset($course['projectid']))
			{
				$projectid = $course['projectid'];
			}else
			{
				$projectid='';
			}
			
			$Actions = $db->smartQuery(array(
				'sql' => "Select subjectreportid FROM staffreportsubject where staffid=:staffid and status=:status and projectid=:projectid",
				'par' => array('staffid'=>$madrichid, 'status'=>true, 'projectid'=>$projectid),
				'ret' => 'all'
			));
			
			if(isset($Actions) && count($Actions)>0)
			{
				foreach($Actions as $Action)
				{
					
					$subject = $db->smartQuery(array(
						'sql' => "Select subject FROM subjectreport where subjectreportid=:subjectreportid and IsShow=:IsShow",
						'par' => array('subjectreportid'=>$Action['subjectreportid'], 'IsShow'=>true),
						'ret' => 'fetch-assoc'
					));
				
					if($subject['subject']=='הדרכה')
					{
						$actionid=$Action['subjectreportid'];
						break;
					}
				}
			}
			
			if(isset($actionid))
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `reportcopy` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`) VALUES ( :date, :staffid, :courseid, :actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status);",
					'par' => array( 'date' => $enddate, 'staffid' => $madrichid, 'courseid' => $courseid, 'actionid' => $actionid,'projectid' => $projectid, 'starthour' => null, 'finishhour' => null, 'carkm' => '', 'cost' => '', 'comment' => '', 'status' => ''),
					'ret' => 'result'
					));
			
				$rid=$db->getLastInsertId();
			
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `report` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`,`reportcopyid`,`automatic`) VALUES ( :date, :staffid, :courseid,:actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status, :reportcopyid, :automatic);",
					'par' => array( 'date' => $enddate, 'staffid' => $madrichid, 'courseid' => $courseid, 'actionid' => $actionid,'projectid' => $projectid, 'starthour' => null, 'finishhour' => null, 'carkm' => '', 'cost' => '', 'comment' => '', 'status' => '', 'reportcopyid' => $rid, 'automatic' => true),
					'ret' => 'result'
				));
			}else
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `reportcopy` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`) VALUES ( :date, :staffid, :courseid, :actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status);",
					'par' => array( 'date' => $enddate, 'staffid' => $madrichid, 'courseid' => $courseid, 'actionid' => 1,'projectid' => $projectid, 'starthour' => null, 'finishhour' => null, 'carkm' => '', 'cost' => '', 'comment' => '', 'status' => 'specialapproval'),
					'ret' => 'result'
					));
			
				$rid=$db->getLastInsertId();
			
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `report` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`,`reportcopyid`,`automatic`) VALUES ( :date, :staffid, :courseid,:actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status, :reportcopyid, :automatic);",
					'par' => array( 'date' => $enddate, 'staffid' => $madrichid, 'courseid' => $courseid, 'actionid' => 1,'projectid' => $projectid, 'starthour' => null, 'finishhour' => null, 'carkm' => '', 'cost' => '', 'comment' => '', 'status' => 'specialapproval', 'reportcopyid' => $rid, 'automatic' => true),
					'ret' => 'result'
				));
			}
		}
		return $result;
	}

	function GetOpenLessonByStudentToken($token){
		global $db;
		global $Course;
		global $AppUser;
		$studentid = $AppUser->GetUserIdByToken($token);
		$Coursesids = $Course->GetCoursesIdByStudentId($studentid);
		if(isset($Coursesids->error))
		{
			return $Coursesids;
		}
		foreach($Coursesids->ids as $courseid)
		{
			$lesson = $db->smartQuery(array(
				'sql' => "Select * FROM `lesson` Where `courseid`=:courseid and `checkout`='' and `checkin`<>''",
				'par' => array( 'courseid' => $courseid),
				'ret' => 'fetch-assoc'
			));
			if(isset($lesson['lessonid']))
			{
				$date = new DateTime();
				$StartLessonTimestamp = $lesson['checkin'];
				$date->setTimestamp($StartLessonTimestamp);
				$StartLesson = $date->format('Y-m-d H:i:s');
				
				$studentstatus = $db->smartQuery(array(
					'sql' => "Select studentlessonstatus FROM `checkstudent` Where `studentid`=:studentid and `lessonid`=:lessonid",
					'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $studentid ),
					'ret' => 'fetch-assoc'
				));
				
				$course = $db->smartQuery(array(
					'sql' => "Select * FROM `course` Where `courseid`=:courseid",
					'par' => array('courseid' => $courseid ),
					'ret' => 'fetch-assoc'
				));
				$syllabus = $Course->GetSyllabusSubjectsByCourseId($courseid);
				
				return (object)array("StartLesson"=>$StartLesson, "lesson" => $lesson, "studentlessonstatus"=>$studentstatus['studentlessonstatus'],"course" => $course,"syllabus" => $syllabus);
			}
		}
		$lesson = array();
		return (object)array("lesson" => $lesson);
	}
	
	function GetOpenOrNextLessonByCourseId($courseid,$token)
	{
		global $db;
		global $AppUser;
		
		$lesson = $db->smartQuery(array(
			'sql' => "Select * FROM `lesson` Where `courseid`=:courseid and `checkout`='' and `checkin`!=''",
			'par' => array( 'courseid' => $courseid),
			'ret' => 'fetch-assoc'
		));
		if(isset($lesson) && $lesson!=""){
			
			if(isset($token))
			{
				$studentid = $AppUser->GetUserIdByToken($token);
				$studentstatus = $db->smartQuery(array(
					'sql' => "Select studentlessonstatus FROM `checkstudent` Where `studentid`=:studentid and `lessonid`=:lessonid",
					'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $studentid ),
					'ret' => 'fetch-assoc'
				));
				if(isset($studentstatus['studentlessonstatus'])){
					$lesson['studentstatus'] = $studentstatus['studentlessonstatus'];
				}
			}
				
			return $lesson;
		}
		else
		{
			//$datenow = date("Y-m-d h:i:sa");
			$datenow = round(microtime(true) * 1000);
			
			$lesson = $db->smartQuery(array(
				'sql' => "Select * FROM `lesson` Where `courseid`=:courseid and `checkout`='' and `checkin`=''  order by beginningdate limit 1", //and beginningdate>:datenow
				'par' => array( 'courseid' => $courseid),//, 'datenow' => $datenow
				'ret' => 'fetch-assoc'
			));
			if(isset($lesson) && $lesson!="")
			{	
				if(isset($token))
				{
					$studentid = $AppUser->GetUserIdByToken($token);
					$studentstatus = $db->smartQuery(array(
						'sql' => "Select studentlessonstatus FROM `checkstudent` Where `studentid`=:studentid and `lessonid`=:lessonid",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $studentid ),
						'ret' => 'fetch-assoc'
					));
					
						if(isset($studentstatus['studentlessonstatus'])){
							$lesson['studentstatus'] = $studentstatus['studentlessonstatus'];
						}
				}
			
				return $lesson;
			}else
			{
				return array();
			}
		}
	}
	function GetSubjectLessonByLessonId($lessonid)
	{
		global $db;
		$subjectsarr = array();
		$subjects = $db->smartQuery(array(
			'sql' => "Select subjectid,subject,subjectinarabic as subjectinarabic ,isChecked FROM `subjectstaught` Where`lessonid`=:lessonid",
			'par' => array( 'lessonid' => $lessonid),
			'ret' => 'all'
		));
		foreach($subjects as $key=>$subject)
		{
			$subjectsarr[$key] = $subject;
			$subsubjects = $db->smartQuery(array(
			'sql' => "Select subsubjectid,subsubject,subsubjectinarabic as subsubjectinarabic, isChecked FROM `subsubjecttaught` Where`subjectid`=:subjectid AND `lessonid`=:lessonid",
			'par' => array( 'subjectid' => $subject['subjectid'], 'lessonid' => $lessonid),
			'ret' => 'all'
			));
			if(isset($subsubjects) && count($subsubjects)>0)
			{
				$subjectsarr[$key]["subsubjects"] = $subsubjects;
			}
		}
		
		return $subjectsarr;
	}
	
	function SetLessonFeedback($lessonid, $FeedbackList)
	{
		global $db;
		//TODO: update to a on duplicate key after fixing the questions mechanism
		foreach($FeedbackList as $feedback)
		{
			$questiontype = isset($feedback->questiontype) ?  $feedback->questiontype : "";
			//check if feedback from this student on this question for this lesson already exists
			$shouldUpdate = $db->smartQuery(array(
					'sql' => "SELECT COUNT(feedbackid) AS exist FROM `madrichfeedback` WHERE `lessonid`=:lessonid AND `question`=:question",
					'par' => array('lessonid' => $lessonid,'question' => $feedback->question),
					'ret' => 'fetch-assoc'
			));
			//if there is, update the relevant row
			if($shouldUpdate["exist"]>0)
			{
				$result = $db->smartQuery(array(
						'sql' => "UPDATE `madrichfeedback` SET `answer`=:answer WHERE `lessonid`=:lessonid AND `question`=:question",
						'par' => array( 'lessonid' => $lessonid,'question' => $feedback->question, 'answer' => $feedback->answer),
						'ret' => 'result'
				));
			}
			//else, insert a new row
			else {
				$result = $db->smartQuery(array(
						'sql' => "INSERT INTO `madrichfeedback` (`lessonid`,`question`,`answer`,`type`) VALUES ( :lessonid,:question, :answer,:type);",
						'par' => array( 'lessonid' => $lessonid,'question' => $feedback->question, 'answer' => $feedback->answer,'type' => $questiontype),
						'ret' => 'result'
				));
			}
			if($result != true)
			{
				return (object)array("error"=>"error in FeedbackList");
			}
		}
		return true;
	}
		
	function GetLessonStatusById($lessonid)
	{
		global $db;
		$status = $db->smartQuery(array(
			'sql' => "Select status FROM `lesson` Where`lessonid`=:lessonid",
			'par' => array( 'lessonid' => $lessonid),
			'ret' => 'fetch-assoc'
		));
		if(isset($status['status']) && $status['status']!="")
		{
			return (object)array("status" => $status['status']);
		}else
		{
			return false;
		}
	}
	
	function GetLessonById($lessonid,$token)
	{
		global $db;
		if(isset($token))
		{
			global $AppUser;
			$studentid = $AppUser->GetUserIdByToken($token);
			$studentstatus = $db->smartQuery(array(
					'sql' => "Select studentlessonstatus, status FROM `checkstudent` Where `studentid`=:studentid and `lessonid`=:lessonid",
					'par' => array( 'lessonid' => $lessonid,'studentid' => $studentid ),
					'ret' => 'fetch-assoc'
			));
		}
		$lesson = $db->smartQuery(array(
				'sql' => "Select * FROM `lesson` Where`lessonid`=:lessonid",
				'par' => array( 'lessonid' => $lessonid),
				'ret' => 'all'
		));
		if(isset($lesson[0]))
		{
			$courseid = $lesson[0]['courseid'];
			$course = $db->smartQuery(array(
					'sql' => "Select name,subname,subnameinarabic FROM `course` Where`courseid`=:courseid",
					'par' => array( 'courseid' => $courseid),
					'ret' => 'fetch-assoc'
			));
			
			$lesson[0]['coursename'] = $course['name'];
			$lesson[0]['coursesubname'] = $course['subname'];
			$lesson[0]['coursenameinarabic'] = $course['subnameinarabic'];
			if(isset($token))
			{
				if(isset($studentstatus['studentlessonstatus'])){
					$lesson[0]['studentstatus'] = $studentstatus['studentlessonstatus'];
					$lesson[0]['attendancestatus'] = $studentstatus['status'];
				}
			}
			
			$syllabus = $this->GetSubjectLessonByLessonId($lessonid);
			return (object)array("lesson"=> $lesson[0], "syllabus"=> $syllabus);
		}else
		{
			return array();
		}
	}
	function getCourseIdByLessonId ($lessonid)
	{
		global $db;
		$courseid = $db->smartQuery(array(
				'sql' => "SELECT courseid FROM `lesson` WHERE `lessonid`=:lessonid",
				'par' => array( 'lessonid' => $lessonid),
				'ret' => 'fetch-assoc'
		));
		if($courseid['courseid'])
			return $courseid['courseid'];
		else
			return null;
	}
	function GetUserFlowPosInLesson($lessonid, $token)
	{
		global $db;
		global $AppUser;
		$flowChecklist = array();
		$lesson = $db->smartQuery(array(
				'sql' => "SELECT lessonid, updatestudentstatus, updatesubjectlesson FROM `lesson` WHERE `lessonid`=:lessonid limit 1",
				'par' => array( 'lessonid' => $lessonid),
				'ret' => 'fetch-assoc'
		));
		if(isset($lesson['lessonid']))
		{
			$UserId = $AppUser->GetUserIdByToken($token);
			$UserType = $AppUser->GetUserTypeById($UserId);
			if($UserType=='madrich')
			{
				$madrichfeedback = $db->smartQuery(array(
						'sql' => "SELECT lessonid FROM `madrichfeedback` WHERE `lessonid`=:lessonid",
						'par' => array( 'lessonid' => $lesson['lessonid']),
						'ret' => 'fetch-assoc'
				));
				$flowChecklist['gaveFeedback'] = isset($madrichfeedback['lessonid'])?true:false;
				$flowChecklist['approvedAttendance'] = (isset($lesson['updatestudentstatus']) && $lesson['updatestudentstatus']=='1')?true:false;
				$flowChecklist['updatedSyllabusProgress'] = (isset($lesson['updatesubjectlesson']) && $lesson['updatesubjectlesson']=='1')?true:false;
				return $flowChecklist;
			}else if($UserType=='student')
			{
				$studentfeedbackspecific = $db->smartQuery(array(
						'sql' => "SELECT feedbackid FROM `feedback` JOIN checkstudent ON checkstudent.checkstudentid = feedback.checkstudentid WHERE `studentid`=:studentid AND `lessonid`=:lessonid AND feedback.type='specific'",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $UserId ),
						'ret' => 'fetch-assoc'
				));
				$flowChecklist['gaveSubjectsFeedback']= isset($studentfeedbackspecific['feedbackid'])?true:false;
				
				$studentfeedbackgeneral = $db->smartQuery(array(
						'sql' => "SELECT feedbackid FROM `feedback` JOIN checkstudent ON checkstudent.checkstudentid = feedback.checkstudentid WHERE `studentid`=:studentid AND `lessonid`=:lessonid AND feedback.type='general'",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $UserId ),
						'ret' => 'fetch-assoc'
				));
				$flowChecklist['gaveGeneralFeedback']= isset($studentfeedbackgeneral['feedbackid'])?true:false;
				$studentstatus = $db->smartQuery(array(
						'sql' => "SELECT status FROM checkstudent WHERE `studentid`=:studentid AND `lessonid`=:lessonid",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $UserId ),
						'ret' => 'fetch-assoc'
				));
				$flowChecklist['status'] = $studentstatus['status'];
			}
			return $flowChecklist;
		}
		else {
			return (object)array("error"=>"couldn't find a lesson with the requested id");
		}
	}
	
	//DEPRECATED - keeping for backwards compatibility only. delete this if this is the far future (>1.2.2018)
	function CheckIfLastLessonFeedBackOrUpdating($courseid,$token)
	{
		global $db;
		global $AppUser;
		$LastLessonFeedBack = array();
		$lesson = $db->smartQuery(array(
				'sql' => "Select lessonid,updatestudentstatus,updatesubjectlesson FROM `lesson` Where `courseid`=:courseid and `checkout`!='' and `checkin`!='' order by `checkout` DESC limit 1",
				'par' => array( 'courseid' => $courseid),
				'ret' => 'fetch-assoc'
		));
		
		if(isset($lesson['lessonid']))
		{
			$LastLessonFeedBack['lessonid'] = $lesson['lessonid'];
			
			$UserId = $AppUser->GetUserIdByToken($token);
			$UserType = $AppUser->GetUserTypeById($UserId);
			
			if($UserType=='madrich')
			{
				$madrichfeedback = $db->smartQuery(array(
						'sql' => "Select * FROM `madrichfeedback` Where `lessonid`=:lessonid",
						'par' => array( 'lessonid' => $lesson['lessonid']),
						'ret' => 'fetch-assoc'
				));
				
				if(isset($madrichfeedback['lessonid']))
				{
					$LastLessonFeedBack['feedback'] = true;
				}else
				{
					$LastLessonFeedBack['feedback'] = false;
				}
				if(isset($lesson['updatestudentstatus']) && $lesson['updatestudentstatus']=='1')
				{
					$LastLessonFeedBack['updatestudentstatus'] = true;
				}else
				{
					$LastLessonFeedBack['updatestudentstatus'] = false;
				}
				
				if(isset($lesson['updatesubjectlesson']) && $lesson['updatesubjectlesson']=='1')
				{
					$LastLessonFeedBack['updatesubjectlesson'] = true;
				}else
				{
					$LastLessonFeedBack['updatesubjectlesson'] = false;
				}
				
				return $LastLessonFeedBack;
				
			}else if($UserType=='student')
			{
				$studentfeedbackspecific = $db->smartQuery(array(
						'sql' => "Select feedbackid FROM `feedback` join checkstudent on checkstudent.checkstudentid = feedback.checkstudentid Where `studentid`=:studentid and `lessonid`=:lessonid and feedback.type='specific'",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $UserId ),
						'ret' => 'fetch-assoc'
				));
				
				if(isset($studentfeedbackspecific['feedbackid']))
				{
					$LastLessonFeedBack['studentfeedbackspecific'] = true;
				}else
				{
					$LastLessonFeedBack['studentfeedbackspecific'] = false;
				}
				
				$studentfeedbackgeneral = $db->smartQuery(array(
						'sql' => "Select feedbackid FROM `feedback` join checkstudent on checkstudent.checkstudentid = feedback.checkstudentid Where `studentid`=:studentid and `lessonid`=:lessonid and feedback.type='general'",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $UserId ),
						'ret' => 'fetch-assoc'
				));
				
				if(isset($studentfeedbackgeneral['feedbackid']))
				{
					$LastLessonFeedBack['studentfeedbackgeneral'] = true;
				}else
				{
					$LastLessonFeedBack['studentfeedbackgeneral'] = false;
				}
				
				$studentstatus = $db->smartQuery(array(
						'sql' => "Select status from checkstudent Where `studentid`=:studentid and `lessonid`=:lessonid",
						'par' => array( 'lessonid' => $lesson['lessonid'],'studentid' => $UserId ),
						'ret' => 'fetch-assoc'
				));
				
				$LastLessonFeedBack['status'] = $studentstatus['status'];
			}
		}
		
		return $LastLessonFeedBack;
	}
}

	