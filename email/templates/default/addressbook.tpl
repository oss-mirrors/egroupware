<!-- $Id$ -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML LANG="en">
<head>
<title>{lang_addressbook_action}</title>
<meta http-equiv="content-type" content="text/html; charset={charset}">
<STYLE type="text/css">
A {
	text-decoration:none;
}
</STYLE>
<script type="text/javascript">
	function ExchangeTo(thisform)
	{
		if (opener.document.doit.to.value =='')
		{
			opener.document.doit.to.value = thisform.elements[0].value;
		}
		else
		{
			opener.document.doit.to.value +=","+thisform.elements[0].value;
		}
	}
	function ExchangeCc(thisform)
	{
		if (opener.document.doit.cc.value=='')
		{
			opener.document.doit.cc.value=thisform.elements[0].value;
		}
		else
		{
			opener.document.doit.cc.value+=","+thisform.elements[0].value;
		}
	}
	function ExchangeBcc(thisform)
	{
		if (opener.document.doit.bcc.value=='')
		{
			opener.document.doit.bcc.value=thisform.elements[0].value;
		}
		else
		{
			opener.document.doit.bcc.value+=","+thisform.elements[0].value;
		}
	}	
</script>
</head>
<body bgcolor="{bg_color}">
<p align="center">{lang_showing}<br>
<table border="0" width="100%">
<form action="{form_action}" name="form" method="POST">
	<tr>
		{left}
		<td align="left">
			<select name="cat_id" onChange="this.form.submit();"><option value="">{lang_select_cats}</option>{cats_list}</select>
			<noscript>&nbsp;<input type="submit" name="submit" value="{lang_submit}"></noscript>
		</td>
		<td align="center">
			<input type="text" size="10" name="query" value="{query}">
			<input type="submit" name="search" value="{lang_search}">
		</td>
		<td align="right">
			<select name="filter" onChange="this.form.submit();">
				{filter_list}
			</select>
		</td>
		{right}
	</tr>
</form>
</table>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="50%" bgcolor="{th_bg}" colspan="2">
			<font face="{font}">{sort_org_name}</font>
		</td>
		<td width="25%" bgcolor="{th_bg}" align="center" rowspan="2">
			<font face="{font}">{lang_email}</font>
		</td>
		<td width="25%" bgcolor="{th_bg}" align="center" rowspan="2">
			<font face="{font}">{lang_hemail}</font>
		</td>
	</tr>
	<tr bgcolor="{th_bg}">
		<td bgcolor="{th_bg}">
			<font face="{font}">{sort_n_family}</font>
		</td>
		<td bgcolor="{th_bg}">
			<font face="{font}">{sort_n_given}</font>
		</td>
	</tr>

  <!-- BEGIN addressbook_list -->
	<tr bgcolor="{tr_color}">
		<td colspan="2">
			<font face="{font}">{company}</font>
		</td>
<form>
		<td align="center" rowspan="2">
			<font face="{font}" size="1">
			<input type="text" size="18" name="email" value="{email}">
			<br>
			<input type="button" name="to" value="To" onClick="ExchangeTo(this.form);">
			<input type="button" name="cc" value="Cc" onClick="ExchangeCc(this.form);">
			<input type="button" name="bcc" value="Bcc" onClick="ExchangeBcc(this.form);">
			</font>
		</td>
</form>
<form>
		<td align="center" rowspan="2">
			<font face="{font}" size="1">
			<input type="text" size="18" name="hemail" value="{hemail}">
			<br>
			<input type="button" name="h_to" value="To" onClick="ExchangeTo(this.form);">
			<input type="button" name="h_cc" value="Cc" onClick="ExchangeCc(this.form);">
			<input type="button" name="h_bcc" value="Bcc" onClick="ExchangeBcc(this.form);">
			</font>
		</td>
</form>
	</tr>
	<tr bgcolor="{tr_color}">
		<td>
			<font face="{font}">{lastname}</font>
		</td>
		<td>
			<font face="{font}">{firstname}</font>
		</td>
	</tr>
<!-- END addressbook_list -->

	<tr>
<form>
		<td colspan="4" align="center">
			<font face="{font}">
			<input type="button" name="done" value="{lang_done}" onClick="window.close()">
			</font>
</form>
		</td>
	</tr>
</table>
</body>
</html>
