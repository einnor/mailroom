<?php
include '../classes/inc.php';
require 'Slim/Slim.php';
$marray=array();

global $app;

$app= new Slim();

$app->post('/logout','logout');//Done
$app->post('/login','login');//Done

//Users
$app->post('/users','createUser');//Done
$app->get('/users/all','getAllUsers');//Done
$app->get('/users/common','getAllCommonUsers');//Done
$app->get('/users/mailroom','getAllMailroomUsers');//Done
$app->get('/users/admin','getAllAdminUsers');//Done

//Mail
//$app->get('/mails/all','getAllMail');//Done
$app->post('/mails','submitMail');//Done
$app->put('/mails/:id','updateMail');//Done
$app->put('/mails/state/:id','changeState');//Done
$app->put('/mails/recipient/confirm','confirmReceived');//Done
$app->get('/mails/state/:id','getState');
$app->delete('/mails/:id','deleteMail');//Done
$app->get('/mails/track/:id','trackMail');//Done
$app->get('/mails/:id','getMailDetails');//Done
$app->get('/mails/code/:id','getMailDetailsByCode');//Done

//Tracker 1.Submitted
$app->get('/mails/get/submitted','getAllSubmittedMail');//Done
$app->get('/mails/get/submitted/:id','getSubmittedMail');//Done

//Tracker 2.Sighted
$app->get('/mails/get/sighted','getAllSightedMail');//Done
$app->get('/mails/get/sighted/:id','getSightedMail');//Done

//Tracker 3.Dispatched
$app->get('/mails/get/dispatched','getAllDispatchedMail');//Done
$app->get('/mails/get/dispatched/:id','getDispatchedMail');//Done

//Tracker 4.Exception
$app->get('/mails/get/exception','getAllExceptionMail');//Done
$app->get('/mails/get/exception/:id','getExceptionMail');//Done

//Tracker 5.Delivered
$app->get('/mails/get/delivered','getAllDeliveredMail');//Done
$app->get('/mails/get/delivered/:id','getDeliveredMail');//Done

//Tracker 6.Received
$app->get('/mails/get/received','getAllReceivedMail');//Done
$app->get('/mails/get/received/:id','getReceivedMail');//Done


//Tracker 8.Missing
$app->get('/mails/get/missing','getAllMissingMail');//Done
$app->get('/mails/get/missing/:id','getMissingMail');//Done


$app->run();



//Login & Logout
function login(){$pdo = getConnection();
	global $app;
	$request = $app->request();
	$reqdata = json_decode($request->getBody());
	
	$ss="SELECT id,name,password,dept,role FROM user WHERE email=?";
	$stmt = $pdo->prepare($ss);$stmt->execute(array($reqdata->email));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($stmt->rowCount()>0){
		include_once '../classes/PasswordHash.php';
		$t_hasher = new PasswordHash(8, FALSE);
		//$mypass = $t_hasher->HashPassword('123456');
		
		$check = $t_hasher->CheckPassword($reqdata->password, $row['password']);
		if ($check!=1){
			$marray['response']="fail";
			echo json_encode($marray);
			return false;
		}else{
			include_once '../classes/ipadd.php';
			$ipadd=new ipadd; $myip=$ipadd->getadd();
			$marray['response']		="success";
			$marray['username']		=$row['name'];
			$marray['role']		=$row['role'];
			$marray['userid']		=$row['id'];
			$marray['userip']		=$myip;
			
			$_SESSION['userid'] = NULL;
			$_SESSION['username'] = NULL;
			$_SESSION['userip'] = NULL;
			$_SESSION['role'] = NULL;
			
			unset($_SESSION['userid']);
			unset($_SESSION['username']);
			unset($_SESSION['userip']);
			unset($_SESSION['role']);
			
			$_SESSION['userid']		=$row['id'];
			$_SESSION['username']	=$row['name'];
			$_SESSION['role']	=$row['role'];
			$_SESSION['userip']		=$myip;

			echo json_encode($marray);
			return false;
		}
	}else{
		$marray['response']="fail";
		echo json_encode($marray);
		return false;	
	}
	return true;
}; //Login
function logout(){$pdo = getConnection();
	global $app;
	$request = $app->request();
	$reqdata = json_decode($request->getBody());
	$_SESSION['userid'] = NULL;
	$_SESSION['username'] = NULL;
	$_SESSION['userip'] = NULL;
	$_SESSION['role'] = NULL;
	
	unset($_SESSION['userid']);
	unset($_SESSION['username']);
	unset($_SESSION['userip']);
	unset($_SESSION['role']);
}

//Mail
function getAllMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT * FROM mail";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllMail

function trackMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT DATE(ms.thedate) AS submitted,DATE(msi.thedate) AS sighted,DATE(mm.thedate) AS missing,DATE(me.thedate) AS exception,
		DATE(mdp.thedate) as dispatched,DATE(md.thedate) AS delivered,DATE(mr.thedate) AS received FROM mail_submitted ms
		LEFT JOIN mail_sighted msi ON ms.mail_id=msi.mail_id
		LEFT JOIN mail_missing mm ON mm.mail_id=msi.mail_id
		LEFT JOIN mail_exception me ON me.mail_id=mm.mail_id
		LEFT JOIN mail_dispatched mdp ON mdp.mail_id=me.mail_id
		LEFT JOIN mail_delivered md ON md.mail_id=mdp.mail_id
		LEFT JOIN mail_received mr ON mr.mail_id=md.mail_id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$dates=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $dates[]=$row; }
		$marray['dates']=$dates;
		
		$ss="SELECT DATEDIFF(msi.thedate,ms.thedate) AS submitted_sighted,
		DATEDIFF(mm.thedate,msi.thedate) AS sighted_missing,
		DATEDIFF(me.thedate,mm.thedate) AS missing_exception,
		DATEDIFF(mdp.thedate,me.thedate) AS exception_dispatched,
		DATEDIFF(md.thedate,mdp.thedate) AS dispatched_delivered,
		DATEDIFF(mr.thedate,md.thedate) AS delivered_received FROM mail_submitted ms
		LEFT JOIN mail_sighted msi ON ms.mail_id=msi.mail_id
		LEFT JOIN mail_missing mm ON mm.mail_id=msi.mail_id
		LEFT JOIN mail_exception me ON me.mail_id=mm.mail_id
		LEFT JOIN mail_dispatched mdp ON mdp.mail_id=me.mail_id
		LEFT JOIN mail_delivered md ON md.mail_id=mdp.mail_id
		LEFT JOIN mail_received mr ON mr.mail_id=md.mail_id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['days']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//trackMail

function getState($id){
	$pdo = getConnection();$marray=array();
	global $app; $request = $app->request();
	$reqdata = json_decode($request->getBody());
	try{
		$ss="SELECT state FROM mail WHERE ref=? OR seal=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id,$id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getState

function submitMail(){
	$pdo = getConnection();$marray=array();
	global $app;$request=$app->request();$reqdata=json_decode($request->getBody());
	try{
		$ss="INSERT INTO address(branch,org_name,postal) VALUES(?,?,?)";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array($reqdata->branch,$reqdata->org_name,$reqdata->postal));
		$address_id = $pdo->lastInsertId();
		
		$ss="INSERT INTO mail(sender_id,address_id,type,category,ref,seal) VALUES(?,?,?,?,?,?)";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array(getUserId(),$address_id,$reqdata->type,$reqdata->category,$reqdata->ref,$reqdata->seal));
		$mail_id = $pdo->lastInsertId();
		
		$ss="INSERT INTO recipient(sender_id,address_id,mail_id,name,dept) VALUES(?,?,?,?,?)";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array(getUserId(),$address_id,$mail_id,$reqdata->name,$reqdata->dept));
		$recipient_id = $pdo->lastInsertId();
		
		$ss="INSERT INTO sender(recipient_id,address_id,mail_id) VALUES(?,?,?)";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array($recipient_id,$address_id,$mail_id));
		
		$ss="INSERT INTO mail_submitted(mail_id) VALUES(?)";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array($mail_id));
		
		$marray['response']="success";
	}catch(PDOExpetion $e){$marray['response']="fail";$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//submitMail

function updateMail($id){
	$pdo = getConnection();$marray=array();
	global $app;$request=$app->request();$reqdata=json_decode($request->getBody());
	try{
		$ss="UPDATE mail SET sender_id=?,address_id=?,type=?,ref=?,seal=? WHERE id=?";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array(getUserId(),$reqdata->address_id,$reqdata->type,$reqdata->ref,$reqdata->seal,$id));
		$marray['response']="success";
	}catch(PDOException $e){$marray['response']="fail";$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//updateMail

function changeState($id){
	$pdo = getConnection();$marray=array();
	global $app;$request=$app->request();$reqdata=json_decode($request->getBody());
	try{
		$ss="UPDATE mail SET state=? WHERE id=?";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array($reqdata->state,$id));
		if($reqdata->state==1||$reqdata->state=="1"){
			$ss="INSERT INTO mail_sighted(mail_id) VALUES(?)";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
		}
		if($reqdata->state==2||$reqdata->state=="2"){
			$ss="INSERT INTO mail_missing(mail_id) VALUES(?)";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
		}
		if($reqdata->state==3||$reqdata->state=="3"){
			$ss="INSERT INTO mail_exception(mail_id) VALUES(?)";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
		}
		if($reqdata->state==4||$reqdata->state=="4"){
			$ss="INSERT INTO mail_dispatched(mail_id) VALUES(?)";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
		}
		if($reqdata->state==5||$reqdata->state=="5"){
			$ss="INSERT INTO mail_delivered(mail_id) VALUES(?)";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
		}
		if($reqdata->state==6||$reqdata->state=="6"){
			$ss="INSERT INTO mail_received(mail_id) VALUES(?)";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
			
			$ss="SELECT u.name,u.email,CONCAT(m.ref,'',m.seal) AS code FROM user u LEFT JOIN recipient r ON u.id=r.sender_id LEFT JOIN mail m ON r.mail_id=m.id WHERE m.id=?";
			$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
			while($row = $stmnt->fetch(PDO::FETCH_OBJ)) {
				include_once('../classes/sendemail.php');
				$s_email = new sendemail;
				$s_email->receptionNotificationToSender($row->email,$row->name,$row->code);
				$marray['email']="sent";
			}
		}
		$marray['response']="success";
	}catch(PDOException $e){$marray['response']="fail";$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//updateMail

function confirmReceived(){
	$pdo = getConnection();$marray=array();
	global $app;$request=$app->request();$reqdata=json_decode($request->getBody());
	try{
		$ss="UPDATE mail SET state=5 WHERE ref=? OR seal=?";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array($reqdata->confirmation,$reqdata->confirmation));
		$marray['response']="success";
	}catch(PDOException $e){$marray['response']="fail";$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//confirmReceived

function deleteMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="DELETE FROM mail WHERE id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//deleteMail

function getMailDetails($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT m.id,(SELECT u.name FROM user u LEFT JOIN mail m ON u.id=m.sender_id WHERE m.id=?) AS sender,
		(SELECT a.branch FROM address a LEFT JOIN mail m ON a.id=m.address_id WHERE m.id=?) AS branch,
		(SELECT a.org_name FROM address a LEFT JOIN mail m ON a.id=m.address_id WHERE m.id=?) AS organization,
		(SELECT a.postal FROM address a LEFT JOIN mail m ON a.id=m.address_id WHERE m.id=?) AS postal,
		m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state,r.name AS recipient,r.dept FROM  mail m LEFT JOIN recipient r ON r.mail_id=m.id WHERE m.id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id,$id,$id,$id,$id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getMailDetails

function getMailDetailsByCode($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT m.id,(SELECT u.name FROM user u LEFT JOIN mail m ON u.id=m.sender_id WHERE m.ref=? OR m.seal=?) AS sender,
		(SELECT a.branch FROM address a LEFT JOIN mail m ON a.id=m.address_id WHERE m.ref=? OR m.seal=?) AS branch,
		(SELECT a.org_name FROM address a LEFT JOIN mail m ON a.id=m.address_id WHERE m.ref=? OR m.seal=?) AS organization,
		(SELECT a.postal FROM address a LEFT JOIN mail m ON a.id=m.address_id WHERE m.ref=? OR m.seal=?) AS postal,
		m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state,r.name AS recipient,r.dept FROM  mail m LEFT JOIN recipient r ON r.mail_id=m.id WHERE m.ref=? OR m.seal=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id,$id,$id,$id,$id,$id,$id,$id,$id,$id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getMailDetailsByCode

function getAllSubmittedMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_submitted ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id NOT IN (SELECT mail_id FROM mail_sighted)";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllSubmittedMail

function getSubmittedMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_submitted ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getSubmittedMail

function getAllSightedMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_sighted ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id NOT IN (SELECT mail_id FROM mail_missing)";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllSightedMail

function getSightedMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_sighted ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getSightedMail

function getAllDispatchedMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_dispatched ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id NOT IN (SELECT mail_id FROM mail_delivered)";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllDispatchedMail

function getDispatchedMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_dispatched ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getDispatchedMail

function getAllExceptionMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_exception ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id NOT IN (SELECT mail_id FROM mail_dispatched)";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllExceptionMail

function getExceptionMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_exception ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getExceptionMail

function getAllDeliveredMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_delivered ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id NOT IN (SELECT mail_id FROM mail_received)";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllDeliveredMail

function getDeliveredMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_delivered ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getDeliveredMail

function getAllReceivedMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_received ms LEFT JOIN mail m ON ms.mail_id=m.id";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllReceivedMail

function getReceivedMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_received ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getReceivedMail

function getAllSentMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_sent ms LEFT JOIN mail m ON ms.mail_id=m.id";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllSentMail

function getSentMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_sent ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getSentMail

function getAllMissingMail(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_missing ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id NOT IN (SELECT mail_id FROM mail_exception)";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getAllMissingMail

function getMissingMail($id){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT ms.mail_id AS id,ms.thedate,m.sender_id,m.address_id,m.type,m.category,CONCAT(m.ref,'',m.seal) AS code,m.state FROM  mail_missing ms LEFT JOIN mail m ON ms.mail_id=m.id WHERE ms.mail_id=?";
		$stmnt=$pdo->prepare($ss);$stmnt->execute(array($id));$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
};//getMissingMail

function createUser(){
	$pdo = getConnection();$marray=array();
	global $app;$request=$app->request();$reqdata=json_decode($request->getBody());
	try{
		include_once '../classes/PasswordHash.php';
		$t_hasher = new PasswordHash(8, FALSE);
		$mypass = $t_hasher->HashPassword('123456');
		$ss="INSERT INTO user(name,password,dept,email,role) VALUES(?,?,?,?,?)";
		$stmnt=$pdo->prepare($ss);$resp=array();
		$stmnt->execute(array($reqdata->name,$mypass,$reqdata->dept,$reqdata->email,$reqdata->role));
		$address_id = $pdo->lastInsertId();
		
		$marray['response']="success";
	}catch(PDOExpetion $e){$marray['response']="fail";$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
}

function getAllUsers(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT id,name,dept,email,role FROM user";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
}

function getAllCommonUsers(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT id,name,dept,email,role FROM user WHERE role=0";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
}

function getAllMailroomUsers(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT id,name,dept,email,role FROM user WHERE role=1";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
}

function getAllAdminUsers(){
	$pdo = getConnection();$marray=array();
	try{
		$ss="SELECT id,name,dept,email,role FROM user WHERE role=2";
		$stmnt=$pdo->prepare($ss);$stmnt->execute();$resp=array();
		while($row = $stmnt->fetch(PDO::FETCH_OBJ)) { $resp[]=$row; }
		$marray['response']=$resp;
	}catch(PDOExpetion $e){$marray['error']=$e->getMessage();}
	echo json_encode($marray);
	return false;
}

?>