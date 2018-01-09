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
## Include the Config file because without we can't do anything!
include "config.php";

## The processing function thingy ma jig.
function processform() {
global $HeaderDistance;
global $BorderColour;
global $TableBGcolour;
global $PageTitle;
global $CurrencyUnit;
global $InvoiceSignature;
global $DisplayVat;
global $TaxName;
global $VatRate;
global $vat_at;
global $customer_id;
global $customer_address;
global $customer_name;
global $date;
global $order;
global $price;
global $notes;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<LINK REL="STYLESHEET" HREF="invoice.css">
</HEAD>
<BODY>
<img src="blank.gif" width="1" height="<?php print $HeaderDistance; ?>">
<DIV align="center">
  <TABLE border="0" width="600">
    <tr> 
      <TD valign="top" colspan="2"> <table>
          <tr> 
            <td align="left"> To:<br> <?php print $customer_name; ?><br> <?php print str_replace("\n", "<br>", $customer_address); ?> 
            </td>
          </tr>
          <tr> 
            <td align="right"> <b>Date:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print "$date"; ?> 
            </td>
          </tr>
		            <tr> 
            <td align="right"> <b>Ref:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print "$customer_id"; ?> 
            </td>
          </tr>
          <tr> 
            <td> <br><br><table width="100%" CELLPADDING="2" CELLSPACING="0" border="1" bordercolor="<?PHP print "$BorderColour"; ?>">
                <tr bgcolor="<?PHP print "$TableBGcolour"; ?>"> 
                  <th>Prodotto</th>
                  <th>Costo</th>
                  <th><?PHP print "$TaxName"; ?></th>
                  <th>Totale</th>
                </tr>
                <tr> 
                  <td><img src="blank.gif" width="450" height="14"></td>
                  <td><img src="blank.gif" width="60" height="14"></td>
                  <td><img src="blank.gif" width="60" height="14"></td>
                  <td><img src="blank.gif" width="60" height="14"></td>
                </tr>
<?php
$tot_cost = "0";
$tot_vat = "0";
$tot_price = "0";
for($l=0; $l<sizeof($order); $l++){
if(!strlen($order[$l]) && $l == (sizeof($order)-1)) { continue; }
?>
                <tr> 
                  <td><?php print $order[$l]; ?></td>
                  <td align="right"><?php print "$CurrencyUnit"; ?><?php print number_format($price[$l]/($vat_at+100)*100, 2); ?></td>
                  <td align="right"><?php print "$CurrencyUnit"; ?><?php print number_format($price[$l]-($price[$l]/($vat_at+100)*100), 2); ?></td>
                  <td align="right" width="50"><?php print "$CurrencyUnit"; ?><?php print number_format($price[$l],2); ?></td>
                </tr>
<?php
$tot_cost += $price[$l]/($vat_at+100)*100;
$tot_vat += $price[$l]-($price[$l]/($vat_at+100)*100);
$tot_price += $price[$l];
}
?>

                <tr> 
                  <td colspan="4"><?php if($DisplayVat == "Y") { print "<i>$TaxName charged at a rate of $vat_at%</i>"; } else print "<img src=\"blank.gif\" width=\"1\" height=\"14\">";?></td>
                </tr>
              </table>
              <br> <table align="right" width="0" CELLPADDING="2" CELLSPACING="1" border="1" bordercolor="<?PHP print "$BorderColour"; ?>">
                <tr> 
                  <td width="60" align="right"><b><?php print "$CurrencyUnit"; ?><?php print number_format($tot_cost,2); ?></b></td>
                  <td align="right" width="60"><b><?php print "$CurrencyUnit"; ?><?php print number_format($tot_vat,2); ?></b></td>
                  <td align="right" width="60"><b><?php print "$CurrencyUnit"; ?><?php print number_format($tot_price,2); ?></b></td>
                </tr>
              </table> <br> <br><br><br><?php print tag($notes); ?>
              <br> <br> <?php print "$InvoiceSignature"; ?> </td>
          </tr></table></TD></TR></TABLE>
</DIV>
</BODY>
</HTML>
<?php
}
function mainform() {
global $HeaderDistance;
global $BorderColour;
global $TableBGcolour;
global $PageTitle;
global $CurrencyUnit;
global $DisplayVat;
global $TaxName;
global $VatRate;
global $InvoiceSignature;
global $n_order;
global $n_price;
global $vat_at;
global $customer_id;
global $customer_address;
global $customer_name;
global $date;
global $order;
global $price;
global $notes;

?>
<HTML>
<HEAD>
<TITLE>PHP InvoiceIt - Create the Invoice</TITLE>
<LINK REL="STYLESHEET" HREF="invoice.css">
</HEAD>
<BODY><img src="blank.gif" width="1" height="<?php print $HeaderDistance; ?>">
<DIV align="center"> 
  <TABLE WIDTH="90%" BORDER="0" CELLPADDING="2" CELLSPACING="2" ALIGN="CENTER">
    <TR> 
      <TD valign="top" colspan="2"><H4 align="center"><?PHP print $PageTitle; ?></H4> 
        <br> <FORM method="post" action="/libs/ext/Invoice/invoice.php">
          <table>
            <tr> 
              <td align="left"> To:<br>
			  <input type="text" name="customer_name" size="38" value="<?php if($customer_name) { print tag($customer_name); } else { print "Insert Customer Name"; } ?>"> 
        <br> <textarea name="customer_address" rows="5" cols="40"><?php if($customer_address) { print $customer_address; } else { print "Insert Customer Address"; } ?></textarea> 
              </td>
            </tr>
            <tr> 
              <td align="right"> <b>Date:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="date" size="15" value="<?php if($date){ print tag($date); } else { print date("d/m/Y"); } ?>"> 
              </td>
            </tr>
			            <tr> 
              <td align="right"> <b>Ref Number:</b>&nbsp;&nbsp;&nbsp;
			 <input type="text" name="customer_id"  size="15" value="<?php if($customer_id){ print tag($customer_id); } else { print "Customer Ref"; } ?>"> 
              </td>
            </tr>
            <tr> 
              <td> <table width="100%" CELLPADDING="2" CELLSPACING="0" border="1" bordercolor="<?PHP print "$BorderColour"; ?>">
                  <tr bgcolor="<?PHP print "$TableBGcolour"; ?>"> 
                    <th>Prodotto</th>
                    <th>Costo</th>
                    <th><?PHP print "$TaxName"; ?></th>
                    <th>Totale</th>
                  </tr>
                  <tr> 
                    <td><img src="blank.gif" width="450" height="14"></td>
                    <td><img src="blank.gif" width="60" height="14"></td>
                    <td><img src="blank.gif" width="60" height="14"></td>
                    <td><img src="blank.gif" width="60" height="14"></td>
                  </tr>
<?php
$tot_cost = 0;
$tot_vat = 0;
$tot_price = 0;
for($l=0; $l<sizeof($order); $l++){
?>
                  <input type="hidden" name="order[<?php print $l; ?>]" value="<?php print tag($order[$l]); ?>">
                  <input type="hidden" name="price[<?php print $l; ?>]" value="<?php print tag($price[$l]); ?>">
                  <tr> 
                    <td><?php print $order[$l]; ?></td>
                    <td align="right"><?php print "$CurrencyUnit"; ?><?php print number_format($price[$l]/($vat_at+100)*100, 2); ?></td>
                    <td align="right"><?php print "$CurrencyUnit"; ?><?php print number_format($price[$l]-($price[$l]/($vat_at+100)*100),2); ?></td>
                    <td align="right"><?php print "$CurrencyUnit"; ?><?php print number_format($price[$l],2); ?></td>
                  </tr>&nbsp;
                  <?php
$tot_cost += $price[$l]/($vat_at+100)*100;
$tot_vat += $price[$l]-($price[$l]/($vat_at+100)*100);
$tot_price += $price[$l];
}
?>
                  <tr> 
                    <td>
                      <input type="text" name="order[<?php print sizeof($order); ?>]" size="70"></td>
                    <td align="right"><img src="blank.gif" width="60" height="14"></td>
                    <td align="right"><img src="blank.gif" width="60" height="14"></td>
                    <td align="right"><?php print "$CurrencyUnit"; ?> <input type="text" name="price[<?php print sizeof($price); ?>]" size="5"></td>
                  </tr> <tr> 
                    <td colspan=4><?php if($DisplayVat == "Y") {
					print "<input type=\"hidden\" name=\"vat_at\" value=\"$VatRate\" size=\"4\">
					<i>$TaxName charged at a rate of $VatRate%</i>"; } else print "<img src=\"blank.gif\" width=\"1\" height=\"14\">"; ?></td>
                  </tr>
                </table> <table align="right" width="0"  CELLPADDING="2" CELLSPACING="1" border="1" bordercolor="<?PHP print "$BorderColour"; ?>">
                  <br>
                  <tr> 
                    <td width="60" align="right"><b><?php print "$CurrencyUnit"; ?><?php print number_format($tot_cost,2); ?></b></td>
                    <td align="right" width="60"><b><?php print "$CurrencyUnit"; ?><?php print number_format($tot_vat,2); ?></b></td>
                    <td align="right" width="60"><b><?php print "$CurrencyUnit"; ?><?php print number_format($tot_price,2); ?></b></td>
                  </tr>
                </table><br>
<br>
<br>Enter any additional notes:<br>
<textarea name="notes" cols="100%" rows="5"><?php print "$notes"; ?></textarea> <br> <br><br>
                <?php print "$InvoiceSignature"; ?><br></td>
            </tr>
            <tr> 
              <td><br> <br> <p align="center"><input name="action" type="submit" value="Update Invoice" class="button"> &nbsp; 
			  <input name="action" type="submit" value="Process Invoice" class="button"></p></td>
            </tr></table>
        </FORM> </TD>
    </TR>
  </TABLE>
</DIV>
</BODY>
</HTML>
<?php
}
if($action == "Process Invoice"){ processform();} else {mainform();}
?>
