<?php
/************************************************************************/
/* PHP InvoiceIt v1.0                                                   */
/* ===========================                                          */
/*                                                                      */
/*   Written by Steve Dawson - http://www.stevedawson.com               */
/*   Freelance Web Developer - PHP, MySQL, HTML programming             */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* but please leave this header intact, thanks                          */
/************************************************************************/

# ------- START OF CONFIG -------#
## Set the distance for the start of your invoice in pixels.
## This allows you to use your own headed paper for the invoice.
$HeaderDistance = "125";
## The page title, Not used for anything just there for show
$PageTitle = "PHP InvoiceIt v1.0";
## Set the Currency Unit for your country.
$CurrencyUnit = "£";
## If you charge Tax or VAT and want to display it use 'Y' = Yes or use 'N' = No to disable
$DisplayVat = "Y";
## Decide what to call the Tax. Could be Tax or it could be VAT!
$TaxName = "VAT";
## Set the VAT or TAX rate to charge. Only used if above set to 'Y'
$VatRate = "17.5";
## Enter invoice sign-off you would like to be displayed at the bottom of the invoice
$InvoiceSignature = "Many Thanks,<br><br><b>Steve Dawson</b><br>Accounts Department.";
## The table printout Border Colour
$BorderColour = "#666666"; ## Dark Grey
## The products background highlight colour
$TableBGcolour = "#F3F3F3"; ## Light Grey

# ------- END OF CONFIG -------#
## Strip out any unwanted charactors in the additional notes field.
function tag($string)
{
	return stripslashes($string);	
}

## More free PHP scripts are available online at www.stevedawson.com
?>
