<!-- BEGIN navbar_header -->
<div id="divLogo"><a href="{logo_url}" target="_blank"><img src="{logo_file}" border="0" title="{logo_title}" alt="eGroupWare"/></a></div>

{app_extra_icons_div}


<div id="divMain">
	<div id="divUpperTabs">
	{upper_tabs}
	</div>

 <div id="divAppIconBar">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-top: 0px">
                        <tr>
                                <td width="178"></td>
                                <td valign="bottom">
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

			</tr>
                </table>
	</div>
	<div id="divStatusBar"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td align="left" id="user_info">{user_info}</td><td align="right" id="admin_info">{current_users}</td></tr></table></div>



<!-- END navbar_header -->


<!-- BEGIN appbox -->	
	<div id="divSubContainer">
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
		<!-- Sidebox Column -->
			{sideboxcolstart}
<!--		<td id="{tdsiteboxid}" valign="top">-->

<!-- END appbox -->

<!-- BEGIN sidebox_hide_header -->
	<!--<script language="javascript" src="SlideOut.js"></script>-->
	<script language="javascript">
		new ypSlideOutMenu("menu2", "right", 0, 103, 160, 200)
	</script>

	<div id="sideboxdragarea" style="position:absolute;left:0px;top:113px">
	<a href="#" {show_menu_event}="ypSlideOutMenu.showMenu('menu2')" onmouseover="//ypSlideOutMenu.showMenu('menu2')" title="{lang_show_menu}"><img src="{img_root}/dragarea_right.png" /></a>
	</div>

	<div id="menu2Container">
	<div id="menu2Content" style="position: relative; left: 0; text-align: left;">
		<table cellspacing="0" cellpadding="0">
		 <tr><td>
		  
		<div style="background-color:#ffffff;border: #17202b 1px solid;padding:5px;">
<!-- END sidebox_hide_header -->



<!-- BEGIN sidebox_hide_footer -->
</div>
</td><td style="padding-top:10px" valign="top">
<a href="#" onClick="ypSlideOutMenu.hide('menu2')" ><img src="{img_root}/dragarea_left.png" align="right" /></a>
</td></tr></table>
</div>
</div>
<!-- END sidebox_hide_footer -->






<!-- BEGIN navbar_footer -->	
		{sideboxcolend}
		<!-- End Sidebox Column -->

		<!-- Applicationbox Column -->
		<td id="tdAppbox" valign="top" {remove_padding}>
		<div id="divAppboxHeader">{current_app_title}</div>
		<div id="divAppbox">
		<table width="100%" cellpadding="0" cellspacing="0">
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
<td width="20" align="center" valign="middle" class="textSideboxStar">{icon_or_star}</td><td class="textSidebox" onmouseover="this.className='textSideboxHover'" onmouseout="this.className='textSidebox'"><a class="textSidebox" href="{item_link}"{target}>{lang_item}</a></td>
</tr>
<!-- END extra_block_row -->

<!-- BEGIN extra_block_row_raw -->
		<tr class="divSideboxEntry">
<td colspan="2">{lang_item}</td>
</tr>
<!-- END extra_block_row_raw -->

<!-- BEGIN extra_block_row_no_link -->
		<tr class="divSideboxEntry">
<td width="20" align="center" valign="middle" class="textSideboxStar">{icon_or_star}</td><td class="textSideboxSpacer">{lang_item}</td>
</tr>
<!-- END extra_block_row_no_link -->

<!-- BEGIN extra_block_spacer -->
<tr class="divSideboxEntry">
<td width="20" align="center" valign="middle" class="textSideboxStar">{icon_or_star}</td><td height="8" class="textSideboxSpacer">&nbsp;</td>
</tr>
<!-- END extra_block_spacer -->
