<!-- BEGIN form -->
<br>

{messages}

<form method="POST" action="{form_action}">

<table border="0" width="95%" cellspacing="0" align="center">
	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>


	<tr class="row_on">
		<td>{lang_cat_name}:</td>
		<td><select name="cat_group[cat_id]">{options_cat_id}</select></b></td>
	</tr>

	<tr class="row_off">
		<td>{lang_group_name}:</td>
		<td><select name="cat_group[account_id]">{options_account_id}</select></b></td>
	</tr>

	<tr height="40">
		<td>
			<input type="submit" name="save" value="{lang_save}"> &nbsp;
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</table>
<br>


<!-- END form -->
