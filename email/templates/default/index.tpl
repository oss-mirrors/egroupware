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

 {V_any_deleted}

<table border="0" cellpadding="4" cellspacing="1" width="95%" align="center">
<tr bgcolor="{arrows_backcolor}"> <!-- this color for the nav arrows -->
	<td bgcolor="{ctrl_bar_back1}" align="center">
		<font size="-1">A:</font>
		<select name="account">
		<option value="default">Default</option>
		<option value="other_1">Other 1</option>
		</select>
	</td>
	<form name="sortbox" action="{sortbox_action}" method="post">
	<td bgcolor="{ctrl_bar_back1}" align="center">
		<font size="-1">S:</font>
		<select name="{sortbox_select_name}" onChange="{sortbox_on_change}">
		{sortbox_select_options}
		</select>
	</td>
	</form>
	<form name="switchbox" action="{switchbox_action}" method="post">
	<td bgcolor="{ctrl_bar_back1}" align="center">
		<font size="-1">F:</font>
		{switchbox_listbox}
	</td>
	</form>
	{prev_arrows}
	{next_arrows}
</tr>
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
<tr>
	<td colspan="7" bgcolor="{hdr_backcolor}" align="center">
		<!-- spacer -->
	</td>
</tr>
</table>

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

<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr>
	<td bgcolor="{hdr_backcolor}" width="3%" align="center">
		&nbsp;
	</td>
	<td bgcolor="{hdr_backcolor}" width="2%">
		&nbsp;
	</td>
	
	<td bgcolor="{hdr_backcolor}" width="34%">
		<font size="2" face="{hdr_font}">
 		<b>{hdr_subject}</b>
		</font>
	</td>
	<td bgcolor="{hdr_backcolor}" width="23%">
		<font size="2" face="{hdr_font}">
		<b>{hdr_from}</b>
		</font>
	</td>
	<td bgcolor="{hdr_backcolor}" width="12%">
		<font size="2" face="{hdr_font}">
		<b>{hdr_date}</b>
		</font>
	</td>
	<td bgcolor="{hdr_backcolor}" width="4%">
		<font size="2" face="{hdr_font}">
		<b>{hdr_size}</b>
		</font>
	</td>
</tr>

{V_msg_list}

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
		<font color="{mlist_newmsg_color}">{mlist_newmsg_char}</font>&nbsp;{mlist_newmsg_txt}
	</td>
</tr>
</table>
<!-- end email_index.tpl -->
