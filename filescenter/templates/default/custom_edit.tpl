<!-- BEGIN main -->

<form name="{form_name}" method="post">
<script language="javascript">
<!--
{javascript_code}
//-->
</script>

<h2>{lang_custom_fields}</h2>

{lang_page_description}

<table width="100%" align="center" class="row_on" border="0">
	<tr class="th">
		<!-- BEGIN h_line -->
		<td {tdopts}>
			{tdcontent}
		</td>
		<!-- END h_line -->
	</tr>
	<tbody>
		<!-- BEGIN tbl_body -->
		<tr>
			<!-- BEGIN b_line -->
			<td {tdopts}>
				{tdcontent}
			</td>
			<!-- END b_line -->
		</tr>
		<!-- END tbl_body -->
	</tbody>
</table>
<!-- END main -->
<br />

<input type="hidden" name="formvar[operation]" value="{val_operation}">

<br />
<div style="text-align: center;">
<input style="width: 50px;" value="{lang_commit}" onclick="{commit_action}" type="button">
<input style="width: 50px;" value="{lang_cancel}" onclick="{cancel_action}" type="button">
</div>
</form>

