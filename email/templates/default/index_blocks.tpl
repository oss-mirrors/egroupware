<!-- begin index_plocks.tpl -->

<!-- BEGIN B_mlist_form_init -->
<form name="{frm_delmov_name}" action="{frm_delmov_action}" method="post">
<input type="hidden" name="what" value="delete">
<input type="hidden" name="folder" value="{current_folder}">
<input type="hidden" name="sort" value="{current_sort}">
<input type="hidden" name="order" value="{current_order}">
<input type="hidden" name="start" value="{current_start}">
<!-- END B_mlist_form_init -->

&nbsp;	<!-- &nbsp; Lame Seperator &nbsp; --> &nbsp;

<!-- BEGIN B_arrows_form_table -->
<script type="text/javascript">
function do_navigate(act)
{
	document.{arrows_form_name}.start.value = act;
	document.{arrows_form_name}.submit();
}

</script>
<table border="0" cellpadding="0" cellspacing="1" width="95%" align="center">
<tr bgcolor="{arrows_backcolor}">
	<td width="2%" align="left" valign="top">
		<form method="POST" action="{arrows_form_action}" name="{arrows_form_name}">
		<input type="hidden" name="folder" value="{current_folder}">
		<input type="hidden" name="sort" value="{current_sort}">
		<input type="hidden" name="order" value="{current_order}">
		<!-- bogus initial start value, will be changed by on click call -->
		<input type="hidden" name="start" value="0">
		<table border="0" bgcolor="{arrows_td_backcolor}" cellspacing="0" cellpadding="0">
		<tr>
			<td align="left">
				{first_page}
			</td>
		</tr>
		</table>
	</td>
	<td width="2%" align="left" valign="top">
		<table border="0" bgcolor="{arrows_td_backcolor}" cellspacing="0" cellpadding="0">
		<tr>
			<td align="left">
				{prev_page}
			</td>
		</tr>
		</table>
	</td>
	<td width="2%" align="right" valign="top">
		<table border="0" bgcolor="{arrows_td_backcolor}" cellspacing="0" cellpadding="0">
		<tr>
			<td align="right">
				{next_page}
			</td>
		</tr>
		</table>
	</td>
	<td width="2%" align="right" valign="top">
		<table border="0" bgcolor="{arrows_td_backcolor}" cellspacing="0" cellpadding="0">
		<tr>
			<td align="right">
				{last_page}
			</td>
		</tr>
		</table>
	</td>
  </form>
</tr>
</table>
<!-- END B_arrows_form_table -->

<!-- end index_plocks.tpl -->
