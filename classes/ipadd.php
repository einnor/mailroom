<?php 
class ipadd{
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
	public function getadd(){//LBBSXIBBB
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
		
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