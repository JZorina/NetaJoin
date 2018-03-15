<?php
class Syllabus {
	function getSubjectsByCourseId ($courseid, $learntin, $understandinglevel)
	{
		$subjects = array ();
		$subjectIds = $this -> getSubjectIdsByCourseId($courseid);
		foreach ($subjectIds as $sid)
		{
			$subjects[] = $this -> getSubjectBySubjectId($sid, $learntin, $understandinglevel);
		}
		return $subjects;
	}
	function getSubjectIdsByCourseId ($courseid)
	{
		global $db;
		$subjectsIds = $db->smartQuery(array(
				'sql' => "SELECT sb.subjectid AS subjectid FROM subject AS sb WHERE sb.courseid=:courseid",
				'par' => array( 'courseid' => $courseid),
				'ret' => 'all'
		));
		$result = array();
		foreach ($subjectsIds as $sid)
		{
			$result[]=$sid["subjectid"];
		}
		return $result;
	}
	function getSubjectBySubjectId ($subjectid, $learntin, $understandinglevel)
	{
		$sub = array ();
		$sub["id"] = $subjectid;
		$sub["name"] = $this -> getSubjetNameBySubjectId($sub["id"]);
		$subsubjectIds = $this -> getSubsubjetIdsBySubjectId($subjectid);
		$sub["subsubjects"] = array ();
		if($learntin)
		{
			$sub["learnt in"] = $this -> wasSubjectLearntInCourse ($sub["id"]);
		}
		foreach ($subsubjectIds as $ssid)
		{
			$sub["subsubjects"][]=$this -> getSubsubjectBySubsubjectId($ssid, $learntin, $understandinglevel);
		}
		return $sub;
	}
	function getSubjetNameBySubjectId ($subjectid)
	{
		global $db;
		$subjectName = $db->smartQuery(array(
				'sql' => "SELECT sb.subject AS he, sb.subjectinarabic AS ar FROM subject AS sb WHERE sb.subjectid=:subjectid",
				'par' => array( 'subjectid' => $subjectid),
				'ret' => 'fetch-assoc'
		));
		return $subjectName;
	}
	function getSubsubjetIdsBySubjectId ($subjectid)
	{
		global $db;
		$subsubjectsIds = $db->smartQuery(array(
				'sql' => "SELECT ssb.subsubjectid AS subsubjectid FROM subsubject AS ssb WHERE ssb.subjectid=:subjectid",
				'par' => array( 'subjectid' => $subjectid),
				'ret' => 'all'
		));
		$result = array();
		foreach ($subsubjectsIds as $ssid)
		{
			$result[]=$ssid["subsubjectid"];
		}
		return $result;
	}
	function getSubsubjectBySubsubjectId ($subsubjectid, $learntin, $understandinglevel)
	{
		$sub = array ();
		$sub["id"] = $subsubjectid;
		$sub["name"] = $this -> getSubsubjetNameBySubsubjectId($sub["id"]);
		if($learntin)
		{
			$sub["learnt in"] = $this -> wasSubsubjectLearntInCourse($sub["id"]);
		}
		return $sub;
	}
	function getSubsubjetNameBySubsubjectId ($subsubjectid)
	{
		global $db;
		$subsubjectName = $db->smartQuery(array(
				'sql' => "SELECT ssb.subsubject AS he, ssb.subsubjectinarabic AS ar FROM subsubject AS ssb WHERE ssb.subsubjectid=:subsubjectid",
				'par' => array( 'subsubjectid' => $subsubjectid),
				'ret' => 'fetch-assoc'
		));
		return $subsubjectName;
	}
	function wasSubjectLearntInCourse ($subjectid)
	{
		global $db;
		$lessonsWereLearnt = $db->smartQuery(array(
				'sql' => "SELECT ls.lessonid AS lessonid, ls.checkin AS date FROM subjectstaught AS sbt JOIN lesson AS ls ON ls.lessonid = sbt.lessonid  WHERE isChecked=1 AND sbt.subjectid = :subjectid AND ls.checkout!=''",
				'par' => array( 'subjectid' => $subjectid),
				'ret' => 'fetch-all'
		));
		return $lessonsWereLearnt;
	}
	function wasSubjectLearntInLesson ($subjectid, $lessonid)
	{
		global $db;
	}
	function wasSubsubjectLearntInCourse ($subsubjectid)
	{
		global $db;
		$lessonsWereLearnt = $db->smartQuery(array(
				'sql' => "SELECT ls.lessonid AS lessonid, ls.checkin AS date FROM subsubjecttaught AS sbt JOIN lesson AS ls ON ls.lessonid = sbt.lessonid  WHERE isChecked=1 AND sbt.subsubjectid = :subsubjectid AND ls.checkout!=''",
				'par' => array( 'subsubjectid' => $subsubjectid),
				'ret' => 'fetch-all'
		));
		return $lessonsWereLearnt;
	}
	function wasSubsubjectLearntInLesson ($subsubjectid, $lessonid)
	{
		global $db;
	}
	function getSubjectUnderstandingInSpecificLesson ($subjectid, $lessonid)
	{
		global $db;
	}
	function getSubjectUnderstandingAcrossCourse ($subjectid, $lessonid)
	{
		global $db;
	}
	function getSubsubjectUnderstandingInSpecificLesson ($subjectid, $lessonid)
	{
		global $db;
	}
	function getSubsubjectUnderstandingAcrossCourse ($subjectid, $lessonid)
	{
		global $db;
	}
}
?>