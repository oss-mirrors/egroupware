<!-- BEGIN navbar_header -->
<div id="divLogo"><a href="http://{logo_url}" target="_blank"><img src="{logo_file}" border="0" alt="eGroupWare"/></a></div>

{app_extra_icons_div}

<div id="divMain">
	<div id="divAppIconBar">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="180" valign="top" align="left"><img src="{img_root}/grey-pixel.png" width="1" height="68" alt="spacer" /></td>
				<td>
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							{app_icons}
						</tr>
						<tr>
							{app_titles}
						</tr>
					</table>

				</td>

				{app_extra_icons_icon}
	
				<td width="1" valign="top" align="right"><img src="{img_root}/grey-pixel.png" width="1" height="68" alt="spacer" /></td>
			</tr>
		</table>
	</div>
	<div id="divStatusBar"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td align="left" id="user_info">{user_info}</td><td align="right" id="admin_info">{current_users}</td></tr></table></div>
	<div id="divSubContainer">
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<!-- Sidebox Column -->
				<td id="tdSidebox" valign="top">

						<!-- start blocks -->
<!-- END navbar_header -->


<!-- BEGIN navbar_footer -->	
						</td>
		<!-- End Sidebox Column -->

		<!-- Applicationbox Column -->
		<td id="tdAppbox" valign="top">
		<div id="divAppboxHeader">{current_app_title}</div>
		<div id="divAppbox">
		<table width="98%" cellpadding"0" cellspacing="0">
		<tr><td>
<!-- END navbar_footer -->


<!-- BEGIN extra_blocks_header -->
<div class="divSidebox">
	<div class="divSideboxHeader"><span>{lang_title}</span></div>
	<div>
		<table width="100%" cellspacing="0" cellpadding="0">
<!-- END extra_blocks_header -->


<!-- BEGIN extra_blocks_footer -->
	</table>	
		</div>
		</div>

		<div class="sideboxSpace"></div>
<!-- END extra_blocks_footer -->



<!-- BEGIN extra_block_row -->
		<tr class="divSideboxEntry">
<td width="20" align="center" valign="middle" class="textSidebox">{icon_or_star}</td><td class="textSidebox"><a class="textSidebox" href="{item_link}"{target}>{lang_item}</a></td>
</tr>
<!-- END extra_block_row -->


<!-- BEGIN extra_block_spacer -->
<tr> 
	<td colspan="2" height="8"></td>
</tr>
<!-- END extra_block_spacer -->
