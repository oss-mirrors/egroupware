<!-- BEGIN main -->
<script language="JavaScript1.2">
var sURL = unescape(window.location.pathname);

// some translations needed for javascript functions

var movingMessages		= '{lang_moving_messages_to}';
var lang_emptyTrashFolder	= '{lang_empty_trash}';
var lang_compressingFolder	= '{lang_compress_folder}';
var lang_select_target_folder	= '{lang_select_target_folder}';
var lang_updating_message_status = '{lang_updating_message_status}';
var lang_loading 		= '{lang_loading}';
var lang_deleting_messages 	= '{lang_deleting_messages}';

// how many row are selected currently
var checkedCounter=0;

// the refreshtimer objects
var aktiv;
var fm_timerFolderStatus;

// refresh time for mailboxview
var refreshTimeOut = {refreshTime};
//refreshTimeOut = 105001;
//document.title=refreshTimeOut;

fm_startTimerFolderStatusUpdate(refreshTimeOut);
fm_startTimerMessageListUpdate(refreshTimeOut);

</script>

<TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" bborder="1" style="border: solid #aaaaaa 1px; border-right: solid black 1px; border-bottom: solid black 1px;">
	<tr class="navbarBackground">
		<td align="right" width="180px">
			<div class="parentDIV">
				{navbarButtonsLeft}
			</div>
		</td>
		<td align="right" width="10px">
			{lang_search}:
		</td>
		<td align="left" wwidth="100px">
			<input class="input_text" type="text" name="quickSearch" id="quickSearch" value="{quicksearch}" onChange="javascript:quickSearch(this.value);" onFocus="this.select();" style="font-size:11px; width:98%; max-width:300px;">
		</td>
		<td align="left" width="10px" valign="bottom">
			<a href="{url_filter}" valign="bottom"><img src="{new}" alt="{lang_edit_filter}" title="{lang_edit_filter}" border="0"></a>&nbsp;
		</td>
		<td align="center" width="140px">
			<select name="filter" id="filter" onchange="javascript:extendedSearch(this)" style="border : 1px solid silver; font-size:11px; width: 130px;">
				{filter_options}
			</select>
		</td>
		<td width="110px" style="white-space:nowrap; align:right; text-align:right;">
			<div class="parentDIV" style="text-align:right; align:right;">
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
		<td align="left" style="font-size:11px;">
			<span id="messageCounter">{message}</span>
		</td>
		<td align="right" style="font-size:11px; width:250px;">
			{quota_display}
		</td>
	</tr>
	<TR>
		<td valign="top" class="folderlist" width="180">
	
			<!-- StartFolderTree -->

			<div id="divFolderTree" style="overflow:auto; width:180px; height:474px; margin-bottom: 0px;padding-left: 0px; padding-top:0px; z-index:100; border : 1px solid Silver;">
			</div>
			{folder_tree}
			<script language="JavaScript1.2">refreshFolderStatus();</script>
		</td>
		<td width="10" valign="middle">
			<div id="vr" align="center">
				::
			</div>
		</td>
		
		<!-- ToDo: ResizeVerticalRule -->		
		
		<TD valign="top" colspan="2">

			<!-- Start Header MessageList -->

			{messageListTableHeader}

			<!-- End Header MessageList -->			


			<!-- Start MessageList -->

			<form id="formMessageList">			
			<div id="divMessageList" style="overflow:auto; height:460px; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom: 0px; z-index:90; border : 1px solid Silver;">
				<!-- <table BORDER="0" style="width:98%; ppadding-left:2; table-layout: fixed;" cellspacing="100" cellpadding="100"> -->
					{header_rows}
				<!-- </table> -->
			</div>
			</form>

			<!-- End MessageList -->

		</TD>
	</TR>
</table>


<!-- END main -->

<!-- BEGIN message_table -->
<table BORDER="0" style="width:98%; padding-left:2; table-layout: fixed;" cellspacing="0">
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

<!-- BEGIN header_row_felamimail -->
	<tr class="{row_css_class}" onMouseOver="style.backgroundColor='#dddddd';" onMouseOut="javascript:style.backgroundColor='#FFFFFF';">
		<td class="mainscreenRow" width="20px" align="left" valign="top">
			<input  style="width:12px; height:12px; border: none; margin: 1px;" class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="{message_uid}" 
			onclick="toggleFolderRadio(this, refreshTimeOut)" {row_selected}>
		</td>
		<td class="mainscreenRow" width="20px" align="center">
			<img src="{image_url}">
		</td>
		<td class="mainscreenRow" width="20px" align="center">
			<img src="{attachment_image_url}" border="0" style="width:12px;>
		</td>
		<td class="mainscreenRow" style="overflow:hidden; white-space:nowrap;"><nobr>
			<a class="{row_css_class}" name="subject_url" href="#" onclick="fm_readMessage('{url_read_message}', '{read_message_windowName}', this); return false;" title="{full_subject}">{header_subject}</a>
		</td>
		<td class="mainscreenRow" width="95px" align="center">
			<nobr><span style="font-size:10px">{date}</span>
		</td>
		<td class="mainscreenRow" style="overflow:hidden; white-space:nowrap;" width="120px"><nobr>
			<a class="{row_css_class}" href="#" onclick="{url_compose} return false;" title="{full_address}">{sender_name}</a>
		</td>
		<td colspan=2 align="right" class="mainscreenRow" width="40px">
			<span style="font-size:10px">{size}</span
		</td>
				
</tr>
<!-- END header_row_felamimail -->

<!-- BEGIN header_row_outlook -->
	<tr class="{row_css_class}" onMouseOver="style.backgroundColor='#dddddd';" onMouseOut="javascript:style.backgroundColor='#FFFFFF';" >
		<td class="mainscreenRow" width="20px" align="left" valign="top">
			<input  style="width:12px; height:12px; border: none; margin: 1px;" class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="{message_uid}" 
			onclick="toggleFolderRadio(this, refreshTimeOut)" {row_selected}>
		</td>
		<td class="mainscreenRow" width="20px" align="center">
			<img src="{image_url}">
		</td>
		<td class="mainscreenRow" width="20px" align="center">
			{attachment_image}
		</td>
		<td class="mainscreenRow" style="overflow:hidden; white-space:nowrap;" width="120px"><nobr>
			<a class="{row_css_class}" href="#" onclick="{url_compose} return false;" title="{full_address}">{sender_name}</a>
		</td>
		<td class="mainscreenRow" style="overflow:hidden; white-space:nowrap;"><nobr>
			<a class="{row_css_class}" name="subject_url" href="#" onclick="fm_readMessage('{url_read_message}', '{read_message_windowName}', this); parentNode.parentNode.parentNode.style.fontWeight='normal'; return false;" title="{full_subject}">{header_subject}</a>
		</td>
		<td class="mainscreenRow" width="95px" align="center">
			<nobr><span style="font-size:10px">{date}</span>
		</td>
		<td colspan=2 align="right" class="mainscreenRow" width="40px">
			<span style="font-size:10px">{size}</span
		</td>
				
</tr>
<!-- END header_row_outlook -->

<!-- BEGIN error_message -->
	<tr>
		<td bgcolor="#FFFFCC" align="center" colspan="6">
			<font color="red"><b>{lang_connection_failed}</b></font><br>
			<br>{message}<br><br>
		</td>
	</tr>
<!-- END error_message -->

<!-- BEGIN quota_block -->
	<table cellpadding="0" cellspacing="0" style="border:1px solid silver;width:150px;height:4px;font-size:4px;">
		<tr valign="middle">
			<td bgcolor="{quotaBG}" align="center" valign="middle" style="width:{leftWidth}%;height:4px;font-size:4px;">
				&nbsp;{quotaUsage_left}
			</td>
			<td align="center" valign="middle" style="height:4px;font-size:4px;">
				&nbsp;{quotaUsage_right}
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

<!-- BEGIN table_header_felamimail -->
			<table WIDTH=100% BORDER="0" CELLSPACING="0" style="table-layout:fixed;">
				<tr class="th">
					<td width="20px" align="left">
						<input style="width:12px; height:12px; border:none; margin: 1px; margin-left: 3px;" type="checkbox" id="messageCheckBox" onclick="selectAll(this, refreshTimeOut)">
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td bgcolor="{th_bg}" align="center" class="{css_class_subject}">
						<a href="javascript:changeSorting('subject');">{lang_subject}</a>
					</td>
					<td width="95px" bgcolor="{th_bg}" align="center" class="{css_class_date}">
						&nbsp;&nbsp;<a href="javascript:changeSorting('date');">{lang_date}</a>
					</td>
					<td width="120px" bgcolor="{th_bg}" align="center" class="{css_class_from}">
						&nbsp;<a href="javascript:changeSorting('from');"><span id='from_or_to'>{lang_from}</span></a>
					</td>
					<td width="40px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						<a href="javascript:changeSorting('size');">{lang_size}</a>&nbsp;
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						&nbsp;
					</td>
				</tr>
			</table>
<!-- END table_header_felamimail -->

<!-- BEGIN table_header_outlook -->
			<table WIDTH=100% BORDER="0" CELLSPACING="0" style="table-layout:fixed;">
				<tr class="th">
					<td width="20px" align="left">
						<input style="width:12px; height:12px; border:none; margin: 1px; margin-left: 3px;" type="checkbox" id="messageCheckBox" onclick="selectAll(this, refreshTimeOut)">
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="text_small">
						&nbsp;
					</td>
					<td width="120px" bgcolor="{th_bg}" align="center" class="{css_class_from}">
						&nbsp;<a href="javascript:changeSorting('from');"><span id='from_or_to'>{lang_from}</span></a>
					</td>
					<td bgcolor="{th_bg}" align="center" class="{css_class_subject}">
						<a href="javascript:changeSorting('subject');">{lang_subject}</a>
					</td>
					<td width="95px" bgcolor="{th_bg}" align="center" class="{css_class_date}">
						&nbsp;&nbsp;<a href="javascript:changeSorting('date');">{lang_date}</a>
					</td>
					<td width="40px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						<a href="javascript:changeSorting('size');">{lang_size}</a>&nbsp;
					</td>
					<td width="20px" bgcolor="{th_bg}" align="center" class="{css_class_size}">
						&nbsp;
					</td>
				</tr>
			</table>
<!-- END table_header_outlook -->
