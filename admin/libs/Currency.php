<?php
class Currency
{
	static public function getFormatDiscount($price, $percentSale)
	{		
		$return['discount']	= ( $price * $percentSale / 100);
		$return['total_discounted'] = $price - $return['discount'];
		
		return $return;
	}
	
	static public function FormatDateFromMysql($date)
	{
		$exp = explode('-', $date);
		return $exp[2].'/'.$exp[1].'/'.$exp[0];
	}	
	
	static public function FormatEuro($str)
	{
		if(strstr($str, ","))
		{
			$exp_price = explode(",", $str);
		
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $str.",00";
			else 
				$return = $str;
		}
		elseif(strstr($str, "."))
		{
			$exp_price = explode(".", $str);
		
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $str.",00";
			else 
				$return = $str;
		}
		else
			$return = $str.",00";
		
		$return = str_replace(".", ",", $return);
		
		return $return;
	}
}
?>