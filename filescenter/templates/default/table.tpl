<form name="filesystem" method="post" action="{form_action}" enctype="multipart/form-data" id="filesystem">
<input name="path" value="{path}" type="hidden">
<input name="formvar" value="" type="hidden">
<input name="ftask" value="" type="hidden">
<input name="return_to_path" value="{returntopath}" type="hidden">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<thead>
		<tr>
		  <th class="TableHead2" width="16"><input onclick=
		  "javascript:invert_selection()" name="dummy"
		  type="checkbox"></th>
		  <!-- BEGIN files_header_tbl_field -->
		  <th class="TableHead2" nowrap="nowrap" {tdhoptions}><span class="lk2">{lang_fieldname}</span></th>
		  <!-- END files_header_tbl_field -->
		</tr>
	</thead>
	
	<tbody id="Tdirs">
	<!-- BEGIN dirs_tbl_row -->
	<tr id="{filename}" class="Table1">
	  <td><input onclick="javascript:item_click(this)"
	  name="files[]" value="{filename}" type="checkbox"></td>

	  <!-- BEGIN dirs_tbl_field -->
	  <td nowrap="nowrap" {tdoptions} class="Table1">{field_content}</td>
	  <!-- END dirs_tbl_field -->
	</tr>
	<!-- END dirs_tbl_row -->
	</tbody>

	<tbody id="Tfiles">
	<!-- BEGIN files_tbl_row -->
	<tr id="{filename}" class="Table1">
	  <td><input onclick="javascript:item_click(this)"
	  name="files[]" value="{filename}" type="checkbox"></td>

	  <!-- BEGIN files_tbl_field -->
	  <td nowrap="nowrap" {tdoptions} class="Table1">{field_content}</td>
	  <!-- END files_tbl_field -->
	</tr>
	<!-- END files_tbl_row -->
	</tbody>
	<tfoot>
	<tr>
	  <td colspan="99" class="go_small" height="18">{footer_information}</td>
	</tr>
	</tfoot>
</table>
</form>
