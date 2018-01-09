<?php
class ImportDenDekker extends DBSmartyMailAction
{
	function ImportDenDekker()
	{
// 		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_GARDESANA_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b') //pwd = md5('stream')
		if(true)
		{
			$pop3=IMPORT_GIACENZE_DENDEKKER_EMAIL_ADMIN_HOST;
			$username=IMPORT_GIACENZE_DENDEKKER_EMAIL_ADMIN_USERNAME;
			$password=IMPORT_GIACENZE_DENDEKKER_EMAIL_ADMIN_PASSWORD;
			
			#######
			# localhost pop3 with and without ssl
			# $authhost="{localhost:995/pop3/ssl/novalidate-cert}";
			$authhost="{".$pop3.":110/pop3/notls}";
			
			# localhost imap with and without ssl
			# $authhost="{localhost:993/imap/ssl/novalidate-cert}";
			# $authhost="{localhost:143/imap/notls}";
			# $user="localuser";
			
			# localhost nntp with and without ssl
			# you have to specify an existing group, control.cancel should exist
			# $authhost="{localhost:563/nntp/ssl/novalidate-cert}control.cancel";
			# $authhost="{localhost:119/nntp/notls}control.cancel";
			
			######
			# web.de pop3 without ssl
			# $authhost="{pop3.web.de:110/pop3/notls}";
			# $user="kay.marquardt@web.de";
			
			#########
			# goggle with pop3 or imap
			# $authhost="{pop.gmail.com:995/pop3/ssl/novalidate-cert}";
			# $authhost="{imap.gmail.com:993/imap/ssl/novalidate-cert}";
			# $user="username@gmail.com";
			
			$user=$username;
			$pass=$password;
			$savedirpath = APP_ROOT.'/admin/upload_fornitori/dendekker/';
			$this->getdata($authhost,$username,$password,$savedirpath,false, "UNSEEN");
			
// 			$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
// 					"To" 			=> 'siso77@gmail.com',
// 					"Cc" 			=> "",
// 					"Bcc" 		=> "",
// 					"Subject" 	=> "Importazione Listino DenDekker per Gardesana",
// 					"Date"		=> date("r")
// 			);
// 			$this->setHeaders($hdrs);
// 			$this->setHtmlText('IMPORTAZIONE MAGAZZINO DEN DEKKER AVVENUTA CON SUCCESSO!');
// 			$this->mail_factory();
// 			$is_send = $this->sendMail('siso77@gmail.com');
		}
	}
	
	function getdata($host,$login,$password,$savedirpath,$delete_emails=false, $read_type="UNSEEN") 
	{
		$savedirpath = str_replace('\\', '/', $savedirpath);
		if (substr($savedirpath, strlen($savedirpath) - 1) != '/')
			$savedirpath .= '/';
	
		$mbox = imap_open ($host, $login, $password) or die("can't connect: " . imap_last_error());
		$message = array();
		$message["attachment"]["type"][0] = "text";
		$message["attachment"]["type"][1] = "multipart";
		$message["attachment"]["type"][2] = "message";
		$message["attachment"]["type"][3] = "application";
		$message["attachment"]["type"][4] = "audio";
		$message["attachment"]["type"][5] = "image";
		$message["attachment"]["type"][6] = "video";
		$message["attachment"]["type"][7] = "other";
		$emails = imap_search($mbox,$read_type) or die(print_r(imap_last_error()));
		$e = imap_search($mbox,$read_type, SE_UID) or die(print_r(imap_last_error()));
		$i=0;

		foreach($emails as $email_number)
		{
			$structure = imap_fetchstructure($mbox, $email_number);
			$attachments = array();
			if(isset($structure->parts) && count($structure->parts)) {
			
				for($i = 0; $i < count($structure->parts); $i++) {
			
					$attachments[$i] = array(
							'is_attachment' => false,
							'filename' => '',
							'name' => '',
							'attachment' => ''
					);
			
					if($structure->parts[$i]->ifdparameters) {
						foreach($structure->parts[$i]->dparameters as $object) {
							if(strtolower($object->attribute) == 'filename') {
								$attachments[$i]['is_attachment'] = true;
								$attachments[$i]['filename'] = $object->value;
							}
						}
					}
			
					if($structure->parts[$i]->ifparameters) {
						foreach($structure->parts[$i]->parameters as $object) {
							if(strtolower($object->attribute) == 'name') {
								$attachments[$i]['is_attachment'] = true;
								$attachments[$i]['name'] = $object->value;
							}
						}
					}
			
					if($attachments[$i]['is_attachment']) {
						$attachments[$i]['attachment'] = imap_fetchbody($mbox, $email_number, $i+1);
						if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
							$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
						}
						elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
							$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
						}
					}
				}
			}
			
			foreach($attachments as $attach)
			{
				if($attach['is_attachment'])
				{
					$filename = $attach['name'];
					$fp=fopen($savedirpath.$filename,"w");
					$data=$this->getdecodevalue($listino,$part->type);
					fputs($fp,$attach['attachment']);
					fclose($fp);
					chmod($savedirpath.$filename, 0777);
					$send_email = true;
						
				}
			}
				
			@imap_delete($mbox, $email_number);
			@imap_expunge($mbox);
			
/*****/
// 			$structure = imap_fetchstructure($mbox, $e[$i] , FT_UID) or die(print_r(imap_last_error()));
// 			$parts = $structure->parts;
// 			$fpos=2;

// 			for($i = 0; $i < count($parts); $i++) {
// 				$message["pid"][$i] = ($i);
// 				$part = $parts[$i];

// 				if($part->disposition == "ATTACHMENT") {
// 					$message["type"][$i] = $message["attachment"]["type"][$part->type] . "/" . strtolower($part->subtype);
// 					$message["subtype"][$i] = strtolower($part->subtype);
// 					$ext=$part->subtype;
// 					$params = $part->dparameters;
// 					$filename=$part->dparameters[0]->value;
	
// 					$listino="";
// 					$data="";
// 					$listino = imap_fetchbody($mbox,$email_number,$fpos);
// 					$fpos+=1;
						
// 					@imap_delete($mbox, $email_number);
// 					@imap_expunge($mbox);
// 					$filename="$filename";

// 					$fp=fopen($savedirpath.$filename,"w");
// 					$data=$this->getdecodevalue($listino,$part->type);
// 					fputs($fp,$data);
// 					fclose($fp);
// 					chmod($savedirpath.$filename, 0777);
// 					$send_email = true;
// 				}
// 			}
// 			++$i;
		}
		imap_close($mbox);

		if(empty($send_email))
			return true;
		/* EMAIL PER DENDEKKER */
		$this->setAttachment($savedirpath.$filename);
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
				"To" 			=> $userAnag['email'],
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "SASSIGARDEN FILE EXCEL MAGAZZINO FORNITORE 1",
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
		$this->setHtmlText("IN ALLEGATO IL LISTINO INVIATO DAL FORNITORE.");
		$this->mail_factory();
		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail('nicolo@sassigarden.it');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		/* EMAIL PER DENDEKKER */
	}
	
	function getdecodevalue($message,$coding) 
	{
		switch($coding) 
		{
			case 0:
			case 1:
				$message = imap_8bit($message);
			break;
			case 2:
				$message = imap_binary($message);
			break;
			case 3:
			case 5:
				$message = imap_base64($message);
			break;
			case 4:
				$message = imap_qprint($message);
			break;
		}
		return $message;
	}
}