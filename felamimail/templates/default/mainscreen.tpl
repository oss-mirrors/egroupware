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

function displayMessage(url) 
{
	window.open(url, "felamimailDisplay", "width=800,height=600,screenX=0,screenY=0,top=0,left=0,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no");
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
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_change_folder}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "changeFolder";
		}
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
		checkedCounter += (inputBox.checked) ? 1 : -1;
		if (checkedCounter > 0)
		{
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_move_message}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "moveMessage";
		}
		else
		{
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

<TABLE BORDER="0" WIDTH="100%" CELLSPACING="0" CELLPADDING="2">
<form name=searchForm method=post action="{url_search_settings}">
<!--	<TR bgcolor="#ffffcc"> -->
	<TR bgcolor="{th_bg}">
		<td align="left" width="70%" style="border-color:silver; border-style:solid; border-width:0px 0px 1px 0px; font-size:10px;">
			<a href="{url_compose_empty}">{lang_compose}</a>
		</td>
		<td align="right" width="30%" valign="middle" style="border-color:silver; border-style:solid; border-width:0px 0px 1px 0px; font-size:10px;">
			{quota_display}
		</td>
	</tr>
	<TR bgcolor="{row_off}">
		<TD ALIGN="left" WIDTH="70%" style="border-color:silver; border-style:solid; border-width:0px 0px 1px 0px; font-size:10px;">
			<!-- <a href="{url_compose_empty}">{lang_compose}</a>&nbsp;&nbsp; -->
			{lang_quicksearch}
			<input class="input_text" type="text" size="50" name="quickSearch" value="{quicksearch}"
			onChange="javascript:document.searchForm.submit()" style="font-size:11px;">
		</td>
		<td align='right' width="30%" style="border-color:silver; border-style:solid; border-width:0px 0px 1px 0px; ">
			<a href="{url_filter}"><img src="{new}" alt="{lang_edit_filter}" title="{lang_edit_filter}" border="0"></a>&nbsp;&nbsp;
			<input type=hidden name="changeFilter">
			<select name="filter" onChange="javascript:document.searchForm.submit()" style="border : 1px solid silver; font-size:11px;">
				{filter_options}
			</select>
		</td>
	</tr>
</form>
</table>


<TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" BORDER="0">
	<TR>
		<TD BGCOLOR="{row_off}">
			<TABLE BBGCOLOR="#ffffcc" COLS=2 BORDER='0' cellpadding="2" cellspacing=0 width="100%" sstyle="table-layout:fixed">
				<TR valign="middle" bgcolor="{th_bg}">
					<FORM name=messageList method=post action="{url_change_folder}">
					<td align="LEFT" valign="center" width="5%">
						<TT><SMALL>
						<SELECT NAME="mailbox" onChange="document.messageList.submit()" style="border-bottom : 1px solid; font-size:11px;
			border-left : 0px; border-right : 0px; border-top : 0px;">
							{options_folder}
						</SELECT></SMALL></TT>
						<input type="hidden" name="folderAction" value="changeFolder">
						<noscript>
							<NOBR><SMALL><INPUT TYPE=SUBMIT NAME="moveButton" VALUE="{lang_doit}"></SMALL></NOBR>
						</noscript>
						<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
					</TD>
					<td nowrap id="folderFunction" wwidth="1%" align="left" style="font-size:10px;">
						{lang_change_folder}
					</td>
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
		</TD>
	</TR>
	{status_row}
	<TR>
		<TD>
			<table WIDTH=100% BORDER=0 CELLPADDING=1 CELLSPACING=1 style="table-layout:fixed">
				<tr>
					<td width="20px" bgcolor="{th_bg}" align="center">
						<input type="checkbox" id="messageCheckBox" onClick="selectAll(this)">
					</td>
					<td width="145px" bgcolor="{th_bg}" align="center" class="{css_class_from}">
						<a href="{url_sort_from}">{lang_from}</a>
					</td>
					<td width="95px" bgcolor="{th_bg}" align="center" class="{css_class_date}">
						<a href="{url_sort_date}">{lang_date}</a>
					</td>
					<td width="70px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td bgcolor="{th_bg}" align="center" class="{css_class_subject}">
						<a href="{url_sort_subject}">{lang_subject}</a>
					</td>
					<td width="40px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						<a href="{url_sort_size}">{lang_size}</a>
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
			<table WIDTH="100%" BORDER="0" CELLPADDING="1" CELLSPACING="2">
				<tr BGCOLOR="{row_off}" class="text_small">
					<td width="18%">
						{link_previous}
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
						{link_next}
					</td>
				</tr>
			</table>
		</td>
	</tr>

<!-- END status_row_tpl -->

<!-- BEGIN header_row -->
<tr class="{row_css_class}" onmouseover="javascript:style.backgroundColor='#F6F7F4';" onmouseout="javascript:style.backgroundColor='#FFFFFF';">
	<td width="1%" align="center">
		<input class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="{message_uid}" onClick="toggleFolderRadio(this)" {row_selected}>
	</td>
	<td width="10%" nowrap>
		<a class="{row_css_class}" href="{url_compose}" title="{full_address}">{sender_name}</a>
<!--		<a href="{url_add_to_addressbook}"><img src="{sm_envelope}" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a> -->
	</td>
	<td nowrap align="center">
		{date}
	</td>
	<td align="center">
		{state}{row_text}
	</td>
	<td>
		<a class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
	</td>
	<td>
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
	<table cellpadding="0" cellspacing="0" width="200" style="border : 1px solid silver;">
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

<!-- BEGIN subject_same_window -->
	<td bgcolor="#FFFFFF">
		<a class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
	</td>
<!-- END subject_same_window -->

<!-- BEGIN subject_new_window -->
	<td bgcolor="#FFFFFF">
		<a class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
	</td>
<!-- END subject_new_window -->
