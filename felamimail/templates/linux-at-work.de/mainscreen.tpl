<!-- BEGIN main -->
<div  class="main_body" style="border-width:0px; border-style:solid; vertical-align : bottom;  width : 100% ; height : 15% ; left : 0px ; top :0px ; overflow : auto">
<script language="JavaScript1.2">
<!--

function doLoad()
{
	// the timeout value should be the same as in the "refresh" meta-tag
	// the timeout is in miliseconds
	// setTimeout( "refresh()", {timeout} );
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
<script LANGUAGE="Javascript">
<!--
	var oldColor, oldFontWeight;
	
	function toggleFolderRadio()
	{
		//alert(document.getElementsByTagName("input")[0].checked);
		document.getElementsByTagName("input")[1].checked = "true";
	}

	function parentOn($_i)
	{
		//alert(document.getElementsByName("link_sender")[$_i].title);
		address = eval(document.getElementsByName("link_sender")[$_i]);
		subject = eval(document.getElementsByName("link_subject")[$_i]);
		
		oldColor = address.style.color;
		oldFontWeight = address.style.fontWeight;
		
		address.style.color = "#000000";
		address.style.fontWeight = "bold";
		subject.style.color = "#000000";
		subject.style.fontWeight = "bold";
	}
	
	function parentOff($_i)
	{
		//alert(document.getElementsByName("link_subject")[$_i].title);
		address = eval(document.getElementsByName("link_sender")[$_i]);
		subject = eval(document.getElementsByName("link_subject")[$_i]);
		
		address.style.color = oldColor;
		address.style.fontWeight = oldFontWeight;
		subject.style.color = oldColor;
		subject.style.fontWeight = oldFontWeight;
	}
	
	function mark_read(action)
	{
		document.messageList.mark_read.value = action;
		document.messageList.submit() ;
	}
	function mark_unread(action)
	{
		document.messageList.mark_unread.value = action;
		document.messageList.submit() ;
	}

	function mark_flagged(action)
	{
		document.messageList.mark_flagged.value = action;
		document.messageList.submit() ;
	}

	function mark_unflagged(action)
	{
		document.messageList.mark_unflagged.value = action;
		document.messageList.submit() ;
	}

	function mark_deleted(action)
	{
		document.messageList.mark_deleted.value = action;
		document.messageList.submit() ;
	}

	function change_filter(action)
	{
		document.messageList.changeFilter.value = action;
		document.messageList.submit() ;
	}
//-->
</script>

<center>

<FORM name=messageList method=post action="{url_change_folder}">

<TABLE WIDTH="99%" CELLPADDING="0" CELLSPACING="0" BORDER="0" style="height:50%; vertical-align : bottom;">
	<TR>
		<TD BGCOLOR="{row_off}">
			<TABLE style='background:#f3f3ff;' bBGCOLOR="{row_off}" COLS=2 BORDER="0" cellpadding=0 cellspacing=0 width="100%">
				<TR valign="middle">
					<td nowrap width="100%" align="LEFT" valign="top" bgcolor="#ffffcc" colspan="7" style='font-size:8.0pt; font-family:Arial;'>
						<TT><SMALL>
						<SELECT NAME="mailbox" onChange="document.messageList.submit()">
							{options_folder}
						</SELECT></SMALL></TT>
						<SMALL><INPUT TYPE=radio NAME="folderAction" value="changeFolder" {change_folder_checked}>{lang_change_folder}</SMALL>
						<SMALL><INPUT TYPE=radio NAME="folderAction" value="moveMessage" {move_message_checked}>{lang_move_message}</SMALL>
						<noscript>
							<NOBR><SMALL><INPUT TYPE=SUBMIT NAME="moveButton" VALUE="{lang_doit}"></SMALL></NOBR>

						</noscript>
						<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
					</TD>
					<td bgcolor="#ffffcc" align="right">
						<SMALL>
						<input type=hidden name="changeFilter">
						<select name="filter" onChange="javascript:change_filter('changeFilter')">
						{filter_options}
						</SELECT></SMALL>
					</td>
				</TR>
				<TR BGCOLOR="{row_off}">
					<TD width="30%" colspan="4" ALIGN="left" nowrap style='font-size:8.0pt; font-family:Arial;color:#5A538D;border=0px solid #B0A3D9;'>
						{lang_mark_messages_as}:&nbsp;
					</td>
					<TD width="30%" colspan="4" ALIGN="right" nowrap style='font-size:8.0pt; font-family:Arial;color:#5A538D;border=0px solid #B0A3D9;'>
						<a class="body_link" href="{url_compose_empty}">{lang_compose}</a>&nbsp;&nbsp;
						<a class="body_link" href="{url_filter}">{lang_edit_filter}</a>
					</td>
				</tr>
				<tr>
					<td width="10%" colspan="4" align="left" nowrap style='font-size:8.0pt; font-family:Arial;color:#5A538D;border=0px solid #B0A3D9;'>
						<input type=hidden name="mark_read">
						<input type=hidden name="mark_unread">
						<input type=hidden name="mark_flagged">
						<input type=hidden name="mark_unflagged">
						<a class="body_link" href="javascript:mark_read('mark_read')">{lang_read}</a>/<a class="body_link" href="javascript:mark_unread('mark_unread')">{lang_unread}</a>&nbsp;
						<a class="body_link" href="javascript:mark_flagged('mark_flagged')">{lang_flagged}</a>/<a class="body_link" href="javascript:mark_unflagged('mark_unflagged')">{lang_unflagged}</a>
                                        </td>
					<td width="10%" colspan="4" align="right" nowrap style='font-size:8.0pt; font-family:Arial;color:#5A538D;border=0px solid #B0A3D9;'>
						<input type=hidden name="mark_deleted">
						<a class="body_link" href="javascript:mark_deleted('mark_deleted')">{lang_delete_selected}</a><br>
                                        </td>
				</tr>
			</TABLE>
		</TD>
	</TR>
	{status_row}
</table>
			<table WIDTH=99% BORDER=0 CELLPADDING="0" CELLSPACING="0" style="height:20%; vertical-align : bottom;">
				<tr>
					<td width="3%" bgcolor="#FFFFCC" align="center">
						&nbsp;
					</td>
					<td width="22%" bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_from}"><font color="black">{lang_from}</font></a></b>
					</td>
					<td width="9%" bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_date}"><font color="black">{lang_date}</font></a></b>
					</td>
					<td width="3%" bgcolor="#FFFFCC" align="center">
						&nbsp;
					</td>
					<td bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_subject}"><font color="black">{lang_subject}</font></a></b>
					</td>
					<td width="7%" bgcolor="#FFFFCC" align="center">
						<b>{lang_size}</b>
					</td>
				</tr>
			</table>
</center>
</div>
<div class="main_body" style="position: absolute; border-width:0px; border-style:dotted; width :
100% ; height: 85%; left : 0px ; top : 15% ; overflow : auto">
	<center>
		<table WIDTH=99% CELLPADING="0" CELLSPACING="0" bgcolor="#FFFFFF">
			{header_rows}
		</table>
	</center>
</div>
</FORM>
<!-- END main -->

<!-- BEGIN status_row_tpl -->
	<tr>
		<TD valign="bottom">
			<table WIDTH=100% BORDER=0 CELLPADDING=1 CELLSPACING=0>
				<tr BGCOLOR="#FFFFFF">
					<td width="18%">
						{link_previous} | {link_next}
					</td>
					<td width="18%">
						&nbsp;
					</td>
					<TD align="center" width="28%">
						{message}
					</td>
					<td width="18%">
						{trash_link}
					</td>
					<td align="right" width="18%">
						{select_all_link}
					</td>
				</tr>
			</table>
		</td>
	</tr>

<!-- END status_row_tpl -->

<!-- BEGIN header_row_S -->
<tr>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_S" width="24%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_S" width="9%" bgcolor="#FFFFFF" nowrap align="center" valign="middle">
		{date}
	</td>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_small.png" width="16" border="0" alt="{lang_read}" title="{lang_read}">
	</td>
	<td class="header_row_S" bgcolor="#FFFFFF" valign="middle">
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_S" width="5%" bgcolor="#FFFFFF" valign="middle">
		{size}
	</td>
</tr>
<!-- END header_row_S -->

<!-- BEGIN header_row_RS -->
<tr>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_S" width="24%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_S" width="9%" bgcolor="#FFFFFF" nowrap align="center" valign="middle">
		{date}
	</td>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_small.png" width="16" border="0" alt="{lang_read}" title="{lang_read}">
	</td>
	<td class="header_row_S" bgcolor="#FFFFFF" valign="middle">
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_S" width="5%" bgcolor="#FFFFFF" valign="middle">
		{size}
	</td>
</tr>
<!-- END header_row_RS -->

<!-- BEGIN header_row_ -->
<tr>
	<td class="header_row_" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/unread_small.png" width="16" border="0" alt="{lang_unread}" title="{lang_unread}">
	</td>
	<td class="header_row_" bgcolor="#FFFFFF">
		<a class="header_row_" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_ -->

<!-- BEGIN header_row_F -->
<tr>
	<td class="header_row_F" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_F" width="24%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_F" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_F" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_F" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<img src="{image_path}/unread_flagged_small.png" width="16" border="0" alt="{lang_unread}, {lang_flagged}" title="{lang_unread}, {lang_flagged}">
	</td>
	<td class="header_row_F" bgcolor="#FFFFFF" valign="middle">
		<a class="header_row_F" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_F" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_F -->

<!-- BEGIN header_row_R -->
<tr>
	<td class="header_row_R" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_R" width="24%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_R" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_R" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_R" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<img src="{image_path}/recent_small.gif" width="16" border="0" alt="{lang_recent}" title="{lang_recent}">
	</td>
	<td class="header_row_R" bgcolor="#FFFFFF">
		<a class="header_row_R" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_R" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_R -->

<!-- BEGIN header_row_AS -->
<tr>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_S" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_S" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td class="header_row_S" bgcolor="#FFFFFF">
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_S" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_AS -->

<!-- BEGIN header_row_RAS -->
<tr>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_S" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_S" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td class="header_row_S" bgcolor="#FFFFFF">
		<a class="header_row_S" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_S" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_RAS -->

<!-- BEGIN header_row_A -->
<tr>
	<td class="header_row_" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td class="header_row_" bgcolor="#FFFFFF">
		<a class="header_row_" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_A -->


<!-- BEGIN header_row_ADS -->
<tr>
	<td class="header_row_ADS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_ADS" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_ADS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_ADS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_ADS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_deleted_small.png" width="16" border="0" alt="{lang_replied}, {lang_deleted}" title="{lang_replied}, {lang_deleted}">
	</td>
	<td class="header_row_ADS" bgcolor="#FFFFFF">
		<a class="header_row_ADS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_ADS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_ADS -->

<!-- BEGIN header_row_FS -->
<tr>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_FS" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_FS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_FS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
                 <img src="{image_path}/read_flagged_small.png" width="16" border="0" alt="{lang_read}, {lang_flagged}" title="{lang_read}, {lang_flagged}">
	</td>
	<td class="header_row_FS" bgcolor="#FFFFFF">
		<a class="header_row_FS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_FS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FS -->

<!-- BEGIN header_row_FAS -->
<tr>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_FS" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_FS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_FS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_flagged_small.png" width="16" border="0" alt="{lang_replied}, {lang_flagged}" title="{lang_replied}, {lang_flagged}">
	</td>
	<td class="header_row_FS" bgcolor="#FFFFFF">
		<a class="header_row_FS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_FS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FAS -->

<!-- BEGIN header_row_FA -->
<tr>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_FS" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_F" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_FS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_flagged_small.png" width="16" border="0" alt="{lang_replied}, {lang_flagged}" title="{lang_replied}, {lang_flagged}">
	</td>
	<td class="header_row_FS" bgcolor="#FFFFFF">
		<a class="header_row_F" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_FS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FA -->

<!-- BEGIN header_row_D -->
<tr>
	<td class="header_row_D" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_D" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_D" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_D" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_D" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/unread_deleted_small.png" width="16" border="0" alt="{lang_unread}, {lang_deleted}" title="{lang_unread}, {lang_deleted}">
	</td>
	<td class="header_row_D" bgcolor="#FFFFFF">
		<a class="header_row_D" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_D" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_D -->

<!-- BEGIN header_row_DS -->
<tr>
	<td class="header_row_DS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_DS" width="24%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_DS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_sender" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_DS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_DS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_deleted_small.png" width="16" border="0" alt="{lang_read}, {lang_deleted}" title="{lang_read}, {lang_deleted}">
	</td>
	<td class="header_row_DS" bgcolor="#FFFFFF">
		<a class="header_row_DS" onmouseover="parentOn('{message_counter}')" onmouseout="parentOff('{message_counter}')" name="link_subject" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_DS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_DS -->

