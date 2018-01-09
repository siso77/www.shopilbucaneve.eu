<?php
/*
*
*
*
*
*
*
*/
class HTTP_Upload
{
	var $obj;
	/**
	*
	*
	*/
	function HTTP_Upload($lang=null)
	{
		$this->obj = new HTTP_Upload($lang);
	}

	/**
	*
	*
	*/
	function getFiles()
	{
		$obj = &$this->obj;

		return $obj->getFiles();
	}

	/**
	*
	*
	*/
	function hasWrongSize()
	{
		$file_obj = $this->getFileObj($file_key);
		return $file_obj->isError();
	}

	/**
	*
	*
	*/
	function moveFileTo($file_key, $dir_dest, $overwrite=true)
	{
		$file_obj = $this->getFileObj($file_key);
		
		return $file_obj->moveTo($dir_dest, $overwrite);
	}

	//private methods
	/**
	*
	*
	*/
	function _setName($file_key, $prepend, $append=null, $dir_dest, $overwrite=true)
	{
		$file_obj = $this->getFileObj($file_key);
		$file_obj->setName($this->getFileName($file_key), $prepend, $append);

		$file_obj->moveTo($dir_dest, $overwrite);
	}


	/**
	*
	*
	*/
	function isMissing()
	{
		$obj = &$this->obj;

		return $obj->isMissing();
	}

	/**
	*
	*
	*/
	function hasBeenSent($file_key)
	{
		$file_obj = $this->getFileObj($file_key);

		return $file_obj->isValid();
	}


	/**
	*
	*
	*/
	function &getFileObj($file_key)
	{
		$obj = &$this->obj;

		return $obj->getFiles($file_key);
	}

	/**
	*
	*
	*/
	function getFileName($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('name');
	}


	/**
	*
	*
	*/
	function getFileRealName($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('real');
	}


	/**
	*
	*
	*/
	function getFileTmpName($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('tmp_name');
	}


	/**
	*
	*
	*/
	function getFileSize($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('size');
	}


	/**
	*
	*
	*/
	function getFormName($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('form_name');
	}


	/**
	*
	*
	*/
	function getFileExt($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('ext');
	}


	/**
	*
	*
	*/
	function getFileType($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('type');
	}


	/**
	*
	*
	*/
	function getFileError($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp('error');
	}


	/**
	*
	*
	*/
	function getFileAttr($file_key)
	{
		$file_obj = &$this->getFileObj($file_key);

		return $file_obj->getProp();
	}


}
?>