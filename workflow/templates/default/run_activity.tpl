<LINK href="/egroupware/workflow/templates/default/css/run_activity.css"  type="text/css" rel="StyleSheet">
<div id="run_activity_zone">
<table class="table_run_activity"  cellpadding="0" cellspacing="0">
	<tr class="tr_run_activity">
		<td class="td_run_activity">
			<form method="post" enctype='multipart/form-data' name="workflow_form">
			<table class="table_activity">
				<!-- BEGIN block_title_zone -->
				<tr class="th">
					<td class="td_title">
						{activity_title}
					</td>
				</tr>
				<!-- END block_title_zone -->

				<tr class="row_on">
					<td>
						{activity_template}
					</td>
				</tr>
				<!-- BEGIN block_priority_zone -->
				<tr class="tr_priority_zone">
					<td class="td_priority_zone">
						<table class="table_priority" cellpadding="0" cellspacing="0">
							<tr class="tr_priority">
								<td class="td_priority_label">
								{Priority_text}&nbsp;
								</td>
								<td class="td_priority_select">
									<select name="wf_priority">
									<!-- BEGIN block_priority_options -->
									<option  {selected_priority_options} value="{priority_option_name}">{priority_option_value}</option>
									<!-- END block_priority_options -->
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- END block_priority_zone -->
				<!-- BEGIN block_submit_zone -->
				<tr class="row_on"> 
					<span id="workflow_submit_zone">
					<td class="td_submit_zone">
						<!-- BEGIN block_submit_select_area -->
							<select name="submit_options">
							<!-- BEGIN block_submit_options -->
							<option value="{submit_option_name}">{submit_option_value}</option>
							<!-- END block_submit_options -->
							</select>
							<input type="submit" name="{submit_button_name}" value="{submit_button_value}">
						<!-- END block_submit_select_area -->
						<!-- BEGIN block_submit_buttons_area -->
						<table class="table_submit_buttons">
							<tr class="th">
									{submit_buttons}
							</tr>
						</table>
						<!-- END block_submit_buttons_area -->
					</td>
					</span>
				</tr>
				<!-- END block_submit_zone -->
			</table>
			</form>
		</td>
	</tr>
	<!-- BEGIN workflow_info_zone -->
	<tr class="tr_run_activity">
	<span id="workflow_info_zone">
		<table class="table_info">
		  <tr class="row_info"> 
		    <td class="cell_info_label">{lang_process:}</td>
		    <td class="cell_info_value">{wf_process_name}:{wf_process_version}</td>
		    <td class="cell_info_label">{lang_instance:}</td>
		    <td class="cell_info_value">({wf_instance_id})-{wf_instance_name}</td>
		    <td class="cell_info_label">{lang_owner:}</td>
		    <td class="cell_info_value">{wf_owner}</td>
		    <td class="cell_info_label">{lang_activity:}</td>
		    <td class="cell_info_value">{wf_activity_name}</td>
		    <td class="cell_info_label">{lang_user:}</td>
		    <td class="cell_info_value">{wf_user_name}</td>
		    <td class="cell_info_label">{lang_date:}</td>
		    <td class="cell_info_value">{wf_date}</td>
		  </tr>
		</table>
	</span>
	</tr>
	<!-- END workflow_info_zone -->
</table>
</div>