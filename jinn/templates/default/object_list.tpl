<!-- BEGIN listheader -->
<div style="width:100%;background-color:grey;height:1px;"></div><br/>
<h3 style="text-align:center">{table_title}</h3>
<input type="button" value="{lang_add_object}" onclick="document.location.href='{link_add_object}'">
<!--<input type="button" value="{lang_auto_add_object}" onclick="document.location.href='{link_auto_add_object}'">-->

<table border="0" cellspacing="1" cellpadding="0" align=center width="100%">
	<tr style="font-weight:bold;padding:3px;">
		<td colspan="2" valign="top" style="background-color:{bgclr};font-weight:bold;padding:3px;">&nbsp;</td>
		{fieldnames}
	</tr>
<!-- END listheader -->

<!-- BEGIN rows -->
<tr valign="top">
<td style="background-color:{bgclr}" align="left"><a href="{link_edit}">{lang_edit}</a></td>
<td style="background-color:{bgclr}" align="left"><a href="{link_del}" onClick="return window.confirm('confirm_del');">{lang_del}</a></td>
{row}
</tr>
<!-- END rows -->

<!-- BEGIN listfooter -->
</table>
<!-- END listfooter -->
