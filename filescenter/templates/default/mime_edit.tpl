<!-- BEGIN mime_table -->
<blockquote>
<h2>{lang_page_description}</h2>
<h3>{lang_page_instructions}</h3>
</blockquote>
<br>
<div align="center">
<form action="{form_action}" method="post" enctype="multipart/form-data">
{hidden_fields}
<table {tableopts}>
	{tablerows}
</table>
<br>
{button_delete} <input type="button" value="{lang_but_cancel}" onclick="window.location='{cancel_url}'"> <input type="submit" value="{lang_but_submit}">

</form>
</div>
<!-- END mime_table -->
<!-- BEGIN mime_row -->
  <tr {tropts}>
	{tabledatas}
  </tr>
<!-- END mime_row -->
<!-- BEGIN mime_data -->
    <td {tdopts}>
	  {tdcontent}
	</td>
<!-- END mime_data -->
