<!-- begin compose.tpl -->
<script type="text/javascript">
<!--
	self.name="first_Window";
	function addybook(extraparm)
	{
		Window1=window.open('{js_addylink}'+extraparm,"Search","width={jsaddybook_width},height={jsaddybook_height},toolbar=no,scrollbars=yes,resizable=yes");
	}
	function attach_window(url)
	{
		document.{form1_name}.attached_filenames.value="";
		awin = window.open(url,"attach","width=500,height=400,toolbar=no,resizable=yes");
	}
	function spellcheck()
	{
		document.doit.btn_spellcheck.value = "'Spell Check*'";
		document.doit.submit() ;
	}
	function send()
	{
		if (document.doit.to.value == "") {
			alert('Please enter a email address in the To box');
		} else {
			document.doit.submit();
		}
	}
-->
</script>

{widget_toolbar}

<table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<!--  <form enctype="multipart/form-data" name="{ form1_name }" action="{form1_action}" method="{ form1_method }"> -->
<form enctype="application/x-www-form-urlencoded" name="{form1_name}" action="{form1_action}" method="{form1_method}">
<tr>
	<td colspan="2" bgcolor="{buttons_bgcolor}">
		<table border="0" cellpadding="1" cellspacing="1" width="100%">
		<tr>
			<td width="20%" align="left" bgcolor="{buttons_bgcolor}">
				<font face="{toolbar_font}">
					{addressbook_button}
				</font>
			</td>
			<td width="20%" align="center" bgcolor="{buttons_bgcolor}">
				<font face="{toolbar_font}">
					{spellcheck_button}
				</font>
			</td>
			<td width="*" align="right" bgcolor="{buttons_bgcolor}">
				&nbsp;
			</td>
			<td width="20%" align="right" bgcolor="{buttons_bgcolor}">
				<font face="{toolbar_font}">
					{send_button}
				</font>
			</td>
			<td width="50">
				&nbsp;
			</td>
			
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}" width="20%" align="left">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{to_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" width="80%" align="left">
		<input type="text" name="{to_box_name}" size="80" value="{to_box_value}">
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{cc_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}">
		<input type="text" name="{cc_box_name}" size="80" value="{cc_box_value}">
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{bcc_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}">
		<input type="text" name="{bcc_box_name}" size="80" value="{bcc_box_value}">
	</td>
</tr>
<tr>
	<td bgcolor="{to_boxs_bgcolor}">
		<font size="2" face="{to_boxs_font}">
		<strong>&nbsp;{subj_box_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}">
		<input type="text" name="{subj_box_name}" size="80" value="{subj_box_value}">
	</td>
</tr>

<!-- BEGIN B_checkbox_sig -->
<tr>
	<td bgcolor="{to_boxs_bgcolor}" colspan="1">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{checkbox_sig_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" colspan="1">
		<input type="checkbox" name="{checkbox_sig_name}" value="{checkbox_sig_value}" checked>
	</td>
</tr>
<!-- END B_checkbox_sig -->
<tr>
	<td bgcolor="{to_boxs_bgcolor}" colspan="1">
		<font size="2" face="{to_boxs_font}"><strong>&nbsp;{checkbox_req_notify_desc}</strong></font>
	</td>
	<td bgcolor="{to_boxs_bgcolor}" colspan="1">
	<input type="checkbox" name="{checkbox_req_notify_name}" value="{checkbox_req_notify_value}">
	</td>
</tr>
<tr>
	 <td bgcolor="{to_boxs_bgcolor}">
		 <font size="2" face="{to_boxs_font}">
		 {attachfile_js_button}
		 </font>
	 </td>
	 <td bgcolor="{to_boxs_bgcolor}">
	 	<input type="text" size="80" name="attached_filenames" onClick="javascript:attach_window('{attachfile_js_link}')">
	<td>
 </tr>
 </table>
<!-- this textarea should be 78 chars each line to conform with RFC822 old line length standard 78+CR+LF= 80 char line -->
<!-- when used with enctype multipart/form-data and wrap=hard this will add the hard wrap CRLF to the end of each line -->
 <table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr align="center">
	<td>
		<textarea name="{body_box_name}" cols="84" rows="15" wrap="hard">{body_box_value}</textarea>
		<!-- <textarea name="{body_box_name}" cols="78" rows="20" wrap="hard">{body_box_value}</textarea> -->
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
