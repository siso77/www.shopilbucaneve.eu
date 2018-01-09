<?php
class GenericEncripting
{
	var $_td = null;
	var $_key = null;
	var $_iv_size = null;
	var $_iv = null;
	var $_plain_text = null;
	
	function GenericEncripting($key = null, $text_to_encript = null)
	{
		if(isset($key))
			$this->_key = $key;
		if(isset($text_to_encript))
			$this->_plain_text = $text_to_encript;

		if(isset($this->_key))
			$this->_init();
	}
	
	function is_create_obk_encript()
	{
		if(isset($this->_td))
			return true;
		else
			return false;
	}
	
	function close_obj_decript()
	{
		mcrypt_module_close($this->_td);
	}

	function decrypt_data($decript)
	{
		if(mcrypt_generic_init($this->_td, $this->_key, $this->_iv) != -1) 
		{
			mcrypt_generic_init($this->_td, $this->_key, $this->_iv);
			$decriptated = mdecrypt_generic($this->_td, $decript);
			return $decriptated;
		}
	}

	function encrypt_data()
	{
		if(mcrypt_generic_init($this->_td, $this->_key, $this->_iv) != -1) 
		{
			$criptated = mcrypt_generic($this->_td, $this->_plain_text);
			mcrypt_generic_deinit($this->_td);
			//mcrypt_generic_deinit($this->_td);			
		}
		return $criptated;
	}

	function _init()
	{
		$this->_td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
		$this->_key = substr($this->_key, 0, mcrypt_enc_get_key_size($this->_td));
		$this->_iv_size = mcrypt_enc_get_iv_size($this->_td);
		$this->_iv = mcrypt_create_iv($this->_iv_size, MCRYPT_RAND);
	}
}
?>
