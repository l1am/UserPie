<?php
/*
	UserPie Version: 1.0
	http://userpie.com
	
*/

class userPieMail {

	//UserPie uses a text based system with hooks to replace various strs in txt email templates
	public $contents = NULL;

	//Function used for replacing hooks in our templates
	public function newTemplateMsg($template,$additionalHooks)
	{
		global $mail_templates_dir,$debug_mode;
	
		$this->contents = file_get_contents($mail_templates_dir.$template);

		//Check to see we can access the file / it has some contents
		if(!$this->contents || empty($this->contents))
		{
			if($debug_mode)
			{
				if(!$this->contents)
				{ 
					echo lang("MAIL_TEMPLATE_DIRECTORY_ERROR",array(getenv("DOCUMENT_ROOT")));
							
					die(); 
				}
				else if(empty($this->contents))
				{
					echo lang("MAIL_TEMPLATE_FILE_EMPTY"); 
					
					die();
				}
			}
		
			return false;
		}
		else
		{
			//Replace default hooks
			$this->contents = replaceDefaultHook($this->contents);
		
			//Replace defined / custom hooks
			$this->contents = str_replace($additionalHooks["searchStrs"],$additionalHooks["subjectStrs"],$this->contents);

			//Do we need to include an email footer?
			//Try and find the #INC-FOOTER hook
			if(strpos($this->contents,"#INC-FOOTER#") !== FALSE)
			{
				$footer = file_get_contents($mail_templates_dir."email-footer.txt");
				if($footer && !empty($footer)) $this->contents .= replaceDefaultHook($footer); 
				$this->contents = str_replace("#INC-FOOTER#","",$this->contents);
			}
			
			return true;
		}
	}
	
	public function sendMail($email,$subject,$msg = NULL)
	{

		date_default_timezone_set('America/Toronto');

		global $websiteName,$emailAddress, $mailSMTPAuth, $mailSMTPSecure, $mailHost, $mailPort, $mailUsername, $mailPassword;
		
		$header = "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		 
		//Check to see if we sending a template email.
		if($msg == NULL)
			$msg = $this->contents; 

		$message .= $msg;

		$message = wordwrap($message, 70);

		require_once('class.phpmailer.php');
		//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

		$mail = new PHPMailer();

		$mail->IsSMTP(); // telling the class to use SMTP
		//$mail->Host       = "ssl://smtp.gmail.com"; // SMTP server
		$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
		                                           // 1 = errors and messages
		                                           // 2 = messages only
		$mail->SMTPAuth   = $mailSMTPAuth;                  // enable SMTP authentication
		$mail->SMTPSecure = $mailSMTPSecure;                 // sets the prefix to the servier
		$mail->Host       = $mailHost;      // sets GMAIL as the SMTP server
		$mail->Port       = $mailPort;                   // set the SMTP port for the GMAIL server
		$mail->Username   = $mailUsername; 		// GMAIL username
		$mail->Password   = $mailPassword;       	// GMAIL password

		$mail->SetFrom($emailAddress, $websiteName);

		$mail->Subject = $subject;

		$mail->MsgHTML($message);
		$mail->AddAddress($email);

		if(!$mail->Send()) {
		  $mail = false;
		} else {
		  $mail = true;
		}

		return $mail;
	}
}


?>