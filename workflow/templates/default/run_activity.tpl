<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<form method="post" enctype='multipart/form-data' name="workflow_form">
			<table style="border: 1px solid black;width:100%;margin-bottom:10px">
				<!-- BEGIN block_title_zone -->
				<tr class="th">
					<td>
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td style="font-size: 120%; font-weight:bold">
								{activity_title}
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- END block_title_zone -->
				<tr class="row_off">
					<td>
						{activity_template}
					</td>
				</tr>
				<!-- BEGIN block_submit_zone -->
				<tr class="row_on"> 
					<td style="text-align: right;">
						<!-- BEGIN block_submit_select_area -->
							<select name="submit_options">
							<!-- BEGIN block_submit_options -->
							<option value="{submit_option_name}">{submit_option_value}</option>
							<!-- END block_submit_options -->
							</select>
							<input type="submit" name="{submit_button_name}" value="{submit_button_value}">
						<!-- END block_submit_select_area -->
						<!-- BEGIN block_submit_buttons_area -->
						<table width="100%" style="border: 1px solid black;">
							<tr class="th">
									{submit_buttons}
							</tr>
						</table>
						<!-- END block_submit_buttons_area -->
					</td>
				</tr>
				<!-- END block_submit_zone -->
			</table>
			
			</form>
		</td>
	</tr>
</table>
