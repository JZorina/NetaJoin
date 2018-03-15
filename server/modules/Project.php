<?php

class Project{
	
	function GetAllProjects()
	{
		global $db;
		$projects =  $db->smartQuery(array(
			'sql' => "Select * FROM project order by `IsShow` Desc",
			'par' => array(),
			'ret' => 'all'
		));
		return $projects;
	}
	
	function GetProjectsOfCourses($courses)
	{
		global $db;
		$coursesid = array();
		foreach($courses as $course)
		{
			$coursesid[]= $course->courseid;
		}
		$ids = "('" . join("','",$coursesid)  . "')";
		//$ids = '(' . implode(',', array_map('intval', $coursesid)) . ')';
		$projectsid =  $db->smartQuery(array(
			'sql' => "Select projectid FROM course where courseid IN $ids and projectid<>'0' GROUP BY projectid",
			'par' => array(),
			'ret' => 'all'
		));
		
		$project = array();
		foreach($projectsid as $projectid)
		{
			$project[] = $this->GetProjectById($projectid['projectid']);
		}
		return $project;
	}
	
	function GetProjectById($id)
	{
		global $db;
		$project =  $db->smartQuery(array(
			'sql' => "Select * FROM project where projectid = :projectid",
			'par' => array('projectid'=>$id),
			'ret' => 'fetch-assoc'
		));
		return $project;
	}
	
	function GetProjects()
	{
		global $db;
		$projects =  $db->smartQuery(array(
			'sql' => "
			SELECT
				project.projectid AS projectid, project.name AS projectname,
				projecttag.projecttagid AS projecttagid, projecttag.name AS projecttagname
			FROM
				project
				JOIN projecttag ON projecttag.projectid = project.projectid
			WHERE project.isShow=1 AND projecttag.isShow=1",
			'par' => array(),
			'ret' => 'all'
		));
		return nestArray($projects, 'projectid', array(
		array('nestIn'=>'projecttags', 'nestBy'=>'projecttagid', 'fieldsToNest'=>array('projecttagid', 'projecttagname'))
		));
	}
	
	function AddProject($data)
	{
		global $db;
		foreach($data as $project)
		{
			if(isset($project->projectid))
			{
				$projectid = $project->projectid;
				$result =  $db->smartQuery(array(
				'sql' => "update project set name=:name,IsShow=:IsShow where projectid=:projectid",
				'par' => array('name'=>$project->name,'IsShow'=>$project->IsShow, 'projectid'=>$projectid),
				'ret' => 'result'
				));
			}else
			{
				$result =  $db->smartQuery(array(
				'sql' => "insert into project (name,IsShow)values(:name,:IsShow)",
				'par' => array('name'=>$project->name,'IsShow'=>$project->IsShow),
				'ret' => 'result'
				));
				$projectid = $db->getLastInsertId();
			}
			if(isset($project->tagproject) && count($project->tagproject)>0)
			{
				foreach($project->tagproject as $tag)
				{
					if(isset($tag->tagprojectid))
					{
						$tagprojectid = $tag->tagprojectid;
						$result =  $db->smartQuery(array(
						'sql' => "update tagproject set name=:name,IsShow=:IsShow where tagprojectid=:tagprojectid",
						'par' => array('name'=>$tag->name,'tagprojectid'=>$tagprojectid,'IsShow'=>$tag->IsShow),
						'ret' => 'result'
						));
					}else
					{
						$result =  $db->smartQuery(array(
						'sql' => "insert into tagproject (name,projectid,IsShow)values(:name,:projectid,:IsShow)",
						'par' => array('name'=>$tag->name,'projectid'=>$projectid,'IsShow'=>$tag->IsShow),
						'ret' => 'result'
						));
					}
				}
			}
		}
		return $result;
	}
	
	function GetMyProjectsReport($staffid=null)
	{
		global $db;
		global $Project;
		global $myid;
		
		if(!isset($staffid))
		{
			$staffid=$myid;
		}
		
		$projectids = $db->smartQuery(array(
				'sql' => "Select distinct projectid FROM staffreportsubject where staffid=:staffid and status=:status",
				'par' => array('staffid'=>$staffid, 'status'=>true),
				'ret' => 'all'
			));
			
		$myproject = array();
		foreach($projectids as $projectid)
		{
			$project = $this->GetProjectById($projectid['projectid']);
			$projectid['projectname'] = $project['name'];
			$myproject[] = $projectid;
		}
		
		return $myproject;
	}
		
}