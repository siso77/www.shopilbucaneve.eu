<?php
//ini_set('display_errors', 'On');

include_once(APP_ROOT."/libs/ext/Excel/reader.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/gruppi_merceologici.php");
include_once(APP_ROOT."/beans/content_extra.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/famiglie.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/images_giacenze.php");

class ImportRealTimePhoto extends DBSmartyMailAction
{
	private $separator = ';';
	private $operator;
	private $num_customers_inserted;
	private $num_products_inserted;
	private $num_family_inserted;
	private $fileCustomer;
	private $fileContent;
	private $fileFamily;
	private $customer_name;
	private $email_customer_logo;
	
	function __construct()
	{
		parent::DBSmartyMailAction();
		
		$this->email_customer_name = PREFIX_META_TITLE;
		$this->email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';
		$pop3=IMPORT_FOTO_EMAIL_ADMIN_HOST;
		$username=IMPORT_FOTO_EMAIL_ADMIN_USERNAME;
		$password=IMPORT_FOTO_EMAIL_ADMIN_PASSWORD;

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
		$savedirpath = APP_ROOT.'/email_images/';
		
		$this->getdata($authhost,$username,$password,$savedirpath,false, "UNSEEN");
		$this->sendEmailConfirmation();
//		$this->ImportRealTimePhoto();
	}


	function sendEmailConfirmation()
	{
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
					  "To" 			=> "siso77@gmail.com",
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Importazione Foto Realtime da FlorSystem per ".$this->email_customer_name,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Import Content</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.$this->email_customer_logo.'"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;"></td>
				</tr>';
		$html .= '<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						Importazione foto avvenuta correttamente.<br>
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
		$to = "siso77@gmail.com";
		$is_send = $this->sendMail($to);
	}
	
	function sendEmailError()
	{
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
					  "To" 			=> "siso77@gmail.com",
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "[ERROR] Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Import Content</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.$this->email_customer_logo.'"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;"></td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						ATTENZIONE NON SONO STATE TROVATE FOTO PER LE GIACENZE
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
	
		
		$to = "siso77@gmail.com";
		$is_send = $this->sendMail($to);
	}
	
	function __destruct()
	{
// 		$this->sendEmailError();
	}
	
	function getdata($host,$login,$password,$savedirpath,$delete_emails=false, $read_type="UNSEEN") {
            // make sure save path has trailing slash (/)
            $savedirpath = str_replace('\\', '/', $savedirpath);
            if (substr($savedirpath, strlen($savedirpath) - 1) != '/') {
                $savedirpath .= '/';
            }

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
            //print_r($message);
            $emails = imap_search($mbox,$read_type) or die(print_r(imap_last_error()));
            $e = imap_search($mbox,$read_type, SE_UID) or die(print_r(imap_last_error()));
            $i=0;
            foreach($emails as $email_number) 
            {
            	$structure = imap_fetchstructure($mbox, $e[$i] , FT_UID) or die(print_r(imap_last_error()));

            	$parts = $structure->parts;
                $fpos=2;
                for($i = 1; $i < count($parts); $i++) {
                    $message["pid"][$i] = ($i);
                    $part = $parts[$i];

                    if($part->disposition == "ATTACHMENT") {
                        $message["type"][$i] = $message["attachment"]["type"][$part->type] . "/" . strtolower($part->subtype);
                        $message["subtype"][$i] = strtolower($part->subtype);
                        $ext=$part->subtype;
                        $params = $part->dparameters;
                        $filename=$part->dparameters[0]->value;

                        $mege="";
                        $data="";
                        $mege = imap_fetchbody($mbox,$email_number,$fpos);  

                        $filename="$filename";
                        $bar_code = substr($filename, 0, -4);
                        $name_img = str_replace('.', '', substr($filename, 0, -4));
                        $ext_img = substr($filename, -4);
                        
                        $BeanImagesGiacenze = new images_giacenze();
                        $images_giacenze = $BeanImagesGiacenze->dbGetAllByBarCode($this->conn, $bar_code);
                        if(empty($images_giacenze))
                        {
                        	$BeanImagesGiacenze->setBar_code($bar_code);
                        	$BeanImagesGiacenze->setName($name_img.'_'.date('Y_m_d_H_i_s').$ext_img);
                        	$BeanImagesGiacenze->setWww_path(WWW_ROOT.'email_images/');
                        	$BeanImagesGiacenze->setLocal_path($savedirpath);
                        	$BeanImagesGiacenze->dbStore($this->conn);
                        	$last_image = $BeanImagesGiacenze->name;
                        }
                        else
                        {
                        	$BeanImagesGiacenze = new images_giacenze($this->conn, $images_giacenze[0]['id']);
                        	$last_image = $BeanImagesGiacenze->name;
                        	$BeanImagesGiacenze->setName($name_img.'_'.date('Y_m_d_H_i_s').$ext_img);
                        	$BeanImagesGiacenze->setWww_path(WWW_ROOT.'email_images/');
                        	$BeanImagesGiacenze->setLocal_path($savedirpath);
                        	$BeanImagesGiacenze->dbStore($this->conn);
                        }
                        
                        $d = dir(APP_ROOT.'/email_images/');
                        while (false !== ($entry = $d->read())) {
                        	if($entry != '.' && $entry != '..' && $entry == $last_image)
                        		unlink(APP_ROOT.'/email_images/'.$entry);
                        }
                        $d->close();
                        
                        $fp=fopen($savedirpath.$BeanImagesGiacenze->name,"w");
                        $data=$this->getdecodevalue($mege,$part->type);
                        fputs($fp,$data);
                        fclose($fp);
                        $fpos+=1;
                        
                        @imap_delete($mbox, $email_number); 
                        @imap_expunge($mbox);
                    }
                }
                ++$i;
            }
            imap_close($mbox);
        }
        
	function getdecodevalue($message,$coding) {
            switch($coding) {
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
?>