<!-- begin class_prefs_ex_accounts.tpl -->
{pref_errors}
<p>
  <b>{page_title}</b>
  <hr>
</p>
<table border="0" cellspacing="2" cellpadding="2" width="95%" align="center">
<tr bgcolor="{tr_titles_color}">
	<td width="70%" align="left">
		<font face="{font}">Account Indentity</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">status</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">action1</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">action2</font>
	</td>
</tr>
<!-- BEGIN B_accts_list -->
<tr bgcolor="{tr_color}">
	<td width="70%" align="left">
		<font face="{font}">{indentity}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{status}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{edit_href}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{delete_href}</font>
	</td>
</tr>
<!-- END B_accts_list -->
<tr>
	<td colspan="4" align="center">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="4" align="center">
		{add_new_acct_href}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{done_href}
	</td>
</tr>
</table>
<p>
	&nbsp;
</p>
<!-- end class_prefs_ex_accounts.tpl -->
