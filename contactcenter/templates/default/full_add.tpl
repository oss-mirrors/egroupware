{cc_api}

<!-- JS MESSAGES -->
<input id="cc_msg_err_empty_field" type="hidden" value="{cc_msg_err_empty_field}">
<input id="cc_msg_type_state" type="hidden" value="{cc_msg_type_state}">
<input id="cc_msg_type_city" type="hidden" value="{cc_msg_type_city}">
<!-- END JS MESSAGES -->

<!-- WINDOW CONTACT -->
<iframe id="cc_photo_frame" style="position: absolute; top: 0px; left: 0px; visibility:hidden"></iframe>
<input id="cc_contact_title" type="hidden" value="{cc_contact_title}">
<input id="cc_contact_personal" type="hidden" value="{cc_contact_personal}">
<input id="cc_contact_addrs" type="hidden" value="{cc_contact_addrs}">
<input id="cc_contact_conns" type="hidden" value="{cc_contact_conns}">

<!-- _PERSONAL DATA -->
<div id="cc_contact_tab_0" class="row_off div_cc_contact_tab" style="position: absolute; visibility: hidden">
	<form id="cc_full_add_form_personal">
	<input id="cc_full_add_contact_id" type="text" style="display: none">
	<table align="center" width="498px" height="347px" class="row_off" border="0">
		<tr class="row_off">
			<td align="right">{cc_pd_select_photo}:</td>
			<td align="left" colspan="2">
				<!-- Mozilla Method -->
				<input id="cc_pd_select_photo" type="file" accept="image/gif,image/jpeg,image/png" name="cc_pd_photo" onchange="Element('cc_pd_photo').src = 'file://'+Element('cc_pd_select_photo').value">
				<!-- IE Method -->
				<input id="cc_pd_select_photo_t" type="text" name="cc_pd_select_photo_t" readonly>
				<input id="cc_pd_select_photo_b" type="button" style="width: 60px" value="{cc_pd_select_photo_b}" onclick="Element('cc_photo_frame').contentWindow.document.all['cc_photo_input'].click();">
			</td>
			<td align="center" colspan="2" rowspan="4"><img id="cc_pd_photo" src="templates/default/images/photo.png" border="0" width="60px" height="80px"></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_alias}:</td>
			<td align="left" colspan="2"><input id="cc_pd_alias" name="{cc_pd_alias}" type="text" style="width: 175px;" value="" maxlength=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_pd_prefix}:</td>
			<td align="left" colspan="2">
				<select id="cc_pd_prefix" name="{cc_pd_prefix}" style="width: 175px;">
					<option value='0'>{cc_pd_choose_prefix}</option>
					{cc_pd_prefix_opts}
				</select></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_given_names}:</td>
			<td align="left" colspan="2"><input id="cc_pd_given_names" name="{cc_pd_given_names}" type="text" style="width: 175px;" value="" maxlength=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_pd_family_names}:</td>
			<td align="left"><input id="cc_pd_family_names" name="{cc_pd_family_names}" type="text" style="width: 175px;" value="" maxlength=""></td>
			<td align="right">{cc_pd_birthdate}:</td>
			<td align="left">
				<input id="cc_pd_birthdate_0" style="text-align: center;" title="{cc_pd_birthdate_0}" name="{cc_pd_birthdate_0}" type="text" maxlength="{cc_pd_birth_size_0}" size="{cc_pd_birth_size_0}">
				<input id="cc_pd_birthdate_1" style="text-align: center;" title="{cc_pd_birthdate_1}" name="{cc_pd_birthdate_1}" type="text" maxlength="{cc_pd_birth_size_1}" size="{cc_pd_birth_size_1}">
				<input id="cc_pd_birthdate_2" style="text-align: center;" title="{cc_pd_birthdate_2}" name="{cc_pd_birthdate_2}" type="text" maxlength="{cc_pd_birth_size_2}" size="{cc_pd_birth_size_2}">
			</td>
		</tr>
		<tr class="row_on">	
			<td align="right">{cc_pd_full_name}:</td>
			<td align="left"><input id="cc_pd_full_name" name="{cc_pd_full_name}" type="text" style="width: 175px;" value="" maxlength=""></td>
			<td align="right">{cc_pd_sex}:</td>
			<td><select id="cc_pd_sex"name="{cc_pd_sex}"><option value="0" selected>{cc_pd_choose_sex}</option><option value="1">{cc_pd_male}</option><option value="2">{cc_pd_female}</option></select>
			</td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_pd_suffix}:</td>
			<td align="left" colspan="3">
				<select id="cc_pd_suffix" name="{cc_pd_suffix}" style="width: 175px;">
					<option value="0">{cc_pd_choose_suffix}</option>
					{cc_pd_suffix_opts}
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_pd_gpg_finger_print}:</td>
			<td colspan="3" align="left"><input id="cc_pd_gpg_finger_print" name="{cc_pd_gpg_finger_print}" type="text" style="width: 350px;" value="" maxlength=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_pd_notes}:</td>
			<td colspan="3" align="left"><textarea id="cc_pd_notes" name="{cc_pd_notes}" style="width: 350px; height: 120px;"></textarea></td>
		</tr>
	</table>
	</form>
</div>

<!-- _ADDRESSES -->
<div id="cc_contact_tab_1" class="row_off div_cc_contact_tab" style="position: absolute; visibility: hidden">
	<form id="cc_full_add_form_addrs">
	<table align="center" width="498px" height="347px" border="0">
		<tr class="row_off">
			<td align="right">{cc_addr_types}:</td>
			<td align="left">
				<select id="cc_addr_types" name="{cc_addr_types}" style="width: 200px;" onchange="updateAddressFields()">
					<option value="_NONE_">{cc_addr_choose_types}</option>
					{cc_addr_types_opts}
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_countries}:</td>
			<td align="left">
				<select id="cc_addr_countries" name="{cc_addr_countries}" style="width: 200px;" onchange="updateAddrStates()">
					<option value="_NONE_">{cc_addr_choose_countries}</option>
					{cc_addr_countries_opts}
				</select>
			</td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_states}:</td>
			<td align="left">
				<select id="cc_addr_states" name="{cc_addr_states}" style="width: 200px;" onchange="updateAddrCities();">
					<option value="_NONE_">{cc_addr_choose_states}</option>
					<!-- <option value="_NEW_">{cc_addr_states_new}</option> -->
					<option value="_NOSTATE_">{cc_addr_states_nostate}</option>
					<option style="text-align: center" value="_SEP_">-----------{cc_available}----------</option>
				</select>
				<!-- <input id="cc_addr_states_new" style="width: 150px;" type="text" onmouseover="updateAddrNewStateOnMouseOver();" onmouseout="updateAddrNewStateOnMouseOut();"> -->
				<input type="button" value="{cc_addr_states_new}" onclick="ccState.open()" />
			</td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_cities}:</td>
			<td align="left">
				<select id="cc_addr_cities" style="width: 200px;" onchange="updateAddrFillingFields();">
					<option value="_NONE_">{cc_addr_choose_cities}</option>
					<!-- <option value="_NEW_">{cc_addr_cities_new}</option> -->
					<option style="text-align: center" value="_SEP_">-----------{cc_available}----------</option>
				</select>
				<!-- <input id="cc_addr_cities_new" style="width: 150px;" type="text" onmouseover="updateAddrNewCityOnMouseOver();" onmouseout="updateAddrNewCityOnMouseOut();"> -->
				<input type="button" value="{cc_addr_cities_new}" onclick="ccCity.open()" />
			</td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_1}:</td>
			<td align="left"><input id="cc_addr_1" name="{cc_addr_1}" style="width: 200px;" type="text" name="" value=""></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_2}:</td>
			<td align="left"><input id="cc_addr_2" name="{cc_addr_2}" style="width: 200px;" type="text" name="" value=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_complement}:</td>
			<td align="left"><input id="cc_addr_complement" name="{cc_addr_complement}" style="width: 200px;" type="text" name="" value=""></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_other}:</td>
			<td align="left"><input id="cc_addr_other" name="{cc_addr_other}" style="width: 200px;" type="text" name="" value=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_postal_code}:</td>
			<td align="left"><input id="cc_addr_postal_code" name="{cc_addr_postal_code}" style="width: 70px;" type="text" name="" value=""></td>
		</tr>
		<tr class="row_on">
			<td align="right">{cc_addr_po_box}:</td>
			<td align="left"><input id="cc_addr_po_box" name="{cc_addr_po_box}"style="width: 70px;" type="text" name="" value=""></td>
		</tr>
		<tr class="row_off">
			<td align="right">{cc_addr_is_default}:</td>
			<td colspan="3" align="left"><input id="cc_addr_is_default" type="checkbox" name=""></td>
		</tr>
		<tr style="visibility: hidden; position: absolute;">
			<td><input id="cc_addr_id" type="hidden"></td>
		</tr>
	</table>
	</form>
</div>

<!-- _CONNECTIONS -->
<div id="cc_contact_tab_2" class="row_off div_cc_contact_tab" style="position: absolute; visibility: hidden; border: 0px solid black">
	<table align="left" width="498px">
	<tbody>
		<tr class="th" align="center">
			<td width="150px">{cc_conn_type}</td>
			<td width="150px">{cc_conn_name}</td>
			<td width="150px">{cc_conn_value}</td>
			<td width="20px" ></td>
		</tr>
		<tr class="row_off">
			<td valign="top">
				<select id="cc_conn_type" style="width: 150px;" onchange="updateConnFields();">
					<option value="_NONE_">{cc_conn_type_none}</option>
					{cc_conn_types_opts}
				</select>
			</td>
			<td valign="top" colspan="3" width="100%" style="border: 0px solid black" cellpadding="0" cellspacing="0">
				<table align="left" width="100%" style="border: 0px solid black">
				<tbody id="cc_conn">
					<!-- Code inside here is inserted dynamically -->
				</tbody>
				</table>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td align="right" colspan="4"><input style="width: 150px;" type="button" value="{cc_new_same_type}" onclick="javascript:connAddNewLine();"></td>
		</tr>
	</tbody>
	</table>
</div>

<!-- _BOTTOM BUTTONS -->
<div id="cc_contact_tab_buttons" style="position: absolute; visibility: hidden; top: 400px; left: 0px; width: 498px; height: 32px; border: 0px solid #999;">
	<table class="row_off" align="center" width="498px" cellpadding="2" cellspacing="0" border="0">
		<tr>
			<td align="center">
				<input id="cc_contact_save" style="width: 100px;" type="button" value="{cc_contact_save}" onclick="javascript:postFullAdd();">
				<input id="cc_contact_reset" style="width: 100px;" type="button" value="{cc_contact_reset}" onclick="javascript:resetFullAdd();">
				<input id="cc_contact_cancel" style="width: 100px;" type="button" value="{cc_contact_cancel}" onclick="javascript:fullAddWin.close();">
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
<!--
	var fullAdd_onload = document.body.onload;
	var tabs;
	var fullAddWin;
	var photo_frame, photo_form, photo_input;

	var __f = function(e)
	{
		tabs = new dTabsManager({'id': 'cc_contact_tab', 'width': '500px'});
		
		tabs.addTab({'id': 'cc_contact_tab_0', 
					 'name': Element('cc_contact_personal').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});
					 
		tabs.addTab({'id': 'cc_contact_tab_1', 
					 'name': Element('cc_contact_addrs').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});
					 
		tabs.addTab({'id': 'cc_contact_tab_2', 
					 'name': Element('cc_contact_conns').value, 
					 'selectedClass': 'tab_box_active', 
					 'unselectedClass': 'tab_box'});

		fullAddWin = new dJSWin({'id': 'cc_full_add',
		                         'content_id': 'cc_contact_tab',
								 'win_class': 'row_off',
								 'width': '500px',
								 'height': '413px',
								 'title_color': '#3978d6',
								 'title': Element('cc_contact_title').value,
								 'title_text_color': 'white',
								 'button_x_img': Element('cc_phpgw_img_dir').value+'/winclose.gif',
								 'include_contents': new Array('cc_contact_tab_0', 'cc_contact_tab_1', 'cc_contact_tab_2','cc_contact_tab_buttons'),
								 'border': true});

		fullAddWin.draw();
		if (is_ie)
		{
			Element('cc_photo_frame').src = 'cc_photo_frame.html';
			Element('cc_pd_select_photo').style.display='none';
			fullAddWin.open();
			tabs._showTab('cc_contact_tab_0');
			fullAddWin.close();
		}
		else
		{
			Element('cc_pd_select_photo_t').style.display='none';
			Element('cc_pd_select_photo_b').style.display='none';
		}

	};

	if (is_ie) // || is_moz1_6)
	{
			
		document.body.onload = function(e) { setTimeout('__f()', 10); fullAdd_onload ? setTimeout('fullAdd_onload()'): false;};
	}
	else
	{
		__f();
	}

//-->
</script>
<!-- END WINDOW CONTACT -->










<!-- RELATIONS 
<div id="cc_contact_tab_3" class="row_off div_cc_contact_tab">
	<table align="center" width="500px" height="100%" cellpadding="2" cellspacing="0" border="0">
		<tr class="row_off">
			<td align="right"><input style="width: 240px;" type="text"></td>
			<td align="left"><input style="width: 150px;" type="button" value="{cc_btn_search}"></td>
		</tr>
		<tr class="row_on">
			<td align="left">{cc_results}:</td>
			<td align="left">{cc_is_my}:</td>
		</tr>
		<tr class="row_off">
			<td align="right"><select style="width: 240px; height: 150px;" multiple></select></td>
			<td align="left"><select id="cc_rels_type" style="width: 240px; height: 150px;" multiple></select></td>
		</tr>
		<tr class="row_on">
			<td align="right"><input style="width: 150px;" type="button" value="{cc_add_relation}"></td>
			<td align="left"><input style="width: 150px;" type="button" value="{cc_del_relation}"></td>
		</tr>
		<tr class="row_off">
			<td align="center" colspan="2"><select style="width: 480px; height: 120px;" multiple></select></td>
		</tr>
	</table>
</div>
-->
