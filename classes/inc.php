<?php
if (!isset($_SESSION)) {
	session_start();
	if (!isset($_SESSION['userid']) ){$_SESSION['userid']='-1';}
	if (!isset($_SESSION['username']) ){$_SESSION['username']='GUEST';}
	if (!isset($_SESSION['userip']) ){$_SESSION['userip']='127.0.0.1';}
	if (!isset($_SESSION['role']) ){$_SESSION['role']='0';}
	if (!isset($_SESSION['dept']) ){$_SESSION['dept']='No dept specified';}
	
}
$userid		= $_SESSION['userid']; 
$username 	= $_SESSION['username'];
$role 	= $_SESSION['role'];
$userrole	= $_SESSION['dept'];

function getConnection() {
	$dbhost="localhost";
	$dbuser="developer";
	$dbpass="dev!mje01";
	$dbname="mailroom";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

function getUserId(){
	return $_SESSION['userid'];
}
function __autoload($class_name) {
	include '../../classes/'.$class_name . '.php';
}
?>
