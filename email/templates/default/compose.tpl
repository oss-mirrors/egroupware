<!-- begin compose.tpl -->
<script type="text/javascript">
<!--
  self.name="first_Window";
  function addybook()
  {
	Window1=window.open('{js_addylink}',"Search","width=640,height=480,toolbar=yes,scrollbars=yes,resizable=yes");
  }
  function attach_window(url)
  {
	awin = window.open(url,"attach","width=500,height=400,toolbar=no,resizable=yes");
  }
-->
</script>

<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<form enctype="multipart/form-data" name="{form1_name}" action="{form1_action}" method="{form1_method}">
<tr>
	<td colspan="2" bgcolor="{buttons_bgcolor}">
		<table border="0" cellpadding="4" cellspacing="1" width="100%">
		<tr>
			<td align="left" bgcolor="{buttons_bgcolor}">
				<input type="{btn_addybook_type}" value="{btn_addybook_value}" onclick="{btn_addybook_onclick}">
			</td>
			<td align="right" bgcolor="{buttons_bgcolor}">
				<input type="{btn_send_type}" value="{btn_send_value}">
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{to_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" width="570">
		<input type="text" name="{to_box_name}" size="80" value="{to_box_value}">
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{cc_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" width="570">
		<input type="text" name="{cc_box_name}" size="80" value="{cc_box_value}">
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{bcc_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" width="570">
		<input type="text" name="{bcc_box_name}" size="80" value="{bcc_box_value}">
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}">
		<strong>&nbsp;{subj_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" width="570">
		<input type="text" name="{subj_box_name}" size="80" value="{subj_box_value}">
	</td>
</tr>

<!-- BEGIN B_checkbox_sig -->
<tr>
	<td bgcolor="{to_boxs_bgcolor}" colspan="2">
		<font size="2" face="{to_boxs_font}">{checkbox_sig_desc}</font>
		<input type="checkbox" name="{checkbox_sig_name}" value="{checkbox_sig_value}" checked>
	</td>
</tr>

<!-- END B_checkbox_sig -->
<tr>
	 <td bgcolor="{to_boxs_bgcolor}" colspan="2">
		 <font size="2" face="{to_boxs_font}">
		 <a href="javascript:attach_window('{attachfile_js_link}')">{attachfile_js_text}</a></font>
	 </td>
 </tr>
 </table>

 <table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr align="center">
	<td>
		<textarea name="{body_box_name}" cols="84" rows="15" wrap="hard">{body_box_value}</textarea>
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
  document.doit.body.focus();
  if(document.doit.subject.value == "") document.doit.subject.focus();
  if(document.doit.to.value == "") document.doit.to.focus();
</script>
<!-- end compose.tpl -->
