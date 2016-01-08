<?php 
class sendemail{
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
	public function receptionNotificationToSender($to, $name, $code) {
		$message='<p><h3>MAIL SUCCESSFULLY DELIVERED</h3>
					Hello '. $name.', <br />Your mail, Ref No. '.$code.' has successfully received by the recipient <br />
				</p>';
		$Headers  = "MIME-Version: 1.0\n";
		$Headers .= "Content-type: text/html; charset=iso-8859-1\n";
		$Headers .= "From: Mailroom <noreply@mailroom.co.ke>\n";
		$Headers .= "Reply-To: noreply@mailroom.co.ke\n";
		$Headers .= "X-Sender: <noreply@mailroom.co.ke>\n";
		$Headers .= "X-Mailer: PHP\n"; 
		$Headers .= "X-Priority: 1\n"; 
		$Headers .= "Return-Path: <noreply@mailroom.co.ke>\n";  
		if(!mail($to, "Mail Successfully Delivered", $message, $Headers)) {return false; } else {return true; }
		return true;
	}
	
	public function systemMailAlert($to, $code) {
		$message='<p><h3>MAIL ALERT!</h3>
					The mail, Ref/Seal No. '.$code.' has stayed too long in its current state! <br />
				</p>';
		$Headers  = "MIME-Version: 1.0\n";
		$Headers .= "Content-type: text/html; charset=iso-8859-1\n";
		$Headers .= "From: Mailroom <system@mailroom.co.ke>\n";
		$Headers .= "Reply-To: system@mailroom.co.ke\n";
		$Headers .= "X-Sender: <system@mailroom.co.ke>\n";
		$Headers .= "X-Mailer: PHP\n"; 
		$Headers .= "X-Priority: 1\n"; 
		$Headers .= "Return-Path: <system@mailroom.co.ke>\n";  
		if(!mail($to, "Mail Alert", $message, $Headers)) {return false; } else {return true; }
		return true;
	}
}
?>