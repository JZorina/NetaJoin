<?php

class Question{
	
	function GetUniformQuestions()
	{
		global $db;
		$questions =  $db->smartQuery(array(
			'sql' => "Select * FROM question",
			'par' => array(),
			'ret' => 'all'
		));
		$temp = array();
		$questions2 = array();
		foreach($questions as $question)
		{
			foreach($question as $key=>$value)
			{
				if($key=="answer")
				{
					$answer = unserialize($value);
					if($answer==false)
					{
						$temp[$key] =array();
					}else
					{
						$temp[$key] = $answer;
					}
				}else
				{
					$temp[$key]=$value;
				}
			}
			
			$questions2[] = $temp;
			
		}
		return $questions2;
	}
	
	function GetMadrichQuestions()
	{
		global $db;
		$questions =  $db->smartQuery(array(
			'sql' => "Select * FROM madrichquestion",
			'par' => array(),
			'ret' => 'all'
		));
		$temp = array();
		$questions2 = array();
		foreach($questions as $question)
		{
			foreach($question as $key=>$value)
			{
				if($key=="answer")
				{
					$answer = unserialize($value);
					if($answer==false)
					{
						$temp[$key] =array();
					}else
					{
						$temp[$key] = $answer;
					}
				}else
				{
					$temp[$key]=$value;
				}
			}
			
			$questions2[] = $temp;
			
		}
		return $questions2;
	}
	
	function AddMadrichQuestion($data)
	{
		global $db;
		foreach($data as $question)
		{
			$questioninarabic = isset($question->questioninarabic)?$question->questioninarabic:'';
			$answer = serialize($question->answer);
			if(isset($question->questionid))
			{
				$questionid = $question->questionid;
				$result =  $db->smartQuery(array(
				'sql' => "update madrichquestion set question=:question,questioninarabic=:questioninarabic, answer=:answer,IsShow=:IsShow where questionid=:questionid",
				'par' => array('question'=>$question->question,'questioninarabic'=>$questioninarabic,'IsShow'=>$question->IsShow, 'answer'=>$answer, 'questionid'=>$questionid),
				'ret' => 'result'
				));
			}else
			{
				$result =  $db->smartQuery(array(
				'sql' => "insert into madrichquestion (question,questioninarabic,answer,IsShow)values(:question,:questioninarabic, :answer,:IsShow)",
				'par' => array('question'=>$question->question,'questioninarabic'=>$questioninarabic,'IsShow'=>$question->IsShow, 'answer'=>$answer),
				'ret' => 'result'
				));
			}
		}
		return $result;
	}
	
	function AddUniformQuestions($data)
	{
		global $db;
		foreach($data as $question)
		{
			$questioninarabic = isset($question->questioninarabic)?$question->questioninarabic:'';
			$answer = serialize($question->answer);
			if(isset($question->questionid))
			{
				$questionid = $question->questionid;
				$result =  $db->smartQuery(array(
				'sql' => "update question set question=:question,questioninarabic=:questioninarabic, answer=:answer,IsShow=:IsShow where questionid=:questionid",
				'par' => array('question'=>$question->question,'questioninarabic'=>$questioninarabic,'IsShow'=>$question->IsShow, 'answer'=>$answer, 'questionid'=>$questionid),
				'ret' => 'result'
				));
			}else
			{
				$result =  $db->smartQuery(array(
				'sql' => "insert into question (question,questioninarabic,answer,IsShow)values(:question,:questioninarabic, :answer,:IsShow)",
				'par' => array('question'=>$question->question,'questioninarabic'=>$questioninarabic,'IsShow'=>$question->IsShow, 'answer'=>$answer),
				'ret' => 'result'
				));
			}
		}
		return $result;
	}
	
	//////get question for api/////
	///////////////////////////////
	///////////////////////////////
	///////////////////////////////
	function GetUniformQuestionsApi()
	{
		global $db;
		$questions =  $db->smartQuery(array(
			'sql' => "Select * FROM question",
			'par' => array(),
			'ret' => 'all'
		));
		$temp = array();
		$questions2 = array();
		foreach($questions as $question)
		{
			foreach($question as $key=>$value)
			{
				if($key=="answer")
				{
					$answer = unserialize($value);
					if($answer==false)
					{
						$temp['answers'] =array();
					}else
					{
						
						foreach($answer as $key2=>$ans)
						{
							$temp['answers'][$key2] = (object)array("text" => $ans->name,"textarabic" => $ans->nameinarabic);
						}
					}
				}else
				{
					$temp[$key]=$value;
				}
			}
			
			$questions2[] = $temp;
			
		}
		return $questions2;
	}
	
	function GetMadrichQuestionsApi()
	{
		global $db;
		$questions =  $db->smartQuery(array(
			'sql' => "Select * FROM madrichquestion",
			'par' => array(),
			'ret' => 'all'
		));
		$temp = array();
		$questions2 = array();
		foreach($questions as $question)
		{
			foreach($question as $key=>$value)
			{
				if($key=="answer")
				{
					$answer = unserialize($value);
					if($answer==false)
					{
						$temp['answers'] =array();
					}else
					{
						foreach($answer as $key2=>$ans)
						{
							$temp['answers'][$key2] = (object)array("text" => $ans->name,"textarabic" => $ans->nameinarabic);
						}
					}
				}else
				{
					$temp[$key]=$value;
				}
			}
			
			$questions2[] = $temp;
			
		}
		return $questions2;
	}
}