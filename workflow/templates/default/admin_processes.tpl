<div style="color:red; text-align:center">{message}</div>

<div>
	<div>
		{proc_bar}
	</div>
	<div>
		{errors}
	</div>
</div>

<form action="{form_details_action}" method="post">
<input type="hidden" name="version" value="{version}" />
<input type="hidden" name="wf_p_id" value="{wf_p_id}" />
<input type="hidden" name="where" value="{where}" />
<input type="hidden" name="find" value="{find}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<table style="border: 1px solid black;width:100%; margin-bottom:10px">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{lang_Add_or_edit_a_process} <a href="{link_new}"><{lang_New}</a>
		</td>
	</tr>
	<tr>
	</tr>
	<tr class="row_on">
		<td>{lang_Process_Name}</td>
		<td><input type="text" maxlength="80" name="name" value="{name}" /> ver:{version}</td>
	</tr>
	<tr class="row_off">
	 	<td>{lang_Description}</td>
	 	<td><textarea rows="5" cols="60" name="description">{description}</textarea></td>
	</tr>
	<tr class="row_on">
		<td>{lang_is_active}?</td>
		<td><input type="checkbox" name="isActive" {is_active} /></td>
	</tr>
	<tr class="th">
		<td>&nbsp;</td>
		<td><input type="submit" name="save" value="{btn_update_create}" /></td>
	</tr>
</table>
</form>

<form enctype="multipart/form-data" action="{form_upload_action}" method="post">
<table style="border: 1px solid black;width:100%; margin-bottom:10px">
<tr class="th">
	<td colspan="2" style="font-size: 120%; font-weight:bold">
		{lang_Or_import_a_process}
	</td>
</tr>
<tr>
  <td>{lang_Upload_file}:</td><td><input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="16" name="userfile1" type="file" /><input style="font-size:9px;" type="submit" name="upload" value="upload" /></td>
</tr>
</table>
</form>

<div style="border: 1px solid black">
<form action="{form_filters_action}" method="post">
<div class="th" style="font-weight:bold; font-size:120%; margin-bottom:4px">{list_processes}</div>
<table width="100%" cellpadding="0" cellspacing="0">
	<tr class="th">
		{left_arrow}
		<td style="text-align:center">
			{lang_Status}:
				<select name="filter_active" onChange="this.form.submit();">
					<option value="">{lang_All}</option>
					<option value="y">{lang_Active}</option>
					<option value="n">{lang_Inactive}</option>
				</select>
			<input size="20" type="text" name="find" value="{find}" /> <input type="submit" name="filter" value="{lang_Search}">
		</td>
		{right_arrow}
	</tr>
</table>
</form>

<form action="{form_last_action}" method="post">
<input type="hidden" name="find" value="{find}" />
<input type="hidden" name="where" value="{where}" />
<table border="0" width="100%">
<tr class="th" style="font-weight:bold">
	<td>{header_name}</td>
	<td>{header_version}</td>
	<td>{header_active}</td>
	<td>{header_valid}</td>
	<td>{lang_Action}</td>
</tr>

<!-- BEGIN block_items -->
<tr bgcolor="{color_line}">
	<td>
	  <a href="{href_item_name}">{item_name}</a>
	</td>
	<td style="text-align:right;">
	  {item_version}
	</td>
	<td style="text-align:center;">
		{img_active}
	</td>
	<td style="text-align:center;">
		{img_valid}
	</td>
	<td style="width:150px">
	  <a href="{href_item_minor}"><img src="{img_new}" alt="{lang_New_minor}" title="{lang_New_minor}"/></a>
	  <a href="{href_item_mayor}"><img src="{img_new}" alt="{lang_New_mayor}" title="{lang_New_mayor}"/></a>
	  <a href="{href_item_activities}"><img src="{img_activities}" alt="{lang_Activities}" title="{lang_Activities}"/></a>
	  <a href="{href_item_code}"><img src="{img_code}" alt="{lang_Code}" title="{lang_Code}"/></a>
	  <a href="{href_item_save}"><img src="{img_save}" alt="{lang_Save}" title="{lang_Save}"/></a>
	  <a href="{href_item_roles}"><img src="{img_roles}" alt="{lang_Roles}" title="{lang_Roles}"/></a>
		<input type="checkbox" name="process[{item_wf_p_id}]" />
	</td>
</tr>
<!-- END block_items -->
<tr class="th">
	<td colspan="5" style="text-align:right">
		<input type="submit" name="delete" value="{lang_Delete_selected}">
	</td>
</tr>
</table>
</form>
</div>
