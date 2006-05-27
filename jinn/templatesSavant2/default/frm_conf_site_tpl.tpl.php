<?php if($this->did_upgrade):?>
<script type="text/javascript" >
	window.location.reload( false );
</script>
<?php endif?>

<script type="text/javascript" src="<?=$GLOBALS['phpgw_info']['server']['webserver_url']?>/phpgwapi/js/tabs/./tabs.js"></script>

<script type="text/javascript" >

   var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

   // init all tabs
   function initAll()
   {
		 tab.init();
		 if(document.frm.currenttab.value)
		 {
			   tab.display(document.frm.currenttab.value);
		 }
	   

		 /*
			set fields according to db type
		 */
		 dbTypeChange(document.getElementById('FLDsite_db_type'),'prod');
		 dbTypeChange(document.getElementById('FLDdev_site_db_type'),'dev');

   } 

   // store current tab so we can open it directly
   function setCurrent(tab)
   {
		 document.frm.currenttab.value=tab;
   }

   function submit_multi_del()
   {
		 if(countSelectedCheckbox()==0)
		 {
			   alert('<?=lang('You must select one or more records for this function.')?>');
		 }
		 else
		 {

			   if(window.confirm('<?=lang('Are you sure you want to delete these multiple records?')?>'))
			   {
					 document.frm.action.value='delete_mult_objects';
					 document.frm.submit();
			   }
			   else
			   {
					 document.frm.action.value='none';

			   }
		 }

   }

   function openhelp()
   {
		 window.open('<?=$this->helplink?>?referer='+encodeURI(location),this.target,'width=800,height=600,scrollbars=yes,resizable=yes'); 
		 return false; 
   }

      function dbTypeChange(element,profile)
   {
		 var target;
		 if(element.value=='egw') target=true;
		 else target=false;

		 if(profile=='prod')
		 {
			   document.getElementById('FLDsite_db_name').disabled=target;	
			   document.getElementById('FLDsite_db_host').disabled=target;	
			   document.getElementById('FLDsite_db_user').disabled=target;	
			   document.getElementById('FLDsite_db_password').disabled=target;	
		 }	
		 else
		 {
			   document.getElementById('FLDdev_site_db_name').disabled=target;	
			   document.getElementById('FLDdev_site_db_host').disabled=target;	
			   document.getElementById('FLDdev_site_db_user').disabled=target;	
			   document.getElementById('FLDdev_site_db_password').disabled=target;	
		 }
   }
 
</script>
<script language="javascript" type="text/javascript">
   function testdbfield()
   {
		 
		 dbvals=document.frm.FLDsite_db_name.value+':'+document.frm.FLDsite_db_host.value+':'+document.frm.FLDsite_db_user.value+':'+document.frm.FLDsite_db_password.value+':'+document.frm.FLDsite_db_type.value  +':'+   document.frm.FLDdev_site_db_name.value+':'+document.frm.FLDdev_site_db_host.value+':'+document.frm.FLDdev_site_db_user.value+':'+document.frm.FLDdev_site_db_password.value+':'+document.frm.FLDdev_site_db_type.value;
		 pathvals=document.frm.FLDupload_path.value+';'+document.frm.FLDdev_upload_path.value+';'+document.frm.FLDupload_url.value+';'+document.frm.FLDdev_upload_url.value;
		 sessionlink='<?=$this->test_access_link?>';
		 link=sessionlink+'&profile='+document.frm.FLDhost_profile.value+'&dbvals='+dbvals+'&pathvals='+pathvals;
		 childWindow=open(link,'', 'width=500,height=300,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no');
		 if (childWindow.opener == null)	childWindow.opener = self;
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
		 background: #EEEEEE;
		 border-top:1px solid #fff;
		 border-bottom:1px solid #aaa;
   }

   #subnav br, #topnav br 
   {
		 clear:both;
   } 

   td
   {
		 vertical-align:top;
   }
</style>

<?php

   /* if configuration is already set use these values */
   if($_POST[submitted])
   {
	  //  	_debug_array($_POST); 
   }
?>
<form name="frm" action="<?=$this->form_action?>" method="post" enctype="multipart/form-data">
   <input type="hidden" name="submitted" value="true">
   <input type="hidden" name="action" value="true">
   <input type="hidden" name="where_key" value="<?=$this->where_key?>">
   <input type="hidden" name="where_value" value="<?=$this->where_value?>">
   <input type="hidden" name="currenttab" id="currenttab" value="<?=$_POST[currenttab]?>">
   <table style="border-spacing: 15px;">
	  <tr>
		 <td><?=lang('Site name')?></td><td style="padding-right:20px;"><input name="FLDsite_name" size="40"  value="<?=$this->site_values['site_name']?>" type="text"></td> 
		 <td ><?=lang('Site id')?></td><td ><input name="FLDsite_id" value="<?=$this->site_values['site_id']?>" type="hidden"><?=($this->site_values['site_id']?$this->site_values['site_id']:lang('New'))?></td>
	  </tr>
	  <tr>
		 <td ><?=lang('Environment profile to use')?></td><td>
		 <?php
			if($this->site_values['host_profile']=='development') 
			{
			   $hp_dev_selected='selected="selected"';
			}
			else
			{
			   $hp_prod_selected='selected="selected"';
			}
		 ?>
		 <select name="FLDhost_profile">
			<option value="production" <?=$hp_prod_selected?>><?=lang('Production')?></option>
			<option value="development" <?=$hp_dev_selected?>><?=lang('Development')?></option>
		 </select>
	  </td>
	  <td><?=lang('Site version')?></td><td><?=($this->site_values['site_version']?$this->site_values['site_version']:0)?></td>
	  <tr>
		 <td><?=lang('Table prefix for data objects')?></td><td ><input name="FLDobject_scan_prefix" size="10"  value="<?=$this->site_values['object_scan_prefix']?>" type="text"></td>

		 <td ><?=lang('Saved with JiNN version')?></td><td ><?=$this->site_values['jinn_version']?></td></tr>

	  </tr>
   </table>

   <div id="topnav">
	  <ul>
		 <li><a href="javascript:void(0);" id="tab1" class="activetab" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); setCurrent(1);return(false);"><?=lang('Production Environment Profile')?></a></li>
		 <li><a href="javascript:void(0);" id="tab2" class="activetab" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); setCurrent(2); return(false);"><?=lang('Development Environment Profile')?></a></li>
	  </ul>
	  <br />
   </div>
   <div id="subnav">
	  <div id="tabcontent1" class="inactivetab" >
		 <table style="border-spacing: 15px;">
			<tr>
			   <td ><?=lang('Database type')?></td>
			   <td >
				  <?php
					 if($this->site_values['site_db_type']=='pgsql') 
					 {
						$site_db_type_pgsql='selected="selected"';
					 }
					 elseif($this->site_values['site_db_type']=='egw')
					 {
						$site_db_type_egw='selected="selected"';
					 }
					 else
					 {
						$site_db_type_mysql='selected="selected"';
					 }
				  ?>
				  <select id="FLDsite_db_type" name="FLDsite_db_type" onchange="dbTypeChange(this,'prod');">
					 <option value="egw" <?=$site_db_type_egw?>>This eGroupware (<?=lang('Only MySQL is supported')?>)</option>
					 <option value="mysql" <?=$site_db_type_mysql?>>MySQL</option>
					 <option value="pgsql" <?=$site_db_type_pgsql?>>PostgreSQL (<?=lang('Very experimental')?>)</option>
				  </select>
			   </td>
			</tr>

			<tr><td ><?=lang('Database name')?></td><td ><input id="FLDsite_db_name" name="FLDsite_db_name" size="30" value="<?=$this->site_values['site_db_name']?>" type="text"></td></tr>
			<tr><td ><?=lang('Database hostname')?></td><td ><input id="FLDsite_db_host" name="FLDsite_db_host" size="30"  value="<?=$this->site_values['site_db_host']?>" type="text"></td></tr>
			<tr><td ><?=lang('Database username')?></td><td ><input id="FLDsite_db_user" name="FLDsite_db_user" size="30"  value="<?=$this->site_values['site_db_user']?>" type="text"></td></tr>
			<tr><td ><?=lang('Database password')?></td><td ><input id="FLDsite_db_password" name="FLDsite_db_password" size="30"  value="<?=$this->site_values['site_db_password']?>" type="text"></td></tr>
			<tr><td ><?=lang('Upload path')?></td><td ><input name="FLDupload_path" size="80"  value="<?=$this->site_values['upload_path']?>" type="text"></td></tr>
			<tr><td ><?=lang('Upload URL')?></td><td ><input name="FLDupload_url" size="80"  value="<?=$this->site_values['upload_url']?>" type="text"></td></tr>
		 </table>

	  </div>
	  <!-- endtab-->

	  <div id="tabcontent2" class="inactivetab">

		 <table style="border-spacing: 15px;">
		<tr>
			   <td ><?=lang('Database type')?></td><td >
				  <?php
					 if($this->site_values['dev_site_db_type']=='pgsql') 
					 {
						$dev_site_db_type_pgsql='selected="selected"';
					 }
					 elseif($this->site_values['site_db_type']=='egw')
					 {
						$dev_site_db_type_egw='selected="selected"';
					 }
	 else
					 {
						$dev_site_db_type_mysql='selected="selected"';
					 }
				  ?>
				  <select id="FLDdev_site_db_type" name="FLDdev_site_db_type" onchange="dbTypeChange(this,'dev');">
					 <option value="egw" <?=$dev_site_db_type_egw?>>This eGroupware (<?=lang('Only MySQL is supported')?>)</option>
					 <option value="mysql" <?=$dev_site_db_type_mysql?>>MySQL</option>
					 <option value="pgsql" <?=$dev_site_db_type_pgsql?>>PostgreSQL (<?=lang('Very experimental')?>)</option>
				  </select>
			   </td>
			</tr>	<tr><td ><?=lang('Database name')?></td><td ><input name="FLDdev_site_db_name" id="FLDdev_site_db_name" size="30"  value="<?=$this->site_values['dev_site_db_name']?>" type="text"></td></tr>
			<tr><td ><?=lang('Database hostname')?></td><td ><input name="FLDdev_site_db_host" id="FLDdev_site_db_host" size="30"  value="<?=$this->site_values['dev_site_db_host']?>" type="text"></td></tr>
			<tr><td ><?=lang('Database username')?></td><td ><input name="FLDdev_site_db_user" id="FLDdev_site_db_user" size="30"  value="<?=$this->site_values['dev_site_db_user']?>" type="text"></td></tr>
			<tr><td ><?=lang('Database password')?></td><td ><input name="FLDdev_site_db_password" id="FLDdev_site_db_password" size="30"  value="<?=$this->site_values['dev_site_db_password']?>" type="text"></td></tr>
			
			<tr><td ><?=lang('Upload path')?></td><td ><input name="FLDdev_upload_path" size="80"  value="<?=$this->site_values['dev_upload_path']?>" type="text"></td></tr>
			<tr><td ><?=lang('Upload URL')?></td><td ><input name="FLDdev_upload_url" size="80"  value="<?=$this->site_values['dev_upload_url']?>" type="text"></td></tr>
		 </table>
	  </div>

   </div>

   <script type="text/JavaScript">
	  initAll();
   </script>

   <div style="padding:10px;">
	  <div style="float:left;width:auto;"><input class="egwbutton" name="continue" value="<?=lang('Save')?>" type="submit"></div>
	  <div style="float:left;width:auto;">
		 <input name="testdbvals" type="hidden">
		 <input class="egwbutton" onclick="testdbfield()" value="<?=lang('test database and paths')?>" type="button">
	  </div>
	  <div style="float:left;width:auto;">
		 <input class="egwbutton" onclick="location='<?=$this->onclick_export?>'" value="<?=lang('export this site')?>" type="button">
		 <input class="egwbutton" onclick="location='<?=$this->onclick_export_to_xml?>'" value="<?= lang('save site to xml')?>" type="button">
	  </div>

	  <div style="float:right;width:auto;"><input type="button" name="reopen" class="egwbutton" value="<?=lang('help')?>" onClick="openhelp();" /></div>
	  <div style="clear:both;height:10px;"></div>
   </div>


   <?php if($this->site_values['site_id']):?>
   <input type="button" value="<?=lang('New Object')?>" class="egwbutton" onclick="parent.window.open('<?=$this->link_add_object?>' , 'genobjoptions', 'width=780,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />

   <input type="button" value="<?=lang('Import Object from File')?>" class="egwbutton" onclick="location='<?=$this->link_import_object?>'" />

   <table border="0" cellspacing="1" cellpadding="0" style="background-color:#ffffff;border:solid 1px #cccccc;margin:3px 0px 3px 0px;">
	  <tr>
		 <td colspan="6" style="font-size:12px;font-weight:bold;padding:2px;border-bottom:solid 1px #006699" align="left"><?=lang('Site Objects in %1',$this->site_values['site_name']);?></td>
	  </tr>
	  <tr>
		 <td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Actions')?></td>
		 <td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Name');?></td>
		 <td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Table');?></td>
		 <td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Relations');?></td>
		 <td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('In Menu');?></td>
		 <td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Max Records');?></td>
	  </tr>

	  <?php if(count($this->object_records)>0):?>
	  <?php foreach($this->object_records as $object_row):?>
	  <?php 
		 if($rowbg=='#e8f0f0') $rowbg='white';
		 else $rowbg='#e8f0f0';
	  ?>
	  <tr valign="top">
		 <td style="padding:0px 4px 0px 2px;width:90px;background-color:<?=$rowbg?>" align="left">
			<input style="border-style: none;" name="objdel<?=$object_row[object_id]?>" value="<?=$object_row[object_id]?>" type="checkbox">
			<a href="<?=$object_row[link_edit]?>" title="<?=lang('edit')?>"><img src=<?=$this->icon_edit?> alt="<?=lang('edit')?>" /></a>
			<a href="<?=$object_row[link_del]?>" onClick="return window.confirm('<?=lang('Do you really want to delete this Site Object?')?>');" title="<?=lang('delete')?>"><img src=<?=$this->icon_del?> alt="<?=lang('delete')?>"  /></a>
			<a href="<?=$object_row[link_export]?>" title="<?=lang('export')?>" ><img src=<?=$this->icon_export?> alt="<?=lang('export')?>" /></a>
			<?php if($object_row[link_upgrade]):?>
			<a href="<?=$object_row[link_upgrade]?>"><?=lang('please upgrade')?></a>
			<?php endif?>
		 </td>
		 <td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$object_row[name]?></td>
		 <td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$object_row[table_name]?></td>
		 <td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>;color:<?=($object_row[old_rel]?'red':'')?>" align="left"><?=($object_row[num_relations]?$object_row[num_relations]:0)?></td>
		 <td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=($object_row[hide_from_menu]?lang('Hidden'):lang('Visible'))?></td>
		 <td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=($object_row[max_records]?1:lang('Unlimited'))?></td>

	  </tr>
	  <?php endforeach?>
	  <tr>
		 <td colspan="6" style="border-top:solid 1px #006699;height:1px;" ></td>
	  </tr>
	  <tr>
		 <td align="left" style="width:90px;padding:0px 4px 0px 2px;background-color:#d3dce3">
			<input title="toggle all above checkboxes" name="CHECKALL" id="CHECKALL" value="TRUE" onclick="doCheckAll(this)" type="checkbox">
			<!--		  <a title="edit all selected records" href="javascript:submit_multi_edit()"><img src="<?=$this->icon_edit?>" alt="edit all selected records" width="16"></a>-->
			<a title="delete all selected records" href="javascript:submit_multi_del()"><img src="<?=$this->icon_del?>" alt="delete all selected records" width="16"></a>
			<!--<a title="export all selected records" href="javascript:submit_multi_export()"><img src="/projects/pim/egw_trunk_pbl/phpgwapi/templates/idots/images/filesave.png" alt="export all selected records" width="16"></a>-->
			<td colspan="5" style="padding:0px 4px 0px 2px;background-color:#d3dce3"><?=lang('Actions to apply on all selected objects')?></td>

		 </tr>
	  </table>

	  <?php else:?>
   </table>
   <?=lang('No objects found for this site.')?>
   <?php endif?>
   <?php endif?>
</form>

