<!-- BEGIN form -->
<p><b>{lang_edit_a_state}</b>
<hr><p>

{messages}

<form method="POST" action="{form_action}">

<table border="0" width="95%" cellspacing="0" align="center">
	<tr bgcolor="{th_bg}">
		<td colspan="2">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_transition_name}:</td>
		<td align="left"><input name="transition[name]" value="{value_name}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_transition_description}:</td>
		<td align="left"><textarea rows="10" name="transition[description]" cols="65" wrap="hard">{value_description}</textarea></td>
	</tr>


	<tr bgcolor="{row_off}">
		<td align="left">{lang_source_state}:</td>
		<td align="left"><select name="transition[source_state]">{options_source_state}</select></b></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_target_state}:</td>
		<td align="left"><select name="transition[target_state]">{options_target_state}</select></b></td>
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
