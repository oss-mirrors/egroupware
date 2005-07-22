<!-- BEGIN main -->
<script language="JavaScript1.2">
<!--
var sURL = unescape(window.location.pathname);

// some translations needed for javascript functions

var movingMessages='{lang_moving_messages_to}';

function doLoad()
{
	// the timeout value should be the same as in the "refresh" meta-tag
	{refreshTime}
}

function refresh()
{
	//var Ziel = '{refresh_url}'
	//window.location.href = Ziel;
	resetMessageSelect();
	xajax_doXMLHTTP('felamimail.ajaxfelamimail.refreshMessageList');
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
		//alert(document.getElementsByTagName('input').length);
		for (var i = 0; i < document.getElementsByTagName('input').length; i++)
		{
			if(document.getElementsByTagName('input')[i].name == 'msg[]')
			{
				//alert(document.getElementsByTagName('input')[i].name);
				document.getElementsByTagName('input')[i].checked = value;
			}
		}

		folderFunctions = document.getElementById('folderFunction');
		if(inputBox.checked)
		{
			checkedCounter = maxMessages;
			//document.getElementsByTagName("input")[3].checked = "true";
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('{lang_select_target_folder}');
			folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "moveMessage";
		}
		else
		{
			checkedCounter = 0;
			//document.getElementsByTagName("input")[2].checked = "true";
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			var textNode = document.createTextNode('');
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
			//folderFunctions.appendChild(textNode);
			document.getElementById("folderFunction").innerHTML="{lang_select_target_folder}";
			document.getElementsByName("folderAction")[0].value = "moveMessage";
		}
		
		else
		{
			document.getElementById('messageCheckBox').checked = false;
			while (folderFunctions.hasChildNodes())
			    folderFunctions.removeChild(folderFunctions.lastChild);
			//var textNode = document.createTextNode('{lang_change_folder}');
			//folderFunctions.appendChild(textNode);
			document.getElementsByName("folderAction")[0].value = "changeFolder";
		}
		
	}

//-->
</script>

<TABLE WIDTH="100%" CELLPADDING="2" CELLSPACING="0" BORDER="0">
	<TR>
		<TD BGCOLOR="{th_bg}" align="left"><nobr>
			<img src="{mail_find}" border="0" name="{lang_quicksearch}" alt="{lang_quicksearch}" title="{lang_quicksearch}" width="16" onClick="javascript:document.searchForm.submit()">
			<input class="input_text" type="text" size="25" name="quickSearch" id="quickSearch" value="{quicksearch}" onChange="javascript:quickSearch(this.value);" onFocus="this.select();" style="font-size:11px;">
		</td>
		<TD BGCOLOR="{th_bg}" align="left"><nobr>
			<a href="{url_filter}"><img src="{new}" alt="{lang_edit_filter}" title="{lang_edit_filter}" border="0"></a>&nbsp;
			<select name="filter" id="filter" onchange="javascript:extendedSearch(this)" style="border : 1px solid silver; font-size:11px;">
				{filter_options}
			</select>
		</TD>
		<TD BGCOLOR="{th_bg}" width="30%" align="center" style="white-space: nowrap;">
			<b>{current_folder}</b>
		</td>
		<td BGCOLOR="{th_bg}" width="30%" align="center" style="white-space: nowrap;">
			{quota_display}
		</td>
		<TD BGCOLOR="{th_bg}" align="right" width="20%">
			<TABLE BORDER="0" cellpadding="2" cellspacing=0>
				<TR valign="middle" bgcolor="{th_bg}">
					<td width="12px" align="left" valign="center">
						<a href="{url_compose_empty}">
						<img src="{write_mail}" border="0" name="{lang_compose}" alt="title="{lang_compose}" title="{lang_compose}" width="16">
						</a>
                                        </td>
                                        <TD WIDTH="4px" ALIGN="MIDDLE" valign="center">|</td>				
					<td width="12px" align="right" valign="center">
						<input type="image" src="{read_small}" name="mark_read" alt="{desc_read}" title="{desc_read}" width="16" onClick="document.messageList.submit()">
                                        </td>
                                        <td width="12px" align="left" valign="center">
						<input type="image" src="{unread_small}" name="mark_unread" alt="title="{desc_unread}" title="{desc_unread}" width="16">
                                        </td>
                                        <TD WIDTH="4px" ALIGN="MIDDLE" valign="center">|</td>
                                        
                                        <td width="12px" align="right" valign="center">
						<input type="image" src="{unread_flagged_small}" name="mark_flagged" alt="{desc_important}" title="{desc_important}" width="16">
                                        </td>
                                        <td width="12px" align="left" valign="center">
						<input type="image" src="{read_flagged_small}" name="mark_unflagged" alt="{desc_unimportant}" title="{desc_unimportant}">
                                        </td>
                                        <TD WIDTH="4px" ALIGN="MIDDLE" valign="center">|</td>
                                        </td>
                                        <td width="12px" align="RIGHT" valign="center">
						<input type="image" src="{trash}" name="mark_deleted" title="{desc_deleted}" onClick="javascript:deleteMessages(xajax.getFormValues('formMessageList'))">
					</TD>
				</TR>
			</TABLE>
			
		</td>
	</TR>
</table>

<TABLE  width="100%" cellpadding="0" cellspacing="0" border="0">
		<input type="hidden" name="folderAction" id="folderAction" value="changeFolder">
		<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
		<INPUT TYPE=hidden NAME="mailbox">

	<tr>
		<td>
			<span id="folderFunction" align="left" style="font-size:10px;">&nbsp;</span>	
		</td>
		<td>
			&nbsp;
		</td>
		<td align="left" style="font-size:10px">
			<span id="messageCounter">{message}</span>&nbsp;{trash_link}
		</td>
		<td align="right">
			<input type="image" src="{start_kde}" border="0" alt="start" onClick="javascript:jumpStart(); return false;">
			<input type="image" src="{previous_kde}" border="0" alt="skipPrevious" onClick="javascript:skipPrevious(); return false;">
			<input type="image" src="{next_kde}" border="0" alt="skipForward" onClick="javascript:skipForward(); return false;">
			<input type="image" src="{finnish_kde}" border="0" alt="end" onClick="javascript:jumpEnd(); return false;">
		</td>
	</tr>
	<TR>
		<td valign="top" class="folderlist" width="180">
	
			<!-- StartFolderTree -->

			<div id="divFolderTree" style="overflow:auto; width:180px; height:474px; margin-bottom: 0px;padding-left: 0px; padding-top:0px; z-index:100; border : 1px solid Silver;">
			</div>
			{folder_tree}
			
		</td>
		<td width="10" valign="middle">
			<div id="vr" align="center">
				::
			</div>
		</td>
		
<!-- ToDo: ResizeVerticalRule -->		
		
		<TD valign="top" colspan="2">

			<!-- Start Header MessageList -->

			<table WIDTH=100% BORDER="0" CELLSPACING="0" style="table-layout:fixed;">
				<tr>
					<!-- <td width="22px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>-->
					<td width="20px" bgcolor="{th_bg}" align="center">
					&nbsp;<input style="width:10px; height:10px; border:none" type="checkbox" id="messageCheckBox" onclick="selectAll(this)">
					</td>
					<td width="120px" bgcolor="{th_bg}" align="left" class="{css_class_from}">
						&nbsp;<a href="{url_sort_from}">{lang_from}</a>
					</td>
					<td width="95px" bgcolor="{th_bg}" align="center" class="{css_class_date}">
						&nbsp;&nbsp;<a href="{url_sort_date}">{lang_date}</a>
					</td>
					<td width="70px" bgcolor="{th_bg}" align="center" class="text_small">
						{lang_status}
					</td>
					<td width="14px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td bgcolor="{th_bg}" align="left" class="{css_class_subject}">
						&nbsp;&nbsp;&nbsp;<a href="{url_sort_subject}">{lang_subject}</a>
					</td>
					<td width="40px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						<a href="{url_sort_size}">{lang_size}</a>&nbsp;
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						&nbsp;
					</td>
				</tr>
			</table>

			<!-- End Header MessageList -->			


			<!-- Start MessageList -->

			<form id="formMessageList">			
			<div id="divMessageList" style="overflow:auto; height:460px; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom: 0px; z-index:90; border : 1px solid Silver;">
				<table BORDER="0" style="width:98%; padding-left:2; table-layout: fixed;">
					{header_rows}
				</table>
			</div>
			</form>

			<!-- End MessageList -->

		</TD>
	</TR>
</table>


<!-- END main -->

<!-- BEGIN message_table -->
<table BORDER="0" style="width:98%; padding-left:2; table-layout: fixed;">
	{message_rows}
</table>
<!-- END message_table -->

<!-- BEGIN status_row_tpl -->
<table WIDTH="100%" BORDER="0" CELLPADDING="1" CELLSPACING="2">
				<tr BGCOLOR="{row_off}" class="text_small">
					<td width="18%">
						{link_previous}
					</td>
					<td width="10%">&nbsp;
						
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


<!-- END status_row_tpl -->

<!-- BEGIN header_row -->
	<tr class="{row_css_class}" onMouseOver="style.backgroundColor='#dddddd';" onMouseOut="javascript:style.backgroundColor='#FFFFFF';">
<!--		<td class="{row_css_class}" width="20px" align="center">
			<img src="{msg_icon_sm}" border="0" title="">
		</td> -->
		<td width="20px" align="center" valign="top">
			<input  style="width:10px; height:10px" class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="{message_uid}" 
			onclick="toggleFolderRadio(this)" {row_selected}>
		</td>
		<td  style="overflow:hidden; white-space:nowrap;" width="120px"><nobr>
			<a class="{row_css_class}" href="{url_compose}" title="{full_address}">{sender_name}</a>
	<!--		<a href="{url_add_to_addressbook}"><img src="{add_address}"  border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>  -->
		</td>
		<td class="{row_css_class}" width="95px" align="center">
			<nobr><span style="font-size:10px">{date}</span>
		</td>
		<td class="{row_css_class}" width="70px" align="center">
			<nobr><span style="font-size:10px">{state}{row_text}</span>
		</td>
		<td class="{row_css_class}" width="14px" align="center">
			<nobr>{attachments}
		</td>
		<td style="overflow:hidden; white-space:nowrap;"><nobr>
			<a  class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
		</td>
		<td colspan=2 align="right" class="{row_css_class}" width="40px">
			<span style="font-size:10px">{size}</span
		</td>
				
</tr>
<!-- END header_row -->

<!-- BEGIN error_message -->
	<tr>
		<td bgcolor="#FFFFCC" align="center" colspan="6">
			<font color="red"><b>{lang_connection_failed}</b></font><br>
			<br>{message}<br><br>
		</td>
	</tr>
<!-- END error_message -->

<!-- BEGIN quota_block -->
	<table cellpadding="0" cellspacing="0" width="200" style="border : 1px solid silver;">
		<tr valign="middle">
			<td bgcolor="{quotaBG}" align="center" valign="middle" style="width : {leftWidth}%;">
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
