<!-- begin email_index.tpl -->
<script>
function do_action(act)
{
	flag = 0;
	for (i=0; i<document.delmov.elements.length; i++) {
		//alert(document.delmov.elements[i].type);
		if (document.delmov.elements[i].type == "checkbox") {
			if (document.delmov.elements[i].checked) {
				flag = 1;
			}
		}
	}
	if (flag != 0) {
		document.delmov.what.value = act;
		document.delmov.submit();
	} else {
		alert("{select_msg}");
		document.delmov.tofolder.selectedIndex = 0;
	}
}

function check_all()
{
	for (i=0; i<document.delmov.elements.length; i++) {
		if (document.delmov.elements[i].type == "checkbox") {
			if (document.delmov.elements[i].checked) {
				document.delmov.elements[i].checked = false;
			} else {
				document.delmov.elements[i].checked = true;
			}
		} 
	}
}
</script>

<!-- BEGIN B_action_report -->
<p><center>{report_this}</center></p>
<!-- END B_action_report -->

<table border="0" cellpadding="4" cellspacing="1" width="95%" align="center">
<tr>
	<td bgcolor="{ctrl_bar_back2}" align="center">
		<a href="{accounts_link}">{accounts_txt}</a>
	</td>
	<td bgcolor="{ctrl_bar_back2}" align="center">
		<a href="{compose_link}">{compose_txt}</a>
	</td>
	<td bgcolor="{ctrl_bar_back2}" align="center">
		<a href="{folders_link}">{folders_txt}</a>
	</td>
	<td colspan="4" bgcolor="{ctrl_bar_back2}" align="center">
		<a href="{routing_link}">{routing_txt}</a>
	</td>
</tr>
<tr bgcolor="{arrows_backcolor}"> <!-- this color for the nav arrows -->
	<form name="switchbox" action="{switchbox_action}" method="post">
	<td bgcolor="{ctrl_bar_back1}" align="center">
		<font size="-1">Folder:</font>
		{switchbox_listbox}
	</td>
	</form>
	<form name="sortbox" action="{sortbox_action}" method="post">
	<td bgcolor="{ctrl_bar_back1}" align="center">
		<font size="-1">Sort:</font>
		<select name="{sortbox_select_name}" onChange="{sortbox_on_change}">
		{sortbox_select_options}
		</select>
	</td>
	</form>
	<td bgcolor="{ctrl_bar_back1}" align="center">
		<font size="-1">Account:</font>
		<select name="account">
		<option value="default">Default</option>
		<option value="other_1">Other 1</option>
		</select>
	</td>
	{prev_arrows}
	{next_arrows}
</tr>
</table>
<br>

<table border="0" cellpadding="4" cellspacing="1" width="95%" align="center">
<tr>
	<td bgcolor="{stats_backcolor}" align="center">
		<font face="{stats_font}" size="{stats_fontsize}" color="{stats_color}">
			<strong>{stats_folder}</strong>
		</font>
	</td>
	<td bgcolor="{stats_backcolor}" align="center">
		<font face="{stats_font}" size="{stats_fontsize}" color="{stats_color}">
			&nbsp;&nbsp;{stats_new}&nbsp;&nbsp;{lang_new}
		</font>
	</td>
	<td bgcolor="{stats_backcolor}" align="center">
		<font face="{stats_font}" size="{stats_fontsize}" color="{stats_color}">
			&nbsp;&nbsp;{stats_saved}&nbsp;&nbsp;{lang_total}
		</font>
	</td>
	<td bgcolor="{stats_backcolor}" align="center">
		<font face="{stats_font}" size="{stats_fontsize}" color="{stats_color}">
			&nbsp;&nbsp;{stats_size}&nbsp;&nbsp;{lang_size}
		</font>
	</td>
	<td bgcolor="{stats_backcolor}" align="center">
		<font face="{stats_font}" size="{stats_fontsize}" color="{stats_color}">
			&nbsp;&nbsp;{stats_first}&nbsp;to&nbsp;{stats_last}
		</font>
	</td>
</tr>
</table>

<table border="0" cellpadding="2" cellspacing="1" width="95%" align="center">
<tr>
	<td bgcolor="{hdr_backcolor}" width="3%" align="center">
		&nbsp;
	</td>
	<td bgcolor="{hdr_backcolor}" width="2%">
		&nbsp;
	</td>
	
	<td bgcolor="{hdr_backcolor}" width="21%">
		<font size="2" face="{hdr_font}">
		{hdr_from}
		</font>
	</td>
	<td bgcolor="{hdr_backcolor}" width="36%">
		<font size="2" face="{hdr_font}">
 		{hdr_subject}
		</font>
	</td>
	<td bgcolor="{hdr_backcolor}" width="12%" align="center">
		<font size="1" face="{hdr_font}">
		{hdr_date}
		</font>
	</td>
	<td bgcolor="{hdr_backcolor}" width="4%" align="center">
		<font size="1" face="{hdr_font}">
		{hdr_size}
		</font>
	</td>
</tr>
<!-- BEGIN B_no_messages -->
<tr>
	<td bgcolor="{mlist_backcolor}" colspan="6" align="center">
		<!-- form delmove init here is just a formality -->
		{mlist_delmov_init}
		<font size="2" face="{mlist_font}">{report_no_msgs}</font>
	</td>
</tr>
<!-- END B_no_messages -->

<!--- &nbsp; LAME BLOCK SEP &nbsp; -->

<!-- BEGIN B_msg_list -->
<tr>
	<td bgcolor="{mlist_backcolor}" align="center">
	<!-- INIT FORM ONCE -->{mlist_delmov_init}
		<input type="checkbox" name="msglist[]" value="{mlist_msg_num}">
	</td>
	<td bgcolor="{mlist_backcolor}" width="1%" align="center">
		{mlist_new_msg}
		&nbsp;&nbsp;
		{mlist_attach}
	</td>
	<td bgcolor="{mlist_backcolor}" align="left">
		{open_newbold}<font size="2" face="{mlist_font}">{mlist_from} {mlist_from_extra}</font>{close_newbold}
	</td>
	<td bgcolor="{mlist_backcolor}" align="left">
		{open_newbold}<font size="2" face="{mlist_font}"><a href="{mlist_subject_link}">{mlist_subject}</a></font>{close_newbold}
	</td>
	<td bgcolor="{mlist_backcolor}" align="center">
		<font size="2" face="{mlist_font}">{mlist_date}</font>
	</td>
	<td bgcolor="{mlist_backcolor}" align="center">
		<font size="1" face="{mlist_font}">{mlist_size}</font>
	</td>
</tr>
<!-- END B_msg_list -->
<tr>
	<td bgcolor="{ftr_backcolor}" align="center">
		<a href="javascript:check_all()">
		<img src="{app_images}/check.gif" border="0" height="16" width="21"></a>
	</td>
	<td bgcolor="{ftr_backcolor}" colspan="5">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<input type="button" value="{delmov_button}" onClick="do_action('delall')">
			</td>
			<td align="right">
				{delmov_listbox}
			</td>
			</form>
		</tr>
		</table>
	</td>
</tr>
</table>

<br> 

<table border="0" align="center" width="95%">
<tr>
	<td align="left">
		<font color="{mlist_newmsg_color}">{mlist_newmsg_char}</font>&nbsp;=&nbsp;{mlist_newmsg_txt}
	</td>
</tr>
</table>
<!-- end email_index.tpl -->
