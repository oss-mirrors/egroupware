<!-- BEGIN gen_table -->
<blockquote>
<h2>{lang_page_description}</h2>
<h3>{lang_page_instructions}</h3>
</blockquote>
{hidden_fields}
{tbl_section}
<!-- END gen_table -->
<!-- BEGIN gen_row -->
  <tr id="{tr_id}" {tropts}>
	{tabledatas}
  </tr>
<!-- END gen_row -->
<!-- BEGIN gen_data -->
    <td {tdopts}>
	  {tdcontent}
	</td>
<!-- END gen_data -->
<!-- BEGIN table_section -->
<blockquote>
<h3><a href="{link_add_file_type}">{lang_add_instructions}</a></h3>
</blockquote>
<br>
<div align="center">
<table {tableopts}>
	{tablerows}
</table>
</div>
<!-- END table_section -->
