<!-- BEGIN main -->
<div  class="main_body" style="border-width:0px; border-style:solid; vertical-align : bottom;  width : 100% ; height : 15% ; left : 0px ; top :0px ; overflow : auto">
<script type="text/javascript">
<!--
	function toggleFolderRadio()
	{
		//alert(document.getElementsByTagName("input")[0].checked);
		document.getElementsByTagName("input")[1].checked = "true";
	}
//-->
</script>
<center>
<TABLE WIDTH="99%" CELLSPACING="0" CELLPADDING="0" BORDER="0" style="height:30%; "> 
	<TR BGCOLOR="{row_off}">
		<TD ALIGN="left" WIDTH="30%">
			<a class="head_link" href="{url_compose_empty}">{lang_compose}</a>&nbsp;&nbsp;
			<a class="head_link" href="{url_search}">{lang_search}</a>
		</td>
		<td align="right" width="70%">
			&nbsp;
		</td>
	</tr>
</table>

<TABLE WIDTH="99%" CELLPADDING="0" CELLSPACING="0" BORDER="0" style="height:50%; vertical-align : bottom;">
	<FORM name=messageList method=post action="{url_change_folder}">
	<TR>
		<TD BGCOLOR="{row_off}">
			<TABLE BGCOLOR="{row_off}" COLS=2 BORDER="0" cellpadding=0 cellspacing=0 width="100%">
				<TR valign="middle">
					<td nowrap width="40%" align="LEFT" valign="center" bgcolor="#ffffcc">
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
                                        <td width="50%">
                                                &nbsp;
                                        </td>
					<td width="2%" align="LEFT" valign="bottom" nowrap>
						<input type="image" src="{image_path}/read_small.png" name="mark_read" alt="{desc_read}" title="{desc_read}" width="16">
                                                |
						<input type="image" src="{image_path}/unread_small.png" name="mark_unread" title="{desc_unread}" width="16">
                                        </td>
					<td nowrap>
						&nbsp;&nbsp;
                                        <td width="2%" align="LEFT" valign="bottom" nowrap>
						<input type="image" src="{image_path}/unread_flagged_small.png" name="mark_flagged" title="{desc_important}" width="16">
                                                |
						<input type="image" src="{image_path}/unread_small.png" name="mark_unflagged" title="{desc_unimportant}">
					<td nowrap>
						&nbsp&nbsp;
					</td>
                                        </td>
                                        <td width="2%" align="RIGHT" valign="bottom" nowrap>
						<input type="image" src="{image_path}/unread_deleted_small.png" name="mark_deleted" title="{desc_deleted}">
						|
					</TD>
				</TR>
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
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_S" width="22%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_S" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_S" width="9%" bgcolor="#FFFFFF" nowrap align="center" valign="middle">
		{date}
	</td>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_small.png" width="16" border="0" alt="{lang_read}" title="{lang_read}">
	</td>
	<td class="header_row_S" bgcolor="#FFFFFF" valign="middle">
		<a class="header_row_S" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_S" width="5%" bgcolor="#FFFFFF" valign="middle">
		{size}
	</td>
</tr>
<!-- END header_row_S -->

<!-- BEGIN header_row_ -->
<tr>
	<td class="header_row_" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/unread_small.png" width="16" border="0" alt="{lang_unread}" title="{lang_unread}">
	</td>
	<td class="header_row_" bgcolor="#FFFFFF">
		<a class="header_row_" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_ -->

<!-- BEGIN header_row_F -->
<tr>
	<td class="header_row_F" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_F" width="22%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_F" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_F" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_F" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<img src="{image_path}/unread_flagged_small.png" width="16" border="0" alt="{lang_unread}, {lang_flagged}" title="{lang_unread}, {lang_flagged}">
	</td>
	<td class="header_row_F" bgcolor="#FFFFFF" valign="middle">
		<a class="header_row_F" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_F" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_F -->

<!-- BEGIN header_row_R -->
<tr>
	<td class="header_row_R" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_R" width="22%" bgcolor="#FFFFFF" nowrap valign="middle">
		<a class="header_row_R" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_R" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_R" width="3%" bgcolor="#FFFFFF" align="center" valign="middle">
		<img src="{image_path}/recent_small.gif" width="16" border="0" alt="{lang_recent}" title="{lang_recent}">
	</td>
	<td class="header_row_R" bgcolor="#FFFFFF">
		<a class="header_row_R" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_R" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_R -->

<!-- BEGIN header_row_AS -->
<tr>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_S" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_S" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_S" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_S" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td class="header_row_S" bgcolor="#FFFFFF">
		<a class="header_row_S" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_S" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_AS -->

<!-- BEGIN header_row_ADS -->
<tr>
	<td class="header_row_ADS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_ADS" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_ADS" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_ADS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_ADS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_deleted_small.png" width="16" border="0" alt="{lang_replied}, {lang_deleted}" title="{lang_replied}, {lang_deleted}">
	</td>
	<td class="header_row_ADS" bgcolor="#FFFFFF">
		<a class="header_row_ADS" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_ADS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_ADS -->

<!-- BEGIN header_row_FS -->
<tr>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_FS" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_FS" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_FS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
                 <img src="{image_path}/read_flagged_small.png" width="16" border="0" alt="{lang_read}, {lang_flagged}" title="{lang_read}, {lang_flagged}">
	</td>
	<td class="header_row_FS" bgcolor="#FFFFFF">
		<a class="header_row_FS" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_FS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FS -->

<!-- BEGIN header_row_FAS -->
<tr>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_FS" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_FS" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_FS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_FS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_flagged_small.png" width="16" border="0" alt="{lang_replied}, {lang_flagged}" title="{lang_replied}, {lang_flagged}">
	</td>
	<td class="header_row_FS" bgcolor="#FFFFFF">
		<a class="header_row_FS" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_FS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FAS -->

<!-- BEGIN header_row_D -->
<tr>
	<td class="header_row_D" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_D" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_D" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_D" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_D" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/unread_deleted_small.png" width="16" border="0" alt="{lang_unread}, {lang_deleted}" title="{lang_unread}, {lang_deleted}">
	</td>
	<td class="header_row_D" bgcolor="#FFFFFF">
		<a class="header_row_D" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_D" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_D -->

<!-- BEGIN header_row_DS -->
<tr>
	<td class="header_row_DS" width="3%" bgcolor="#FFFFFF" align="center">
		<input class="header_row" type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td class="header_row_DS" width="22%" bgcolor="#FFFFFF" nowrap>
		<a class="header_row_DS" href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td class="header_row_DS" width="9%" bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td class="header_row_DS" width="3%" bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_deleted_small.png" width="16" border="0" alt="{lang_read}, {lang_deleted}" title="{lang_read}, {lang_deleted}">
	</td>
	<td class="header_row_DS" bgcolor="#FFFFFF">
		<a class="header_row_DS" name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td class="header_row_DS" width="5%" bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_DS -->

