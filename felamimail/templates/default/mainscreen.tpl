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
			//folderFunctions.appendChild(textNode);
			document.getElementById("folderFunction").innerHTML="<b>{lang_move_message}</b> <br><small>{lang_select_target_folder}</small>";
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

<form name=searchForm method=post action="{url_search_settings}">
<TABLE BORDER="0" WIDTH="100%" CELLSPACING="0" CELLPADDING="2">
	<TR bgcolor="{row_off}">
		<TD ALIGN="left" WIDTH="70%" style="border-color:silver; border-style:solid; border-width:0px 0px 1px 0px; font-size:10px;">
			<!-- <a href="{url_compose_empty}">{lang_compose}</a>&nbsp;&nbsp; -->
			{lang_quicksearch}
			<input class="input_text" type="text" size="50" name="quickSearch" value="{quicksearch}"
			onChange="javascript:document.searchForm.submit()" style="font-size:11px;">
		</td>
		<td align='right' width="30%" style="border-color:silver; border-style:solid; border-width:0px 0px 1px 0px; ">
			<a href="{url_filter}"><img src="{new}" alt="{lang_edit_filter}" title="{lang_edit_filter}" border="0"></a>
			<input type=hidden name="changeFilter">
			<select name="filter" onchange="javascript:document.searchForm.submit()" style="border : 1px solid silver; font-size:11px;">
				{filter_options}
			</select>
		</td>
	</tr>
</form>
</table>


<TABLE WIDTH="100%" CELLPADDING="2" CELLSPACING="0" BORDER="0">
		<FORM name="messageList" method="post" action="{url_change_folder}">
		<input type="hidden" name="folderAction" value="changeFolder">
		<noscript>
			<NOBR><SMALL><INPUT TYPE=SUBMIT NAME="moveButton" VALUE="{lang_doit}"></SMALL></NOBR>
		</noscript>
		<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
		<INPUT TYPE=hidden NAME="mailbox">
<!--		
			<td align="LEFT" valign="center" width="5%">
				<TT><SMALL>
				
				<SELECT NAME="mailbox" onChange="document.messageList.submit()" style="border-bottom : 1px solid; font-size:11px;border-left : 0px; border-right : 0px; border-top : 0px;">
					{options_folder}
					</SELECT></SMALL></TT>

				</TD>
				<td nowrap id="folderFunction" align="left" style="font-size:10px;">
					{lang_change_folder}
			</td>
-->
	<TR>
		<TD BGCOLOR="{th_bg}" width="20%">
			<a href="{url_compose_empty}" style="font-size:12px; font-weight:bold;">{lang_compose}</a>
		</td>
		<TD BGCOLOR="{th_bg}" align="center" style="white-space: nowrap;">
			<b>{current_folder}</b>{quota_display}
		</td>
		<TD BGCOLOR="{th_bg}" align="right" width="10%">
			<TABLE BORDER="0" cellpadding="2" cellspacing=0>
				<TR valign="middle" bgcolor="{th_bg}">
					<td width="12px" align="right" valign="center">
						<input type="image" src="{read_small}" name="mark_read" alt="{desc_read}" title="{desc_read}" width="16">
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
						<input type="image" src="{trash}" name="mark_deleted" title="{desc_deleted}">
					</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
	<tr>
		<td align="center" colspan="3" style="font-size:10px">
			&lt;-&nbsp;{link_previous}&nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;{message}&nbsp;]&nbsp;&nbsp;&nbsp;&nbsp;{link_next}&nbsp;-&gt;&nbsp;{trash_link}
		</td>
	</tr>
</table>

<TABLE width="100%" cellpadding="0" cellspacing="0">
	<TR>
		<td valign="top" class="folderlist" width="180">
	
	
			<!-- StartFolderTree -->

			<div id="divFolderTree" style="overflow:auto; width:180px; height:472px; margin-bottom: 0px;padding-left: 4px; padding-top:4px; z-index:100; border : 1px solid Silver;">
			
				<table width=100% BORDER=0 style="table-layout:fixed;padding-left:2;">
				<tr><td><span id="folderFunction" align="left" style="font-size:10px;">&nbsp;</span></td><tr>
	
	
					<tr>
						<td width="160" nowrap style="font-size:10px">
							<nobr>
							{folder_tree}
							<br>
							<br>
							<br>
							<br>
							<p align="center">
							<small><a href="javascript: d.openAll();">open all</a> | <a href="javascript: d.closeAll();">close all</a></small>
							</p>
						</td>
					</tr>
				</table>
			</div>
			
		</td>
		<td width="10" valign="middle">
			<div id="vr" align="center">
				::
			</div>
		</td>
		
<!-- ToDo: ResizeVerticalRule -->		
		
		<TD>

			<!-- Start Header MessageList -->

			<table WIDTH=100% BORDER="0" CELLSPACING="0" style="table-layout:fixed;">
				<tr>
					<td width="22px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center">
					&nbsp;<input style="width:9px; height:9px" type="checkbox" id="messageCheckBox" onclick="selectAll(this)">
					</td>
					<td width="120px" bgcolor="{th_bg}" align="center" class="{css_class_from}">
						&nbsp;<a href="{url_sort_from}">{lang_from}</a>
					</td>
					<td width="95px" bgcolor="{th_bg}" align="center" class="{css_class_date}">
						&nbsp;&nbsp;<a href="{url_sort_date}">{lang_date}</a>
					</td>
					<td width="70px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;&nbsp;&nbsp;Status*
					</td>
					<td width="14px" bgcolor="{th_bg}" align="center" class="text_small">&nbsp;
						
					</td>
					<td bgcolor="{th_bg}" align="center" class="{css_class_subject}">
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
			
			<div id="divMessageList" style="overflow:auto; height:460px; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom: 0px; z-index:90; border : 1px solid Silver;">
				<table BORDER="0" style="width:98%; padding-left:2; table-layout: fixed;">
					{header_rows}
				</table>
			</div>

			<!-- End MessageList -->

		</TD>
	</TR>
</table>


<!-- END main -->

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
		<td class="{row_css_class}" width="20px" align="center">
			<img src="{msg_icon_sm}" border="0" title="">
		</td>
		<td width="20px" align="center" valign="top">
			<input  style="width:9px; height:9px" class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="{message_uid}" 
			onclick="toggleFolderRadio(this)" {row_selected}>
		</td>
		<td width="120px" style="overflow:hidden; white-space:nowrap;">
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
		<td style="overflow:hidden; white-space:nowrap;">
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
