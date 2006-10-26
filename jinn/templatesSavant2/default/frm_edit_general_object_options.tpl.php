<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?=$this->lang?>" xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title><?=$this->website_title?></title>
	  <meta http-equiv="content-type" content="text/html; charset=<?=$this->charset?>" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="description" content="eGroupware" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="copyright" content="eGroupWare http://www.egroupware.org (c) 2005" />
	  <meta name="author" content="eGroupWare http://www.egroupware.org" />
	  <meta name="robots" content="none" />
	  <link rel="icon" href="<?=$this->img_icon?>" type="image/x-ico" />
	  <link rel="shortcut icon" href="<?=$this->img_shortcut?>" />
	  <link href="<?=$this->theme_css?>" type="text/css" rel="StyleSheet" />
	  <script type="text/javascript" src="<?=$GLOBALS['phpgw_info']['server']['webserver_url']?>/phpgwapi/js/tabs/./tabs.js"></script>

	  <script type="text/javascript" >

		 var tab = new Tabs(1,'activetab','inactivetab','tab','tabcontent','','','tabpage');

		 // init all tabs
		 function initAll()
		 {
			   tab.init();
			   if(document.popfrm.currenttab.value)
			   {
					 tab.display(document.popfrm.currenttab.value);
			   }

			   <?php if($_POST[submitted]):?>
			   opener.window.location.href=opener.window.location.href+'&this_site=true';
			   <?php endif?>
		 } 

		 // store current tab so we can open it directly
		 function setCurrent(tab)
		 {
			   document.popfrm.currenttab.value=tab;
		 }

		 // this set the plugchanges field so the class know it has changed
		 function changeplugin()
		 {
			   document.popfrm.plugchanges.value="true";
			   document.popfrm.submit();
		 }
	  </script>
	  <style type="text/css">

		 div.activetab
		 { 
			   display:block; 
			   background-color:#EEEEEE;
			   padding:10px;

		 }
		 div.inactivetab
		 { 
			   display:none; 
		 }

		 body {
			   color: #333;
			   background-color: #eeeeee;
			   /*			   padding: 0 5%;*/
			   margin:0;
			   font-family: arial, geneva, lucida, sans-serif;
			   font-size:83.333%;
		 }

		 a:link, a:visited {
			   text-decoration:none;
			   font-weight:bold;
			   color: #FF4000;
		 }

		 a:hover {
			   color:#002c99;
		 }

		 #topnav {
			   margin:0;
			   padding: 0 0 0 12px;
		 }

		 #topnav ul 
		 {
			   list-style: none;
			   margin: 0;
			   padding: 0;
			   border: none;
		 } 

		 #topnav li,
		 li.inactivetab{
			   display: block;
			   margin: 0;
			   padding: 0;
			   float:left;
			   width:auto;
		 }

		 #topnav A {
			   color:#444;
			   display:block;
			   width:auto;
			   text-decoration:none;
			   background: #BBBBBB;
			   margin:0;
			   padding: 2px 10px;
			   border-left: 1px solid #fff;
			   border-top: 1px solid #fff;
			   border-right: 1px solid #aaa;
		 }

		 #topnav A:hover, 
		 #topnav A:active 
		 {
			   background: #EEEEEE;
		 }

		 #topnav A.activetab:visited,#topnav A.activetab:link,#topnav A.here:link, #topnav A.here:visited {
			   position:relative;
			   z-index:102;
			   background: #EEEEEE;
			   font-weight:bold;
		 }

		 #subnav
		 {
			   position:relative;
			   top:-1px;
			   z-index:101;
			   margin:0;
			   /*padding: 0px 0 3px 0;*/
			   background: #EEEEEE;
			   border-top:1px solid #fff;
			   border-bottom:1px solid #aaa;
		 }

		 #subnav BR, #topnav BR 
		 {
			   clear:both;
		 } 

		 td
		 {
			   vertical-align:top;
		 }
	  </style>
   </head>

   <body onload="initAll()">

	  <?php

		 /* if configuration is already set use these values */
		 if($_POST[submitted])
		 {
			$set_arr=$this->post_general;
		 }
		 elseif($this->field_conf_arr)
		 {
			$set_arr=$this->field_conf_arr;
		 }
	  ?>
	  <form name="popfrm" action="<?=$this->action?>" method="post" enctype="multipart/form-data">
		 <input type="hidden" name="submitted" value="true">
		 <input type="hidden" name="plugchanges" value="">
		 <input type="hidden" name="currenttab" id="currenttab" value="<?=$_POST[currenttab]?>">
		 <input type=hidden name="FLDparent_site_id" value="<?=$this->parent_site_id;?>">
		 <div id="divMain">
			<div id="divAppboxHeader"><?=lang('General Objects Settings')?>: <?=$this->object_name?></div>
			<div id="divAppbox">

			   <br/>

			   <div id="topnav">
				  <ul>
					 <li><a href="#" id="tab1" class="activetab" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); setCurrent(1);return(false);"><?=lang('Main Settings')?></a></li>
					 <!--				 <li><a href="#" id="tab2" class="activetab" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); setCurrent(2); return(false);"><?=lang('Plugins')?></a></li>-->
				  </ul>
				  <br />
			   </div>
			   <div id="subnav">
				  <div id="tabcontent1" class="inactivetab" >

					 <input type="hidden" name="where_key" value="<?= $this->where_key;?>">
					 <input type="hidden" name="where_value" value="<?= $this->where_value;?>">

					 <table align="" cellspacing="2" cellpadding="2" style="border-spacing: 15px;">
						<!--		  <tr>
						   <td><?= lang('Object id');?></td>
						   <td><input type="hidden" name="FLDobject_id" value="<?=$this->where_value;?>"><?=$this->where_value;?></td>
						</tr>
						<tr>
						   <td  ><?= lang('Parent site id');?></td>
						   <td ><input type=hidden name="FLDparent_site_id" value="<?=$this->global_values['parent_site_id'];?>"><?=$this->global_values['parent_site_id'];?></td>
						</tr>
						-->
						<tr>
						   <td  ><?= lang('Name');?></td>
						   <td ><input type="text" name="FLDname" size="40" input_max_length value="<?= $this->global_values['name']?>"></td>
						</tr>
						<tr>
						   <td  ><?= lang('Table name');?></td>
						   <td >
							  <select name="FLDtable_name" onchange="<?php if($_GET['new']) echo 'return;';?>alert('<?=lang('If you change the table of this object you\n may loose field configuration data.\n\n\n Think of this before you save these settings.')?>')">
								 <?php  foreach($this->tables as $table):?>
								 <?php if($table['table_name']==$this->global_values[table_name]):?>
								 <option value="<?=$table['table_name'];?>" selected="selected"><?=$table['table_name'];?></option>
								 <?php  else:?>
								 <option value="<?=$table['table_name'];?>"><?=$table['table_name'];?></option>
								 <?php endif?>
								 <?php endforeach?>
							  </select>
						   </td>
						</tr>
						<!--				
						<tr>
						   <td  ><?= lang('Upload path');?></td>
						   <td ><input type="text" name="FLDupload_path" size="40" $input_max_length value="<?=$this->global_values['upload_path']?>"></td>
						</tr>
						<tr>
						   <td  ><?= lang('Development (Test) site upload path');?></td>
						   <td ><input type="text" name="FLDdev_upload_path" size="40" input_max_length value="<?=$this->global_values['dev_upload_path']?>"></td>
						</tr>
						-->
						<tr>
						   <td  ><?= lang('Max. records');?></td>
						   <td >
							  <select name="FLDmax_records">
								 <?php if($this->global_values['max_records'] == 1): ?>
								 <option value=""><?= lang('unlimited');?></option>
								 <option  value="1"selected="selected"><?= lang('only one');?></option>
								 <?php  else: ?>
								 <option value=""  selected="selected"><?= lang('unlimited');?></option>
								 <option  value="1"><?= lang('only one');?></option>			 
								 <?php endif?>
							  </select>
						   </td>
						</tr>
						<tr>
						   <td  ><?= lang('Hide from object menu');?></td>
						   <td >
							  <select name="FLDhide_from_menu">
								 <?  if($this->global_values['hide_from_menu'] == 1):?>
								 <option value=""><?= lang('No');?></option>
								 <option  value="1" selected="selected"><?= lang('Yes, hide from menu');?></option>
								 <?php  else:?>
								 <option value=""selected="selected"><?= lang('No');?></option>
								 <option  value="1" ><?= lang('Yes, hide from menu');?></option>
								 <?php endif?>
							  </select>
						   </td>
						</tr>
						<!--
						<tr>
						   <td  ><?= lang('Upload url');?></td>
						   <td ><input type="text" name="FLDupload_url" size="40" input_max_length value="<?=$this->global_values['upload_url']?>"></td>
						</tr>
						<tr>
						   <td  ><?= lang('Dev upload url');?></td>
						   <td ><input type="text" name="FLDdev_upload_url" size="40" input_max_length value="<?=$this->global_values['dev_upload_url']?>"></td>
						</tr>
						-->
						<tr>
						   <td  ><?= lang('Extra where sql filter');?></td>
						   <td ><textarea name="FLDextra_where_sql_filter" cols="60" rows="2"><?=$this->global_values['extra_where_sql_filter']?></textarea></td>
						</tr>
						<tr>
						   <td  ><?= lang('Informative description');?></td>
						   <td ><textarea name="FLDhelp_information" cols="60" rows="2"><?=$this->global_values['help_information']?></textarea></td>
						</tr>

					 </table>

					 <table align="" cellspacing="2" cellpadding="2" style="border-spacing: 15px;">

						<?php
						   $chk='checked="checked"';
						   if(!$this->global_values['disable_multi']) 		$checked_disable_multi = $chk;
						   if(!$this->global_values['disable_create_rec'])		$checked_disable_create_rec = $chk;
						   if(!$this->global_values['disable_del_rec'])			$checked_disable_del_rec = $chk;
						   if(!$this->global_values['disable_edit_rec'])		$checked_disable_edit_rec = $chk;
						   if(!$this->global_values['disable_view_rec'])		$checked_disable_view_rec = $chk;
						   if(!$this->global_values['disable_copy_rec'])		$checked_disable_copy_rec = $chk;
						   if(!$this->global_values['disable_reports']) 		$checked_disable_reports = $chk;
						   if(!$this->global_values['disable_simple_search'])  	$checked_disable_simple_search = $chk;
						   if(!$this->global_values['disable_filters']) 		$checked_disable_filters = $chk;
						   if(!$this->global_values['disable_export'])  		$checked_disable_export = $chk;
						   if(!$this->global_values['disable_import'])  		$checked_disable_import = $chk;
						?>
						<tr>
						   <td><?=lang('Enable Multiple Record Actions');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_multi" value="1"/>
							  <input type="checkbox" <?=$checked_disable_multi?>  name="FLDdisable_multi" value="0"/>
						   </td>
						   <td><?= lang('Enable Reports');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_reports" value="1"/>
							  <input type="checkbox" <?=$checked_disable_reports?>  name="FLDdisable_reports" value="0"/>
						   </td>
						</tr>
						<tr>
						   <td><?= lang('Enable Create Record');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_create_rec" value="1"/>
							  <input type="checkbox" <?=$checked_disable_create_rec?> name="FLDdisable_create_rec" value="0"/>
						   </td>
						   <td><?= lang('Enable Simple Search');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_simple_search" value="1"/>
							  <input type="checkbox" <?=$checked_disable_simple_search?> name="FLDdisable_simple_search" value="0"/>
						   </td>
						</tr>
						<tr>
						   <td><?= lang('Enable Delete Record');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_del_rec" value="1"/>
							  <input type="checkbox" <?=$checked_disable_del_rec?> name="FLDdisable_del_rec" value="0"/>
						   </td>
						   <td><?= lang('Enable Filters');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_filters" value="1"/>
							  <input type="checkbox" <?=$checked_disable_filters?>  name="FLDdisable_filters" value="0"/>
						   </td>
						</tr>
						<tr>
						   <td><?= lang('Enable Edit Record');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_edit_rec" value="1"/>
							  <input type="checkbox" <?=$checked_disable_edit_rec?> name="FLDdisable_edit_rec" value="0"/>
						   </td>
						   <td><?= lang('Enable Exports');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_export" value="1"/>
							  <input type="checkbox" <?=$checked_disable_export?> name="FLDdisable_export" value="0"/>
						   </td>
						</tr>
						<tr>
						   <td><?= lang('Enable View Records');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_view_rec" value="1"/>
							  <input type="checkbox" <?=$checked_disable_view_rec?>  name="FLDdisable_view_rec" value="0"/>
						   </td>
						   <td><?= lang('Enable Imports');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_import" value="1"/>
							  <input type="checkbox" <?=$checked_disable_import?>  name="FLDdisable_import" value="0"/>
						   </td>
						</tr>
						<tr>
						   <td><?= lang('Enable Copy Record');?></td>
						   <td>
							  <input type="hidden" name="FLDdisable_copy_rec" value="1"/>
							  <input type="checkbox" <?=$checked_disable_copy_rec?> name="FLDdisable_copy_rec" value="0"/>
						   </td>
						</tr>
						<tr>
						</tr>
						<tr>
						</tr>
						<tr>
						</tr>
						<tr>
						</tr>
						<tr>
						</tr>

					 </table> 

				  </div>
				  <!-- endtab-->

				  <!--
				  <div id="tabcontent3" class="inactivetab">
					 <h1>TAB 3</h1>
				  </div>

				  <div id="tabcontent4" class="inactivetab">
					 <h1>TAB 4</h1>
				  </div>
				  -->

			   </div>

			   <script type="text/JavaScript">
			   </script>
			   <br/>
			   <div align="center" style="<?=$this->buttons_visibility?>">
				  <input class="egwbutton"  type="submit" value="<?=lang('save')?>"  onclick="" />
				  <input class="egwbutton"  type="button" value="<?=lang('close')?>" onClick="self.close()" />
			   </div>
			</div>
			<!-- end maindiv -->


		 </form>
	  </body>
   </html>

