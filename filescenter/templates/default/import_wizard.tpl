<!-- BEGIN wizard -->

<form action="{form_action}" method="post">
<div style="text-align: justify">
{text_explaining_import_process}
<br><br>
<blockquote>
<input type="radio" name="formvar[import]" value="Y">{lang_import_yes}<br>
<input type="radio" name="formvar[import]" value="N">{lang_import_no}<br>
</blockquote>

<br>
<br>
</div>
<div style="text-align: center">
<input type="submit" value="{value_button_submit}">
</div>
</form>

<!-- END wizard -->
