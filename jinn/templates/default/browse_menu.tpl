	<table cellpadding="0" cellspacing="0" style="border:solid 1px #cccccc">
		<tr>
			<td><form action="{menu_action}" method="post">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td><input type="submit" name="direction" value="<<"></td>
							<td><input type="submit" name="direction" value="<"></td>
							<td><input type="submit" name="direction" value=">"></td>
							<td><input type="submit" name="direction" value=">>">
								<input type="hidden" name="limit_start" value="{limit_start}">
								<input type="hidden" name="limit_stop" value="{limit_stop}">
								<input type="hidden" name="search" value="{search_string}">
								<input type="hidden" name="orderby" value="{orderby}">
							</td>
						</tr>
					</table>
				</form>
			</td>
			<td align="center" style="padding-left:20px;">
				<form action="{menu_action}" method="post">{search_for}&nbsp;<input type="text" size="8" name="search" value="{search_string}">
				<input type="submit" value="{search}">
				<input type="hidden" name="limit_start" value="0">
				<input type="hidden" name="limit_stop" value="30">
				</form>	
			</td>
		</tr>
	</table>

<script language="javascript" type="text/javascript">
function img_popup(img,pop_width,pop_height,attr)
{
   options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
   parent.window.open("{popuplink}&path="+img+"&attr="+attr, "pop", options);
}
</script>
		<br/>
