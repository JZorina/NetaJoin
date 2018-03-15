<?php
class Course{
	function AddCourse($course)
	{
		global $db;
		$madrichid = $course->madrichid;
		$fullcoursename = $course->name;
		$shortcoursenamear = $course->subnameinarabic;
		$shortcoursenamehe = $course->subname;
		$cityid = $course->cityid;
		$projectid = $course->projectid;
		$yearbudgetid = $course->yearbudgetid;
		$status = $course->status;
		$tags = $course->tags;
		$syllabus = $course->subjects;

		$code = $this->GetCodeForCourse();

		$result = $db->smartQuery(array(
			'sql' => "INSERT INTO `course` (`madrichid`, `name`, `subname`, `subnameinarabic`, `cityid`, `code`, `projectid`, `yearbudgetid`, `status`) VALUES (:madrichid, :name, :subname, :subnameinarabic, :cityid, :code, :projectid, :yearbudgetid, :status);",
			'par' => array('madrichid' => $madrichid, 'name' => $fullcoursename, 'subname' => $shortcoursenamehe, 'subnameinarabic'=>$shortcoursenamear, 'cityid' => $cityid, 'code' => $code, 'projectid'=>$projectid, 'yearbudgetid'=>$yearbudgetid, 'status' => $status),
			'ret' => 'result'
		));
		$courseid = $db->getLastInsertId();

		if($tags!='')
		{
			foreach($tags as $tag)
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `tagproject_course` (`courseid`, `tagprojectid`) VALUES (:courseid, :tagprojectid);",
					'par' => array('courseid' => $courseid, 'tagprojectid' => $tag),
					'ret' => 'result'
				));
			}
		}
		$this->AddSyllabus($syllabus,$courseid);
		return (object)array("courseid"=>$courseid);
	}
	function UpdateCourse($course)
	{
		global $db;
		$courseid = $course->courseid;
		$madrichid = $course->madrichid;
		$fullcoursename = $course->name;
		$shortcoursenamear = $course->subnameinarabic;
		$shortcoursenamehe = $course->subname;
		$cityid = $course->cityid;
		$projectid = $course->projectid;
		$yearbudgetid = $course->yearbudgetid;
		$status = $course->status;
		$tags = $course->tags;
		$syllabus = $course->subjects;
		
		$result = $db->smartQuery(array(
			'sql' => "DELETE FROM `tagproject_course` WHERE courseid=:courseid;",
			'par' => array('courseid' => $courseid),
			'ret' => 'result'
		));
		
		$result = $db->smartQuery(array(
			'sql' => "UPDATE `course` SET `madrichid`=:madrichid, `subnameinarabic`=:subnameinarabic, `name`=:name, `subname`=:subname, `cityid`=:cityid, `projectid`=:projectid, `yearbudgetid`=:yearbudgetid, `status`=:status WHERE `courseid`=:courseid",
			'par' => array('madrichid' => $madrichid, 'name' => $fullcoursename, 'subname' => $shortcoursenamehe, 'subnameinarabic' => $shortcoursenamear, 'cityid' => $cityid, 'projectid'=>$projectid, 'yearbudgetid'=>$yearbudgetid, 'status' => $status, 'courseid'=>$courseid),
			'ret' => 'result'
		));

		if($tags!='')
		{
			foreach($tags as $tag)
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `tagproject_course` (`courseid`, `tagprojectid`) VALUES (:courseid, :tagprojectid);",
					'par' => array('courseid' => $courseid, 'tagprojectid' => $tag),
					'ret' => 'result'
				));
			}
		}
		$this->AddSyllabus($syllabus,$courseid);
		return (object)array("courseid"=>$courseid);
	}
	function GetCodeForCourse()
	{
		 global $db;
		 $codeLength=4;
		 $maxTries=20;
		 $proposedCode = "";
		 $chars1 = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		 for ($uniqueGenerationAttempt = 0; $uniqueGenerationAttempt<= $maxTries; $uniqueGenerationAttempt++) {
		 	$proposedCode="";
		 	for ($charIndex = 0; strlen($proposedCode)<$codeLength; $charIndex++)
		 	{
		 		$index = rand(0, count($chars1)-1);
		 		$proposedCode.=$chars1[$index];
		 	}
		 	$alreadyUsed =  $db->smartQuery(array(
		 			'sql' => "SELECT courseid FROM course WHERE code=:code",
		 			'par' => array('code'=>$proposedCode),
		 			'ret' => 'fetch-assoc'
		 	));
		 	if(!isset($alreadyUsed['courseid']))
		 	{
		 		return $proposedCode;
		 	}
		 }
		 return (object)array("error"=>"too many tries before an unused course code was found");
	}
	function AddSyllabus($syllabus,$courseid)
	{
		global $db;
		foreach($syllabus as $subject)
		{
			if(!isset($subject->subjectid))
			{
				$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `subject` (`courseid`, `subject`,`subjectinarabic`) VALUES (:courseid, :subject, :subjectinarabic);",
					'par' => array('courseid' => $courseid, 'subject' => $subject->subject, 'subjectinarabic' => $subject->subjectinarabic),
					'ret' => 'result'
				));
				$subjectid=$db->getLastInsertId();
				foreach($subject->subsubjects as $subsubject)
				{
					$result = $db->smartQuery(array(
						'sql' => "INSERT INTO `subsubject` (`subjectid`, `subsubject`,`subsubjectinarabic`) VALUES (:subjectid, :subsubject,:subsubjectinarabic)",
						'par' => array('subjectid' => $subjectid, 'subsubject' => $subsubject->subsubject, 'subsubjectinarabic' => $subsubject->subsubjectinarabic),
						'ret' => 'result'
					));
				}
			}
			else
			{
				$result = $db->smartQuery(array(
					'sql' => "UPDATE `subject` AS s SET s.subject=:subject, s.subjectinarabic=:subjectinarabic WHERE s.subjectid=:subjectid",
					'par' => array('subjectid' => $subject->subjectid, 'subject' => $subject->subject, 'subjectinarabic' => $subject->subjectinarabic),
					'ret' => 'result'
				));
				foreach($subject->subsubjects as $subsubject)
				{
					if(!isset($subsubject->subsubjectid))
					{
						$result = $db->smartQuery(array(
							'sql' => "INSERT INTO `subsubject` (`subjectid`, `subsubject`,`subsubjectinarabic`) VALUES (:subjectid, :subsubject,:subsubjectinarabic)",
							'par' => array('subjectid' => $subject->subjectid, 'subsubject' => $subsubject->subsubject, 'subsubjectinarabic' => $subsubject->subsubjectinarabic),
							'ret' => 'result'
						));
					}else
					{
						$result = $db->smartQuery(array(
							'sql' => "UPDATE `subsubject` SET subjectid=:subjectid, subsubject=:subsubject, subsubjectinarabic=:subsubjectinarabic WHERE subsubjectid=:subsubjectid ",
							'par' => array('subjectid' => $subject->subjectid, 'subsubject' => $subsubject->subsubject, 'subsubjectinarabic' => $subsubject->subsubjectinarabic, 'subsubjectid' => $subsubject->subsubjectid),
							'ret' => 'result'
						));
					}
				}
			}
		}
	}
	function DeleteCourse($courseid)
	{
		global $db;
		$subjects = $db->smartQuery(array(
			'sql' => "SELECT * FROM `subject` WHERE courseid=:courseid;",
			'par' => array('courseid' => $courseid),
			'ret' => 'fetch-all'
		));
		foreach($subjects as $subject)
		{
			$result = $db->smartQuery(array(
				'sql' => "DELETE FROM `subsubject` WHERE subjectid=:subjectid;",
				'par' => array('subjectid' => $subject['subjectid']),
					'ret' => 'result'
			));
		}
		$result = $db->smartQuery(array(
			'sql' => "DELETE FROM `subject` WHERE courseid=:courseid;",
			'par' => array('courseid' => $courseid),
			'ret' => 'result'
		));		
		$result = $db->smartQuery(array(
			'sql' => "DELETE FROM `tagproject_course` WHERE courseid=:courseid;",
			'par' => array('courseid' => $courseid),
			'ret' => 'result'
		));	
		$result = $db->smartQuery(array(
			'sql' => "DELETE FROM `course` WHERE courseid=:courseid;",
			'par' => array('courseid' => $courseid),
			'ret' => 'result'
			));	
		return $result;
	}
	function GetAccessibleCourses()
	{
		global $db;
		global $me;
		global $myid;
		
		if($me['isAdmin'])
		{
			$courses = $db->smartQuery(array(
				'sql' => "SELECT courseid FROM `course`",
				'par' => array(),
				'ret' => 'all'
			));
		}
		else
		{
			global $mySubStaff;
			array_push($mySubStaff, $myid);
			$params = array();
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
			$sql.=")";
			//fetch courses
			$courses = $db->smartQuery(array(
				'sql' => $sql,
				'par' => $params,
				'ret' => 'all'
			));
		}
		//return indexed array (lose 'courseid' key wrap): [{'courseid':0},{'courseid':1}...]->[0, 1...]
		return array_column($courses, "courseid");
	}
	/**
		 * Gets a list of search perimeters, and returns a list of students according to said perimeters, filtered by
		 * whether or not the user who makes the request is authorized to view them,
		 * along side the number of pages filled by the full results set
		 * @param String $search - the search term to use
		 * @param String $sorting - based on which field to sort the results
		 * @param boolean $desc - whether to order the results in a descending order
		 * @param int $coursestatus - which user status to filter by
		 * @param int $page - which page of the results to return
		 * @return results[]:
		 * {
		 * "courses":
		 * [{
		 * "studentid,
		 * "firstname","lastname",
		 * "firstnameinarabic","lastnameinarabic",
		 * "tznumber","phone",
		 * "birthday",
		 * "email",
		 * "cityname","gendername","religionname"
		 * }],
		 * pages:208
		 * }
	 */
	function SearchCourses($search, $sorting, $desc, $coursestatus, $page)
	{
		global $db;
		$sortByField='courseid';
		//permit only certain ORDER BY values to avoid injection
		in_array($sorting, array(
				'firstname', 'lastname', 'firstnameinarabic', 'lastnameinarabic',
				'tznumber', 'phone', 'birthday', 'email', 'cityname'
		), true)?$sortByField=$sorting:'';
		$sortingDirection = $desc?"DESC":"ASC";
		$coursesids = $this->GetAccessibleCourses();
		if(count($coursesids)==0)
		{
			$ans = array('courses'=>array(), 'pages'=>0);
			return $ans;
		}
		//construct a query template which includes all of the student ids
		//and populate the parameter array with the ids themselves
		$params = array('status'=>$coursestatus, 'search'=>'%'.$search.'%');
		$sql =
		"SELECT
			c.courseid, c.code,
			c.name, c.subname, c.subnameinarabic,
			city.name AS cityname,
			year.year AS year, project.name AS project,
			COUNT(enrollments.userid) AS studentnum
		FROM `course` AS c
		LEFT JOIN city AS city ON city.cityid = c.cityid
		LEFT JOIN project AS project ON project.projectid = c.projectid
		LEFT JOIN yearbudget AS year ON year.yearbudgetid = c.yearbudgetid
		LEFT JOIN enrollment AS enrollments ON enrollments.courseid = c.courseid
		WHERE
			c.status=:status
			AND c.courseid IN (";
		foreach ($coursesids AS $index=>$cid)
		{
			$sql.=":courseid".$index;
			//add a comma to seperate values, unless working on the last value
			$sql.=($index<count($coursesids)-1)?",":"";
			//add coresponding parameter to the array
			$params['courseid'.$index]=$cid;
		}
		$sql.=")
			AND CONCAT(c.code,' ',c.name,' ',c.subname,' ',c.subnameinarabic,' ',city.name,' ',project.name) LIKE :search
		GROUP BY c.courseid
		ORDER BY c.".$sortByField." ".$sortingDirection;
		//fetch students
		$courses = $db->smartQuery(array(
				'sql' => $sql,
				'par' => $params,
				'ret' => 'all'
		));
		return cutPage($courses, 'courses', $page);
	}
	function GetCourseById($courseid)
	{
		global $db;
		global $Staff;
		global $Lesson;
		$course = $db->smartQuery(array(
				'sql' => "
				SELECT * FROM course
				WHERE courseid = :courseid",
				'par' => array('courseid'=>$courseid),
				'ret' => 'fetch-assoc'
		));
		if (!isset($course["courseid"]))
		{
			return (object)array("error"=>"course id not found"); 
		}
		$course["tags"] = $this->GetTagsByCourseId($courseid);
		$course["subjects"] = $this->GetSyllabusSubjectsByCourseId($courseid);
		return $course;
	}
	function GetTagsByCourseId($courseid)
	{
		global $db;
		$tags =  $db->smartQuery(array(
			'sql' => "SELECT projecttagid FROM course_projecttags WHERE courseid = :courseid",
			'par' => array('courseid'=>$courseid),
			'ret' => 'fetch-all'
		));
		$tags = array_column($tags, "projecttagid");
		return $tags;
	}
	function GetSyllabusSubjectsByCourseId($courseid)
	{
		global $db;
		$subjectsarr = array();
		$subjects = $db->smartQuery(array(
			'sql' => "
			SELECT
				subjectid, subject, subjectinarabic, supersubjectid
			FROM subject
			WHERE courseid=:courseid",
			'par' => array('courseid' => $courseid),
			'ret' => 'all'
		));
		return arrayToTrees($subjects, 'subjectid','supersubjectid', 'subsubjects');
	}
	/**
	 * returns basic data that has to be displayed in the course page in the app: course name in both he and ar, instructor name, and my classroom notifications 
	 * @param int $courseid - the unique id of the course
	 * @return array {code:"I4G2", name:"some course name 2017 ramala", subname:"some course name", subnameinarabic:"self explanatory",
	 * madrichname:"ploni shemtov", myClassroomNotificationCount:3}
	 */
	function GetCourseDataById($courseid)
	{
		global $db;
		global $Staff;
		global $Statistic;
		$coursearray = $db->smartQuery(array(
				'sql' => "SELECT courseid, code, name, subname, subnameinarabic, madrichid FROM `course` WHERE `courseid` = :courseid ",
				'par' => array('courseid'=>$courseid),
				'ret' => 'all'
		));
		//if a course with the supplied id is found
		if(isset($coursearray[0]) && isset($coursearray[0]['courseid']))
		{
			//create a course object, and set its content to the first of the courses with the coressponding id (should be only 1 anyway)
			$course = $coursearray[0];
			//get all data about the staff
			$madrich = $Staff->GetStaffById($course['madrichid']);
			//concatenate the madrich first and last name field to get his full name (in hebrew only, for now)
			$course['madrichname'] = $madrich['firstname'].' '.$madrich['lastname'];
			//discard the madrich id field - it's not needed anymore
			unset($course['madrichid']);
			//get the number of students who had either a low attendance streak of 2 or more
			//or a a low understanding streak of 2 or more
			$course['nStudentAlerts'] = 0;//$Statistic->GetStudentStatsSummary($courseid);
		}else
		{
			return (object)array("error"=>"course id not found");
		}
		return $course;
	}
	/**
	 * returns frequently updated data about the course - the lessonid of the next lesson, and the lesson id and user status of the last/current lesson
	 * @param int $courseid - the unique id of the course
	 * @param String $token - the unique user token for the session
	 * @return array {nextlesson: 13, lastlesson: {lessonid:12, closed:true, status:{gaveFeedback:true, approvedAttendance:false...}}
	 */
	function GetUserFlowPosInCourse($courseid, $token)
	{
		global $db;
		global $Lesson;
		$course = array();
		$course['nextlesson'] = $this->GetNextLessonByCourseId($courseid);
		$course['lastlesson'] = $this->GetCurrentLessonByCourseId($courseid);
		if(isset($course['lastlesson']))
		{
			$course['lastlesson']['checkoutProgress'] = $Lesson->GetUserFlowPosInLesson($course['lastlesson']['lessonid'], $token);
		}
		return $course;
	}
	/**
	 * returns the lessonid of the next (i.e. created but not yet opened) lesson in the course
	 * @param int $courseid - the unique id of the course
	 * @return int lessonid - the unique id of next lesson if one exists
	 */
	function GetNextLessonByCourseId($courseid)
	{
		global $db;
		$nextLesson = $db->smartQuery(array(
				'sql' => "SELECT lessonid FROM `lesson` WHERE `courseid`=:courseid AND `checkout`='' AND `checkin`=''",
				'par' => array( 'courseid' => $courseid),
				'ret' => 'fetch-assoc'
		));
		return $nextLesson['lessonid'];
	}
	/**
	 * returns the lessonid of the current (last opened) lesson in the course
	 * @param int $courseid - the unique id of the course
	 * @return int lessonid - the unique id of next lesson if one exists
	 */
	function GetCurrentLessonByCourseId($courseid)
	{
		global $db;
		$currLesson= $db->smartQuery(array(
				'sql' => "SELECT lessonid, checkout FROM `lesson` WHERE `courseid`=:courseid AND `checkin`!='' ORDER BY lessonid DESC LIMIT 1",
				'par' => array('courseid' => $courseid),
				'ret' => 'fetch-assoc'
		));
		if(isset($currLesson['lessonid']))
		{
			$currLesson["closed"] = $currLesson["checkout"]!="";
			unset($currLesson['checkout']);
		}
		else {
			$currLesson=null;
		}
		return $currLesson;
	}
	function AddCourseToStudent($token,$code)
	{
		global $db;
		global $AppUser;
		$studentid = $AppUser->GetUserIdByToken($token);
		if(!isset($studentid) || $studentid=="")
		{
			return (object)array("error"=>"token is not exist");
		}
		$course = $this->GetCourseByCode($code);
		if(!isset($course['courseid']))
		{
			return (object)array("error"=>"course code is not existing in the system");
		}
		if($course['status']==0)
		{
			return (object)array("error"=>"the course associated with the code is inactive");
		}
		if($this->IsStudentEnrolledInCourse($course['courseid'], $studentid))
		{
			return (object)array("error"=>"this student was register to this course");
		}
		$time = date("Y-m-d H:i:s");
		
		$result = $db->smartQuery(array(
			'sql' => "INSERT INTO `student_course`(`courseid`,`studentid`, `statusincourse`, `laststatuschange`) VALUES ( :courseid, :studentid, 1, :time);",
			'par' => array( 'courseid' => $course['courseid'], 'studentid' => $studentid, 'time' => $time),
			'ret' => 'result'
		));
		
		return (object)array("coursename"=>$course['subname']);
	}
	function UpdateStudentStatus ($courseid, $studentid, $status)
	{
		global $db;
		$time = date("Y-m-d H:i:s");
		$result= $db->smartQuery(array(
				'sql' => "UPDATE `student_course` SET statusincourse=:status, laststatuschange=:time WHERE courseid=:courseid AND studentid=:studentid",
				'par' => array( 'courseid' => $courseid, 'studentid' => $studentid, 'status' => $status, 'time' => $time),
				'ret' => 'result'
		));
		return $result;
	}
	function GetCoursesIdByStudentId($studentid)
	{
		global $db;
		if(!isset($studentid) || $studentid=="")
		{
			return (object)array("error" => "studentid not exist");
		}
		
		$coursesid = $db->smartQuery(array(
			'sql' => "Select courseid FROM `student_course` Where `studentid`=:studentid",
			'par' => array( 'studentid' => $studentid),
			'ret' => 'fetch-assoc'
		));
		if( $coursesid['courseid']=="")
		{
			return (object)array("error" => "there are no course for this token");
		}else
		{
			$myArray = explode(',', $coursesid['courseid']);
			return (object)array("ids" => $myArray);
		}
	}
	function GetCoursesByStudentToken($token)
	{
		global $db;
		global $AppUser;
		$studentid = $AppUser->GetUserIdByToken($token);
		$courses = $db->smartQuery(array(
			'sql' => "SELECT * FROM course JOIN student_course ON course.courseid=student_course.courseid WHERE `studentid`=:studentid AND student_course.statusincourse <> 3",
			'par' => array( 'studentid' => $studentid),
			'ret' => 'all'
		));
		foreach($courses as $i=>$course)
		{
			if($course['status']==0)
			{
				$courses[$i]['status']="closed";
			}
			else
			{
				$courses[$i]['status']="active";
			}
		}
		return $courses;
	}
	function GetCoursesByMadrichToken($token)
	{
		global $db;
		global $AppUser;
		$madrichid = $AppUser->GetUserIdByToken($token);
		$courses = $db->smartQuery(array(
			'sql' => "SELECT * FROM course WHERE `madrichid`=:madrichid",
			'par' => array( 'madrichid' => $madrichid),
			'ret' => 'all'
		));
		foreach($courses as $i=>$course)
		{
			if($course['status']==0)
			{
				$courses[$i]['status']="closed";
			}
			else
			{
				$courses[$i]['status']="active";
			}
		}
		return $courses;
	}
	function IsStudentEnrolledInCourse($courseid, $studentid)
	{
		global $db;
		$student_course = $db->smartQuery(array(
			'sql' => "SELECT * FROM student_course WHERE `courseid`=:courseid AND `studentid`=:studentid",
			'par' => array( 'courseid' => $courseid,'studentid' => $studentid),
			'ret' => 'fetch-assoc'
		));
		return isset($student_course['studentid']);
	}
	function GetCourseByCode($code)
	{
		global $db;
		$course = $db->smartQuery(array(
			'sql' => "SELECT * FROM course WHERE `code`=:code",
			'par' => array( 'code' => $code),
			'ret' => 'fetch-assoc'
		));
		return $course;
	}
	function GetActiveStudentsInCourse($courseid)
	{
		$students = $this -> GetStudentsInCourse($courseid);
		$studentstemp = array();
		foreach($students as $student)
		{
			if($student['status'] == 'פעיל')
			{
				$studentstemp[] = $student;
			}
		}
		return $studentstemp;
	}
	function GetCoursesOfProject($pid,$staffid=null)
	{
		global $db;
		global $myid;
		
		if(!isset($staffid))
		{
			$staffid=$myid;
		}
		
		$courses = $db->smartQuery(array(
				'sql' => "Select distinct courseid,name, code FROM course where madrichid=:madrichid and projectid=:projectid",
				'par' => array('madrichid'=>$staffid, 'projectid'=>$pid),
				'ret' => 'all'
			));
			
		return $courses;
	}
	function getCourseNameByLessonId($lessonid)
	{
		global $db;
		$course = $db->smartQuery(array(
				'sql' => "SELECT course.subname AS subname FROM `lesson` AS ls JOIN `course` AS course WHERE ls.lessonid=:lessonid",
				'par' => array( 'lessonid' => $lessonid),
				'ret' => 'fetch-assoc'
		));
		return $course["subname"];
	}
	//notifications related functions
	function sendNotificationToStudentOnMeetingCreation($courseid,$lessonid,$beginningdate, $isUpdate)
	{
		global $db;
		$course = $db->smartQuery(array(
		'sql' => "Select subname FROM `course` Where`courseid`=:courseid",
		'par' => array( 'courseid' => $courseid),
		'ret' => 'fetch-assoc'
		));
		$beginningdate = substr($beginningdate,0,10);
		$beginningdate = date('d/m/Y H:i', (int)$beginningdate);
		$message=  $beginningdate;
		if(!$isUpdate)
			$title= "נוצר מפגש חדש בקורס ".$course['subname'];
		else 
			$title= "עודכנו פרטי המפגש בקורס ".$course['subname'];
		$type = 'newLesson';
		$this -> sendNotificationToStudentByCourseid($courseid,$lessonid,$message, $title, $type);
	}
	function sendNotificationToStudentOnMeetingActivation($courseid,$lessonid)
	{
		global $db;
		$course = $db->smartQuery(array(
		'sql' => "Select subname FROM `course` Where`courseid`=:courseid",
		'par' => array( 'courseid' => $courseid),
		'ret' => 'fetch-assoc'
		));
		$message=  "נראה אותך?";
		$title= "המפגש שלך בקורס ".$course['subname']." תיכף מתחיל";
		$type = 'lessonActivated';
		$this -> sendNotificationToStudentByCourseid($courseid,$lessonid,$message, $title, $type);
	}
	function sendNotificationToStudentByCourseid($courseid,$lessonid,$message, $title, $type)
	{
		global $db;
		global $FireBaseFCM;
		
		$students = $db->smartQuery(array(
		'sql' => "Select studentid FROM `student_course` Where`courseid`=:courseid and (statusincourse='1' or statusincourse='פעיל' or statusincourse='משתתף')",
		'par' => array( 'courseid' => $courseid),
		'ret' => 'all'
		));
		
		$tokens = array();
		foreach($students as $student)
		{
			$fbtoken = $db->smartQuery(array(
			'sql' => "Select fbtokenid FROM `appuser` Where`appuserid`=:appuserid",
			'par' => array( 'appuserid' => $student['studentid']),
			'ret' => 'fetch-assoc'
			));
			if(isset($fbtoken['fbtokenid']) && $fbtoken['fbtokenid']!='')
			{
				$tokens[] = $fbtoken['fbtokenid'];
			}
		}
		$FireBaseFCM->sendMessage($title,$message,$tokens,$courseid,$lessonid,$type);
	}
}	