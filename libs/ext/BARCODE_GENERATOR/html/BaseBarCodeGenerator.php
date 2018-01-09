<?php
define('IN_CB', true);

if(!defined('IN_CB')) die('You are not allowed to access to this page.');
define('VERSION', '4.0.0');
if(version_compare(phpversion(), '5.0.0', '>=') !== true)
	exit('Sorry, but you have to run this script with PHP5... You currently have the version <b>' . phpversion() . '</b>.');

if(!function_exists('imagecreate'))
	exit('Sorry, make sure you have the GD extension installed before running this script.');

include('config.php');
require('function.php');
include('LSTable.php');

class BaseBarCodeGenerator
{
	var $barcode_type;
	var $output;
	var $dpi;
	var $thickness;
	var $res;
	var $rotation;
	var $font_family;
	var $font_size;
	var $text2display;
	
	function BaseBarCodeGenerator()
	{
		//$this->configure($barcode_type,$output,$dpi,$thickness,$res,$rotation,$font_family,$font_size,$text2display);
	}
	
	function output()
	{
		// FileName & Extension
		$system_temp_array = explode('/', $_SERVER['PHP_SELF']);
		$system_temp_array2 = explode('.', $system_temp_array[count($system_temp_array) - 1]);
		
		$default_value = array();
		$default_value['output'] = 1;
		$default_value['dpi'] = 72;
		$default_value['thickness'] = 30;
		$default_value['res'] = 1;
		$default_value['rotation'] = 0.0;
		$default_value['font_family'] = '0';
		$default_value['font_size'] = 8;
		$default_value['text2display'] = '';
		$default_value['a1'] = '';
		$default_value['a2'] = '';
		$default_value['a3'] = '';
		
		$output = intval(isset($this->output) ? $this->output : $default_value['output']);
		$dpi = isset($this->dpi) ? $this->dpi : $default_value['dpi'];
		$thickness = intval(isset($this->thickness) ? $this->thickness : $default_value['thickness']);
		$res = intval(isset($this->res) ? $this->res : $default_value['res']);
		$rotation = isset($this->rotation) ? $this->rotation : $default_value['rotation'];
		$font_family = isset($this->font_family) ? $this->font_family : $default_value['font_family'];
		$font_size = intval(isset($this->font_size) ? $this->font_size : $default_value['font_size']);
		$text2display = isset($this->text2display) ? $this->text2display : $default_value['text2display'];
		$a1 = isset($this->a1) ? $this->a1 : $default_value['a1'];
		$a2 = isset($this->a2) ? $this->a2 : $default_value['a2'];
		$a3 = isset($this->a3) ? $this->a3 : $default_value['a3'];
		
		echo '<img src="'.WWW_ROOT.'/libs/ext/BARCODE_GENERATOR/html/image.php?code=' . $this->barcode_type . '&amp;o=' . $output . '&amp;dpi=' . $dpi . '&amp;t=' . $thickness . '&amp;r=' . $res . '&amp;rot=' . $rotation . '&amp;text=' . urlencode($text2display) . '&amp;f1=' . $font_family . '&amp;f2=' . $font_size . '&amp;a1=' . $a1 . '&amp;a2=' . $a2 . '&amp;a3=' . $a3 . '" alt="Barcode Image" />';
	}
	
	function configureCode39($text2display)
	{
		$barcode_type = 'code39';
		$output = '1';
		$dpi = '72';
		$thickness = '30';
		$res = '2';
		$rotation = '0';
		$font_family = 'Arial.ttf';
		$font_size = '10';
		$BaseBarCodeGenerator = new BaseBarCodeGenerator();
		
		$this->barcode_type = $barcode_type;
		$this->output = $output;
		$this->dpi = $dpi;
		$this->thickness = $thickness;
		$this->res = $res;
		$this->rotation = $rotation;
		$this->font_family = $font_family;
		$this->font_size = $font_size;
		$this->text2display = $text2display;
	}
}
?>