<!-- BEGIN form -->
<p><b>{lang_edit_state}</b>
<hr><p>

{messages}

<form method="POST" action="{form_action}">

<table border="0" width="95%" cellspacing="0" align="center">
	<tr bgcolor="{th_bg}">
		<td colspan="2">&nbsp;</td>
	</tr>

<!-- BEGIN autoid -->
	<tr bgcolor="{row_off}">
		<td align="left" colspan="2"><input name="state[autoid]" type="checkbox" CHECKED >{lang_auto_id}</td>
	</tr>
<!-- END autoid -->
	<tr bgcolor="{row_off}">
		<td align="left">{lang_state_id}:</td>
		<td align="left"><input name="state[id]" value="{value_id}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_state_name}:</td>
		<td align="left"><input name="state[name]" value="{value_name}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_state_description}:</td>
		<td align="left"><textarea rows="10" name="state[description]" cols="65" wrap="hard">{value_description}</textarea></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="2" align="left"><input type="checkbox" name="state[initial]" {value_initial}>{lang_new_ticket_into_state}</td>
	</tr>


	<tr bgcolor="{row_off}">
		<td colspan="2" align="center"><hr></td>
	</tr>

	<tr>
		<td align="left"><input type="submit" name="submit" value="{lang_submit}"></td>
		<td align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
	</tr>
</table>

<!-- END form -->
