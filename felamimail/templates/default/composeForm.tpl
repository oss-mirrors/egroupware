<!-- BEGIN header -->
<center>
<form method="post" action="{link_action}" ENCTYPE="multipart/form-data">
<table width="98%" border="0" cellspacing="0" cellpading="1">
<tr bgcolor="{th_bg}">
	<td colspan="2">
		{lang_back_to_folder}:&nbsp;<a class="head_link" href="{link_message_list}">{folder_name}</a>
	</td>
	<td align="right">
		<input class="text" type="submit" value="{lang_send}" name="send">
	</td>
</tr>
<tr bgcolor="{bg01}">
	<td width="10%">
		<b>{lang_to}:</b>
	</td>
	<td width="60%">
		<input class="text" type=text size="60" name="to" value='{to}'>
	</td>
	<td align="center">
		&nbsp;<iinput type="submit" value="{lang_addressbook}" name="addressbook">
	</td>
</tr>
<tr bgcolor="{bg02}">
	<td>
		{lang_cc}:
	</td>
	<td colspan="2">
		<input class="text" type=text size="60" name="cc" value='{cc}'>
	</td>
</tr>
<tr bgcolor="{bg01}">
	<td>
		{lang_bcc}:
	</td>
	<td colspan="2">
		<input class="text" type=text size="60" name="bcc" value='{bcc}'>
	</td>
</tr>
<tr bgcolor="{bg02}">
	<td>
		{lang_reply_to}:
	</td>
	<td colspan="2">
		<input class="text" type=text size="60" name="reply_to" value='{reply_to}'>
	</td>
</tr>
<tr bgcolor="{bg01}">
	<td>
		<b>{lang_subject}:</b>
	</td>
	<td>
		<input class="text" type=text size="60" name="subject" value='{subject}'>
	</td>
	<td align="right">
		{lang_priority}
		<select name="priority">
			<option value="5">{lang_high}</option>
			<option value="3" selected>{lang_normal}</option>
			<option value="1">{lang_low}</option>
		</select>
	</td>
</tr>
</table>
<!-- END header -->

<!-- BEGIN body_input -->
<table width="98%" border="0" cellspacing="0" cellpading="0">
<tr bgcolor="{bg02}">
	<td colspan="2">
		&nbsp;<br>
	</td>
</tr>
<tr bgcolor="{bg02}">
	<td width="10%">
		&nbsp;
	</td>
	<td align="left">
		<TEXTAREA class="text" NAME=body ROWS=20 COLS="76" WRAP=HARD>{body}</TEXTAREA>
	</td>
</tr>
<tr bgcolor="{bg02}">
	<td width="10%" valign="top">
		{lang_signature}
	</td>
	<td align="left">
		<TEXTAREA class="text" NAME=signature ROWS=5 COLS="76" WRAP=HARD>{signature}</TEXTAREA>
	</td>
</tr>
<tr bgcolor="{bg02}">
	<td colspan="2">
		&nbsp;<br>
	</td>
</tr>
</table>
<!-- END body_input -->

<!-- BEGIN attachment -->
<br>
<table width="95%" border="0" cellspacing="0" cellpading="0">
<tr bgcolor="{th_bg}">
	<td>
		<b>{lang_attachments}</b>
	</td>
	<td width="80%" align="center">
		<INPUT class="text" NAME="attachfile" SIZE=48 TYPE="file">
	</td>
	<td align="left" width="20%">
		<input class="text" type="submit" name="addfile" value="{lang_add}">
	</td>
	<td>
		<input class="text" type="submit" value="{lang_send}" name="send">
	</td>
</tr>
</table>
<br>
<table width="95%" border="0" cellspacing="1" cellpading="0">
{attachment_rows}
</table>

</form>
</center>
<!-- END attachment -->

<!-- BEGIN attachment_row -->
<tr bgcolor="{row_color}">
	<td>
		{name}
	</td>
	<td>
		{type}
	</td>
	<td>
		{size}
	</td>
	<td align="center">
		<input type="checkbox" name="attachment[{attachment_number}]" value="{lang_remove}">
	</td>
</tr>
<!-- END attachment_row -->

<!-- BEGIN attachment_row_bold -->
<tr bgcolor="{th_bg}">
	<td>
		<b>{name}</b>
	</td>
	<td>
		<b>{type}</b>
	</td>
	<td>
		<b>{size}</b>
	</td>
	<td align="center">
		<input type="submit" name="removefile" value="{lang_remove}">
	</td>
</tr>
<!-- END attachment_row_bold -->
