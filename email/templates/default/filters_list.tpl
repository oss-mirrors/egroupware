<!-- begin filter_list.tpl -->
{pref_errors}
<p>
  <b>{page_title}</b>
  <hr>
</p>
<table border="0" cellspacing="2" cellpadding="2" width="95%" align="center">
<tr bgcolor="{tr_titles_color}">
	<td width="60%" align="left">
		<font face="{font}">{filter_name_header}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{lang_move_up}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{lang_move_down}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{lang_edit}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{lang_delete}</font>
	</td>
</tr>
<!-- BEGIN B_filter_list_row -->
<tr bgcolor="{tr_color}">
	<td width="60%" align="left">
		<font face="{font}">{filter_identity}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{move_up_href}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{move_down_href}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{edit_href}</font>
	</td>
	<td width="10%" align="center">
		<font face="{font}">{delete_href}</font>
	</td>
</tr>
<!-- END B_filter_list_row -->
<tr>
	<td colspan="4" align="center">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="4" align="center">
		{add_new_filter_href}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{done_href}
	</td>
</tr>
</table>

<p>&nbsp;</p>

<hr>
<p>&nbsp;</p>
	
<table width="90%" border="0" cellPadding="4" cellSpacing="4" align="center">
<tr> 
	<td colspan="2" align="center">
		<em>Under Development</em>
		<br>test or apply ALL filters.
	</td>
</tr>
<tr> 
	<td width="50%" align="center">
		{test_all_filters_href}
	</td>
	<td width="50%" align="center">
		{run_all_filters_href}
	</td>
</tr>
</table>

<p>&nbsp;</p>
<!-- end filter_list.tpl -->
