	<table cellspacing=0 align=center width="80%">
		<tr>
			<td bgcolor={row_off}>
				<form name=form2 action="{menu_action}" method=post>
					<table>
						<tr>
							<td><input type=submit name=direction value="<<"></td>
							<td><input type=submit name=direction value="<"></td>
							<td><input type=submit name=direction value=">"></td>
							<td><input type=submit name=direction value=">>">
								<input type=hidden name=limit_start value="{limit_start}">
								<input type=hidden name=limit_stop value="{limit_stop}">
								<input type=hidden name=search value="{search_string}">
								<input type=hidden name=order value="{order}">
							</td>
						</tr>
					</table>
				</form>
			</td>
			<td align=center bgcolor={row_off}>
				<form name=form3 action="{menu_action}" method=post>{search_for}<input type=text size="8" name=search value="{search_string}">
				<input type="submit" value="{search}">
				<input type=hidden name=limit_start value="0">
				<input type=hidden name=limit_stop value="30">
				</form>	
			</td>

		</tr>
	</table>
	<br>
