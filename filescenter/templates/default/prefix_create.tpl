<!-- BEGIN gen_table -->
<blockquote>
<h2>{lang_page_description}</h2>
<h3>{lang_page_instructions}</h3>
</blockquote>
<br>
<div align="center">
<form action="{form_action}" method="post">
{hidden_fields}
<table {tableopts}>
	{tablerows}
</table>
<!-- BEGIN buttons_from_form -->
<br>
<input type="button" value="{lang_but_cancel}" onclick="window.location='{cancel_url}'"> <input type="submit" value="{lang_but_submit}">
<!-- END buttons_from_form -->

</form>
</div>
<!-- END gen_table -->
<!-- BEGIN gen_row -->
  <tr {tropts}>
	{tabledatas}
  </tr>
<!-- END gen_row -->
<!-- BEGIN gen_data -->
    <td {tdopts}>
	  {tdcontent}
	</td>
<!-- END gen_data -->
