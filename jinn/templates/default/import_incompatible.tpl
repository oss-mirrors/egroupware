<p>
<p>
<form name="formimport" action="{form_action}" method="post" enctype="multipart/form-data">
<table align="center">
<tr>
<td>{lang_Select_JiNN_site_file}</td>
<td><input size="30" readonly name="importfile" type="text" value="{loaded_file}"></td>
</tr>
<tr>
<td>{lang_Replace_existing_Site_with_the_same_name}</td>
<td><input name="replace_existing" type="checkbox" {checked}></td>
</tr>
<tr>
<td colspan="2"><input type="submit" name="incompatibility_ok" value="{lang_submit_and_import}"><input onClick="location='{cancel_redirect}'"  type="button" value="{lang_cancel}"></td>
</tr>
</table>
</form>
