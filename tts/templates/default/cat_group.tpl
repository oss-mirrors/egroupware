<!-- $Id$ -->
<!-- BEGIN cat_group.tpl -->

<br>
<!-- BEGIN cat_group_list -->
<table width="98%" cellspacing="1" cellpadding="3" border="0" align="center">
	<tr class="th">
		<td align="center">{tts_head_cat_name}</td>
		<td>{tts_head_cat_description}</td>
		<td>{tts_head_group_name}</td>
		<td align="center">{lang_edit}</td>
		<td align="center">{lang_delete}</td>
	</tr>
	{rows}
	<tr class="{row_class}">
		<td colspan=3>&nbsp;</td>
		<td align="center"><A HREF="{tts_cat_group_add_link}">[{lang_add}]</A></td>
		<td >&nbsp;</td>
	</tr>
</table>
<br>
<!-- END cat_group_list -->

<!-- END cat_group.tpl -->

<!-- BEGIN cat_group_row -->
	<tr class="{row_class}">
		<td align="center">{cat_name}</td>
		<td>{cat_description}</td>
		<td>{group_name}</td>
		<td align="center"><A HREF="{tts_cat_group_edit_link}">[{lang_edit}]</A></td>
		<td align="center"><A HREF="{tts_cat_group_delete_link}">[{lang_delete}]</A></td>
	</tr>
<!-- END cat_group_row -->
