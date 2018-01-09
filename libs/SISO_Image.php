<?php
/*
 * Params:
 * 		- $frm_fild_name   Nome del campo file del form
 * 		- $dest_path       Percorso di destinazione del file da uploadare
 * 		- $dest_file       Nome del file da uploadare, di default prende il nome originale
 * 		- $width_to_resize Larghezza per il ridimensionamente
 * 						   Per lasciare la dimenzione originale dell'imagine mettere a null l'ultimo parametro
 * 
 * $obj = new SISO_UpladImageResize($frm_fild_name, $dest_path, $dest_file, $width_to_resize);
 * 
 * Esempi di utilizzo:
 * 			1) Il file uploadato viene lasciato della dimensione originale
 * 			   $obj = new SISO_UpladImageResize("uploadedfile", "c:/Apache2/htdocs/ResizeImg/", null, null);
 * 			2) Il file uploadato viene viene ridimensionato con le impostazioni di default
 * 			   $obj = new SISO_UpladImageResize("uploadedfile", "c:/Apache2/htdocs/ResizeImg/");
 * 			3) Il file uploadato viene viene ridimensionato con le dimensionipassate
 * 			   $obj = new SISO_UpladImageResize("uploadedfile", "c:/Apache2/htdocs/ResizeImg/", null, 312);
 * */

class SISO_Image
{
	var $image;
	
	function SISO_Image($image = null)
	{
		if(is_file($value))
			$this->image = $image;
	}
}

class SISO_ImageResize extends SISO_Image
{
	var $width_to_resize = null;
	
	function SISO_ImageResize($image = null)
	{
		parent::SISO_Image($image);
	}
	
	function imageTypeToExtension($imageType, $includeDot = false) 
	{
		$dot = $includeDot ? '.' : '';
		$ext = false;
		if(!empty($imageType)) 
		{
			switch($imageType) 
			{
				case image_type_to_mime_type(IMAGETYPE_GIF)     : $ext = $dot.'gif';  break;
				case image_type_to_mime_type(IMAGETYPE_JPEG)    : $ext = $dot.'jpg';  break;
				case image_type_to_mime_type(IMAGETYPE_PNG)     : $ext = $dot.'png';  break;
				case image_type_to_mime_type(IMAGETYPE_SWF)     : $ext = $dot.'swf';  break;
				case image_type_to_mime_type(IMAGETYPE_PSD)     : $ext = $dot.'psd';  break;
				case image_type_to_mime_type(IMAGETYPE_BMP)     : $ext = $dot.'wbmp'; break;
				case image_type_to_mime_type(IMAGETYPE_WBMP)    : $ext = $dot.'wbmp'; break;
				case image_type_to_mime_type(IMAGETYPE_XBM)     : $ext = $dot.'xbm';  break;
				case image_type_to_mime_type(IMAGETYPE_TIFF_II) : $ext = $dot.'tiff'; break;
				case image_type_to_mime_type(IMAGETYPE_TIFF_MM) : $ext = $dot.'tiff'; break;
				case image_type_to_mime_type(IMAGETYPE_IFF)     : $ext = $dot.'aiff'; break;
				case image_type_to_mime_type(IMAGETYPE_JB2)     : $ext = $dot.'jb2';  break;
				case image_type_to_mime_type(IMAGETYPE_JPC)     : $ext = $dot.'jpc';  break;
				case image_type_to_mime_type(IMAGETYPE_JP2)     : $ext = $dot.'jp2';  break;
				case image_type_to_mime_type(IMAGETYPE_JPX)     : $ext = $dot.'jpf';  break;
				case image_type_to_mime_type(IMAGETYPE_SWC)     : $ext = $dot.'swc';  break;
				case "undefined"     							: $ext = false;       break;
			}
		}
		return $ext;
	}	
	
	function getImageInformation($full_file, $key_ret = false)
	{
		$imagesizeinfo = getimagesize($full_file);
		list($width, $height, $type, $attr) = $imagesizeinfo;
		
		$ret = array("width" => $width, 
								 "height" => $height, 
								 "type" => $type, 
								 "attr" => $attr, 
								 "mime" => $imagesizeinfo['mime']);
	
		if(!$imagesizeinfo)
			return "undefined";
	
		if($key_ret)
			return $ret[$key_ret];
		else
			return $ret;
	}
	
	function Resize($source_file_path, $source_file_name, $type)
	{
		$func_call_create = "imagecreatefrom".(($type == 'jpg') ? "jpeg" : $type);
		$fullsize         = $func_call_create($source_file_path.$source_file_name);
		$fullsize_height  = imagesy($fullsize);
		$fullsize_width   = imagesx($fullsize);
			
		$thumb_width      = (isset($this->width_to_resize)) ? $this->width_to_resize : $fullsize_width;
		$thumb_height     = floor($fullsize_height/($fullsize_width/$thumb_width));
		
//		if($thumb_height >= 130)
//		{
//			if($this->width_to_resize == 80)
//				$thumb_width = 60;
//			else
//				$thumb_width = 130;
//			$thumb_height     = floor($fullsize_height/($fullsize_width/$thumb_width));			
//		}
			$thumb_height     = floor($fullsize_height/($fullsize_width/$thumb_width));			
		
		$thumb            = imagecreatetruecolor($thumb_width,$thumb_height);
		imagecopyresampled($thumb,$fullsize,0,0,0,0,$thumb_width,$thumb_height,$fullsize_width,$fullsize_height);
	 
		imagedestroy($fullsize);
		
		$func_call = "image".(($type == 'jpg') ? "jpeg" : $type);
		$func_call($thumb, $source_file_path.$source_file_name);
		
		imagedestroy($thumb);
		
//		if($thumb_height > 80 && $this->width_to_resize == 80)
//		{
//			$this->width_to_resize = 60;
//			$this->Resize($this->dest_path, $this->dest_file, $type);
//			$this->width_to_resize = null;
//		}	
//		elseif($thumb_height > 80 && $this->width_to_resize != 80)
//		{
//			$this->width_to_resize = 80;
//			$this->Resize($this->dest_path, $this->dest_file, $type);
//			$this->width_to_resize = null;
//		}	
	}
}

class SISO_UpladImageResize extends SISO_ImageResize
{
	var $upl_data;
	var $dest_path;
	var $dest_file;
	var $is_uploaded = false;
	
	function SISO_UpladImageResize($frm_fild_name = null, $dest_path = null, $dest_file = null, $width_to_resize = 800)
	{
		$this->setUpladData($frm_fild_name);
		parent::SISO_ImageResize($this->getUploadPath().$this->getUploadFile());
		
		$this->width_to_resize = $width_to_resize;
		$this->dest_path = $dest_path;
		$this->dest_file = (isset($dest_file)) ? $dest_file : $this->upl_data['real_file_name'];

		$this->start();
	}

	function start()
	{
		if($this->upl_data['upl_tmp_file_name'] <> "none")
		{
			if(!$this->_copy()) 
				return new Error("Errore nel caricamento dell'immagine.", __FILE__, __CLASS__, __FUNCTION__, __LINE__);
			else 
			{
				$this->is_uploaded = true;
				$type = $this->imageTypeToExtension($this->getImageInformation($this->dest_path.$this->dest_file, "mime"));
				if(!$type)
					return;
				
				if($type == "wbmp")
					return;// new Error("Non &egrave possibile uploadare file Bitmap (.bmp)", __FILE__, __CLASS__, __FUNCTION__, __LINE__);
				
				//if($this->upl_data['file_size'] <= 2000000)
					//return;
				
				$this->Resize($this->dest_path, $this->dest_file, $type);
			}
		}		
	}
	
	function _copy()
	{
		if(!copy($this->upl_data['upl_tmp_path'].$this->upl_data['upl_tmp_file_name'], $this->dest_path.$this->dest_file))
			return false;
		else
			return true;
	}
	
	function getUploadPath($frm_fild_name = null)
	{
		if(!isset($this->upl_data))
			$this->setUpladData($frm_fild_name);

		return $this->upl_data['upl_tmp_path'];
	}
	
	function getUploadFile($frm_fild_name = null)
	{
		if(!isset($this->upl_data))
			$this->setUpladData($frm_fild_name);

		return $this->upl_data['upl_tmp_file_name'];
	}
	
	function setUpladData($frm_fild_name)
	{
		if(!isset($frm_fild_name))
			return new Error("Il nome del campo file del form deve essere passato", __FILE__, __CLASS__, __FUNCTION__, __LINE__);

		$dir       = explode("\\", $_FILES[$frm_fild_name]['tmp_name']);
		$file_name = $dir[(count($dir)-1)];
		unset($dir[(count($dir)-1)]);
		$dir = implode("/", $dir)."/";
		
		$this->upl_data = array("upl_tmp_file_name" => $file_name, 
					            "upl_tmp_path" => $dir, 
								"file_size" => $_FILES[$frm_fild_name]['size'],
								"real_file_name" => $_FILES[$frm_fild_name]['name']);
	}
	
	function getUploadData()
	{
		return $this->upl_data;
	}
	
	function is_uploaded()
	{
		return $this->is_uploaded;
	}
}
?>