<?php
class Reporter{
	
	function DeleteReportById($reportid)
	{
		global $db;
		$report = $db->smartQuery(array(
				'sql' => "SELECT `reportcopyid`, `reportid` FROM `report` WHERE reportid=:reportid;",
				'par' => array('reportid' => $reportid),
				'ret' => 'fetch-assoc'
		));
		
		$result = $db->smartQuery(array(
		'sql' => "delete from `report` where reportid=:reportid;",
		'par' => array('reportid' => $reportid),
		'ret' => 'result'
		));
		
		$result = $db->smartQuery(array(
		'sql' => "delete from `reportcopy` where reportcopyid=:reportcopyid;",
		'par' => array('reportcopyid' => $report['reportcopyid']),
		'ret' => 'result'
		));
		
		return $result;
	}
	
	function GetAllReporters($month,$year)
	{
		global $db;
		global $myid;
		global $mySubStaff;
		global $me;
		array_push($mySubStaff, $myid);
		$users = $db->smartQuery(array(
			'sql' => "Select staffid,firstname,lastname, status, superstaffid FROM staff",
			'par' => array(),
			'ret' => 'all'
		));
		$reporters = array();
		if($me['type']=='admin')
		{
			foreach($users as $user)
			{
				$user['reports'] = $this->GetReporters($user['staffid'],$month,$year);
				$user['reportingPerimeter'] = $this->GetReporterPerimeter($user['staffid']);
				$reporters[] = $user;
			}
		}else
		{
			foreach($users as $user)
			{
				if($user['superstaffid'] === $myid)
				{
					$user['reports'] = $this->GetReporters($user['staffid'],$month,$year);
					$user['reportingPerimeter'] = $this->GetReporterPerimeter($user['staffid']);
					$reporters[] = $user;
				}
			}
		}
		return $reporters;
	}
	
	function GetReporters($staffid=null,$month,$year)
	{
		global $db;
		global $AppUser;
		global $myid;
		global $Staff;
		global $Course;
		
		if(!isset($staffid))
		{
			$staffid = $myid;
		}
		
		$reports = $db->smartQuery(array(
			'sql' => "Select * FROM report where staffid=:staffid and YEAR(date) =:year AND MONTH(date) =:month order by `date`,`starthour`",
			'par' => array('staffid'=>$staffid, 'year'=>$year, 'month'=>$month),
			'ret' => 'all'
		));
		
		$tempreport = array();
		
		$index=0;
		foreach($reports as $report)
		{
			if(isset($report['reportcopyid']) && $report['reportcopyid']!="" && $report['reportcopyid']!=0 )
			{
				$report['copyreport'] = $this->GetCopyReport($report['reportcopyid']);
			}
			$course = $db->smartQuery(array(
				'sql' => "Select projectid,name,code FROM course where courseid=:courseid",
				'par' => array('courseid'=>$report['courseid']),
				'ret' => 'fetch-assoc'
			));
			
			$project = $db->smartQuery(array(
				'sql' => "Select name FROM project where projectid=:projectid",
				'par' => array('projectid'=>$report['projectid']),
				'ret' => 'fetch-assoc'
			));
			
			$action = $db->smartQuery(array(
				'sql' => "Select subject FROM subjectreport where subjectreportid=:subjectreportid",
				'par' => array('subjectreportid'=>$report['actionid']),
				'ret' => 'fetch-assoc'
			));
			
			$report['coursename'] = $course['name'];
			$report['coursecode'] = $course['code'];
			$report['projectname'] = $project['name'];
			$report['projectid'] = $report['projectid'];
			$report['subject'] = $action['subject'];
			
			if(isset($report['finishhour']) && isset($report['starthour']))
			{
				$date_a = new DateTime($report['finishhour']);
				$date_b = new DateTime($report['starthour']);

				$interval = date_diff($date_a,$date_b);
				if($interval->h<10)
				{
					
					$h = '0'.$interval->h;
				}else
				{
					$h = $interval->h;
				}
				if($interval->i<10)
				{
					$i = '0'.$interval->i;
				}else
				{
					$i = $interval->i;
				}
				$report['hours'] = $h.':'.$i;
				$report['finishhour'] = date("H:i", strtotime($report['finishhour']));
				$report['starthour'] = date("H:i", strtotime($report['starthour']));
			}else
			{
				$report['hours'] = '';
				$report['finishhour'] = '';
				$report['starthour'] = '';
			}
			$report['date'] = date("d/m/y", strtotime($report['date']));
			
			$tempreport[] = $report;
		}
		
		return $tempreport;
	}
	
	function GetReporterPerimeter($staffid)
	{
			global $Project;
			global $Course;
			global $Subjectreport;
			$projects=$Project->GetMyProjectsReport($staffid);
			$options = array();
			foreach ($projects as $project)
			{
				$project['actions'] = $Subjectreport -> GetMyActionsOfProject($project['projectid'], $staffid);
				$project['courses'] = $Course -> GetCoursesOfProject($project['projectid'], $staffid);
			$options[] = $project;
			}
			return $options;
	}
	
	function GetCopyReport($reportcopyid)
	{
		global $db;
		$copyreport = $db->smartQuery(array(
			'sql' => "Select * FROM reportcopy where reportcopyid=:reportcopyid",
			'par' => array('reportcopyid'=>$reportcopyid),
			'ret' => 'fetch-assoc'
		));
		
		$course = $db->smartQuery(array(
				'sql' => "Select projectid,name,code FROM course where courseid=:courseid",
				'par' => array('courseid'=>$copyreport['courseid']),
				'ret' => 'fetch-assoc'
			));
			
			$project = $db->smartQuery(array(
				'sql' => "Select name FROM project where projectid=:projectid",
				'par' => array('projectid'=>$copyreport['projectid']),
				'ret' => 'fetch-assoc'
			));
			
			$action = $db->smartQuery(array(
				'sql' => "Select subject FROM subjectreport where subjectreportid=:subjectreportid",
				'par' => array('subjectreportid'=>$copyreport['actionid']),
				'ret' => 'fetch-assoc'
			));
			
			$copyreport['subject'] = $action['subject'];
			$copyreport['coursename'] = $course['name'];
			$copyreport['coursecode'] = $course['code'];
			$copyreport['projectname'] = $project['name'];
			$copyreport['projectid'] = $copyreport['projectid'];
			
			if(isset($copyreport['finishhour']) && isset($copyreport['starthour']))
			{
				$date_a = new DateTime($copyreport['finishhour']);
				$date_b = new DateTime($copyreport['starthour']);

				$interval = date_diff($date_a,$date_b);
				if($interval->h<10)
				{
					
					$h = '0'.$interval->h;
				}else
				{
					$h = $interval->h;
				}
				if($interval->i<10)
				{
					$i = '0'.$interval->i;
				}else
				{
					$i = $interval->i;
				}
				$copyreport['hours'] = $h.':'.$i;
				$copyreport['finishhour'] = date("H:i", strtotime($copyreport['finishhour']));
				$copyreport['starthour'] = date("H:i", strtotime($copyreport['starthour']));
			}else
			{
				$copyreport['hours'] = '';
				$copyreport['finishhour'] = '';
				$copyreport['starthour'] = '';
			}
			$copyreport['date'] = date("d/m/y", strtotime($copyreport['date']));
			
			return $copyreport;
	}
	
	// function GetReporters($staffid=null,$month,$year)
	// {
		// global $db;
		// global $AppUser;
		// global $myid;
		// $staffid = $myid;
		
		// $reports = $db->smartQuery(array(
			// 'sql' => "Select * FROM report where staffid=:staffid and YEAR(date) =:year AND MONTH(date) =:month",
			// 'par' => array('staffid'=>$staffid, 'year'=>$year, 'month'=>$month),
			// 'ret' => 'all'
		// ));
		// $tempreport = array();
		// $index=0;
		// foreach($reports as $report)
		// {
			// $course = $db->smartQuery(array(
				// 'sql' => "Select projectid FROM course where courseid=:courseid",
				// 'par' => array('courseid'=>$report['courseid']),
				// 'ret' => 'fetch-assoc'
			// ));
			
			// $project = $db->smartQuery(array(
				// 'sql' => "Select name FROM project where projectid=:projectid",
				// 'par' => array('projectid'=>$course['projectid']),
				// 'ret' => 'fetch-assoc'
			// ));
			
			// //$report['coursename'] = $course['name'];
			// //$report['coursecode'] = $course['code'];
			// $date_a = new DateTime($report['finishhour']);
			// $date_b = new DateTime($report['starthour']);

			// $interval = date_diff($date_a,$date_b);
			// if($interval->h<10)
			// {
				
				// $h = '0'.$interval->h;
			// }else
			// {
				// $h = $interval->h;
			// }
			// if($interval->i<10)
			// {
				// $i = '0'.$interval->i;
			// }else
			// {
				// $i = $interval->i;
			// }
			// $report['hours'] = $h.':'.$i;
			// $report['finishhour'] = date("G:i", strtotime($report['finishhour']));
			// $report['starthour'] = date("G:i", strtotime($report['starthour']));
			// $report['date'] = date("d/m/y", strtotime($report['date']));

			// // $report['hours'] = $report['finishhour'] - $report['starthour'];
			
			// if(isset($tempreport[$project['name']]))
			// {
				// $tempreport[$project['name']][] = $report;
			// }else
			// {
				
				// $courses = $db->smartQuery(array(
					// 'sql' => "Select courseid, code, name FROM course where projectid=:projectid",
					// 'par' => array('projectid'=>$course['projectid']),
					// 'ret' => 'fetch-all'
				// ));
			
				// $tempreport[$project['name']] = array();
				// $tempreport[$project['name']][] = $report;
				// $tempreport[$project['name']]['courses'] = $courses;
			// }
		// }
		// return $tempreport;
	// }
	
	 function SaveReporters($data)
	 {
		 global $Staff;
		 global $myid;
		 $lastactivedate = "";
		 foreach($data as $report)
		 {
			if(isset($report->status) && $report->status=='accept'){}
			else{
				$this->SaveReport($report);
			}
			if($report->date > $lastactivedate)
			{
				$lastactivedate = $report->date; 
			}
		 }
		 $pieces = explode("/", $lastactivedate);
		 $date = $pieces[2].'-'.$pieces[1].'-'.$pieces[0];
		 
		 $Staff->UpdateActiveDate($date,$myid);
	 }
	 
	 function SaveReport($report)
	 {
		global $db;
		global $AppUser;
		global $myid;
		$staffid = $myid;
		
		$pieces = explode("/", $report->date);
		$date = $pieces[2].'-'.$pieces[1].'-'.$pieces[0];
		
		$reportid = isset($report->reportid) ? $report->reportid : "";
		$courseid = isset($report->courseid) ? $report->courseid : "";
		$projectid = isset($report->projectid) ? $report->projectid : "";
		$starthour = (isset($report->starthour) && $report->starthour) ? $report->starthour : null;
		$finishhour = (isset($report->finishhour) && $report->finishhour) ? $report->finishhour : null;
		$carkm = isset($report->carkm) ? $report->carkm : "";
		$cost = isset($report->cost) ? $report->cost : "";
		$comment = isset($report->comment) ? $report->comment : "";
		$status = isset($report->status) ? $report->status : "";
		$reportcopyid = isset($report->reportcopyid) ? $report->reportcopyid : "";
		$actionid = isset($report->actionid) ? $report->actionid : "";
		
		if($reportid==-1)
		{
			$result = $db->smartQuery(array(
				'sql' => "INSERT INTO `reportcopy` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`) VALUES ( :date, :staffid, :courseid, :actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status);",
				'par' => array( 'date' => $date, 'staffid' => $staffid, 'courseid' => $courseid,'actionid' => $actionid,'projectid' => $projectid, 'starthour' => $starthour, 'finishhour' => $finishhour, 'carkm' => $carkm, 'cost' => $cost, 'comment' => $comment, 'status' => $status),
				'ret' => 'result'
			));
			
			$rid=$db->getLastInsertId();
			
			$result = $db->smartQuery(array(
				'sql' => "INSERT INTO `report` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`,`reportcopyid`) VALUES ( :date, :staffid, :courseid,:actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status, :reportcopyid);",
				'par' => array( 'date' => $date, 'staffid' => $staffid, 'courseid' => $courseid,'actionid' => $actionid,'projectid' => $projectid, 'starthour' => $starthour, 'finishhour' => $finishhour, 'carkm' => $carkm, 'cost' => $cost, 'comment' => $comment, 'status' => $status, 'reportcopyid' => $rid),
				'ret' => 'result'
			));
			
		}else
		{
			$result = $db->smartQuery(array(
				'sql' => "update reportcopy set date=:date, staffid=:staffid, courseid=:courseid,actionid=:actionid,projectid=:projectid, starthour=:starthour, finishhour=:finishhour, carkm=:carkm, cost=:cost, comment=:comment, status=:status where reportcopyid=:reportcopyid",
				'par' => array( 'date' => $date, 'staffid' => $staffid, 'courseid' => $courseid,'actionid' => $actionid,'projectid' => $projectid, 'starthour' => $starthour, 'finishhour' => $finishhour, 'carkm' => $carkm, 'cost' => $cost, 'comment' => $comment, 'status' => $status, 'reportcopyid' => $reportcopyid),
				'ret' => 'result'
			));
			
			$result = $db->smartQuery(array(
				'sql' => "update report set date=:date, staffid=:staffid, courseid=:courseid,actionid=:actionid,projectid=:projectid, starthour=:starthour, finishhour=:finishhour, carkm=:carkm, cost=:cost, comment=:comment, status=:status, reportcopyid=:reportcopyid where reportid=:reportid",
				'par' => array( 'date' => $date, 'staffid' => $staffid, 'courseid' => $courseid, 'actionid' => $actionid, 'projectid' => $projectid, 'starthour' => $starthour, 'finishhour' => $finishhour, 'carkm' => $carkm, 'cost' => $cost, 'comment' => $comment, 'status' => $status, 'reportcopyid' => $reportcopyid, 'reportid' => $reportid),
				'ret' => 'result'
			));
		}
	 }
	 
	  function SaveReportofUnderstaff($report)
	 {
		global $db;
		
		$pieces = explode("/", $report->date);
		$date = $pieces[2].'-'.$pieces[1].'-'.$pieces[0];
		
		$reportid = isset($report->reportid) ? $report->reportid : "";
		//$date = isset($report->date) ? date("Y-m-d", strtotime($report->date)) : "";
		$staffid = isset($report->staffid) ? $report->staffid : "";
		$courseid = isset($report->courseid) ? $report->courseid : "";
		$projectid = isset($report->projectid) ? $report->projectid : "";
		$actionid = isset($report->actionid) ? $report->actionid : "";
		$starthour = (isset($report->starthour) && $report->starthour!='') ? $report->starthour : null;
		$finishhour = (isset($report->finishhour) && $report->finishhour!='') ? $report->finishhour : null;
		$carkm = isset($report->carkm) ? $report->carkm : "";
		$cost = isset($report->cost) ? $report->cost : "";
		$comment = isset($report->comment) ? $report->comment : "";
		$status = isset($report->status) ? $report->status : "";
		$reportcopyid = isset($report->reportcopyid) ? $report->reportcopyid : "";
		
		if($status=='specialapproval' && $actionid!="" && $actionid!='0')
		{
			$status='';
		}
		
		if($reportid==-1)
		{
			$result = $db->smartQuery(array(
					'sql' => "INSERT INTO `report` (`date`,`staffid`,`courseid`,`actionid`,`projectid`,`starthour`,`finishhour`,`carkm`,`cost`,`comment`,`status`,`reportcopyid`) VALUES ( :date, :staffid, :courseid,:actionid,:projectid, :starthour, :finishhour, :carkm, :cost, :comment, :status, :reportcopyid);",
					'par' => array( 'date' => $date, 'staffid' => $staffid, 'courseid' => $courseid,'actionid' => $actionid,'projectid' => $projectid, 'starthour' => $starthour, 'finishhour' => $finishhour, 'carkm' => $carkm, 'cost' => $cost, 'comment' => $comment, 'status' => $status, 'reportcopyid' => ""),
					'ret' => 'result'
			));
		}else
		{
			$result = $db->smartQuery(array(
					'sql' => "UPDATE report SET date=:date, staffid=:staffid, courseid=:courseid,actionid=:actionid,projectid=:projectid, starthour=:starthour, finishhour=:finishhour, carkm=:carkm, cost=:cost, comment=:comment, status=:status, reportcopyid=:reportcopyid WHERE reportid=:reportid",
					'par' => array( 'date' => $date, 'staffid' => $staffid, 'courseid' => $courseid, 'actionid' => $actionid, 'projectid' => $projectid, 'starthour' => $starthour, 'finishhour' => $finishhour, 'carkm' => $carkm, 'cost' => $cost, 'comment' => $comment, 'status' => $status, 'reportcopyid' => $reportcopyid, 'reportid' =>  $reportid),
					'ret' => 'result'
			));
		}
	 }

	 function SetReportApproval($reportids, $status){
	 	global $db;
	 	$result="no ids supplied";
	 	foreach ($reportids as $reportid)
	 	{
	 	$result = $db->smartQuery(array(
	 			'sql' => "UPDATE report SET status=:status WHERE reportid=:reportid",
	 			'par' => array('reportid' => $reportid, 'status' => $status),
	 			'ret' => 'result'
	 	));
	 	}
	 	return $result;
	 }
}