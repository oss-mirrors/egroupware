<div style="color:red; text-align:center">{message}</div>
<div>
	<div>
		{proc_bar}
	</div>
	<div>
		{errors}
	</div>
</div>

<form action="{form_action_adminexport}" method="post">
<table style="border: 1px solid black;width:100%; margin-bottom:10px">
<input type="hidden" name="p_id" value="{p_id}" />
<tr class="th">
	<td colspan="2" style="font-size: 120%; font-weight:bold">
		{lang_export_a_process}
	</td>
</tr>
<tr>
  <td>{lang_select_export_file}:</td>
  <td><input size="64" name="exportfile" type="text" value="{value_initial_filename}" />
  		<input style="font-size:9px;" type="submit" name="save" value="save" />
  </td>
</tr>
</table>
</form>