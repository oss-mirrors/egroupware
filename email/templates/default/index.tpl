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

<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr bgcolor="{arrows_backcolor}" align="center">
	<td>&nbsp;</td>
	{prev_arrows}
	<td>&nbsp;</td>
	{next_arrows}
	<td>&nbsp;</td>
</tr>
</table>

<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr>
	<td colspan="6" bgcolor="{stats_backcolor}">
		<table border="0" cellpadding="0" cellspacing="1" width="100%">
		<tr>
			<td>
				<font face="{stats_font}" color="{stats_color}">
					<strong> {stats_folder} - </strong> <br>
					Saved messages: {stats_saved} <br>
					New messages: {stats_new} <br>
					Total size of folder: {stats_size}
				</font>
			</td>
			<td align="right">
				<table border="0" cellpadding="0" cellspacing="0">
				<tr>
				<form name="switchbox" action="{switchbox_action}" method="post">
					<td>
						{switchbox_listbox}
					</td>
					<td>
						&nbsp;&nbsp;
						{folder_maint_button}
					</td>
				</form>
				</tr>
				</table>
			</td>
		</tr>
		</table>
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
				<a href="{ftr_compose_link}">{ftr_compose_txt}</a>
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