<!-- BEGIN list -->
<p>&nbsp;</p>

<table width="90%" border="0" cellspacing="2" cellpadding="0" align="center">
	<tr bgcolor="{BGROUND}"> 
		<td colspan="5"><a href="{MAIN_LINK}">{LANG_MAIN}</a> : {CATEGORY}</td>
	</tr>

{rows}

</table>
<!-- END list -->

<!-- BEGIN row_empty -->
<tr bgcolor="{tr_color}">
	<td colspan="5" align="center"><b>{lang_no_forums}</b></td>
</tr>
<!-- END row_empty -->

<!-- BEGIN row -->
<tr bgcolor="{tr_color}">
	<td width="2%" valign="top"><img src="{IMG_URL_PREFIX}forum.gif" width="16" height="16" alt="{LANG_SUBCAT}"></td>
	<td width="40%"><a href="{THREADS_LINK}">{NAME}</a></td>
	<td width="30%">{DESC}</td>
	<td width="20%">&nbsp;{value_last_post}</td>
	<td width="4%" align="right">{value_total}&nbsp;</td>
</tr>
<!-- END row -->
