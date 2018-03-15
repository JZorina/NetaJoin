<?php
class User{
	//student user signup
	function signup($email, $pass1, $pass2, $firstname, $lastname, $phone, $phone2, $genderid, $cityid, $image, $tz, $birthday, $address)//$image not requierd// tz check if not exist
	{
		if($pass1!= $pass2)
		{
			return (object)array("error"=>"password1 != password2");
		}
		$ErrorPassword = checkPassword($pass1);
		if($ErrorPassword!==true)
		{
			return (object)array("error"=>$ErrorPassword);
		}
		global $db;
		$userIdByTz = $this->GetUserIdByTz($tz);
		if($userIdByTz)
		{
			return (object)array("error"=>"tz exist in the system");
		}
		else if($this -> getUserIdByEmail($email)!=null)
		{
			return (object)array("error"=>"email exist in the system");
		}
		else
		{
			$Studentid = $db->getUUID();
			$registerdate =  date('Y-m-d H:i:s', time());
			if($birthday!="")
			{
				$pieces = explode("/", $birthday);
				if(count($pieces)>2)
				$birthday = $pieces[2].'-'.$pieces[1].'-'.$pieces[0];
			}
			$result=$db->smartQuery(array(
			'sql' => "INSERT INTO `student` (`studentid`,`firstname`,`lastname`,`tznumber`,`phone`,`phone2`,`genderid`,`cityid`,`adress`,`birthday`,`email`,`image`,`registerdate`,`status`) VALUES (:studentid,:firstname,:lastname,:tznumber,:phone,:phone2,:genderid,:cityid,:adress,:birthday,:email,:image,:registerdate,:status)",
			'par' => array('firstname' => ''.$firstname,'studentid' => $Studentid,'lastname' => ''.$lastname,'tznumber' => ''.$tz,'phone' => ''.$phone,'phone2' => ''.$phone2,'genderid' => ''.$genderid,'cityid' => ''.$cityid,'adress' => ''.$adress,'birthday' => $birthday,'email' => ''.$email,'image' => ''.$image,'registerdate' => ''.$registerdate,'status' => '1'),					
			'ret' => 'result'
			));
			
			$password = hash('sha256', $pass1);
			
			$result=$db->smartQuery(array(
			'sql' => "INSERT INTO `user` (`userid`,`email`,`password`,`type`,`needacceptregister`) VALUES (:userid,:email,:password,:type,:needacceptregister)",
					'par' => array('userid' => $Studentid,'email' => ''.$email,'password' => ''.$password,'type' => 'student','needacceptregister' => "xxx"),					
			'ret' => 'result'
			));
			return true;
		}
	}
	//mail verification management
	function sendSignupMail($email)
	{
		global $db;
		global $mail;
		if($this -> getUserIdByEmail($email)!=null)
		{
			if($this -> isEmailApproved($email))
			{
				return (object) array("error" => "email was already approved");
			}
		}
		else {
			return (object) array("error" => "couldn't find a user with this email");
		}
		//create a random registration token
		$registerid = md5(uniqid(rand(), true));
		//give that token to the user with the corresponding email
		$result=$db->smartQuery(array(
				'sql' => "UPDATE `user` SET `needacceptregister`=:needacceptregister WHERE `email`=:email",
				'par' => array('email' => ''.$email, 'needacceptregister' => $registerid),
				'ret' => 'result'
		));
		
		$mail->setFrom('noreply@dc.appleseeds.org.il', 'Digital Classroom');
		$mail->addAddress($email);     // Add a recipient
		$mail->addReplyTo('leet@appleseeds.org.il', 'Information');
		
		//Set email format to HTML
		$mail->isHTML(true);
		$mail->CharSet = 'UTF-8';
		$subject = 'אפליקצית הכיתה הדיגיטלית - הפעלת חשבון';
		$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
		$mail->Subject = $subject;
		$link = 'https://'.$_SERVER['SERVER_NAME'].'/server/acceptregister.php?id='.$registerid;
		$message = "<span dir='rtl' style='text-align:right'><p> נא ללחוץ על הלינק על מנת לאשר את כתובת המייל הזו: <a href=\"".$link."\">".$link."</a></p><p>זהו מייל אוטומטי שנשלח ממערכת הכיתה הדיגיטלית.</p></span>";
		$mail->Body    = $message;
		$message = "נא ללחוץ על הלינק על מנת לאשר את כתובת המייל הזו: ".$link." זהו מייל אוטומטי שנשלח ממערכת הכיתה הדיגיטלית.";
		$mail->AltBody = $message;
		if(!$mail->send()) {
			return false;
		}
		else
			return true;
	}
	function reg_ChangeEmail($email1, $email2)
	{
		global $db;
		if($email2!=$email1&&$this->getUserIdByEmail($email2)!=null)
		{
			return (object)array("error"=>"email exist in the system");
		}
		if($this -> getUserIdByEmail(email1)!=null)
		{
			if($this -> isEmailApproved($email1))
			{
				if($email2!=$email1)
				{
					$result=$db->smartQuery(array(
						'sql' => "UPDATE `user` SET `email`=:email2 WHERE `email`=:email1",
						'par' => array('email1' => ''.$email1, 'email2' => ''.$email2),
						'ret' => 'result'
					));
				}
				return $this -> sendSignupMail($email2);
			}
			else
			{
				return (object) array("error" => "email to replace was already approved");
			}
		}
		else
		{
			return (object) array("error" => "email to replace wasn't found");
		}
	}
	function isEmailApproved ($email)
	{
		global $db;
		$regitrationToken = $db->smartQuery(array(
				'sql' => "SELECT `needacceptregister` FROM user WHERE email=:email",
				'par' => array('email'=>$email),
				'ret' => 'fetch-assoc'
		));
		if(isset($regitrationToken['needacceptregister']) && $regitrationToken['needacceptregister']=="")
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function approveUserEmail ($userid)
	{
		global $db;
		return $db->smartQuery(array(
			'sql' => "UPDATE `user` SET needacceptregister='' WHERE userid=:userid;",
			'par' => array('userid' => $userid),
			'ret' => 'result'
		));
	}
	
	//login
	function login($pass,$email){
		
		if($this->getUserIdByEmail($email)==null)
		{
			return (object)array("error" => "incorrect credentials");
		}
		if(!$this->isEmailApproved($email))
		{
			return (object)array("error"=>"need to accept register");
		}
		if(!$this->isNonRepetitiveLoginAttempt($email))
		{
			return (object)array("error"=>"too many failed login attempts - please try again later");
		}
		$pass = hash('sha256', $pass);
		$userId = $this->getUserIdByCredentials($pass,$email);
		if(!$userId)
		{
			$this->incrementLoginAttemptDelay($email);
			return (object)array("error" => "incorrect credentials");
		}
		$token=$userId.md5(uniqid(rand(), true));
		$this->startUserSession($userId, $token);
		return (object)array("token" => $token);
	}
	
	function isNonRepetitiveLoginAttempt($email)
	{
		global $db;
		$user=$db->smartQuery(array(
				'sql' => "SELECT `logintimestamp` FROM `user` WHERE `email`=:email",
				'par' => array('email'=>$email),
				'ret' => 'fetch-assoc'
		));
		if(isset($user['logintimestamp']))
		{
			return $user['logintimestamp'] < time();
		}
		return false;
	}
	
	function incrementLoginAttemptDelay($email)
	{
		global $db;
		$user=$db->smartQuery(array(
				'sql' => "SELECT `logintimestamp`, `passtry` FROM `user` WHERE `email`=:email",
				'par' => array('email'=>$email),
				'ret' => 'fetch-assoc'
		));
		$passtry = $user['passtry'] + 1;
		$logintimestamp = time() + $passtry;
		$db->smartQuery(array(
				'sql' => "UPDATE `user` SET `logintimestamp`=:logintimestamp, `passtry`=:passtry   WHERE `email`=:email",
				'par' => array( 'logintimestamp' => $logintimestamp,'passtry' => $passtry,'email'=>$email),
				'ret' => 'result'
		));
	}
	
	function removeOverflowingToken($userid)
	{
		$maxTokens = 3;
		global $db;
		$existingTokens = $db->smartQuery(array(
				'sql' => "SELECT * FROM `user_session` WHERE `userid`=:userid ORDER BY `createtime`",
				'par' => array('userid' => $userid),
				'ret' => 'all'
		));
		if(count($existingTokens)>=$maxTokens)
		{
			for ($i=0; $i<=count($existingTokens)-$maxTokens;$i++)
			{
				$this->logout($existingTokens[$i]['token']);
			}
		}
	}
	function startUserSession ($userid,$token){
		$this -> removeOverflowingToken($userid);
		global $db;
		$now = date("Y-m-d H:i:s");
		$db->smartQuery(array(
				'sql' => "INSERT INTO `user_session` (`token`, `userid`, `createtime`) VALUES (:token,:userid, :now);",
				'par' => array('userid' => $userid,'token'=>$token, 'now'=>$now),
				'ret' => 'result'
		));
	}
	
	function saveFireBaseToken($token,$fbtoken){
		global $db;
		$userid = $this->GetUserIdByToken($token);
		if(isset($userid)){
			//remove the fb token from any user that currently has it
			$db->smartQuery(array(
					'sql' => "UPDATE `user` SET fbtokenid=''  WHERE `fbtokenid`=:fbtokenid",
					'par' => array( 'fbtokenid' => $fbtoken),
					'ret' => 'result'
			));
			//set fb token for the given user
			$userid = $this->GetUserIdByToken($token);
			$ans = $db->smartQuery(array(
					'sql' => "UPDATE `user` SET fbtokenid=:fbtokenid  WHERE `userid`=:userid",
					'par' => array( 'fbtokenid' => $fbtoken,'userid'=>$userid),
					'ret' => 'result'
			));
			return $ans;
		}
		else
		{
			return (object)array("token"=>"token not found");
		}
	}
	
	/**
	 * gets data about a user who has a currently active session using their session token
	 * @param String $token - a token from the client to identify the session
	 * @return Array - an object containing basic properties of the user who has a matching token
	 */
	function getLoggedInUser($token)
	{
		global $db;
		$userId = $this->GetUserIdByToken($token);
		if(isset($userId))
		{
			$user = $db->smartQuery(array(
					'sql' => "SELECT userid, email, isAdmin FROM `user` WHERE `userid`=:userId;",
					'par' => array('userId' => $userId),
					'ret' => 'fetch-assoc'
			));
			return $user;
		}
		return (object)array("error" => "user not found");
	}
	
	/**
	 * gets the role of a user who has a currently active session using their session token
	 * @param String $token - a token from the client to identify the session
	 * @return String/boolean - a string singnifying the role of the user, and a 
	 */
	function getLoggedInUserType($token)
	{
		global $db;
		$userId = $this->GetUserIdByToken($token);
		if(isset($userId))
		{
			$user = $db->smartQuery(array(
					'sql' => "SELECT type FROM `user` WHERE `userid`=:userId;",
					'par' => array('userId' => $userId),
					'ret' => 'fetch-assoc'
			));
			return $user['type'];
		}
		return false;
	}

	/**
	 * logs out of a particular session, according to a session token sent from a client
	 * @param String $token - the token of the session that should be terminated
	 * @return boolean|StdClass - true if all went well, an object with error details otherwise
	 */
	function logout($token){
		global $db;
		$userid = $this->GetUserIdByToken($token);
		if(isset($userid)){
			$result = $db->smartQuery(array(
					'sql' => "DELETE FROM `user_session` WHERE token=:token;",
					'par' => array('token' => $token),
					'ret' => 'result'
			));
			return true;
		}
		else
		{
			return (object)array("error" => "token ".$userid." not found");
		}
	}
	
	/**
	 * checks whether a user has a session running currently
	 * @param String $token - some token sent from the client
	 * @return boolean|StdClass returns true if the token was found in the sessions table, otherwise returns an Object with error details
	 */
	function isLoggedIn($token){
		global $db;
		global $Student;
		$session = $this->GetUserIdByToken($token);
		if($session!=null)
		{
			$Student->UpdateActiveDate($session);
			return true;
		}
		else return (object)array("error" => "token not found");
	}
	function GetUserIdByTz($tz)
	{
		global $db;
		if($tz=="")
			return false;
		$user = $db->smartQuery(array(
			'sql' => "
				SELECT student.tznumber, student.studentid AS userid
				FROM student
				WHERE student.tznumber = :tznumber
				UNION ALL
				SELECT staff.tznumber, staff.staffid AS userid
				FROM staff
				WHERE staff.tznumber = :tznumber",
			'par' => array( 'tznumber' => $tz),
			'ret' => 'fetch-assoc'
		));
		if(isset($user['userid']))
			return $user['userid'];
		return false;
	}
	function GetUserIdByToken($token){
		global $db;
		if(!isset($token) || $token=='')
		{
			return null;
		}
		$userid = $db->smartQuery(array(
		'sql' => "SELECT `userid` FROM `user_session` WHERE `token`=:token",
		'par' => array( 'token' => $token),
		'ret' => 'fetch-assoc'
		));
		return isset($userid['userid'])?$userid['userid']:null;
	}
	function getUserIdByEmail($email)
	{
		global $db;
		$user = $db->smartQuery(array(
				'sql' => "SELECT userid FROM user WHERE email=:email",
				'par' => array('email'=>$email),
				'ret' => 'fetch-assoc'
		));
		if(isset($user['userid']))
		{
			return $user['userid'];
		}else
		{
			return null;
		}
	}
	function InitPassApp($email)
	{
		global $db;
		$user = $this->getUserIdByEmail($email);
		if($user!=null)
		{
			$missPassToken = md5(uniqid(rand(), true));
			
			$db->smartQuery(array(
			'sql' => "UPDATE `user` SET missPassToken=:missPassToken WHERE email=:email",
					'par' => array('missPassToken' => $missPassToken, 'email'=>$email),
			'rel' => 'result'
			));
			
			global $mail;
			$mail->setFrom('noreply@dc.appleseeds.org.il', 'Digital Classroom');
			$mail->addAddress($email);     // Add a recipient
			$mail->addReplyTo('leet@appleseeds.org.il', 'Information');
			
			//Set email format to HTML
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			$subject = 'אפליקצית הכיתה הדיגיטלית - החלפת סיסמה באפליקציה';
			$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
			$mail->Subject = $subject;
			$link = 'https://'.$_SERVER['SERVER_NAME'].'/app/main.html#/changePassword/forget/'.$missPassToken;
			$message = "<span dir='rtl' style='text-align:right'><p>"."נא ללחוץ על הלינק על מנת ליצור סיסמה חדשה:<br> <a href=\"".$link."\">".$link."</a></p><p>זהו מייל אוטומטי שנשלח ממערכת הכיתה הדיגיטלית.</p></span>";
			$mail->Body    = $message;
			$message = "נא ללחוץ על הלינק על מנת ליצור סיסמה חדשה: ".$link." זהו מייל אוטומטי שנשלח ממערכת הכיתה הדיגיטלית.";
			$mail->AltBody = $message;
			
			$mail->send();
			return (object)array("success"=>"password sent to this email");
		}else
		{
			return (object)array("error"=>"email not exist");
		}
	}
	function ChangeMisPass($token,$pass1,$pass2)
	{
		global $db;
		if($pass1!= $pass2)
		{
			return (object)array("error"=>"password1 != password2");
		}
		if($token=='')
		{
			return (object)array("error"=>"token can't be empty");
		}
		$ErrorPassword = checkPassword($pass1);
		if($ErrorPassword!==true)
		{
			return (object)array("error"=>$ErrorPassword);
		}
		if($this->MissPassTokenExists($token))
		{
			$pass1 = hash('sha256', $pass1);
			$db->smartQuery(array(
			'sql' => "UPDATE `user` SET password=:password, missPassToken='', passtry=0 WHERE missPassToken=:missPassToken;",
			'par' => array('password' => $pass1,'missPassToken'=>$token),
			'ret' => 'result'
			));
			return (object)array("success"=>"password updated");
		}else
		{
			return (object)array("token"=>"token not found");
		}
	}
	
	function MissPassTokenExists($missPassToken)
	{
		global $db;
		if(!isset($missPassToken) || $missPassToken=='')
		{
			return false;
		}
		$userid = $db->smartQuery(array(
		'sql' => "SELECT userid FROM `user` WHERE `missPassToken`=:missPassToken",
		'par' => array( 'missPassToken' => $missPassToken),
		'ret' => 'fetch-assoc'
		));
		return isset($userid['userid']);
	}
	
	function ChangeExistPass($token,$pass,$newpass1,$newpass2)
	{
		global $db;
		$UserId = $this->GetUserIdByToken($token);
		if(isset($UserId))
		{
			$userPass = $this->GetUserPassByid($UserId);
			$pass = hash('sha256', $pass);
			if($userPass != $pass)
			{
				return (object)array("error"=>"pass not exist");
			}
			
			if($newpass1 != $newpass2)
			{
				return (object)array("error"=>"newpass not equals");
			}
			
			$ErrorPassword = checkPassword($newpass1);
			if($ErrorPassword!==true)
			{
				return (object)array("error"=>$ErrorPassword);
			}
			
			$newpass = hash('sha256', $newpass1);
			$db->smartQuery(array(
			'sql' => "UPDATE `user` SET password=:password WHERE userid=:userid;",
					'par' => array('password' => $newpass,'userid'=>$UserId),
			'ret' => 'result'
			));
			return (object)array("success"=>"password updated");
		}else
		{
			return (object)array("error"=>"token not found");
		}
	}
	
	function GetUserPassByid($userid)
	{
		global $db;
		$user = $db->smartQuery(array(
		'sql' => "SELECT password FROM `user` WHERE `userid`=:userid",
		'par' => array( 'userid' => $userid),
		'ret' => 'fetch-assoc'
		));
		return $user['password'];
	}
	
	function getUserIdByCredentials($pass,$email){
		global $db;
		$user=$db->smartQuery(array(
		'sql' => "SELECT `userid` FROM `user` WHERE `password`=:pass AND `email`=:email",
		'par' => array('pass' => $pass, 'email' => $email),
		'ret' => 'fetch-assoc'
		));
		if(isset($user['userid']))
		{
			return $user['userid'];
		}else
		{
			return null;
		}
	}
	
	function GetUser($pass,$email)
	{
		global $db;
		$User=$db->smartQuery(array(
		'sql' => "SELECT userid, needacceptregister FROM `user` WHERE password=:pass AND email=:email",
		'par' => array('pass' => $pass, 'email' => $email),
		'ret' => 'fetch-assoc'
		));
		
		if(isset($User['userid']) && $User['userid']!="")
		{
			return $User;
		}else
		{
			return null;
		}
	}
	function GetUserByid($id){
		global $db;
		$user = $db->smartQuery(array(
		'sql' => "SELECT userid, email, type FROM `user` WHERE `userid`=:userid",
		'par' => array( 'userid' => $id),
		'ret' => 'fetch-assoc'
		));
		return $user;
	}
}