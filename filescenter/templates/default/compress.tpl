<!-- BEGIN main -->
<center>
<form action="{action_url}" name="form1" method="post">
<br>
<input type="hidden" name="return_to_path" value="{return_to_path}" />
<input type="hidden" name="formvar[files_list]" value="{files_list}" />
<input type="hidden" name="formvar[type]" value="zip" />


<div style="border: 1px solid rgb(153, 153, 153); position: relative; width: 500px; height: 100%; z-index: 0; height: 400px;">

<table width="100%" height="100%" border="0" cellspacing="0" cellpading="0" class="row_off" style="z-index: 0; visibility: visible;">
	<tbody> <tr height="25">
		<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">{lang_operation}</a></th>
	</tr>
	<tr>
		<td valign="top">


<!-- BEGIN OF MAIN CONTENT -->
			<div id="tabcontent1" class="inactivetab">
				<table width="100%" align="center" class="row_on" border="0">
					<tbody>
					<tr class="th">
						<td colspan="2" class="td_left">
							<b>{lang_select_name}:<b>
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							<input type="text" name="formvar[archname]">
						</td>
					</tr>
					<tr class="th">
						<td colspan="2" class="td_left">
							<b>{lang_select_type}:<b>
						</td>
					</tr>
					<tr class="row_off">
						<!-- td width="10" style="text-align: right;">
							<input type="radio" name="formvar[type]" value="zip" CHECKED>
						</td -->
						<td colspan="2" style="text-align: left;">
							zip
						</td>
					</tr>
					<!-- tr class="row_on">
						<td style="text-align: right;">
							<input type="radio" name="formvar[type]" value="gz">
						</td>
						<td style="text-align: left;">
							gzip
						</td>
					</tr -->
					<tr class="th">
						<td colspan="2" class="td_left">
							<b>{lang_select_prefix}:<b>
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							{select_prefix}
						</td>
					</tr>
					</tbody>
				</table>
			</div>

<!-- END OF MAIN CONTENT -->
		</td>
	</tr>

	<tr>
		<td align="center" height="1%">
			<!-- input style="width: 50px;" name="is_global" value="{lang_ok}"  onclick="(document.forms[0].formvar['task']).value='ok'; document.forms[0].submit();" type="button" -->
			<input style="width: 80px;" value="{lang_okb}" name="ok" type="submit">
			<input style="width: 50px;" value="{lang_cancelb}" onclick="location=document.forms[0].return_to_path.value;" type="button">
		</td>
	</tr>

</table>

</div>
</form>
</center>
<!-- END main -->

