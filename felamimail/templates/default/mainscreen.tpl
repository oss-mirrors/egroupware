<!-- BEGIN main -->
<script language="JavaScript1.2">
<!--
var sURL = unescape(window.location.pathname);

// some translations needed for javascript functions

var movingMessages		= '{lang_moving_messages_to}';
var msg_emptyTrashFolder	= '{lang_empty_trash}';
var msg_compressingFolder	= '{lang_compress_folder}';
var lang_select_target_folder	= '{lang_select_target_folder}';

// how many row are selected currently
var checkedCounter=0;

// the refreshtimer object
var aktiv;

// refresh time for mailboxview
var refreshTimeOut = {refreshTime};

function doLoad()
{
	// the timeout value should be the same as in the "refresh" meta-tag
	if(refreshTimeOut > 0)
	{
		aktiv = window.setInterval("refresh()", refreshTimeOut);
	}
}

doLoad();

//-->
</script>

<TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" style="border: solid #aaaaaa 1px; border-right: solid black 1px; border-bottom: solid black 1px;">
	<tr class="navbarBackground">
		<td align="right" width="170px">
			<div class="parentDIV">
				{navbarButtonsLeft}
			</div>
		</td>
		<td align="right" width="200px">
			<img src="{mail_find}" border="0" name="{lang_quicksearch}" alt="{lang_quicksearch}" title="{lang_quicksearch}" width="16" onClick="javascript:document.searchForm.submit()">
			<input class="input_text" type="text" size="25" name="quickSearch" id="quickSearch" value="{quicksearch}" onChange="javascript:quickSearch(this.value);" onFocus="this.select();" style="font-size:11px;">
		</td>
		<td align="left" width="210px">
			&nbsp;
			<a href="{url_filter}"><img src="{new}" alt="{lang_edit_filter}" title="{lang_edit_filter}" border="0"></a>&nbsp;
			<select name="filter" id="filter" onchange="javascript:extendedSearch(this)" style="border : 1px solid silver; font-size:11px; width: 170px;">
				{filter_options}
			</select>
		</td>
		<td align="center" style="white-space: nowrap;">
			{quota_display}
		</td>
		<td width="105px" align="right" style="white-space: nowrap;">
			<div class="parentDIV">
				{navbarButtonsRight}
			</div>
		</td>
	</TR>
</table>

<TABLE  width="100%" cellpadding="0" cellspacing="0" border="0" style="height:100px;">
		<input type="hidden" name="folderAction" id="folderAction" value="changeFolder">
		<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
		<INPUT TYPE=hidden NAME="mailbox">

	<tr style="height: 20px;">
		<td>
			<span id="folderFunction" align="left" style="font-size:11px;">&nbsp;</span>	
		</td>
		<td>
			&nbsp;
		</td>
		<td align="center" style="font-size:11px;" colspan="2">
			<span id="messageCounter">{message}</span>
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
						&nbsp;<a href="javascript:changeSorting('from');"><span id='from_or_to'>{lang_from}</span></a>
					</td>
					<td width="95px" bgcolor="{th_bg}" align="center" class="{css_class_date}">
						&nbsp;&nbsp;<a href="javascript:changeSorting('date');">{lang_date}</a>
					</td>
					<td width="70px" bgcolor="{th_bg}" align="center" class="text_small">
						{lang_status}
					</td>
					<td width="14px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td bgcolor="{th_bg}" align="left" class="{css_class_subject}">
						&nbsp;&nbsp;&nbsp;<a href="javascript:changeSorting('subject');">{lang_subject}</a>
					</td>
					<td width="40px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						<a href="javascript:changeSorting('size');">{lang_size}</a>&nbsp;
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
			<a class="{row_css_class}" href="#" onclick="{url_compose} return false;" title="{full_address}">{sender_name}</a>
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
			<a  class="{row_css_class}" name="subject_url" href="#" onclick="{url_read_message} return false;" title="{full_subject}">{header_subject}</a>
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
	<table cellpadding="0" cellspacing="0" width="100%" style="border : 1px solid silver; max-width:200px;">
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
