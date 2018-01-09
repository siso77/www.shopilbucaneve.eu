<?php
require_once APP_ROOT.'/libs/ext/PHPExcel/Classes/PHPExcel.php';

class PHPExcelImplement
{
	var $alphaChars = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	var $objPHPExcel;
	
	function PHPExcelImplement($timeZone, $type, $data, $fieldToDisplay, $itemToSubIterate, $prefixFileName, $creator)
	{
		date_default_timezone_set($timeZone);
		$this->objPHPExcel = new PHPExcel();
		$this->setProperties($creator, $prefixFileName, $category = '');
		$this->setCells($data, $fieldToDisplay, $itemToSubIterate, $prefixFileName);
		
		$func = 'output'.strtolower(ucfirst($type));
		$this->$func($prefixFileName, $type);
	}
	
	function setCells($data, $fieldToDisplay, $itemToSubIterate, $nameSheet)
	{
		$i = 0;
		
		foreach ($fieldToDisplay as $key => $value)
		{
			$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue($this->alphaChars[$i].'1', $key);
			$i++;
		}		
		
		$i = 2;
		foreach ($data as $key => $value)
		{
			$alphaIndex = 0;
			$increment = false;
			foreach ($value as $k => $val)
			{
				if(in_array($k, $fieldToDisplay))
				{
					if(in_array($k, $itemToSubIterate))
					{
						if(is_array($value[$k]))
						{
							foreach($value[$k] as $subK => $subV)
							{
								$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue($this->alphaChars[$alphaIndex].$i, $subV[$k]);
								$i++;
							}
							$alphaIndex++;
						}
					}
					else
					{
						$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue($this->alphaChars[$alphaIndex].$i, $value[$k]);
						if($alphaIndex == 25)
							$alphaIndex = 0;
						else
							$alphaIndex++;
					}
					$increment = true;
				}
			}
			if($increment)
				$i++;		
		}
		
		// Rename sheet
		$this->objPHPExcel->getActiveSheet()->setTitle($nameSheet);
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$this->objPHPExcel->setActiveSheetIndex(0);
	}
	
	function setProperties($creator, $prefixFileName, $category = '')
	{
				// Set properties
		$this->objPHPExcel->getProperties()->setCreator($creator)
									 ->setLastModifiedBy($creator)
									 ->setTitle($prefixFileName)
									 ->setSubject($prefixFileName)
									 ->setDescription($prefixFileName)
									 ->setKeywords($prefixFileName)
									 ->setCategory($category);
	}
	
	function outputXls($prefixFileName)
	{
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$prefixFileName.'.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	function outputXlsx($prefixFileName)
	{
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$prefixFileName.'.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
}
?>