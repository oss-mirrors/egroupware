<!-- BEGIN main -->
<script language="JavaScript1.2">
<!--
var sURL = unescape(window.location.pathname);

function doLoad()
{
	// the timeout value should be the same as in the "refresh" meta-tag
	{refreshTime}
}

function refresh()
{
	var Ziel = '{refresh_url}'
	window.location.href = Ziel;
}

doLoad();

//-->
</script>

<script type="text/javascript">
<!--
	var checkedCounter={checkedCounter}, aktiv;
	var maxMessages = {maxMessages};
	
	function selectAll(inputBox)
	{
		if(aktiv)
		{
			// do not reload, while we try to select some messages
			window.clearTimeout(aktiv);
			{refreshTime}
		}

		if(inputBox.checked)
		{
			value = true;
			checkedCounter = maxMessages;
		}
		else
		{
			value = false;
			checkedCounter = 0;
		}
		//alert(document.forms["messageList"].elements['msg[]'][10].checked);
		for (var i = 0; i < document.forms["messageList"].elements['msg[]'].length; i++)
		{
			document.forms["messageList"].elements['msg[]'][i].checked = value;
		}
		folderFunctions = document.getElementById('folderFunction');
		if(inputBox.checked)
		{
			checkedCounter = maxMessages;
			document.getElementsByTagName("input")[3].checked = "true";
//			//{lang_move_message}
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_move_message}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "moveMessage";
		}
		else
		{
			checkedCounter = 0;
			document.getElementsByTagName("input")[2].checked = "true";
//			//{lang_change_folder}
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_change_folder}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "changeFolder";
		}
		//alert(checkedCounter);
		//alert(document.getElementsByName("folderaction")[0].value);
		
	}

	function toggleFolderRadio(inputBox)
	{
		if(aktiv)
		{
			// do not reload, while we try to select some messages
			window.clearTimeout(aktiv);
			{refreshTime}
		}

		folderFunctions = document.getElementById("folderFunction");
		//var counter = parseInt(_counter);
		//alert(document.getElementById("msg_input_"+_counter).checked);
		//document.getElementsByTagName("input")[1].checked = "true";
		//tr	= eval(document.getElementsByTagName("tr")[counter+23]);
		//input	= eval(document.getElementsByTagName("input")[counter+10]);
		//tr	= document.getElementById("msg_tr_"+_counter);
		//input	= document.getElementById("msg_input_"+_counter);
		checkedCounter += (inputBox.checked) ? 1 : -1;
		if (checkedCounter > 0)
		{
			//document.getElementsByTagName("input")[3].checked = true;
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_move_message}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "moveMessage";
		}
		else
		{
			//document.getElementsByTagName("input")[2].checked = true;
			document.getElementById('messageCheckBox').checked = false;
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_change_folder}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "changeFolder";
		}
	}

//-->
</script>
<TABLE BBORDER="0" WIDTH="100%" CELLSPACING="0" CELLPADDING="2">
	<TR bgcolor="#ffffcc">
		<TD ALIGN="left" WIDTH="70%" >
			<a href="{url_compose_empty}">{lang_compose}</a>&nbsp;&nbsp;
			<a href="{url_filter}">{lang_edit_filter}</a>&nbsp;&nbsp;
		</td>
		<td align='right' width="30%" >
			{quota_display}
		</td>
	</tr>
	<TR bgcolor="#ffffcc">
		<form name=searchForm method=post action="{url_search_settings}">
		<td align="left" width="70%"  style="border-color:silver; border-style:solid; border-width:1px 0px 1px 0px; font-size:10px;">
			{lang_quicksearch}
			<input type="text" size="50" name="quickSearch" value="{quicksearch}"
			onChange="javascript:document.searchForm.submit()">
		</td>
		<td align="right" width="30%" valign="middle" style="border-color:silver; border-style:solid; border-width:1px 0px 1px 0px; ">
			<input type=hidden name="changeFilter">
			<select name="filter" onChange="javascript:document.searchForm.submit()">
				{filter_options}
			</select>
		</td>
		</form>
	</tr>
</table>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="0" CELLSPACING="0">
	<TR>
		<TD BGCOLOR="{row_off}">
			<TABLE BGCOLOR="#ffffcc" COLS=2 BORDER='0' cellpadding="2" cellspacing=0 width="100%" sstyle="table-layout:fixed">
				<TR valign="middle" bgcolor="#ffffcc">
					<FORM name=messageList method=post action="{url_change_folder}">
					<td nowrap id="folderFunction" width="1%" align="left" style="font-size:10px;">
						{lang_change_folder}
					</td>
					<td align="LEFT" valign="center">
						<TT><SMALL>
						<SELECT NAME="mailbox" onChange="document.messageList.submit()">
							{options_folder}
						</SELECT></SMALL></TT>
						<input type="hidden" name="folderAction" value="changeFolder">
						<noscript>
							<NOBR><SMALL><INPUT TYPE=SUBMIT NAME="moveButton" VALUE="{lang_doit}"></SMALL></NOBR>
						</noscript>
						<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
					</TD>
					<td width="12px" align="right" valign="center">
						<input type="image" src="{read_small}" name="mark_read" alt="{desc_read}" title="{desc_read}" width="16">
                                        </td>
                                        <TD WIDTH="4px" ALIGN="MIDDLE" valign="center">|</td>
                                        <td width="12px" align="left" valign="center">
						<input type="image" src="{unread_small}" name="mark_unread" title="{desc_unread}" width="16">
                                        </td>
                                        <TD WIDTH="2px" ALIGN="MIDDLE" valign="center">
                                                &nbsp;
                                        </td>
                                        <td width="12px" align="right" valign="center">
						<input type="image" src="{unread_flagged_small}" name="mark_flagged" title="{desc_important}" width="16">
                                        </td>
                                        <TD WIDTH="4px" ALIGN="MIDDLE" valign="center">|</td>
                                        <td width="12px" align="left" valign="center">
						<input type="image" src="{unread_small}" name="mark_unflagged" title="{desc_unimportant}">
                                        </td>
                                        <TD WIDTH="2px" ALIGN="MIDDLE" valign="center">
                                                &nbsp;&nbsp;
                                        </td>
                                        <td width="12px" align="RIGHT" valign="center">
						<input type="image" src="{unread_deleted_small}" name="mark_deleted" title="{desc_deleted}">
					</TD>
				</TR>
			</TABLE>
			<br>
		</TD>
	</TR>
	{status_row}
	<TR>
		<TD>
			<table WIDTH=100% BORDER=0 CELLPADDING=1 CELLSPACING=1 style="table-layout:fixed">
				<tr>
					<td width="20px" bgcolor="#FFFFCC" align="center">
						<input type="checkbox" id="messageCheckBox" onClick="selectAll(this)">
					</td>
					<td width="145px" bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_from}"><font color="black">{lang_from}</font></a></b>
					</td>
					<td width="95px" bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_date}"><font color="black">{lang_date}</font></a></b>
					</td>
					<td width="70px" bgcolor="#FFFFCC" align="center">
						&nbsp;
					</td>
					<td bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_subject}"><font color="black">{lang_subject}</font></a
					</td>
					<td width="40px" bgcolor="#FFFFCC" align="center">
						<b>{lang_size}</b>
					</td>
				</tr>
				{header_rows}
			</table>
		</TD>
	</TR>
	{status_row}
</table>
<!-- END main -->

<!-- BEGIN status_row_tpl -->
	<tr>
		<TD>
			<table WIDTH="100%" BORDER="0" CELLPADDING="1" CELLSPACING="0">
				<tr BGCOLOR="#FFFFFF">
					<td width="18%">
						{link_previous} | {link_next}
					</td>
					<td width="10%">
						&nbsp;
					</td>
					<TD align="center" width="36%">
						{message}
					</td>
					<td width="18%">
						{trash_link}
					</td>
					<td align="right" width="18%">
						<noscript>{select_all_link}</noscript>
					</td>
				</tr>
			</table>
		</td>
	</tr>

<!-- END status_row_tpl -->

<!-- BEGIN header_row -->
<tr class="{row_css_class}" style=" height:15px; ">
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="{message_uid}" onClick="toggleFolderRadio(this)" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a class="{row_css_class}" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{sm_envelope}" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" align="center">
		{state}{row_text}
	</td>
	<td bgcolor="#FFFFFF">
		<a class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row -->

<!-- BEGIN error_message -->
	<tr>
		<td bgcolor="#FFFFCC" align="center" colspan="6">
			<font color="red"><b>{lang_connection_failed}</b></font><br>
			{message}
		</td>
	</tr>
<!-- END error_message -->

<!-- BEGIN quota_block -->
	<table border="1" cellpadding="0" cellspacing="0" width="200">
		<tr valign="middle">
			<td width="{leftWidth}%" bgcolor="{quotaBG}" align="center" valign="middle">
				<small>{quotaUsage_left}</small>
			</td>
			<td align="center" valign="middle">
				<small>{quotaUsage_right}</small>
			</td>
		</tr>
	</table>
<!-- END quota_block -->
