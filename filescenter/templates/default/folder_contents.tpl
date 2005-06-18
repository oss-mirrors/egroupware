<!-- BEGIN main -->
{javascripts}

<div class="files-container">
<!-- form name="filesystem" method="post" action="{form_action}" enctype="multipart/form-data" id="filesystem" -->

<div class="folder_title" id="fcDisplayLocation">{display_location}</div>
<input name="path" value="{path}" type="hidden">
<input name="formvar" value="" type="hidden">
<input name="ftask" value="" type="hidden">
<input name="task" type="hidden">
<input name="return_to_path" value="{this_url}" type="hidden">

<link href="{css_dir}/style.css" rel="stylesheet" type="text/css">
<div align="left" style="margin-bottom: 2px;">
<table border="0" cellpadding="0" cellspacing="0" id="fcToolbar">
  <tbody>
	<tr>
	 {header_menu_contents}
	</tr>
	<!-- { navbar_second_row } -->
  </tbody>
</table>
</div>

<table border="0" cellpadding="0" cellspacing="0" width="100%" id="fcContents">
	<thead>
		<tr>
		  <th class="th" width="16"><input title="{lang_invert_selection}" style="cursor:pointer;cursor:hand;" onclick="fcFolderView.invert_selection()" name="dummy" type="checkbox"></th>
			{files_header_contents}
		</tr>
	</thead>
	
	<tbody id="fcTfolders">
	<!--{ dirs_tbl_row_contents }-->
	</tbody>

	<tbody id="fcTfiles">
	<!--{ files_tbl_row_contents }-->
	</tbody>
	<tfoot>
	<tr>
	  <td colspan="99" class="go_small" height="18" id="fcFolderInfo">{folder_information}</td>
	</tr>
	</tfoot>
</table>
<!--/form-->
<script language="javascript">
<!-- 

	var fcFolderView;
	dynapi.onLoad(function(){
		fcFolderView = new fcFolderViewPlugin();
		fcFolderView.refresh();
	});

//-->
</script>
</div>
<!-- END main -->

<!-- BEGIN header_menu -->
<td valign="bottom" align="center" style="text-align: center;" width="{td_width}"><a class="go_small" href="{icon_link}" {icon_other}>{navbar_element}</a></td>
<!-- END header_menu -->

<!-- BEGIN files_header_tbl_field -->
<th class="th" nowrap="nowrap" {tdhoptions} style="font-size: 11px;">{lang_fieldname}</th>
<!-- END files_header_tbl_field -->

<!-- BEGIN dirs_tbl_field -->
<td nowrap="nowrap" {tdoptions} class="Table1">{field_content}</td>
<!-- END dirs_tbl_field -->

<!-- BEGIN files_tbl_field -->
<td nowrap="nowrap" {tdoptions} class="Table1">{field_content}</td>
<!-- END files_tbl_field -->

<!-- BEGIN dirs_tbl_row -->
<tr id="{filename}" class="Table1" align="left">
  <td><input onclick="javascript:item_click(this)" name="files[]" value="{filename}" type="checkbox"></td>
  {dirs_tbl_field_contents}
</tr>
<!-- END dirs_tbl_row -->

<!-- BEGIN files_tbl_row -->
<tr id="{filename}" class="Table1" align="left">
  <td><input onclick="javascript:item_click(this)" name="files[]" value="{filename}" type="checkbox"></td>
  {files_tbl_field_contents}
</tr>
<!-- END files_tbl_row -->
