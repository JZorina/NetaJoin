<?php
use Aws\Sns\MessageValidator\Message;

class notificationCron{
	
	function CheckActivationReminderNotification()
	{
		global $db;
		global $Course;
		//$Course -> getCourseNameByLessonId(1);
		$timeend = time() + 3600;// an hour from now
		//lessons that are about to start
		$lessons =  $db->smartQuery(array(
				'sql' => "SELECT ls.lessonid AS lessonid, ls.courseid AS courseid, ls.name AS name FROM lesson AS ls WHERE ls.checkin='' AND ls.beginningdate>:timeend AND sentActivationReminderNotification='0'",
				'par' => array('timeend'=>$timeend),
				'ret' => 'all'
		));
		
		if(count($lessons)>0)
		{
			$type = "activationReminder";
			$message = "לא לשכוח להפעיל אותו!";
			$result = true;
			foreach($lessons as $lesson)
			{
				$courseName = $Course->getCourseNameByLessonId($lesson["lessonid"]);
				$title= "המפגש שלך בקורס ".$courseName." יתחיל עוד שעה";
				$ans = $this->sendNotificationToTeacherByLesson($lesson, $type, $title, $message);
				if($ans==true)
				{
					$result =  $db->smartQuery(array(
							'sql' => "UPDATE lesson SET sentActivationReminderNotification=:sendingtime WHERE lessonid=:lessonid",
							'par' => array('lessonid'=>$lesson['lessonid'], 'sendingtime'=>time()),
							'ret' => 'result'
					));
				}
			}
			return $result;
		}
		return null;
	}
	
	function CheckClosingReminderNotification()
	{
		global $db;
		global $Course;
		$timeend = time() - 60000;// ten hourse ago
		//lessons that are active for more than 10 hours
		$lessons =  $db->smartQuery(array(
				'sql' => "SELECT ls.lessonid AS lessonid, ls.courseid AS courseid, ls.name AS name FROM lesson AS ls WHERE ls.checkin!='' AND ls.checkin<:timeend AND ls.checkout='' AND sentClosingReminderNotification='0'",
				'par' => array('timeend'=>$timeend),
				'ret' => 'all'
		));
		if(count($lessons)>0)
		{
			$type = "closingReminder";
			$message = "אם המפגש הסתיים, לא לשכוח לסגור אותו!";
			foreach($lessons as $lesson)
			{
			$course = $Course -> getCourseNameByLessonId($lesson["lessonid"]);
			$title = "האם המפגש שלך ב-".$course." כבר הסתיים?";
			$result = true;
				$ans = $this->sendNotificationToTeacherByLesson($lesson, $type, $title, $message);
				if($ans==true)
				{
					$result =  $db->smartQuery(array(
							'sql' => "UPDATE lesson SET sentClosingReminderNotification=:sendingtime WHERE lessonid=:lessonid",
							'par' => array('lessonid'=>$lesson['lessonid'], 'sendingtime'=>time()),
							'ret' => 'result'
					));
				}
			}
			return $result;
		}
		return null;
	}
	
	function CheckCheckoutNotification()
	{
		global $db;
		$timeend = time() - 14400; //4 hours ago
		
		$checkstudents =  $db->smartQuery(array(
				'sql' => "
					SELECT 
						cs.checkstudentid, cs.studentid, l.* 
					FROM checkstudent AS cs 
					JOIN lesson AS l 
						ON cs.lessonid = l.lessonid
					WHERE 
						(cs.checkin!='') 
						AND cs.checkout='' 
						AND l.checkin!='' 
						AND l.checkout!='' 
						AND l.checkout<:timeend 
						AND CheckoutNotification='0'",
				'par' => array('timeend'=>$timeend),
				'ret' => 'all'
		));
		
		if(count($checkstudents)>0)
		{
			$type = "checkout";
			$title = "נא לעשות צ'קאאוט בקורס";
			$message = "נשמח לשמוע איך היה לכם";
			$ans = $this->sendNotificationToStudentByCheckStudents($checkstudents, $type,$title, $message);
			if($ans==true)
			{
				foreach($checkstudents as $checkstudent)
				{
					$result =  $db->smartQuery(array(
							'sql' => "UPDATE checkstudent SET CheckoutNotification='1' WHERE checkstudentid=:checkstudentid",
							'par' => array('checkstudentid'=>$checkstudent['checkstudentid']),
							'ret' => 'result'
					));
				}
				
				return $result;
				
			}else
			{
				return false;
			}
		}else
		{
			return null;
		}
	}
	
	
	function CheckDashboardReminderNotification()
	{
		global $db;
		global $Statistic;
		//$maxTimeLimit = time() - 86400; //an day ago
		//$minTimeLimit = time() - 172800; //two days ago
		//lessons that were closed more than 24 hours ago (but not more than 48), and which's dashboards weren't looked at
		$lessons =  $db->smartQuery(array(
				'sql' => "SELECT ls.lessonid AS lessonid, ls.courseid AS courseid, ls.name AS name
				FROM lesson AS ls
				WHERE
					ls.usabilty LIKE ''
					AND ls.checkout<>''
					AND sentDashboardReminderNotification='0'",
				'par' => array(),
				'ret' => 'all'
		));
		
		if(count($lessons)>0)
		{
			$title = "הדאשבוארד שלך מוכן!";
			$message = "סקרו את הנתונים מהמפגש האחרון";
			$type = "dashboardReminder";
			$result = null;
			$cooking_threshold=0.6;
			//iterate through lessons
			foreach($lessons as $lesson)
			{
				$students = $Statistic->GetStudentsDetailsForLesson($lesson['lessonid']);
				//the number of students who have attended (late or not) the meeting
				$attendanceCount=0;
				//the number of students who have supplied any feedback about the meeting
				$feedbackCount=0;
				//iterate through students and aggregate feedback count and attendance count
				/*for($i=0; $i<count($students); $i++)
				{
					if($students[$i]['attendance']=='late'||$students[$i]['attendance']=='attendance')
					{
						$attendanceCount++;
					}
					if($students[$i]['givenFeedback']>0)
					{
						$feedbackCount++;
					}
				}*//*
				//check that enough data exists to generate dashboard (response rate > 60%)
				if($attendanceCount>0&&($feedbackCount)/$attendanceCount>=$cooking_threshold)
				{
					$ans = $this->sendNotificationToTeacherByLesson($lesson, $type, $title, $message);
					if($ans==true)
					{
						$result =  $db->smartQuery(array(
								'sql' => "UPDATE lesson SET sentDashboardReminderNotification=:sendingtime WHERE lessonid=:lessonid",
								'par' => array('lessonid'=>$lesson['lessonid'], 'sendingtime'=>time()),
								'ret' => 'result'
						));
					}
				}*/
			}
			return $result;
		}
		return null;
	}
	
	function sendNotificationToStudentByCheckStudents($checkstudents, $type,$title, $message)
	{
		global $db;
		global $FireBaseFCM;
		
		$tokens = array();
		foreach($checkstudents as $student)
		{
			$fbtoken = $db->smartQuery(array(
					'sql' => "SELECT fbtokenid FROM `appuser` WHERE `appuserid`=:appuserid",
					'par' => array( 'appuserid' => $student['studentid']),
					'ret' => 'fetch-assoc'
			));
			$tokens = array();
			$tokens[] = $fbtoken['fbtokenid'];
			if(isset($fbtoken['fbtokenid']) && $fbtoken['fbtokenid']!='')
			{
				$course = $db->smartQuery(array(
						'sql' => "SELECT subname FROM `course` WHERE`courseid`=:courseid",
						'par' => array( 'courseid' => $student['courseid']),
						'ret' => 'fetch-assoc'
				));
				
				$title= " $title ".$course['subname'];
				$lessonid = $student['lessonid'];
				$courseid = $student['courseid'];
				$FireBaseFCM->sendMessage($title,$message,$tokens,$courseid,$lessonid,$type);
			}
		}
		return true;
	}
	
	function sendNotificationToStudentBylessons($lessons,$type,$title, $message)
	{
		global $db;
		global $FireBaseFCM;
		
		foreach($lessons as $lesson)
		{
			$students = $db->smartQuery(array(
					'sql' => "SELECT studentid FROM `student_course` WHERE `courseid`=:courseid AND (statusincourse='1' OR statusincourse='פעיל' OR statusincourse='משתתף')",
					'par' => array( 'courseid' => $lesson['courseid']),
					'ret' => 'all'
			));
			
			
			$tokens = array();
			foreach($students as $student)
			{
				$fbtoken = $db->smartQuery(array(
						'sql' => "SELECT fbtokenid FROM `appuser` WHERE `appuserid`=:appuserid",
						'par' => array( 'appuserid' => $student['studentid']),
						'ret' => 'fetch-assoc'
				));
				if(isset($fbtoken['fbtokenid']) && $fbtoken['fbtokenid']!='')
				{
					$tokens[] = $fbtoken['fbtokenid'];
				}
			}
			
			$course = $db->smartQuery(array(
					'sql' => "SELECT subname FROM `course` WHERE`courseid`=:courseid",
					'par' => array( 'courseid' => $lesson['courseid']),
					'ret' => 'fetch-assoc'
			));
			
			$title= " $title ".$course['subname'];
			
			$lessonid = $lesson['lessonid'];
			$courseid = $lesson['courseid'];
			
			$FireBaseFCM->sendMessage($title,$message,$tokens,$courseid,$lessonid,$type);
			return true;
		}
	}
	function sendNotificationToTeacherByLesson($lesson, $type, $title, $message)
	{
		global $db;
		global $FireBaseFCM;
		$courseid = $lesson['courseid'];
		$lessonid = $lesson['lessonid'];
		$madrich = $db->smartQuery(array(
				'sql' => "SELECT madrichid FROM `course` WHERE`courseid`=:courseid AND status='active'",
				'par' => array( 'courseid' => $courseid),
				'ret' => 'fetch-assoc'
		));
		$tokens = array();
		$fbtoken = $db->smartQuery(array(
				'sql' => "SELECT fbtokenid FROM `appuser` WHERE `appuserid`=:appuserid",
				'par' => array( 'appuserid' => $madrich['madrichid']),
				'ret' => 'fetch-assoc'
		));
		if(isset($fbtoken['fbtokenid']) && $fbtoken['fbtokenid']!='')
		{
			$tokens[] = $fbtoken['fbtokenid'];
		}
		$FireBaseFCM->sendMessage($title,$message,$tokens,$courseid,$lessonid,$type);
		return true;
	}
}