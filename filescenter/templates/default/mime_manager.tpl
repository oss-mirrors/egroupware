<!-- BEGIN mime_table -->
<blockquote>
<h2>{lang_page_description}</h2>
<h3>{lang_page_instructions}</h3>
<h3><a href="{link_add_file_type}">{lang_add_instructions}</a></h3>
</blockquote>
<br>
{hidden_fields}
<div align="center">
<table {tableopts}>
	{tablerows}
</table>
</div>
<!-- END mime_table -->
<!-- BEGIN mime_row -->
  <tr id="{tr_id}" {tropts}>
	{tabledatas}
  </tr>
<!-- END mime_row -->
<!-- BEGIN mime_data -->
    <td {tdopts}>
	  {tdcontent}
	</td>
<!-- END mime_data -->

