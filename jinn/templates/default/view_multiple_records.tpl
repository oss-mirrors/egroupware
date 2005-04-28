<!-- BEGIN header -->
<script language="javascript" type="text/javascript">
<!--

function img_popup(img,pop_width,pop_height,attr)
{
   options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
   parent.window.open("{popuplink}&path="+img+"&attr="+attr, "pop", options);
}


//-->
</script>

<!-- END header -->

<!-- BEGIN recordheader -->
<table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;width:570px">
<!-- END recordheader -->

<!-- BEGIN rows -->
	<tr>
		<td bgcolor="{row_color}" nowrap="nowrap" valign="top">{fieldname}{tipmouseover}&nbsp;</td>
		<td style="width:570px" bgcolor="{row_color}">{input}</td>
	</tr>
<!-- END rows -->

<!-- BEGIN recordfooter -->
</table>
<br/>
<!-- END recordfooter -->

<!-- BEGIN back_button -->
	<input type="button" onClick="{back_onclick}" value="{lang_back}">
<!-- END back_button -->

<!-- BEGIN footer -->
<table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;width:570px">
	<tr>
	<td colspan="2" bgcolor="{row_color}">
	<!--input type="button" onClick="{edit_onclick}" value="{lang_edit}"-->
	{extra_back_button}
	</td></tr>
	<tr><td colspan="2" >
	</tr>
</table>

	<table align="right" style="background-color:#ffffff">
	<tr>
	<td>	
	</td>
	</tr>
	</table>
	
<!-- END footer -->

