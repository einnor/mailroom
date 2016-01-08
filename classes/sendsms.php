<?php 
class sendsms{
	public function __construct(){
		//echo date('h i s')."<br />";
  	}
	public function __destruct(){
      //echo 'The class "', __CLASS__, '" was destroyed.<br />';
	  //echo  date('h i s')."<br />";
  	}
  	public function __toString(){
      //echo "Using the toString method: ";
      return $this->getProperty();
  	}
	public function sendsms($phone,$sms,$pdo) {
		$ss="INSERT INTO smsout(phone,message) VALUES (?,?)";
		$stmt = $pdo->prepare($ss);$stmt->execute(array($phone,$sms));
		$smsoutid=$pdo->lastInsertId();
		//SEND USING CURL
		$message = urlencode($sms);
		$ch = curl_init("http://ke.mtechcomm.com/logreq.php?MSISDN=$phone&shortCode=PATAWORKS&MESSAGE=$message&user=pataworks&pass=be8d6ef9632681f986332e6e90c8d5e0&messageID=$smsoutid");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$str = curl_exec($ch); 
		curl_close($ch);
		$pdo->query("UPDATE smsout SET curlresult='$str' WHERE id='$smsoutid'");
		//UPDATE status=1 if delivered by curl
		$retarray=explode(' ',$str);
		$retcode=$retarray[0];
		if ($retcode==200){
			$pdo->query("UPDATE smsout SET status='1' WHERE id='$smsoutid'");
		}
		return true;
	}
}
/*
$folders = new class_folders;
$targetDir="../../interviews/254722823746";

$folders->createfolder($targetDir);

$sourcefile="http://www.pataworks.com/interviews/default/details.php?jobid=170&schedule=25 June 2014&jobtitle=Test this job Type&name= Maina K&unsubsc=&email=mem@pataworkx.com&phone=2547228237454&bdate=25 june 1911";
$sourcefile=str_replace(" ","%20",$sourcefile);

$folders->copyfile($targetDir."/details.html",$sourcefile);
*/
?>