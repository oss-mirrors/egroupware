<link rel="stylesheet" type="text/css" href="{cc_css}">
<script src="js/cc.js" type="text/javascript"></script>

<!-- JS MESSAGES -->
<input id="cc_msg_no_cards" type="hidden" value="{cc_msg_no_cards}">
<input id="cc_msg_err_no_room" type="hidden" value="{cc_msg_err_no_room}">

<input id="cc_root_dir" type="hidden" value="{cc_root_dir}">
<input id="cc_msg_card_new" type="hidden" value="{cc_msg_card_new}">
<input id="cc_msg_card_edit" type="hidden" value="{cc_msg_card_edit}">
<input id="cc_msg_card_remove" type="hidden" value="{cc_msg_card_remove}">
<input id="cc_msg_card_remove_confirm" type="hidden" value="{cc_msg_card_remove_confirm}">
					
<input id="cc_panel_search_text" type="hidden" value="{cc_panel_search}" style="cursor: pointer; cursor: hand;" onclick="javascript:ccSearch()" /> 
<!-- END JS MESSAGES -->

<!-- VIEW CARDS -->
<table id="cc_main" width="100%" border="0" style="border: 0px solid #999;">
	<tr width="100%">
		<td id="cc_left" style="width: 200px" valign="top" style="border: 0px solid #999;">
			<table id="cc_left_main" width="100%" height="400px" cellpadding="1" cellspacing="1" border="0">
				<!-- __TREE -->
				<tr>
					<td class="th" align="center" valign="top">{cc_catalogs}</td>
				</tr>
				<tr>
					<td style="width: 200px;" height="100%" align="left" valign="top"> 
						<div id="cc_tree" style="position: relative; width: 200px; height: 100%; border: 0px solid #999; overflow: auto;"></div>
					</td>
				</tr>
				<!-- __END TREE -->
			</table>
		</td>
		<td id="cc_right" width="100%" height="100%" align="center" valign="top" style="border: 0px solid #999;">
			<table width="100%" border="0">
				<!-- ___PANEL -->
			<!--	<tr>
					<td align="center" colspan="5">
						<table width="100%" border="0">
							<tr valign="top">
								<td id="show_view_cards_tab_0" class="tab_box_active" onclick="show_view_cards_tab(0);">{cc_view_name}</td>
								<td id="show_view_cards_tab_1" class="tab_box" onclick="show_view_cards_tab(1);">{cc_view_company}</td>
								<td id="show_view_cards_tab_2" class="tab_box" onclick="show_view_cards_tab(2);">{cc_view_location}</td>
							</tr>
						</table>
					</td>
				</tr>-->
				<tr align="left">
					<td align="left">
					<input id="cc_quick_add" type="button" value="{cc_quick_add}" />
					<input type="button" value="{cc_panel_new}" style="cursor: pointer; cursor: hand;" onclick="newContact();" />
					<div id="cc_panel_search_call" style="display: inline"></div>
					<div id="cc_panel_table" style="display: none;">
					<input type="button" value="{cc_panel_table}" class="tab_box" style="cursor: pointer; cursor: hand;" onclick="javascript: ccChangeVisualization('table')" />
					</div>
					<div id="cc_panel_cards" style="display: none; text-align: right">
					<input type="button" value="{cc_panel_cards}" class="tab_box" style="cursor: pointer; cursor: hand;" onclick="javascript: ccChangeVisualization('cards')" />
					</div>
					</td>
				</tr>
				<tr>
					<td align="center" colspan="1">
					<div id="cc_panel_letters" style="display:inline">
						<table width="100%" border="0">
							<tr valign="top">
								<td id="cc_letter_0" class="letter_box" onclick="showCards('number', getActualPage()); selectLetter(0);">123...</td>
								<td id="cc_letter_1" class="letter_box_active" onclick="showCards('a', getActualPage()); selectLetter(1);">A</td>
								<td id="cc_letter_2" class="letter_box" onclick="showCards('b', getActualPage()); selectLetter(2);">B</td>
								<td id="cc_letter_3" class="letter_box" onclick="showCards('c', getActualPage()); selectLetter(3);">C</td>
								<td id="cc_letter_4" class="letter_box" onclick="showCards('d', getActualPage()); selectLetter(4);">D</td>
								<td id="cc_letter_5" class="letter_box" onclick="showCards('e', getActualPage()); selectLetter(5);">E</td>
								<td id="cc_letter_6" class="letter_box" onclick="showCards('f', getActualPage()); selectLetter(6);">F</td>
								<td id="cc_letter_7" class="letter_box" onclick="showCards('g', getActualPage()); selectLetter(7);">G</td>
								<td id="cc_letter_8" class="letter_box" onclick="showCards('h', getActualPage()); selectLetter(8);">H</td>
								<td id="cc_letter_9" class="letter_box" onclick="showCards('i', getActualPage()); selectLetter(9);">I</td>
								<td id="cc_letter_10" class="letter_box" onclick="showCards('j', getActualPage()); selectLetter(10);">J</td>
								<td id="cc_letter_11" class="letter_box" onclick="showCards('k', getActualPage()); selectLetter(11);">K</td>
								<td id="cc_letter_12" class="letter_box" onclick="showCards('l', getActualPage()); selectLetter(12);">L</td>
								<td id="cc_letter_13" class="letter_box" onclick="showCards('m', getActualPage()); selectLetter(13);">M</td>
								<td id="cc_letter_14" class="letter_box" onclick="showCards('n', getActualPage()); selectLetter(14);">N</td>
								<td id="cc_letter_15" class="letter_box" onclick="showCards('o', getActualPage()); selectLetter(15);">O</td>
								<td id="cc_letter_16" class="letter_box" onclick="showCards('p', getActualPage()); selectLetter(16);">P</td>
								<td id="cc_letter_17" class="letter_box" onclick="showCards('q', getActualPage()); selectLetter(17);">Q</td>
								<td id="cc_letter_18" class="letter_box" onclick="showCards('r', getActualPage()); selectLetter(18);">R</td>
								<td id="cc_letter_19" class="letter_box" onclick="showCards('s', getActualPage()); selectLetter(19);">S</td>
								<td id="cc_letter_20" class="letter_box" onclick="showCards('t', getActualPage()); selectLetter(20);">T</td>
								<td id="cc_letter_21" class="letter_box" onclick="showCards('u', getActualPage()); selectLetter(21);">U</td>
								<td id="cc_letter_22" class="letter_box" onclick="showCards('v', getActualPage()); selectLetter(22);">V</td>
								<td id="cc_letter_23" class="letter_box" onclick="showCards('w', getActualPage()); selectLetter(23);">W</td>
								<td id="cc_letter_24" class="letter_box" onclick="showCards('x', getActualPage()); selectLetter(24);">X</td>
								<td id="cc_letter_25" class="letter_box" onclick="showCards('y', getActualPage()); selectLetter(25);">Y</td>
								<td id="cc_letter_26" class="letter_box" onclick="showCards('z', getActualPage()); selectLetter(26);">Z</td>
								<td id="cc_letter_27" class="letter_box" onclick="showCards('all', getActualPage()); selectLetter(27);">{cc_all}</td>
							</tr>
						</table>
					</div>
					<div id="cc_panel_search" style="display: none">
						<table width="100%" border="0">
							<tr valign="top">
								<td class="letter_box" style="width: 10%" onclick="ccSearchHide()">123ABC...</td>
								<td class="letter_box_active" style="text-align: center; text-valign: middle">{cc_panel_search_found}</td>
							</tr>
						</table>
					</div>
					</td>
				</tr>
				<tr class="th">
					<td colspan="1">
						<table cellpadding="0" cellspacing="0" width="100%" border="0">
							<tr>
								<td>
									<table bgcolor="#D3DCE3" cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td align="left" valign="middle"><span id="cc_panel_arrow_first"><img src="templates/default/images/first-grey.png" border="0" alt="{cc_panel_first_page}" hspace="2" onclick="javascript:false"/></span></td>
										</tr>
									</table>
								</td>
								<td width="2%" align="right" valign="center">
									<table bgcolor="#D3DCE3" cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td align="left" valign="middle"><span id="cc_panel_arrow_previous"><img src="templates/default/images/left-grey.png" border="0" alt="{cc_panel_previous_page}" hspace="2" onclick="" /></span></td>
										</tr>
									</table>
								</td>
								<td align="center" bgcolor="#D3DCE3" valign="center" width="92%">
									<table bgcolor="#D3DCE3" cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td id="cc_panel_pages"></td>
										</tr>
									</table>
								</td>
								<td width="2%" align="left" valign="center">
									<table bgcolor="#D3DCE3" cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td align="left" valign="middle"><span id="cc_panel_arrow_next"><img src="templates/default/images/right-grey.png" border="0" alt="{cc_panel_next_page}" hspace="2" /></span></td>
										</tr>
									</table>
								</td>
								<td width="2%" align="right" valign="center">
									<table bgcolor="#D3DCE3" cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td align="left" valign="middle"><span id="cc_panel_arrow_last"><img src="templates/default/images/last-grey.png" border="0" alt="{cc_panel_last_page}" hspace="2" /></span></td>
										</tr>
									</table>			
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- ___END PANEL -->
				<!-- __CARDS -->
				<tr>
					<td style="border: 0px solid #666;" id="cc_card_space" width="100%" height="350px" valign="top" align="center" colspan="3"></td>
				</tr>
				<!-- __END CARDS -->
			</table>
		</td>
	</tr>
</table>
<!-- _END VIEW CARDS -->


<!-- BOTTOM DETAILS-->
<script type="text/javascript">
		var Main_pre_load = document.body.onload;
		var ccSearch, ccTree;

		var Main_load = function () 
		{
			Connector.setProgressBox(Element('cc_loading'), true);
			Connector.setProgressHolder(Element('cc_loading_inner'));

			/* Associate the Quick Add Button with the Plugin */
			ccQuickAdd.associateAsButton(Element('cc_quick_add'));
			ccQuickAdd.afterSave = function ()
			{
				updateCards();
			}

			/* Create the Search Object */
			var search_params = new Array();
			search_params['holder'] = Element('cc_panel_search_call');
			search_params['total_width'] = '280px';
			search_params['input_width'] = '200px';
			search_params['progress_top'] = '150px';
			search_params['progress_left'] = '-260px';
			search_params['progress_color'] = '#3978d6';
			search_params['progress_width'] = '250px';
			search_params['conn_1_msg'] = Element('cc_loading_1').value;
			search_params['conn_2_msg'] = Element('cc_loading_2').value;
			search_params['conn_3_msg'] = Element('cc_loading_3').value;
			search_params['button_text'] = Element('cc_panel_search_text').value;
			search_params['Connector'] = Connector;

			ccSearch = new ccSearchClass(search_params);
			ccSearch.DOMresult.style.visibility = 'hidden';
			ccSearch.onSearchFinish = ccSearchUpdate;

			Connector.setProgressBox(Element('cc_loading'), true);
			Connector.setProgressHolder(Element('cc_loading_inner'));
			
			/* Create the Tree Object */
			ccTree = new ccCatalogTree({name: 'ccTree', id_destination: 'cc_tree', afterSetCatalog: 'ccSearchHidePanel(); updateCards()'});

			ccTree.Connector = Connector;
		}

		if (is_ie)
		{
			document.body.onload = function (e)
			{
				Main_pre_load();
				Main_load();
			}
		}
		else
		{
			Main_load();
		}
		
</script>
<!-- END BOTTOM DETAILS-->
