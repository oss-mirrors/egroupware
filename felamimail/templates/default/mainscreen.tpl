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
	function toggleFolderRadio()
	{
		//alert(document.getElementsByTagName("input")[0].checked);
		document.getElementsByTagName("input")[1].checked = "true";
	}
//-->
</script>
<TABLE BORDER=0 WIDTH="100%" CELLSPACING=0 CELLPADDING=2>
	<TR BGCOLOR="{row_off}">
		<TD ALIGN="left" WIDTH="40%">
			<a href="{url_compose_empty}">{lang_compose}</a>&nbsp;&nbsp;
			<a href="{url_filter}">{lang_edit_filter}</a>&nbsp;&nbsp;
			<a href="{url_status_filter}">{lang_status_filter}</a>
		</td>
		<td align="right" width="60%">
			&nbsp;
		</td>
	</tr>
</table>

<TABLE WIDTH="100%" BORDER="0" CELLPADDING="0" CELLSPACING="0">
	<FORM name=messageList method=post action="{url_change_folder}">
	<colgroup>
		<col width="100%">
	</colgroup>
	<TR>
		<TD BGCOLOR="{row_off}">
			<TABLE BGCOLOR="{row_off}" COLS=2 BORDER=0 cellpadding=0 cellspacing=0 width="100%">
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
					<td width="2%" align="LEFT" valign="center">
						<input type="image" src="{image_path}/read_small.png" name="mark_read" alt="{desc_read}" title="{desc_read}" width="16">
                                        </td>
                                        <TD WIDTH="2%" ALIGN="MIDDLE" valign="center">
                                                &nbsp;|&nbsp;
                                        </td>
                                        <td width="2%" align="RIGHT" valign="center">
						<input type="image" src="{image_path}/unread_small.png" name="mark_unread" title="{desc_unread}" width="16">&nbsp;&nbsp;
                                        </td>
                                        <td width="2%" align="LEFT" valign="center">
						<input type="image" src="{image_path}/unread_flagged_small.png" name="mark_flagged" title="{desc_important}" width="16">
                                        </td>
                                        <TD WIDTH="2%" ALIGN="MIDDLE" valign="center">
                                                &nbsp;|&nbsp;
                                        </td>
                                        <td width="2%" align="RIGHT" valign="center">
						<input type="image" src="{image_path}/unread_small.png" name="mark_unflagged" title="{desc_unimportant}">&nbsp;&nbsp;
                                        </td>
                                        <td width="2%" align="RIGHT" valign="center">
						<input type="image" src="{image_path}/unread_deleted_small.png" name="mark_deleted" title="{desc_deleted}">
					</TD>
				</TR>
			</TABLE>
			<br>
		</TD>
	</TR>
	{status_row}
	<TR>
		<TD>
			<table WIDTH=100% BORDER=0 CELLPADDING=1 CELLSPACING=1>
				<colgroup>
					<col width="1%">
					<col width="10%">
					<col width="10%">
					<col width="1%">
					<col width="70%">
					<col width="8%">
				</colgroup>
				<tr>
					<td width="1%" bgcolor="#FFFFCC" align="center">
						&nbsp;
					</td>
					<td width="20%" bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_from}"><font color="black">{lang_from}</font></a></b>
					</td>
					<td bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_date}"><font color="black">{lang_date}</font></a></b>
					</td>
					<td bgcolor="#FFFFCC" align="center">
						&nbsp;
					</td>
					<td bgcolor="#FFFFCC" align="center">
						<b><a href="{url_sort_subject}"><font color="black">{lang_subject}</font></a
					</td>
					<td bgcolor="#FFFFCC" align="center">
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
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_small.png" width="16" border="0" alt="{lang_read}" title="{lang_read}">
	</td>
	<td bgcolor="#FFFFFF">
		<a name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_S -->

<!-- BEGIN header_row_RS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_small.png" width="16" border="0" alt="{lang_read}" title="{lang_read}">
	</td>
	<td bgcolor="#FFFFFF">
		<a name="subject_url" href="{url_read_message}">{header_subject}</a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_RS -->

<!-- BEGIN header_row_ -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<b><a href="{url_compose}" title="{full_address}">{sender_name}</a></b>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/unread_small.png" width="16" border="0" alt="{lang_unread}" title="{lang_unread}">
	</td>
	<td bgcolor="#FFFFFF">
		<b><a href="{url_read_message}">{header_subject}</a></b>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_ -->

<!-- BEGIN header_row_F -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<b><a href="{url_compose}" title="{full_address}"><font color="red">{sender_name}</font></a></b>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF">
		<img src="{image_path}/unread_flagged_small.png" width="16" border="0" alt="{lang_unread}, {lang_flagged}" title="{lang_unread}, {lang_flagged}">
	</td>
	<td bgcolor="#FFFFFF">
		<b><a href="{url_read_message}"><font color="red">{header_subject}</font></a></b>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_F -->

<!-- BEGIN header_row_R -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<b><a href="{url_compose}" title="{full_address}">{sender_name}</a></b>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF">
		<img src="{image_path}/recent_small.gif" width="16" border="0" alt="{lang_recent}" title="{lang_recent}">
	</td>
	<td bgcolor="#FFFFFF">
		<b><a href="{url_read_message}">{header_subject}</a></b>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_R -->

<!-- BEGIN header_row_AS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td bgcolor="#FFFFFF">
		<a href="{url_read_message}">{header_subject}</a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_AS -->

<!-- BEGIN header_row_RAS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}">{sender_name}</a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td bgcolor="#FFFFFF">
		<a href="{url_read_message}">{header_subject}</a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_RAS -->

<!-- BEGIN header_row_A -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<b><a href="{url_compose}" title="{full_address}">{sender_name}</a></b>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}" title="{lang_replied}">
	</td>
	<td bgcolor="#FFFFFF">
		<b><a href="{url_read_message}">{header_subject}</a></b>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_A -->

<!-- BEGIN header_row_ADS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}"><font color="#CCCCCC">{sender_name}</font></a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		<font color="#CCCCCC">{date}</font>
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_deleted_small.png" width="16" border="0" alt="{lang_replied}, {lang_deleted}" title="{lang_replied}, {lang_deleted}">
	</td>
	<td bgcolor="#FFFFFF">
		<a href="{url_read_message}"><font color="#CCCCCC">{header_subject}</font></a>
	</td>
	<td bgcolor="#FFFFFF">
		<font color="#CCCCCC">{size}</font>
	</td>
</tr>
<!-- END header_row_ADS -->

<!-- BEGIN header_row_FS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}"><font color="red">{sender_name}</font></a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF">
                 <img src="{image_path}/read_flagged_small.png" width="16" border="0" alt="{lang_read}, {lang_flagged}" title="{lang_read}, {lang_flagged}">
	</td>
	<td bgcolor="#FFFFFF">
		<a href="{url_read_message}"><font color="red">{header_subject}</font></a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FS -->

<!-- BEGIN header_row_FAS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}"><font color="red">{sender_name}</font></a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}, {lang_flagged}" title="{lang_replied}, {lang_flagged}">
	</td>
	<td bgcolor="#FFFFFF">
		<a href="{url_read_message}"><font color="red">{header_subject}</font></a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FAS -->

<!-- BEGIN header_row_FA -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}"><font color="red">{sender_name}</font></a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		{date}
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_answered_small.png" width="16" border="0" alt="{lang_replied}, {lang_flagged}" title="{lang_replied}, {lang_flagged}">
	</td>
	<td bgcolor="#FFFFFF">
		<a href="{url_read_message}"><font color="red">{header_subject}</font></a>
	</td>
	<td bgcolor="#FFFFFF">
		{size}
	</td>
</tr>
<!-- END header_row_FA -->

<!-- BEGIN header_row_D -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<b><a href="{url_compose}" title="{full_address}"><font color="#CCCCCC">{sender_name}</font></a></b>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		<font color="#CCCCCC">{date}</font>
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/unread_deleted_small.png" width="16" border="0" alt="{lang_unread}, {lang_deleted}" title="{lang_unread}, {lang_deleted}">
	</td>
	<td bgcolor="#FFFFFF">
		<b><a name="subject_url" href="{url_read_message}"><font color="#CCCCCC">{header_subject}</font></a></b>
	</td>
	<td bgcolor="#FFFFFF">
		<font color="#CCCCCC">{size}</font>
	</td>
</tr>
<!-- END header_row_D -->

<!-- BEGIN header_row_DS -->
<tr>
	<td width="1%" bgcolor="#FFFFFF" align="center">
		<input type="checkbox" name="msg[{message_counter}]" value="{message_uid}" onClick="toggleFolderRadio()" {row_selected}>
	</td>
	<td width="10%" bgcolor="#FFFFFF" nowrap>
		<a href="{url_compose}" title="{full_address}"><font color="#CCCCCC">{sender_name}</font></a>
		<a href="{url_add_to_addressbook}"><img src="{phpgw_images}/sm_envelope.gif" width="10" height="8" border="0" align="absmiddle" alt="{lang_add_to_addressbook}" title="{lang_add_to_addressbook}"></a>
	</td>
	<td bgcolor="#FFFFFF" nowrap align="center">
		<font color="#CCCCCC">{date}</font>
	</td>
	<td bgcolor="#FFFFFF" valign="middle" align="center">
		<img src="{image_path}/read_deleted_small.png" width="16" border="0" alt="{lang_read}, {lang_deleted}" title="{lang_read}, {lang_deleted}">
	</td>
	<td bgcolor="#FFFFFF">
		<a name="subject_url" href="{url_read_message}"><font color="#CCCCCC">{header_subject}</font></a>
	</td>
	<td bgcolor="#FFFFFF">
		<font color="#CCCCCC">{size}</font>
	</td>
</tr>
<!-- END header_row_DS -->

<!-- BEGIN error_message -->
	<tr>
		<td bgcolor="#FFFFCC" align="center" colspan="6">
			<font color="red"><b>{lang_connection_failed}</b></font><br>
			{message}
		</td>
	</tr>
<!-- END error_message -->