<?php
class Subjectreport{
	
	function GetSubjectreportById($cid)
	{
		global $db;
		$Subjectreport =  $db->smartQuery(array(
			'sql' => "Select * FROM subjectreport where subjectreportid = :subjectreportid",
			'par' => array('subjectreportid'=>$cid),
			'ret' => 'fetch-assoc'
		));
		return $Subjectreport;
	}
	
	function GetSubjectreports()
	{
		global $db;
		$subjectreports = $db->smartQuery(array(
			'sql' => "Select * FROM subjectreport",
			'par' => array(),
			'ret' => 'all'
		));
		return $subjectreports;
	}
	
	function AddSubjectreports($data)
	{
		global $db;
		foreach($data as $subjectreport)
		{
			if(isset($subjectreport->subjectreportid))
			{
				$id = $subjectreport->subjectreportid;
				$result = $db->smartQuery(array(
					'sql' => "update subjectreport set subject=:subject,subjectnum=:subjectnum, IsShow =:IsShow  where subjectreportid=:id",
					'par' => array('subject'=>$subjectreport->subject,'subjectnum'=>$subjectreport->subjectnum,'IsShow'=>$subjectreport->IsShow, 'id'=>$id),
					'ret' => 'result'
				));
			}else
			{
				$result = $db->smartQuery(array(
					'sql' => "insert into subjectreport (subject,subjectnum,IsShow)values(:subject,:subjectnum,:IsShow)",
					'par' => array('subject'=>$subjectreport->subject,'subjectnum'=>$subjectreport->subjectnum, 'IsShow'=>$subjectreport->IsShow),
					'ret' => 'result'
				));
			}
		}
		
		return $result;
	}
	
	function GetMyActionsOfProject($pid, $staffid)
	{
		global $db;
		global $myid;
		
		if(!isset($staffid))
		{
			$staffid=$myid;
		}
		
		$subjectreports = $db->smartQuery(array(
				'sql' => "Select distinct subjectreportid FROM staffreportsubject where staffid=:staffid and status=:status and projectid=:projectid",
				'par' => array('staffid'=>$staffid, 'status'=>true, 'projectid'=>$pid),
				'ret' => 'all'
			));
			
		$actions = array();
		foreach($subjectreports as $subjectreport)
		{
			$subjects = $this->GetSubjectreportById($subjectreport['subjectreportid']);
			$subjectreport['actionname'] = $subjects['subject'];
			$actions[] = $subjectreport;
		}
		return $actions;
	}
}