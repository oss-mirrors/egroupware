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
	  <script type="text/javascript" src="<?=$GLOBALS['phpgw_info']['server']['webserver_url']?>/jinn/js/jinn/./displasday_func.js"></script>

	  <script type="text/javascript" >

		 var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

		 // init all tabs
		 function initAll()
		 {
			   tab.init();
			   if(document.popfrm.currenttab.value)
			   {
					 tab.display(document.popfrm.currenttab.value);
			   }
			   <?php if($_POST[parentreload]):?>
			   opener.window.location.href=opener.window.location.href;
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
	  <?=$this->css?>
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
		 <input type="hidden" name="plugin_name" value="<?=$this->plug_name?>">
		 <input type="hidden" name="submitted" value="true">
		 <input type="hidden" name="plugchanges" value="">
		 <input type="hidden" name="uploaddelete" value="">
		 <input type="hidden" name="currenttab" id="currenttab" value="<?=$_POST[currenttab]?>">
		 <div id="divMain">
			<div id="divAppboxHeader"><?=lang('Fieldconfiguration')?>: <?=$this->fieldname?></div>
			<div id="divAppbox">

			   <br/>

			   <div id="topnav">
				  <ul>
					 <li><a href="#" id="tab1" class="activetab" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); setCurrent(1);return(false);"><?=lang('Main Settings')?></a></li>
					 <li><a href="#" id="tab2" class="activetab" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); setCurrent(2); return(false);"><?=lang('Plugins')?></a></li>
				  </ul>
				  <br />
			   </div>
			   <div id="subnav">
				  <div id="tabcontent1" class="inactivetab" >
					 <table style="border-spacing: 15px;">
						<tr>
						   <td><?=lang('Data source')?>:</td>
						   <td><?=$this->data_source?></td>
						</tr>
						<tr>
						   <td><?=lang('Text label')?>:</td>
						   <td><input type="text" name="GENXXXelement_label" value="<?=$set_arr[element_label]?>" /></td>
						</tr>
						<tr>
						   <td><?=lang('Tooltip text')?>:</td>
						   <td><textarea name="GENXXXfield_help_info" rows="3" cols="30"><?=$set_arr[field_help_info]?></textarea></td>
						</tr>
						<tr>
						   <td style="width:150px;"><?=lang('Enabled')?>:</td>
						   <td>
							  <?php
								 if($set_arr[field_enabled]=='0' && $set_arr[field_enabled]!=null)
								 {
									$enabledchecked_off='checked="checked"';	
								 }
								 else
								 {
									$enabledchecked_on='checked="checked"';	
								 }
							  ?>
							  <input <?=$enabledchecked_on?> type="radio" name="GENXXXfield_enabled" value="1" /><?=lang('Yes');?><br/>
							  <input <?=$enabledchecked_off?> type="radio" name="GENXXXfield_enabled" value="0" /><?=lang('No');?><br/>
						   </td>
						</tr>
						<tr>
						   <td style="width:150px;"><?=lang('Form Visibility')?>:</td>
						   <td>
							  <?php
								 if($set_arr[form_visibility]=='0' && $set_arr[form_visibility]!=null)
								 {
									$formvchecked_off='checked="checked"';	
								 }
								 else
								 {
									$formvchecked_on='checked="checked"';	
								 }
							  ?>
							  <input <?=$formvchecked_on?> type="radio" name="GENXXXform_visibility" value="1" /><?=lang('Yes');?><br/>
							  <input <?=$formvchecked_off?>type="radio" name="GENXXXform_visibility" value="0" /><?=lang('No');?><br/>
						   </td>
						</tr>
						<tr>
						   <td style="width:150px;"><?=lang('List Visibility')?>:</td>
						   <td>
							  <?php
								 if($set_arr[list_visibility]=='0')
								 {
									$listvchecked_off='checked="checked"';	
								 }
								 else
								 {
									$listvchecked_on='checked="checked"';	
								 }
							  ?>
							  <input <?=$listvchecked_on?> type="radio" name="GENXXXlist_visibility" value="1" /><?=lang('Yes');?><br/>
							  <input <?=$listvchecked_off?> type="radio" name="GENXXXlist_visibility" value="0" /><?=lang('No');?><br/>
						   </td>
						</tr>
						<tr>
						   <td style="width:150px;"><?=lang('Label Visibility')?>:</td>
						   <td>
							  <?php
								 if($set_arr[label_visibility]=='0')
								 {
									$labelvchecked_off='checked="checked"';	
								 }
								 else
								 {
									$labelvchecked_on='checked="checked"';	
								 }
							  ?>
							  <input <?=$labelvchecked_on?> type="radio" name="GENXXXlabel_visibility" value="1" /><?=lang('Yes');?><br/>
							  <input <?=$labelvchecked_off?> type="radio" name="GENXXXlabel_visibility" value="0" /><?=lang('No');?><br/>
						   </td>
						</tr>
						<tr>
						   <td style="width:150px;"><?=lang('Span columns')?>:</td>
						   <td>
							  <?php
								 if($set_arr[single_col]=='0')
								 {
									$single_colvchecked_off='checked="checked"';	
								 }
								 else
								 {
									$single_colvchecked_on='checked="checked"';	
								 }
							  ?>
							  <input <?=$single_colvchecked_on?> type="radio" name="GENXXXsingle_col" value="1" /><?=lang('Yes');?><br/>
							  <input <?=$single_colvchecked_off?> type="radio" name="GENXXXsingle_col" value="0" /><?=lang('No');?><br/>
						   </td>
						</tr>
						<tr>
						   <td style="width:150px;"><?=lang('Read Only')?>:</td>
						   <td>
							  <?php
								 if($set_arr[fe_readonly]=='1')
								 {
									$readonlychecked_on='checked="checked"';	
								 }
								 else
								 {
									$readonlychecked_off='checked="checked"';	
								 }
							  ?>
							  <input <?=$readonlychecked_on?>  type="radio" name="GENXXXfe_readonly" value="1" /><?=lang('Yes');?><br/>
							  <input <?=$readonlychecked_off?> type="radio" name="GENXXXfe_readonly" value="0" /><?=lang('No');?><br/>
						   </td>
						</tr>
						<tr>
						   <td></td>
						   <td></td>
						</tr>
						<tr>
						   <td></td>
						   <td></td>
						</tr>
					 </table>
				  </div>
				  <!-- endtab-->

				  <div id="tabcontent2" class="inactivetab">

					 <?=lang('Change plugin')?> 

					 <?php
					 ?>
					 <select name="newplug" onchange="changeplugin();">
						<?php foreach($this->avail_plugins_arr as $avail_plug):?>
						<?php 
						   unset($selected_cp);
						   if($avail_plug[value]==$this->plug_name)
						   {
							  $selected_cp='selected="selected"';
						   }
						?>
						<option <?=$selected_cp ?> value="<?=$avail_plug[value]?>"><?=$avail_plug[name]?></option>
						<?php endforeach?>
					 </select>

					 <hr>
					 <!--startplug-->
					 <table style="font-weight:bold;">
						<tr>
						   <td><?=$this->lang_plugin_name?>:</td>
						   <td><?=$this->plug_title?></td>
						</tr>
						<tr>
						   <td><?=$this->lang_version?>:</td>
						   <td><?=$this->plug_version?></td>
						</tr>
					 </table>
					 <p><?=$this->plug_descr?></p>
					 <p><?=$this->plug_help?></p><br/>
					 <?php //_debug_array($this->plug_reg_conf_arr);?>
					 <table style="border-spacing: 19px;" align="center" cellpadding="3" cellspacing="3" >
						<?php echo($this->plugjes);?>
					 </div>
				  </table>
				  <!--endplug-->
			   </div>
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
			   <?php 
				  $parentreloadchecked='checked="checked"'; 
			   ?>
			   <?=lang('Reload Parent Window')?><input class="egwbutton" name="parentreload" <?=$parentreloadchecked?> type="checkbox" value="true" />
			   <input class="egwbutton"  type="submit" value="<?=lang('save')?>"  />
			   <input class="egwbutton"  type="button" value="<?=lang('close')?>" onClick="self.close()" />
			</div>
		 </div>
		 <!-- end maindiv -->


	  </form>
   </body>
</html>
