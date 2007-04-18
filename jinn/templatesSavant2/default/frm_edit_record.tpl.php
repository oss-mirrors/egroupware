<script language="javascript" type="text/javascript">
   <?php if($this->edit_object):?>
   var design_object=true;
   <?php else:?>
   var design_object=false;
   <?php endif?>
   var thisobjectid='<?=$this->site_object_arr['object_id']?>';
   function check_m2o_form()
   {
		 if(typeof block_parent_save !='undefined' && block_parent_save )
		 {
			   alert('<?=lang('You must save or close the subform to be able to save this record.')?>');
			   return false;
		 }
		 return true;
   }

   function img_popup(img,pop_width,pop_height,attr)
   {
		 options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
		 parent.window.open("<?=$this->popuplink?>&path="+img+"&attr="+attr, "pop", options);
   }

   //FIXME: move to general js file
   function openhelp()
   {
		 window.open('<?=$this->helplink?>?referer='+encodeURI(location),this.target,'width=800,height=600,scrollbars=yes,resizable=yes'); 
		 return false; 
   }

   function onSubmitForm() 
   {
		 var valid = true;

		 for(var i = 0; i < document.frm.length; i++)
		 {
			   var element = document.frm.elements[i];
		 }

		 // here php put extra things to do on submit 
		 <?=$this->submit_script?>
		 // end php 

		 return true;
   }

   var activerow;
   function rowactive(elid,field)
   {
		 if(!design_object)
		 {
			   return;
		 }
		 if(activerow)
		 {
			   document.getElementById(activerow).style.background="";
		 }
		 document.getElementById(elid).style.background="#dddddd";
		 activerow=elid;

		 xajax_doXMLHTTP("jinn.ajaxjinn.getPanelFieldProps",field,thisobjectid);
   }

   var activeRec;
   function recActive(elid)
   {
		 return;
		 if(activeRec)
		 {
			   document.getElementById(activeRec).style.borderColor="#dddddd";
		 }
		 document.getElementById(elid).style.borderColor="#dd0000";
		 activeRec=elid;
   }

   function toggleFieldVisProp(field,toggleTo,object_id,prop)
   {
		 xajax_doXMLHTTP("jinn.ajaxjinn.toggleFieldVisProp",field,toggleTo,object_id,prop);
   }

   function toggleFieldEnabled(field,toggleTo,object_id)
   {
		 xajax_doXMLHTTP("jinn.ajaxjinn.toggleFieldEnabled",field,toggleTo,object_id);
   }

   var snapdist=10;

</script>

<?=$this->m2ojavascript?>

<style>
   table.editrecordtable tr td
   {
		 padding:10px 10px 10px 10px;
		 vertical-align:top;
		 border:solid 0px green;
   }

   td.propertiescell
   {
		 padding:3px 0px 3px 0px;
   }

   table.m2o_list tr td
   {
		 padding:0px 0px 0px 0px;
		 vertical-align:top;
		 border:solid 0px green;
   } 

   h1
   {
		 font-size:28px;
   }
</style>

<?php 
   if($this->edit_object)
   {
	  $_record_arr[]=$this->records_arr[0];
	  $this->records_arr=$_record_arr;

	  $this->site_object_arr['formwidth']=($this->site_object_arr['formwidth']?$this->site_object_arr['formwidth']:600);
	  $this->site_object_arr['formheight']=($this->site_object_arr['formheight']?$this->site_object_arr['formheight']:1000);
	  unset($this->form_action);
   }
?>

<!-- edit buttons -->
<?=$this->devtoolbar?>

<?php if($this->edit_object):?>

<div style="background-color:#ffdbb3;padding:3px;margin-top:1px;">
   <input type="button" value="<?=lang('add form element')?>" class="egwbutton" onclick="parent.window.open('<?=$this->add_element_link?>' , 'addelement', 'width=480,height=300,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />
   &nbsp;|&nbsp;
   <?= lang('Reports');?>
   <select id="report_list">
	  <?=$this->report_list;?>
   </select>
   <input class="egwbutton"  type='button' value='<?=lang('Edit');?>' onClick="parent.window.open('<?=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uireport.edit_report_popup&parent_site_id='.$this->report_vals['parent_site_id'].'&table_name='.$this->report_vals['table_name'].'&report_id=');?>'+document.getElementById('report_list').value, 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')">

   <input class="egwbutton"  type='button' value='<?=lang('Delete');?>' onClick="if(window.confirm('<?=lang('Are you sure?');?>'))location='<?=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.boreport.delete_report').'&report_id=';?>'+document.getElementById('report_list').value;">

   <input class="egwbutton"  type="button" value="<?=lang('Add');?>" onClick="parent.window.open('<?=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uireport.add_report_popup').'&parent_site_id='.$this->report_vals['parent_site_id'].'&table_name='.$this->report_vals['table_name'].'&obj_id='.$this->site_object_arr['object_id'];?>', 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"/>

</div>
<?php endif?>

<?php 
   $checked_table='checked="checked"';
?>

<form method="post" name="frm" action="<?=$this->form_action?>" enctype="multipart/form-data" onSubmit="return onSubmitForm()">

   <?=$this->extrahiddens?>
   <input type="hidden" name="submitted" value="true" />
   <?php if($this->where_string_form):?>
   <input type="hidden" name="where_string" value="<?=$this->where_string_form?>" />
   <?php endif?>	


   <?php if(is_array($this->where_string_record_arr)):?>
   <input type="hidden" name="MLTNUM" value="<?=$this->mult_records?>">
   <?php foreach($this->where_string_record_arr as $where_rec_name => $where_rec_value):?>
   <input type="hidden" name="<?=$where_rec_name?>" value="<?=$where_rec_value?>" />
   <?php endforeach?>
   <?php endif?>

   <div style="padding:0px 30px 0px 30px;">
	  <h1 style="margin-bottom:5px;"><?=$this->site_object_arr['name']?></h1>
	  <div style="font-weight:bold;padding-bottom:5px;"><?=$this->site_object_arr['help_information']?></div>

	  <!-- BEGIN change_num -->
	  <?php if(!$this->edit_object && !is_array($this->where_string_record_arr) && !$this->where_string_form):?>
	  <input type="hidden" name="MLTNUM" value="<?=$this->mult_records?>" />
	  <?php if($this->max_records!=1):?>
	  <input type="hidden" name="changerecnumbers" />
	  <input type="text" name="num_records" maxlength="2" size="2" value="<?=$this->mult_records?>" />
	  <input  class="egwbutton" type="submit" value="<?= lang('change number of records')?>" onclick="document.frm.changerecnumbers.value='true'" />
	  <?php endif?>

	  <?php endif?>

	  <!-- END change_num -->
	  <?php if(count($this->records_arr[0])>10 || $this->mult_records>1):?>
	  <?php if($this->readonly and !$this->site_object_arr['disable_edit_rec']):?>
	  <div style="float:left;width:auto;"><input type="button" name="edit" onClick="location='<?=$this->edit_record_link?>'" class="egwbutton" value="<?=lang('Edit this Record')?>"></div>
	  <?php elseif(!$this->site_object_arr['disable_edit_rec']):?>
	  <div style="margin:5px;">
		 <input type="submit" onclick="return check_m2o_form();" name="savereopen" class="egwbutton" value="<?=lang('Save')?>">
		 <input type="submit" onclick="return check_m2o_form();" name="savefinish" class="egwbutton" value="<?=lang('Save and finish')?>">

		 <?php if(!$this->readonly):?>
		 <input type="button" onclick="location='<?=$this->listing_link?>'" name="finish" class="egwbutton" value="<?=lang('finish, discard changes')?>">
		 <?php endif?>

	  </div>

	  <?php endif?>
	  <?php endif?>
	  <?php
		 $rec_i=0;
		 $row_i=0;
	  ?>

	  <?php foreach($this->records_arr as $record_rows):?>
	  <?php $rec_i++ ?>

	  <div style="float:left;margin:0px 20px 0px 0px">

		 <?php if($this->mult_records > 1):?>
		 <div style="background-color:#cccccc;width:70px;padding:3px;"><?=lang('Record %1',$rec_i)?></div>
		 <?php endif?>

		 <?php if($this->usecanvas):?>
		 <!-- design canvas -->
		 <?php
			$labelx=20;
			$fieldx=150;
			$setfieldy=0;
			$setlabely=0;
		 ?>

		 <div style="display:none;" id="designinfo">X=?<br/>Y=?</div>
		 <div onmousedown="recActive('fieldcontainer<?=$rec_i?>')" style="border:dashed 1px #cccccc;margin-bottom:20px;position:relative;width:<?=$this->site_object_arr['formwidth']?>px;height:<?=$this->site_object_arr['formheight']?>px;background-image:url(<?=$this->gridimg?>);" id="fieldcontainer<?=$rec_i?>">
			<?php foreach($record_rows as $r):?>
			<?php
			   if($this->edit_object) $fbgcolor='#fff79f';

			   $setDHTMLstr.=',"divlabel'.$r['fieldname'].'"';
			   $setDHTMLstr.=',"divfield'.$r['fieldname'].'"';

			   $setlabelx=($r['canvas_label_x']?$r['canvas_label_x']:$labelx);
			   $setfieldx=($r['canvas_field_x']?$r['canvas_field_x']:$fieldx);

			   if($r['canvas_label_y'])
			   {
				  $setlabely=$r['canvas_label_y'];
			   }
			   else
			   {
				  $setlabely=$setlabely+40;
			   }

			   if($r['canvas_field_y'])
			   {
				  $setfieldy=$r['canvas_field_y'];
			   }
			   else
			   {
				  $setfieldy=$setfieldy+40;
			   }
			?>

			<?php if($this->edit_object):?>
			<input type="hidden" name="POS<?=$r['fieldname']?>canvas_label_x" value="<?=$setlabelx?>" id="POS<?=$r['fieldname']?>canvas_label_x" />
			<input type="hidden" name="POS<?=$r['fieldname']?>canvas_label_y" value="<?=$setlabely?>" id="POS<?=$r['fieldname']?>canvas_label_y" />
			<input type="hidden" name="POS<?=$r['fieldname']?>canvas_field_x" value="<?=$setfieldx?>" id="POS<?=$r['fieldname']?>canvas_field_x" />
			<input type="hidden" name="POS<?=$r['fieldname']?>canvas_field_y" value="<?=$setfieldy?>" id="POS<?=$r['fieldname']?>canvas_field_y" />


			<input type="hidden" name="FIELDS<?=$r['fieldname']?>" value="<?=$r['fieldname']?>" />
			<?php endif?>

			<div style="background-color:<?=$fbgcolor?>;padding:2px;position:absolute;left:<?=$setlabelx?>px;top:<?=$setlabely?>px;" id="div<?='label'.$r['fieldname']?>">

			   <?php if($this->edit_object && $r['editfieldlink']):?>
			   <a href="javascript:void(0);" onclick="parent.window.open('<?=$r['editfieldlink']?>' , 'poplang_code', 'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"><img src="<?=$this->img_edit?>" alt="" /></a>
			   <?php endif?> 

			   <span style="font-weight:bold"><?=$r['display_name']?></span>
			</div>

			<div style="background-color:<?=$fbgcolor?>;position:absolute;left:<?=$setfieldx?>px;top:<?=$setfieldy?>px;" id="div<?='field'.$r['fieldname']?>"><?=$r['input']?>
			   <?php if($this->edit_object):?>
			   <img src="<?=$this->draghandle?>" alt="<?=$r['fieldname']?>" title="<?=$r['fieldname']?>" />
			   <?php endif?>
			</div>

			<?php endforeach?>

		 </div>

		 <?php if($this->edit_object):?>
		 <script language="javascript" type="text/javascript">
			SET_DHTML(CURSOR_MOVE,'fieldcontainer1'+NO_DRAG <?=$setDHTMLstr?>);
			<?=$moveto?>
		 </script>
		 <?php endif?>

		 <?php endif?>

		 <?php if(!$this->usecanvas):?>
		 <!-- WHEN WE NO NOTHING WE USE A SIMPLE TABLE TO LAY_OUT THE FORM -->

		 <!-- OUR DEVELOPERS PANEL -->
		 <?php if($this->edit_object):?>
		 <div style="float:left;">
			<style>
			   .paneltable 
			   {
					 border-spacing:0;
					 border-top:solid 1px #cccccc;
					 border-left:solid 1px #cccccc;
					 margin-right:10px;
					 margin-left:10px;
			   }
			   .paneltable td
			   {
					 margin-top
					 padding:2px;
					 border-bottom:solid 1px #cccccc;
					 border-right:solid 1px #cccccc;
			   }

			</style>
			<table class="paneltable">
			   <tr style="font-weight:bold;">
				  <td style="width:100px"><?=lang('Name')?></td>
				  <td style="width:150px"><?=lang('Value')?></td>
			   </tr>
			   <tr>
				  <td><?=lang('Name')?></td>
				  <td id="panel_fname"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Field Type')?></td>
				  <td id="panel_ftype"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Enabled')?></td>
				  <td id="panel_fenabled"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Label')?></td>
				  <td id="panel_flabel"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Label Visibility')?></td>
				  <td id="panel_flabel_visibility"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Help Info')?></td>
				  <td id="panel_fhelpinfo"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Order')?></td>
				  <td id="panel_forder"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Form Visibility')?></td>
				  <td id="panel_fform_visibility"></td>
			   </tr>
			   <tr>
				  <td><?=lang('List Visibility')?></td>
				  <td id="panel_flist_visibility"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Span Column')?></td>
				  <td id="panel_fsingle_col"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Read Only')?></td>
				  <td id="panel_ffe_readonly"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Data Source')?></td>
				  <td id="panel_fdata_source"></td>
			   </tr>
			   <tr>
				  <td><?=lang('Delete')?></td>
				  <td id="panel_del"></td>
			   </tr>
			   <!--	   <tr>
				  <td><?=lang('Debug')?></td>
				  <td id="panel_debug"></td>
			   </tr>-->
			</table>
		 </div>
		 <?php endif?>

		 <table id="rec<?=$rec_i?>" style="border:dashed 1px #cccccc;margin-bottom:20px;" onmousedown="recActive('rec<?=$rec_i?>')" align="" class="editrecordtable" style="border-spacing: 0px;" cellpadding="0" cellspacing="0"  >
			<?php if($this->edit_object):?>
			<tr>
			   <td class="propertiescell"></td>
			   <td class="propertiescell"><?=lang("order")?></td>
			   <td class="propertiescell"></td>
			</tr>
			<?php endif?>

			<?php foreach($record_rows as $r):?>
			<?php $row_i++ ?>
			<tr id="TR<?=$row_i?>" onmousedown="rowactive('TR<?=$row_i?>','<?=$r['fieldname']?>');">
			   <?php if($this->edit_object && $r['editfieldlink']):?>
			   <td style=""  class="propertiescell">
				  <a href="javascript:void(0);" onclick="parent.window.open('<?=$r['editfieldlink']?>' , 'poplang_code', 'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"><img src="<?=$this->img_edit?>" alt="" /></a>
			   </td>
			   <td style="" class="propertiescell">
				  <a href="<?=$this->change_field_order_link?>&movefield=<?=$r['fieldname']?>&up=true"><img src="<?=$GLOBALS['egw']->common->image('phpgwapi','up2')?>" alt="<?=lang('move up')?>" title="<?=lang('move up')?>" /></a>
				  <a href="<?=$this->change_field_order_link?>&movefield=<?=$r['fieldname']?>&down=true"><img src="<?=$GLOBALS['egw']->common->image('phpgwapi','down2')?>" alt="<?=lang('move down')?>" title="<?=lang('move down')?>" /></a>
			   </td>
			   <?php endif?>

			   <?php if($r['single_col']):?>
			   <td colspan="2" style="" valign="top" >
				  <?php if($r['label_visibility']==1 || $r['label_visibility']==null):?>
				  <span style="font-weight:bold"><?=$r['display_name']?></span><br/>
				  <?php endif?>
				  <?php if($r['field_help_info']):?>
				  <br/>
				  <div style=""><?=$r['field_help_info']?></div>
				  <?php endif?>
				  <?=$r['input']?>
			   </td>
			   <?php else:?>
			   <td style="line-height:130%;width:130px;" valign="top" >
				  <?php if($r['label_visibility']!=0 || $r['label_visibility']==null):?>
				  <span style="font-weight:bold;"><?=$r['display_name']?></span>
				  <?php elseif($this->edit_object):?>
				  <span style="font-weight:bold;font-style:italic"><?=$r['display_name']?></span><br/>(<?=lang('Label hidden')?>)
				  <?php endif?>
				  <?php if($r['field_help_info']):?>
				  <br/>
				  <div style=""><?=$r['field_help_info']?></div>
				  <?php endif?>
			   </td>
			   <td style="" id="<?=$r['fieldname']?>"><?=$r['input']?></td>
			   <?php endif?>
			</tr>
			<?php endforeach?>

		 </table>
		 <div style="clear:both"></div>
		 <?php endif?>

	  </div>
	  <?php endforeach?>

	  <!-- ############################# edit record buttons ############################## -->
	  <?php if(!$this->edit_object):?>

	  <div style="clear:both;height:20px;"></div>

	  <?php if($this->readonly and !$this->site_object_arr['disable_edit_rec']):?>
	  <div style="float:left;width:auto;"><input type="button" name="edit" onClick="location='<?=$this->edit_record_link?>'" class="egwbutton" value="<?=lang('Edit this Record')?>"></div>
	  <?php elseif(!$this->site_object_arr['disable_edit_rec']):?>

	  <div style="float:left;width:auto;">
		 <input type="submit" onclick="return check_m2o_form();" name="savereopen" class="egwbutton" value="<?=lang('Save')?>">
		 <input type="submit" onclick="return check_m2o_form();" name="savefinish" class="egwbutton" value="<?=lang('Save and finish')?>">
	  </div>

	  <?php endif?>

	  <?php if(!$this->readonly):?>
	  <div style="float:left;width:auto;"><input type="button" onclick="location='<?=$this->listing_link?>'" name="finish" class="egwbutton" value="<?=lang('finish, discard changes')?>"></div>
	  <?php endif?>

	  <?php if(!$this->japie):?>
	  <div style="float:right;width:auto;"><input type="button" onclick="openhelp()" name="help" class="egwbutton" value="<?=lang('Help')?>"></div>
	  <?php endif?>

	  <?=$this->runonrecordbuttons?>

	  <div style="clear:both;height:10px;"></div>
	  <!-- ############################# edit record buttons ############################## -->
	  <?php endif?>

   </div>
   <span id="debug"></span>

   <?=$this->hiddenfields?>

   <script language="JavaScript">
	  <?=$this->jshidefields?>
   </script>

</form>
