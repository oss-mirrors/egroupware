<!-- $Id$ -->
<!-- BEGIN delete_transition.tpl -->
<p><b>{lang_delete_transition}</b></p>
<p><b>{lang_are_you_sure}</b></p>
<hr><p>

<center><font color=red>{messages}</font></center>

<form method="POST" action="{delete_transition_link}">
<table border="0" width="80%" cellspacing="0" align="center">
	<tr bgcolor="{row_off}">
		<td>&nbsp;</td>
		<td align="center"><input type="submit" value="{lang_ok}" name="submit"></td>
		<td align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
		<td colspan="2">&nbsp;</td>
	</tr>

</table>
</form>

<!-- END delete_transition.tpl -->

