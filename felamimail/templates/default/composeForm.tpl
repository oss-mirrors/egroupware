<!-- BEGIN header -->
<script language="JavaScript1.2">
  self.focus();
  
  self.name="first_Window";
  function addybook()
  {
	Window1=window.open('{link_addressbook}',"{lang_search}","width=800,height=480,toolbar=no,scrollbars=yes,resizable=yes");
  }
  function attach_window(url)
  {
	awin = window.open(url,"attach","width=500,height=400,toolbar=no,resizable=yes");
  }
</script>

<center>
<form method="post" name="doit" action="{link_action}" ENCTYPE="multipart/form-data">
<table width="98%" border="0" cellspacing="0" cellpading="1">
<tr bgcolor="{th_bg}">
	<td colspan="2">
		{lang_back_to_folder}:&nbsp;<a class="head_link" href="{link_message_list}">{folder_name}</a>
	</td>
	<td align="right">
		<input class="input_text" type="submit" value="{lang_send}" name="send">
	</td>
</tr>
<tr>
	<td class="body" width="10%">
		<b>{lang_from}:</b>
	</td>
	<td class="body" align="left" width="60%">
		{from}
	</td>
	<td class="body" align="right">
		<input class="input_text" type="button" value="{lang_addressbook}" onclick="addybook();">
	</td>
</tr>
<tr>
	<td width="10%">
		<b>{lang_to}:</b>
	</td>
	<td width="60%" colspan="2">
		<input class="input_text" type=text size="76" name="to" value="{to}">
	</td>
</tr>
<tr>
	<td>
		{lang_cc}:
	</td>
	<td colspan="2">
		<input class="input_text" type=text size="76" name="cc" value='{cc}'>
	</td>
</tr>
<tr>
	<td>
		{lang_bcc}:
	</td>
	<td colspan="2">
		<input class="input_text" type=text size="76" name="bcc" value='{bcc}'>
	</td>
</tr>
<tr>
	<td>
		{lang_reply_to}:
	</td>
	<td colspan="2">
		<input class="input_text" type=text size="76" name="reply_to" value='{reply_to}'>
	</td>
</tr>
<tr>
	<td>
		<b>{lang_subject}:</b>
	</td>
	<td>
		<input class="input_text" type=text size="76" name="subject" value='{subject}'>
	</td>
	<td align="right">
		{lang_priority}
		<select name="priority">
			<option value="1">{lang_high}</option>
			<option value="3" selected>{lang_normal}</option>
			<option value="5">{lang_low}</option>
		</select>
	</td>
</tr>
</table>

<!-- END header -->

<!-- BEGIN body_input -->
<table width="98%" border="0" cellspacing="0" cellpading="0">
<tr>
	<td>
		&nbsp;<br>
	</td>
	<td>
		{errorInfo}<br>
	</td>
</tr>
<tr>
	<td width="10%">
		&nbsp;
	</td>
	<td align="left">
		<TEXTAREA class="input_text" NAME=body ROWS=20 COLS="76" WRAP=HARD>{body}</TEXTAREA>
	</td>
</tr>
<tr>
	<td width="10%" valign="top">
		{lang_signature}
	</td>
	<td align="left">
		<TEXTAREA class="input_text" NAME=signature ROWS=5 COLS="76" WRAP=HARD>{signature}</TEXTAREA>
	</td>
</tr>
<tr>
	<td colspan="2">
		&nbsp;<br>
	</td>
</tr>
</table>
<!-- END body_input -->

<!-- BEGIN attachment -->
<script language="javascript1.2">
// position cursor in top form field
document.doit.{focusElement}.focus();
//sString = document.doit.{focusElement}.innerHTML;
//document.doit.{focusElement}.innerHTML = sString;
</script>

<br>
<table width="98%" border="0" cellspacing="0" cellpading="0">
<tr bgcolor="{th_bg}">
	<td>
		<b>{lang_attachments}</b>
	</td>
	<td width="80%" align="center">
		<INPUT class="input_text" NAME="attachfile" SIZE=48 TYPE="file">
	</td>
	<td align="left" width="20%">
		<input class="input_text" type="submit" name="addfile" value="{lang_add}">
	</td>
	<td>
		<input class="input_text" type="submit" value="{lang_send}" name="send">
	</td>
</tr>
</table>
<br>
<table width="98%" border="0" cellspacing="1" cellpading="0">
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
		<input class="text" type="submit" name="removefile" value="{lang_remove}">
	</td>
</tr>
<!-- END attachment_row_bold -->
