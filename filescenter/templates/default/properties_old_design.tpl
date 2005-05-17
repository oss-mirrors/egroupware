<!-- BEGIN main -->
<center>
<form action="{action_url}" name="mailsettings" method="post">
<br>
<table width="90%" border="0" cellspacing="0" cellpading="0">
	<tr>
		<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">{lang_general}</a></th>
		<th id="tab2" class="activetab" onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);">{lang_read_permissions}</a></th>
		<th id="tab3" class="activetab" onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);">{lang_write_permissions}</a></th>
	</tr>
</table>
<br><br>


<!-- The code for General File Properties Tab -->

<div id="tabcontent1" class="inactivetab">
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_general_properties}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td width="50%" class="td_left">
				{lang_filename}:
			</td>
			<td width="50%" class="td_right">
				<input type="text" size="30" name="globalsettings[filename]" value="{value_filename}">
			</td>
		</tr>
		<tr class="row_on">
			<td width="50%" class="td_left">
				{lang_filelocation}:
			</td>
			<td width="50%" class="td_right">
				{value_filelocation}
			</td>
		</tr>
		<tr class="row_off">
			<td width="50%" class="td_left">
				{lang_filetype}:
			</td>
			<td width="50%" class="td_right">
				{value_filetype}
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</table>
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_history}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td class="td_left">
				{lang_datetime_created}:
			</td>
			<td class="td_right">
				{value_datetime_created}
			</td>
		</tr>
		<tr class="row_on">
			<td class="td_left">
				{lang_datetime_accessed}:
			</td>
			<td class="td_right">
				{value_datetime_accessed}
			</td>
		</tr>
		<tr class="row_off">
			<td class="td_left">
				{lang_datetime_modified}:
			</td>
			<td class="td_right">
				{value_datetime_modified}
			</td>
		</tr>
		<tr class="row_on">
			<td class="td_left">
				{lang_filehistory}:
			</td>
			<td class="td_right">
				{value_filehistory}
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</table>
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_sharing}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td colspan="2" class="td_left">
				<input type='checkbox' name='sharing' value='1'>{lang_activate_sharing}
			</td>
		</tr>
	</table>
</div>


<!-- The code for Read Permissions Tab -->

<div id="tabcontent2" class="inactivetab">
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_auth_groups}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td colspan="2" class="td_left">
				{select_r_auth_groups}
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</table>
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_auth_users}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td colspan="2" class="td_left">
				{select_r_auth_users}
			</td>
		</tr>
	</table>
</div>

<!-- The code for Write Permissions Tab -->

<div id="tabcontent3" class="inactivetab">
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_auth_groups}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td colspan="2" class="td_left">
				{select_w_auth_groups}
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</table>
	<table width="88%" border="0" cellspacing="0" cellpading="1">
		<tr class="th">
			<td width="50%" class="td_left">
				<b>{lang_auth_users}<b>
			</td>
			<td class="td_right">
				&nbsp;
			</td>
		</tr>
		<tr class="row_off">
			<td colspan="2" class="td_left">
				{select_w_auth_users}
			</td>
		</tr>
	</table>
</div>


<br><br>
<table width="90%" border="0" cellspacing="0" cellpading="1">
	<tr>
		<td width="90%" align="left"  class="td_left">
			<a href="{back_url}">{lang_back}</a>
		</td>
		<td width="10%" align="center" class="td_right">
			<a href="javascript:document.mailsettings.submit();">{lang_save}</a>
		</td>
	</tr>
</table>
</form>
</center>
<!-- END main -->

