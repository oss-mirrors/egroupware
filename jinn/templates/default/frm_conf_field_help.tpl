<!-- BEGIN head -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xml:lang="{lang}" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{website_title}</title>
		<meta http-equiv="content-type" content="text/html; charset={charset}" />
		<meta name="keywords" content="eGroupWare" />
		<meta name="description" content="eGroupware" />
		<meta name="keywords" content="eGroupWare" />
		<meta name="copyright" content="eGroupWare http://www.egroupware.org (c) 2003" />
		<meta name="language" content="{lang_code}" />
		<meta name="author" content="eGroupWare http://www.egroupware.org" />
		<meta name="robots" content="none" />
		<link rel="icon" href="{img_icon}" type="image/x-ico" />
		<link rel="shortcut icon" href="{img_shortcut}" />
		<link href="{theme_css}" type="text/css" rel="StyleSheet" />
<!--		{slider_effects}-->
<!--		{simple_show_hide}-->
<!--		{java_script}-->
{css}
<style type="text/css">
<!--
	body
	{
		margin:0px;
	}

-->
</style>
</head>
<!-- END head -->

<!-- BEGIN bodyhead -->
<body {body_tags}>

<form name="popfrm" action="{action}" method="post">
<div id="divMain">
<div id="divAppboxHeader">{lang_field_help_information}</div>
<div id="divAppbox">
{lang_field_orig_name}: {field_orig_name}<br/>
<br/>
<b>{fld_info_cnf}</b><br/>
	<table align="center" cellpadding="3" cellspacing="3" width="100%">
<tr><td valign="top" class="{rowval}">{lang_alt_name}</td><td valign="top" class="{rowval}"><input type="text" name="FLDfield_alt_name" value="{val_field_alt_name}"/></td></tr>
<tr><td valign="top" class="{rowval}">{lang_help_text}</td><td valign="top" class="{rowval}"><textarea style="border:solid 1px #cccccc;width:300px;height:100px" name="FLDfield_help_info">{val_field_help_info}</textarea></td></tr>
<!-- END bodyhead -->

<!-- BEGIN row -->
<tr><td valign="top" class="{rowval}">{descr}</td><td valign="top" class="{rowval}">{fld}</td></tr>
<!-- END row -->


<!-- BEGIN footer -->
    </table>
	<script type="text/JavaScript">
	<!--
	
	function fake_submit()
		  {
			 window.opener.document.frm.{fld_name}.value={newconfig};
			 self.close();
		  }
		  

	//-->
	</script>
	<div align="center" style="{buttons_visibility}">
		<input type="submit" value="{save}"  />
		<input type="button" value="{cancel}" onClick="self.close()" />
	</div>
</div>
</div>
</form>
</body>
</html>

<!-- END footer -->


