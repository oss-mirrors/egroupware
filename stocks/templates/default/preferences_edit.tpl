<!-- $Id$ -->

<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">

<center>
<form method="POST" name="preferences_edit" action="{actionurl}">
{hidden_vars}
<table border="0" cellspacing="2" cellpadding="2" width="40%">
	<tr bgcolor="{th_bg}">
		<td colspan="2" align="center">{h_lang_edit}</td>
	</tr>
	<tr bgcolor="{tr_color1}">
		<td>{lang_symbol}:</td>
		<td align="center"><input type="text" name="values[symbol]" value="{symbol}"></td>
	</tr>
	<tr bgcolor="{tr_color2}">
		<td>{lang_company}:</td> 
		<td align="center"><input type="text" name="values[name]" value="{name}"></td>
	</tr>

<!-- BEGIN edit -->

	<tr valign="bottom">
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="{lang_save}">
		</td>
	</tr>
</table>
</form>
</center>
         
<!-- END edit -->
