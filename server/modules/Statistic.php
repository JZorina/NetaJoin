<?php
class Statistic {
	function GetStatistic($lessonid) {
		$statistic = array ();
		//a meeting's feedback is only displayed if this percent of the students who checked in have already provided their feedback
		//otherwise, a list of students who haven't provided their feedback is displayed
		$cooking_threshold=0.6;
			//****DEPRECATED****
				//get data about attendance to this meeting
				$attendances = $this->GetAttendance ($lessonid);
				$statistic ['attendances'] = $attendances;
		//get data about how many students of those who attended haven't given any feedback
		$students = $this->GetStudentsDetailsForLesson($lessonid);
		$statistic ['students']=$students;
		//the number of students who have attended (late or not) the meeting
		$attendanceCount=0;
		//the number of students who have supplied any feedback about the meeting
		$feedbackCount=0;
		//iterate through students and aggregate feedback count and attendance count
		for($i=0; $i<count($students); $i++)
		{
			if($students[$i]['attendance']=='late'||$students[$i]['attendance']=='attendance')
			{
				$attendanceCount++;
			}
			if($students[$i]['givenFeedback']>0)
			{
				$feedbackCount++;
			}
		}
		//if the lesson is the last of the course, we might want to hide the data, in case response rate is low
		global $Course;
		global $Lesson;
		//get the course id for the course that this lesson is associated with
		$courseid = $Lesson -> getCourseIdByLessonId($lessonid);
		//get the id of the last opened lesson in that course
		$lastLessonId = $Course -> GetCurrentLessonByCourseId($courseid)['lessonid'];
		//check if that lesson id is identical to the lesson id for the lesson which's statistics are to be provided
		$isLastLesson = $lastLessonId == $lessonid;
		if($isLastLesson)
		{
			$isCooking = $attendanceCount>0&&($feedbackCount)/$attendanceCount<$cooking_threshold;
		}
		else {
			$isCooking = false;
		}
		if($isCooking)
		{
			$statistic ['cooking'] = true;
			$statistic ['generalCloseQuestions']=array();
			$statistic ['specificCloseQuestions']=array();
			$statistic ['generalOpenQuestions']=array();
			$statistic ['specificOpenQuestions']=array();
		}
		else {
			$statistic ['cooking'] = false;
			//rating feedback
			//general questions
			$generalCloseQuestions = $this->GetGeneralCloseQuestionsSummary ($lessonid);
			$statistic ['generalCloseQuestions'] = $generalCloseQuestions;
			//syllabus question
			$specificCloseQuestions = $this->GetSpecificCloseQuestionsSummary ($lessonid);
			$statistic ['specificCloseQuestions'] = $specificCloseQuestions;
			//open feedback
			//general questions
			$generalOpenQuestions = $this->GetGeneralOpenQuestionsSummary ($lessonid);
			$statistic ['generalOpenQuestions'] = $generalOpenQuestions;
			//syllabus questions
			$specificOpenQuestions = $this->GetSpecificOpenQuestionsSummary ($lessonid);
			$statistic ['specificOpenQuestions'] = $specificOpenQuestions;
		}
		return $statistic;
	}
	
	function GetGeneralCloseQuestionsSummary($lessonid) {
		global $db;
		$generalCloseQuestions = $db->smartQuery ( array (
				'sql' => "SELECT  fb.question, CAST((sum(fb.answer/4)/count(fb.answer))*100 AS UNSIGNED) AS avg, COUNT(fb.answer) AS responseCount
					FROM `checkstudent` AS cs
					JOIN feedback AS fb
						ON fb.checkstudentid=cs.checkstudentid
					WHERE
						fb.type='general'
						AND fb.questiontype='close'
						AND cs.lessonid=:lessonid
						AND
							(cs.status='attendance'
							OR cs.status='late')
					GROUP BY fb.question",
				'par' => array (
						'lessonid' => $lessonid 
				),
				'ret' => 'all' 
		) );
		
		return $generalCloseQuestions;
	}
	function GetSpecificCloseQuestionsSummary($lessonid) {
		global $db;
		$specificCloseQuestions = $db->smartQuery ( array (
				'sql' => "SELECT  fb.question, CAST((sum(fb.answer/4)/count(fb.answer))*100 AS UNSIGNED) AS avg, COUNT(fb.answer) AS responseCount
					FROM `checkstudent` AS cs
					JOIN feedback AS fb
						ON fb.checkstudentid=cs.checkstudentid
					WHERE
						fb.type='specific'
						AND fb.questiontype<>'open'
						AND cs.lessonid=:lessonid
						AND 
							(cs.status='attendance'
							OR cs.status='late')
					GROUP BY fb.question",
				'par' => array (
						'lessonid' => $lessonid 
				),
				'ret' => 'all' 
		) );
		
		return $specificCloseQuestions;
	}
	function GetGeneralOpenQuestionsSummary($lessonid) {
		global $db;
		$generalOpenQuestions = $db->smartQuery(array(
				'sql' => "SELECT  fb.answer 
					FROM `checkstudent` AS cs
					JOIN feedback AS fb
						ON fb.checkstudentid=cs.checkstudentid
					WHERE
						fb.type='general'
						AND fb.questiontype='open'
						AND cs.lessonid=:lessonid
						AND
							(cs.status='attendance'
							OR cs.status='late')",
				'par' => array (
						'lessonid' => $lessonid
				),
				'ret' => 'all'
		));
		
		return $generalOpenQuestions;
	}
	function GetSpecificOpenQuestionsSummary($lessonid) {
		global $db;
		$specificOpenQuestions = $db->smartQuery(array(
				'sql' => "SELECT  fb.answer
					FROM `checkstudent` AS cs
					JOIN feedback AS fb
						ON fb.checkstudentid=cs.checkstudentid
					WHERE
						fb.type='specific'
						AND fb.questiontype='open'
						AND cs.lessonid=:lessonid
						AND
							(cs.status='attendance'
							or cs.status='late')",
				'par' => array (
						'lessonid' => $lessonid 
				),
				'ret' => 'all' 
		));
		return $specificOpenQuestions;
	}
	function GetStudentsDetailsForLesson($lessonid)
	{
		global $db;
		$missingStudents = $specificOpenQuestions = $db->smartQuery(array(
				'sql' => "
					SELECT
						st.firstname AS firstname,
						st.lastname AS lastname,
						st.image AS image,
						st.studentid AS studentid,
						cs.status AS attendance,
						CASE
							WHEN COUNT(fb.feedbackid)>0
								THEN 1
								ELSE 0
						END
						AS givenFeedback
					FROM `student` AS st
					JOIN checkstudent AS cs
						ON cs.studentid = st.studentid
					LEFT JOIN feedback as fb
						ON cs.checkstudentid = fb.checkstudentid
					WHERE 
						cs.lessonid = :lessonid
					GROUP BY cs.checkstudentid",
				'par' => array (
						'lessonid' => $lessonid
				),
				'ret' => 'all'
		));
		return $missingStudents;
	}
	// all statistic for course Summary page//
	function GetCourseStatistic($courseid) {
		global $db;
		$lessons = $db->smartQuery ( array (
				'sql' => "SELECT  lessonid, name
					FROM `lesson` AS lesson
					WHERE
						`courseid`=:courseid
						AND checkout <> ''
						AND checkin IS NOT NULL
					ORDER BY checkin",
				'par' => array (
						'courseid' => $courseid 
				),
				'ret' => 'all' 
		) );
		
		$SummaryCourse = array ();
		$lessonsids = array ();
		foreach ( $lessons AS $lesson ) {
			$lessonid = $lesson ['lessonid'];
			$lessonsids [] = $lesson ['lessonid']; 
			$attendance = $this->GetAttendance ($lessonid);
			$lesson ['exist'] = $attendance->exist;
			$lesson ['from'] = $attendance->from;
			$lesson ['attendance'] = $attendance->percent;
			$lesson ['late'] = $attendance->late;
			$lesson ['understanding'] = $this->GetUnderstandingAveragePerLesson($lessonid);
			$SummaryCourse ['lessons'] [] = $lesson;
		}
		$GeneralCloseAllQuestionsCourse = $this->GetGeneralFeedbackAverageAcrossCourse($courseid);
		$SummaryCourse ['GeneralCloseQuestions'] = $GeneralCloseAllQuestionsCourse;
		$CourseUnderstanding = $this->GetCourseUnderstanding ($courseid);
		$SummaryCourse ['AvgCourseUnderstanding'] = $CourseUnderstanding;
		$SummaryCourse ['dropouts']=$this->GetNumOfDropouts($courseid);
		return $SummaryCourse;
	}
	function GetAttendance($lessonid) {
		global $db;
		$exist = $db->smartQuery ( array (
				'sql' => "SELECT  count(*) AS exist
					FROM `checkstudent`
					WHERE
						`lessonid`=:lessonid
						AND
							(`status`='attendance'
							OR `status`='late') ",
				'par' => array (
						'lessonid' => $lessonid
				),
				'ret' => 'fetch-assoc'
		) );
		
		$late = $db->smartQuery ( array (
				'sql' => "SELECT  count(*) AS late
					FROM `checkstudent`
					WHERE
						`lessonid`=:lessonid
						AND `status`='late' ",
				'par' => array (
						'lessonid' => $lessonid
				),
				'ret' => 'fetch-assoc'
		) );
		
		$notexist = $db->smartQuery ( array (
				'sql' => "SELECT  count(*) AS notexist
					FROM `checkstudent`
					WHERE
						`lessonid`=:lessonid
						AND
							(`status`='not attendance'
							OR `status`='not checkin') ",
				'par' => array (
						'lessonid' => $lessonid
				),
				'ret' => 'fetch-assoc'
		) );
		
		$attendance = $exist ['exist'];
		$from = $exist ['exist'] + $notexist ['notexist'];
		if ($from == '0') {
			$percent = 0;
		} else {
			$percent = ( int ) (($attendance / $from) * 100);
		}
		$attendances = ( object ) array (
				"late" => $late ['late'] . '',
				"exist" => $attendance . '',
				"from" => $from . '',
				"percent" => $percent . ''
		);
		
		return $attendances;
	}
	function GetNumOfDropouts($courseid)
	{
		global $db;
		$dropouts = $db->smartQuery ( array (
				'sql' => "SELECT  COUNT(*) AS count
					FROM student_course AS sc
					WHERE
						sc.courseid=:courseid
						AND sc.statusincourse=3",
				'par' => array (
						'courseid' => $courseid
				),
				'ret' => 'fetch-assoc'
		) );
		return $dropouts['count'];
	}
	
	function GetGeneralFeedbackAverageAcrossCourse($courseid) {
		global $db;
		$GeneralCloseAllQuestionsCourse = $db->smartQuery ( array (
				'sql' => "SELECT fb.question, CAST((AVG(fb.answer/4))*100 AS UNSIGNED) AS avg
					FROM lesson AS ls
					JOIN `checkstudent` AS cs
						ON cs.lessonid=ls.lessonid
					JOIN feedback AS fb
						ON fb.checkstudentid=cs.checkstudentid
					WHERE
						fb.type='general'
						AND fb.questiontype='close'
						AND ls.courseid=:courseid
						AND
							(cs.status='attendance'
							OR cs.status='late')
					GROUP BY fb.question",
				'par' => array ('courseid' => $courseid),
				'ret' => 'all'
		) );
		return $GeneralCloseAllQuestionsCourse;
	}
	function GetUnderstandingAveragePerLesson($lessonid) {
		global $db;
		$averageUndestanding = $db->smartQuery ( array (
				'sql' => "SELECT CAST((sum(fb.answer/4)/count(fb.answer))*100 AS UNSIGNED) AS avg
					FROM `checkstudent` AS cs
					JOIN feedback AS fb
						ON fb.checkstudentid=cs.checkstudentid
					WHERE
						fb.type='specific'
						AND fb.questiontype<>'open'
						AND cs.lessonid=:lessonid
						AND
							(cs.status='attendance'
							OR cs.status='late')",
				'par' => array (
						'lessonid' => $lessonid
				),
				'ret' => 'fetch-assoc'
		) );
		
		return $averageUndestanding['avg'];
	}
	function GetCourseUnderstanding($courseid) {
		global $db;
		$CourseUnderstanding = $db->smartQuery ( array (
				'sql' => "SELECT  CAST((sum(fb.answer/4)/count(fb.answer))*100 AS UNSIGNED) AS avg
                    FROM `checkstudent` AS cs
					JOIN feedback AS fb ON fb.checkstudentid=cs.checkstudentid
					JOIN lesson AS ls ON ls.lessonid=cs.lessonid
					WHERE
						fb.type='specific'
						and fb.questiontype <>'open'
						AND ls.courseid=:courseid
						AND
						(cs.status='attendance'
						or cs.status='late')",
				'par' => array (
						'courseid' => $courseid
				),
				'ret' => 'fetch-assoc'
		) );
		
		if (isset ( $CourseUnderstanding ['avg'] )) {
			return $CourseUnderstanding ['avg'];
		} else {
			return 0;
		}
	}
	
	function SaveUsabilityInStatisticScreen($lessonid, $usabilty) {
		global $db;
		$use = json_encode ( $usabilty );
		
		$result = $db->smartQuery ( array (
				'sql' => "UPDATE lesson SET usabilty=:usabilty WHERE lessonid=:lessonid",
				'par' => array (
						'lessonid' => $lessonid,
						'usabilty' => $use 
				),
				'ret' => 'result' 
		) );
		return $result;
	}
	function GetUsabilityInStatisticScreen($lessonid) {
		global $db;
		$lesson = $db->smartQuery ( array (
				'sql' => "SELECT usabilty FROM lesson WHERE lessonid=:lessonid",
				'par' => array (
						'lessonid' => $lessonid 
				),
				'ret' => 'fetch-assoc' 
		) );
		if (isset ( $lesson ['usabilty'] ) && $lesson ['usabilty'] != '') {
			$usabilty = json_decode ( $lesson ['usabilty'] );
		} else {
			$usabilty = ( object ) array (
					"ShowTime" => "0",
					"ClickInCommentsLesson" => "false",
					"ClickInCommentsSubjects" => "false",
					"ClickInShowSubjects" => "false" 
			);
		}
		return $usabilty;
	}

	function GetStudentStatsSummary($courseid){
		global $db;
		global $Course;
		$students =  $Course->GetStudentsInCourse($courseid);
		$studentAlerts = 0;
		foreach ($students AS $student) {
			$studentid = $student ['studentid'];
			$studentAlerts+=(
					floor ( $this->getStudentAttendanceStreak ( $studentid, $courseid ) )>=2||
					floor ( $this->getStudentUnderstandingStreak ( $studentid, $courseid ) )>=2)?1:0;
		}
		return $studentAlerts;
	}

	function GetStudentStats($courseid) {
		global $db;
		global $Course;
		$students =  $Course->GetStudentsInCourse($courseid);
		$SummaryStudents = array ();
		foreach ( $students AS $student ) {
			$studentid = $student ['studentid'];
			$student ['attendance'] = array ();
			$student ['attendance'] ['average'] = $this->getStudentAttendance ( $studentid, $courseid );
			$student ['attendance'] ['average'] ['rate'] = floor ( $student ['attendance'] ['average'] ['rate'] * 100 );
			$student ['attendance'] ['average'] ['numOfMeetings'] = floor ( $student ['attendance'] ['average'] ['numOfMeetings'] );
			$student ['attendance'] ['streak'] = floor ( $this->getStudentAttendanceStreak ( $studentid, $courseid ) );
			$student ['understanding'] = array ();
			$student ['understanding'] ['average'] = $this->getStudentUnderstanding ( $studentid, $courseid );
			$student ['understanding'] ['average'] ['rate'] = floor ( $student ['understanding'] ['average'] ['rate'] * 100 );
			$student ['understanding'] ['average'] ['numOfMeetings'] = floor ( $student ['understanding'] ['average'] ['numOfMeetings'] );
			$student ['understanding'] ['streak'] = floor ( $this->getStudentUnderstandingStreak ( $studentid, $courseid ) );
			$student ['mentoring'] = $this->getStudentMentoringSessions($studentid, $courseid);
			$SummaryStudents [] = $student;
		}
		$ans = $SummaryStudents;
		return $ans;
	}
	function getStudentAttendance($studentid, $courseid) {
		global $db;
		$attendance = $db->smartQuery ( array (
				'sql' => "
		SELECT  Avg(cs.status) AS rate, COUNT(cs.status) AS numOfMeetings
		FROM
			(SELECT  cs.lessonid  AS lessonid, 
				cs.studentid AS studentid, 
				CASE 
					WHEN cs.status = 'not checkin' THEN 0 
					WHEN cs.status = 'not attendance' THEN 0 
					WHEN cs.status = 'late' THEN 0.5 
					ELSE 1 
					end
				AS status 
			FROM   `checkstudent`AS cs
			WHERE	cs.studentid=:studentid
			) AS cs 
		JOIN `lesson` AS ls 
		ON cs.lessonid = ls.lessonid 
		WHERE  ls.courseid = :courseid
		GROUP  BY cs.studentid",
				'par' => array (
						'courseid' => $courseid,
						'studentid' => $studentid 
				),
				'ret' => 'fetch-assoc' 
		) );
		if ($attendance)
			return $attendance;
		else 
			return array("rate" => 0, "numOfMeetings" => 0);
	}
	function getStudentAttendanceStreak($studentid, $courseid) {
		global $db;
		$attstreak = $db->smartQuery ( array (
				'sql' => "SELECT  streakIndex.status          AS status, 
       Count(*)                    AS streak, 
       Max(streakIndex.lessondate) AS EndDate 
FROM  (SELECT  R.lessondate                              AS lessondate, 
              R.status                                  AS status, 
              (SELECT  Count(*) 
               FROM   (SELECT  ls.checkin AS lessondate, 
                              CASE 
                                WHEN cs.status = 'not checkin' THEN 0 
                                WHEN cs.status = 'not attendance' THEN 0 
                                ELSE 1 
                              end        AS status 
                       FROM   `checkstudent`AS cs 
                              JOIN lesson AS ls 
                                ON cs.lessonid = ls.lessonid 
                      WHERE  ls.courseid = :courseid 
                              AND 
                      cs.studentid = :studentid) AS 
                      S 
              WHERE  S.status <> R.status 
                      AND S.lessondate <= R.lessondate) AS streakGroup 
       FROM   (SELECT  ls.checkin AS lessondate, 
                      CASE 
                        WHEN cs.status = 'not checkin' THEN 0 
                        WHEN cs.status = 'not attendance' THEN 0 
                        ELSE 1 
                      end        AS status 
               FROM   `checkstudent`AS cs 
                      JOIN lesson AS ls 
                        ON cs.lessonid = ls.lessonid 
              WHERE  ls.courseid = :courseid 
                      AND cs.studentid = :studentid) 
              AS R) 
      AS streakIndex 
GROUP  BY streakIndex.status, 
          streakIndex.streakgroup 
ORDER  BY enddate DESC",
				'par' => array (
						'courseid' => $courseid,
						'studentid' => $studentid 
				),
				'ret' => 'fetch-assoc' 
		) );
		$noattfor = $attstreak ['status'] == 1 ? 0 : $attstreak ['streak'];
		return $noattfor;
	}
	function getStudentUnderstanding($studentid, $courseid) {
		global $db;
		$attendance = $db->smartQuery ( array (
				'sql' => "SELECT  COUNT(cs.status) AS numOfMeetings, AVG(fb.answer)/5 AS rate 
					FROM feedback AS fb
					JOIN checkstudent AS cs ON cs.checkstudentid = fb.checkstudentid
					JOIN lesson AS ls ON ls.lessonid = cs.lessonid
					WHERE
						fb.type = 'specific'
						AND
							(fb.questiontype = 'close'
							OR fb.questiontype = '')
						AND ls.courseid = :courseid
						AND cs.studentid= :studentid",
				'par' => array (
						'courseid' => $courseid,
						'studentid' => $studentid 
				),
				'ret' => 'fetch-assoc' 
		) );
		return $attendance;
	}
	function getStudentUnderstandingStreak($studentid, $courseid) {
		global $db;
		$attstreak = $db->smartQuery ( array (
				'sql' => "SELECT  streakIndex.status          AS status, 
       Count(*)                    AS streak, 
       Max(streakIndex.lessondate) AS EndDate 
FROM   (SELECT  R.lessondate                              AS lessondate, 
               R.status                                  AS status, 
               (SELECT  Count(*) 
                FROM   (SELECT  ls.checkin       AS lessondate, 
                               Count(cs.status) AS numOfMeetings, 
                               CASE 
                                 WHEN Avg(fb.answer) / 5 < 0.6 THEN 0 
                                 ELSE 1 
                               END              AS status 
                        FROM   feedback AS fb 
                               JOIN checkstudent AS cs 
                                 ON cs.checkstudentid = fb.checkstudentid 
                               JOIN lesson AS ls 
                                 ON ls.lessonid = cs.lessonid 
                       WHERE  fb.type = 'specific' 
                               AND ( fb.questiontype = 'close' 
                                      OR fb.questiontype = '' ) 
                               AND ls.courseid = :courseid
                               AND cs.studentid = :studentid 
                               AND ( cs.status = 'attendance' 
                                      OR cs.status = 'late' ) 
                        GROUP  BY ls.lessonid) AS S 
               WHERE  S.status <> R.status 
                       AND S.lessondate <= R.lessondate) AS streakGroup 
        FROM   (SELECT  ls.checkin       AS lessondate, 
                       Count(cs.status) AS numOfMeetings, 
                       CASE 
                         WHEN Avg(fb.answer) / 5 < 0.6 THEN 0 
                         ELSE 1 
                       END              AS status 
                FROM   feedback AS fb 
                       JOIN checkstudent AS cs 
                         ON cs.checkstudentid = fb.checkstudentid 
                       JOIN lesson AS ls 
                         ON ls.lessonid = cs.lessonid 
               WHERE  fb.type = 'specific' 
                       AND ( fb.questiontype = 'close' 
                              OR fb.questiontype = '' ) 
                       AND ls.courseid = :courseid 
                       AND cs.studentid = :studentid 
                       AND ( cs.status = 'attendance' 
                              OR cs.status = 'late' ) 
                GROUP  BY ls.lessonid) AS R) AS streakIndex 
GROUP  BY streakIndex.status, 
          streakIndex.streakgroup 
ORDER  BY enddate DESC ",
				'par' => array (
						'courseid' => $courseid,
						'studentid' => $studentid 
				),
				'ret' => 'fetch-assoc' 
		) );
		return $attstreak ['streak'];
	}
	function getStudentMentoringSessions($studentid, $courseid) {
		global $db;
		$sessions = $db->smartQuery ( array (
				'sql' => "SELECT ms.scheduleddate AS date, mst.nameinhebrew AS he, mst.nameinarabic AS ar
		FROM
			`mentoringsession_student` AS mss
			JOIN mentoringsession AS ms ON ms.mentoringsessionid = mss.mentoringsessionid
			JOIN mentoringsessiontype AS mst ON mst.mentoringsessiontypeid = ms.mentoringsessiontypeid
			WHERE  mss.studentid = :studentid AND ms.courseid = :courseid",
				'par' => array (
						'courseid' => $courseid,
						'studentid' => $studentid 
				),
				'ret' => 'all' 
		) );
		return $sessions;
	}
}
?>