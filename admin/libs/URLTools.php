<?php

class URLTools
{
	var $WWWDir;
	var $controller;

	function URLTools()
	{
		$this->setController();
		$this->setWWWDir();
	}

	function getWWWDir()
	{
		return $this->WWWDir;
	}

	function setWWWDir()
	{
				$WWWDir = 'http' . (@$_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . @$_SERVER['SERVER_NAME'];
		$this->WWWDir = $WWWDir;

		$script_name = $_SERVER["SCRIPT_NAME"];
		$url_tokens = explode(URL_SEPARATOR, $script_name);
		
		$subPath = null;
		if(sizeof($url_tokens) > 2)
		{
			array_pop($url_tokens);
			array_shift($url_tokens);
			
			$subPath = "/";
			foreach($url_tokens as $subDir)
				$subPath .= $subDir."/";
		}

		if ($_SERVER['SERVER_PORT'] != 80 &&
			$_SERVER['SERVER_PORT'] != 443) {
			$WWWDir .= ':' . $_SERVER['SERVER_PORT'];
		}

		if(isset($subPath) && $subPath != "")
			$WWWDir .= $subPath;
		else
			$WWWDir .= "/";

		$this->WWWDir = $WWWDir;
	}

	function getController()
	{
		return $this->controller;
	}

	function setController()
	{
		$script_name = $_SERVER["SCRIPT_NAME"];

		$tokens = explode(URL_SEPARATOR, $script_name);

		$this->controller = end($tokens);
	}

	/**
	*
	* Da ricordare che il parametro di default per il recupero delle azioni � "action". Fare attenzione ai campi hidden sui forms.
	* si setta sul file di configurazione dell'applicazione (/site.ini).
	*
	*@access private
	*@static
	*/
	function _getURLAction()
	{
		if($_POST[PARAM_ACTION_REQUEST])
			return $_POST[PARAM_ACTION_REQUEST];
		elseif($_GET[PARAM_ACTION_REQUEST])
			return $_GET[PARAM_ACTION_REQUEST];
		else
			return DEFAULT_ACTION;
	}

	/**
	*@static
	*@return string
	*/
	function getCurrentModule()
	{
		$action = URLTools::_getURLAction();
		$url_params = explode(URL_SEPARATOR, $action);
		return $url_params[0];
	}

	/**
	*@static
	*/
	function getCurrentActionName()
	{
		$action = URLTools::_getURLAction();
		$URL_SEPARATOR = URL_SEPARATOR;
		if(!empty($URL_SEPARATOR))
		{
			$url_params = explode(URL_SEPARATOR, $action);
			return $url_params[1];
		}
		else
			return $action;
	}

}


?>