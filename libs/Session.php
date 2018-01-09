<?php

// Session::is_active();
// Session::set($key, $value)
// Session::unsetKey($key)
// Session::get($key)
// Session::start($name)
// Session::getSID();
// Session::unsetMyKey($WF, $key="")
// Session::getSessionID()
// Session::getSessionName()
// Session::setAccount($_POST['nome'], $_POST['cognome'], $account);
// Session::removeAccount($id)
// Session::setComando($vars)
// Session::removeCommand();
// Session::finalUnset()
// Session::updateAccount($id, $key, $val)


class Session
{
	function get($key="")
	{
		$ret = false;
		if(Session::is_active() && isset($_SESSION[$key]))
			$ret = $_SESSION[$key];

		return $ret;
	}

	function set($key, $value)
	{
		if(!Session::is_active() )
			return false;

		$ret = $_SESSION[$key];
		$_SESSION[$key]=$value;
		return $ret;
	}
	function setAccount($cognome, $nome, $account, $id)
	{
		if(!Session::is_active() )
			return false;

		$_SESSION['session_account'][$id] = Array("cognome"=>$cognome, "nome"=>$nome, "account"=>$account, "id"=>$id);
	}
	function updateAccount($id, $key, $val)
	{
		if(!Session::is_active() )
			return false;

		$_SESSION['session_account'][$id][$key] = $val;
	}

	function setComando($vars)
	{
		if(!Session::is_active() )
			return false;
		foreach($vars as $k=>$v)
			$value[$k] = $v;
		
		$_SESSION['session_command'] = $value;
	}

	function removeAccount($id)
	{
		if(!Session::is_active() )
			return false;

		unset($_SESSION['session_account'][$id]);
	}

	function removeCommand()
	{
		if(!Session::is_active() )
			return false;

		unset($_SESSION['session_command']);
	}

	function finalUnset()
	{
		unset($_SESSION['session_account']);
		unset($_SESSION['session_command']);
	}
	
	function getSID()
	{
		$ret = false;
		if(Session::is_active())
			$ret = session_name()."=".session_id();

		return $ret;
	}

	function getSessionName()
	{
		if(Session::is_active())
			return session_name();
		else
			return false;
	}

	function getSessionID()
	{
		if(Session::is_active())
			return session_id();
		else
			return false;
	}

	function unsetKey($key="")
	{
		if(Session::is_active() && isset($_SESSION[$key]))
			unset($_SESSION[$key]);
	}

	function unsetMyKey($WF, $key="")
	{
		if(Session::is_active() && isset($_SESSION[$WF][$key]))
			unset($_SESSION[$WF][$key]);
	}

	function is_active()
	{
		$ret = false;
		if(session_id())
			$ret = true;
		return $ret;
	}

	function start($name=null)
	{
		if(!Session::is_active())
		{
			if(isset($name) && is_string($name))
				session_name($name);
		
			session_start();
		}
	}

	
	function destroy()
	{
		$ret = false;
		if(Session::is_active())
		{
			$_SESSION = array();
			$ret = session_destroy();
		}

		return $ret;
	}

	function savePath($value=null)
	{
		if(isset($value))
			ini_set("session.save_path", $value);
	}

	function useCookie($value)
	{
		$use = "0";
		if((bool)$value)
			$use = "1";

		ini_set("session.use_cookies", $use);
	}

	function cookiePath($value)
	{
		if(isset($value))
			ini_set("session.cookie_path", $value);
	}
}

?>