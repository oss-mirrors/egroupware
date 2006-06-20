<script language="javascript" type="text/javascript">
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

   function rowactive(elid)
   {
		 if(activerow)
		 {
			   document.getElementById(activerow).style.background="";
		 }
		 document.getElementById(elid).style.background="#dddddd";
		 activerow=elid;
   }

   var activeRec;
   function recActive(elid)
   {
		 if(activeRec)
		 {
			   document.getElementById(activeRec).style.borderColor="#dddddd";
		 }
		 document.getElementById(elid).style.borderColor="#dd0000";
		 activeRec=elid;
   }

   /**
   * toggleVisible
   * 
   * @param field $field 
   * @param toggleTo $toggleTo 
   * @param object_id because fields in this form can have different object_ids
   * @access public
   * @return void
   * @todo check status of action
   */
   function toggleVisible(field,toggleTo,object_id)
   {
		 url='<?=$this->xmlhttp_visible_link?>&object_id='+object_id+'&field_name='+field+'&toggleTo='+toggleTo;

		 // branch for native XMLHttpRequest object
		 if (window.XMLHttpRequest) 
		 {
			   req = new XMLHttpRequest();
			   //req.onreadystatechange = eval(func);
			   req.open("GET", url, true);
			   req.send(null);
		 } 
		 // branch for IE/Windows ActiveX version
		 else if (window.ActiveXObject) 
		 {
			   req = new ActiveXObject("Microsoft.XMLHTTP");
			   if (req) 
			   {
					 //req.onreadystatechange = eval(func);
					 req.open("GET", url, true);
					 req.send();
			   }
		 }

		 var newlink;
		 if(toggleTo=='hide')
		 {
			   newlink='<a href="javascript:void(0);" onclick="toggleVisible(\''+field+'\',\'visible\',\'object_id\')"><img src="<?=$this->img_eyehidden?>" alt="" /></a>';
		 }
		 else
		 {
			   newlink='<a href="javascript:void(0);" onclick="toggleVisible(\''+field+'\',\'hide\',\'object_id\')"><img src="<?=$this->img_eyevisible?>" alt="" /></a>';
		 }
		 document.getElementById('visible'+field).innerHTML=newlink;	
   }

   /**
   * toggleVisible
   * 
   * @param field $field 
   * @param toggleTo $toggleTo 
   * @param object_id because fields in this form can have different object_ids
   * @access public
   * @return void
   * @todo check status of action
   */
   function toggleListVisible(field,toggleTo,object_id)
   {
		 url='<?=$this->xmlhttp_listvisible_link?>&object_id='+object_id+'&field_name='+field+'&toggleTo='+toggleTo;

		 // branch for native XMLHttpRequest object
		 if (window.XMLHttpRequest) 
		 {
			   req = new XMLHttpRequest();
			   //req.onreadystatechange = eval(func);
			   req.open("GET", url, true);
			   req.send(null);
		 } 
		 // branch for IE/Windows ActiveX version
		 else if (window.ActiveXObject) 
		 {
			   req = new ActiveXObject("Microsoft.XMLHTTP");
			   if (req) 
			   {
					 //req.onreadystatechange = eval(func);
					 req.open("GET", url, true);
					 req.send();
			   }
		 }

		 var newlink;
		 if(toggleTo=='hide')
		 {
			   newlink='<a href="javascript:void(0);" onclick="toggleListVisible(\''+field+'\',\'visible\',\'object_id\')"><img src="<?=$this->img_eyehidden?>" alt="" /></a>';
		 }
		 else
		 {
			   newlink='<a href="javascript:void(0);" onclick="toggleListVisible(\''+field+'\',\'hide\',\'object_id\')"><img src="<?=$this->img_eyevisible?>" alt="" /></a>';
		 }
		 document.getElementById('listvisible'+field).innerHTML=newlink;	
   }

   /**
   * toggleEnabled
   * 
   * @param field $field 
   * @param toggleTo $toggleTo 
   * @param object_id because fields in this form can have different object_ids
   * @access public
   * @return void
   * @todo check status of action
   */
   function toggleEnabled(field,toggleTo,object_id)
   {
		 url='<?=$this->xmlhttp_enabled_link?>&object_id='+object_id+'&field_name='+field+'&toggleTo='+toggleTo;

		 document.getElementById('debug').innerHTML=url;
		 // branch for native XMLHttpRequest object
		 if (window.XMLHttpRequest) 
		 {
			   req = new XMLHttpRequest();
			   //req.onreadystatechange = eval(func);
			   req.open("GET", url, true);
			   req.send(null);
		 } 
		 // branch for IE/Windows ActiveX version
		 else if (window.ActiveXObject) 
		 {
			   req = new ActiveXObject("Microsoft.XMLHTTP");
			   if (req) 
			   {
					 //req.onreadystatechange = eval(func);
					 req.open("GET", url, true);
					 req.send();
			   }
		 }

		 var newlink;
		 if(toggleTo=='enable')
		 {
			   newlink='<a href="javascript:void(0);" onclick="toggleEnabled(\''+field+'\',\'disable\',\'object_id\')"><img src="<?=$this->img_fld_enabled?>" alt="" /></a>';
		 }
		 else
		 {
			   newlink='<a href="javascript:void(0);" onclick="toggleEnabled(\''+field+'\',\'enable\',\'object_id\')"><img src="<?=$this->img_fld_disabled?>" alt="" /></a>';
		 }
		 document.getElementById('enable'+field).innerHTML=newlink;	
   }

   var snapdist=10;

   function my_DropFunc()
   {
		 // Calculate the snap position which is closest to the drop coordinates

		 var snapY = Math.round(dd.obj.y/snapdist);
		 //if(snapY<0)snapY=0;
		 var y = snapdist*snapY;
		 if(y<dd.elements.fieldcontainer1.y)y=dd.elements.fieldcontainer1.y;

		 var snapX = Math.round(dd.obj.x/snapdist);
		 //if(snapX<0)snapX=0;
		 var x = snapdist*snapX;
		 if(x<dd.elements.fieldcontainer1.x)x=dd.elements.fieldcontainer1.x;

		 var x_store=x-dd.elements.fieldcontainer1.x;
		 var y_store=y-dd.elements.fieldcontainer1.y;
		 // Let the dropped item snap to position
		 dd.obj.moveTo(x, y);

		 var tmpfieldname=dd.obj.name;
		 fieldname=tmpfieldname.substring(8);
		 fieldtype=tmpfieldname.substring(3,8);

		 //alert(fieldname);
		 //alert(fieldtype);

		 document.getElementById('POS'+fieldname+'canvas_'+fieldtype+'_x').value=x_store;
		 document.getElementById('POS'+fieldname+'canvas_'+fieldtype+'_y').value=y_store;

		 //document.getElementById('designinfo').innerHTML="X="+x_store+"<br/>Y="+y_store;
   }



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
		 padding:10px 0px 10px 0px;
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

	  $this->site_object_arr[formwidth]=($this->site_object_arr[formwidth]?$this->site_object_arr[formwidth]:600);
	  $this->site_object_arr[formheight]=($this->site_object_arr[formheight]?$this->site_object_arr[formheight]:1000);
	  unset($this->form_action);
   }
?>

<!-- edit this record button -->
<?php if(!$this->edit_object && !$this->japie):?>
<!--<div style="text-align:right;"><a href="<?=$this->edit_object_link?>"><img src="<?=$this->img_edit?>" alt="" /></a></div>-->
<?php endif?>

<!-- edit buttons -->
<?=$this->devtoolbar?>

<?php if($this->edit_object):?>

<!--<div style="background-color:#ffdbb3;padding:3px;">
   <input type="button" onclick="javascript:location.href='<?=$this->normal_mode_link?>'" style="float:right;" class="egwbutton"  value="<?=lang('Back to normal mode')?>" />

   <input type="button" value="<?=lang('general options')?>" class="egwbutton" onclick="parent.window.open('<?=$this->gen_obj_options_link?>' , 'genobjoptions', 'width=780,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />

   <input type="button" value="<?=lang('object event plugins')?>" class="egwbutton" onclick="parent.window.open('<?=$this->obj_event_plugins_link?>' , 'genobjoptions', 'width=980,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />

   <input type="button" value="<?=lang('relation widgets')?>" class="egwbutton" onclick="parent.window.open('<?=$this->relation_link?>' , 'relwidget', 'width=980,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />
</div>
-->

<div style="background-color:#ffdbb3;padding:3px;margin-top:1px;">
   <input type="button" onclick="javascript:location.href='<?=$this->dev_apply_changes?>'" style="float:right;background-color:#ff0000" class="egwbutton"  value="<?=lang('Apply Changes')?>" />
   <input type="button" value="<?=lang('add form element')?>" class="egwbutton" onclick="parent.window.open('<?=$this->add_element_link?>' , 'addelement', 'width=480,height=300,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />
   &nbsp;|&nbsp;
   <?= lang('Reports');?>
<select id="report_list">
   <?=$this->report_list;?>
</select>
<input class="egwbutton"  type='button' value='<?=lang('Edit');?>' onClick="parent.window.open('<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.edit_report_popup&parent_site_id='.$this->report_vals['parent_site_id'].'&table_name='.$this->report_vals['table_name'].'&report_id=');?>'+document.getElementById('report_list').value, 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')">

<input class="egwbutton"  type='button' value='<?=lang('Delete');?>' onClick="if(window.confirm('<?=lang('Are you sure?');?>'))location='<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boreport.delete_report').'&report_id=';?>'+document.getElementById('report_list').value;">

<input class="egwbutton"  type="button" value="<?=lang('Add');?>" onClick="parent.window.open('<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.add_report_popup').'&parent_site_id='.$this->report_vals['parent_site_id'].'&table_name='.$this->report_vals['table_name'].'&obj_id='.$this->site_object_arr[object_id];?>', 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"/>

</div>
<?php endif?>

<?php 
   // _debug_array($this->site_object_arr);
   if($this->site_object_arr[layoutmethod]=='c')
   {
	  $checked_canvas='checked="checked"';
	  #$this->usecanvas=true;
   }
   else
   {
	  $checked_table='checked="checked"';
   }
?>

<form method="post" name="frm" action="<?=$this->form_action?>" enctype="multipart/form-data" onSubmit="return onSubmitForm()">

   <?php
	  /*
	  <?php if($this->edit_object):?>
	  <div style="background-color:#fff79f">
		 <input type="hidden" name="objectsaved" value="true" />
		 <input type="hidden" name="object_id" value="<?=$this->site_object_arr[object_id]?>" />

		 <?=lang('Form Type')?>: <input <?=$checked_table?> type="radio" value="t" name="formtype" onchange="document.frm.submit();"> <?=lang('Table')?> <input  onchange="document.frm.submit();" type="radio" <?=$checked_canvas?> value="c" name="formtype"> <?=lang('Canvas')?> 
		 <?php if($this->usecanvas):?>
		 &nbsp;&nbsp;&nbsp;<?=lang('Form Height')?><input type="text" name="formheight" size="4" onBlur="dd.elements.fieldcontainer1.resizeTo(document.frm.formwidth.value,document.frm.formheight.value)" value="<?=$this->site_object_arr[formheight]?>">
		 &nbsp;<?=lang('Form Width')?><input type="text" name="formwidth" onBlur="dd.elements.fieldcontainer1.resizeTo(document.frm.formwidth.value,document.frm.formheight.value)" size="4" value="<?=$this->site_object_arr[formwidth]?>">
		 <?php endif?>
		 &nbsp;&nbsp;&nbsp;<input type="submit" value="<?=lang('Save Form Lay-out')?>">
	  </div>

	  <?php endif?>
	  */
   ?>

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
	  <h1 style="margin-bottom:5px;"><?=$this->site_object_arr[name]?></h1>
	  <div style="font-weight:bold;padding-bottom:5px;"><?=$this->site_object_arr[help_information]?></div>

	  <?php
		 $rec_i=0;
		 $row_i=0;
		 //_debug_array($this->records_arr);
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
		 <div xxxonmousedown="recActive('fieldcontainer<?=$rec_i?>')" style="border:dashed 1px #cccccc;margin-bottom:20px;position:relative;width:<?=$this->site_object_arr[formwidth]?>px;height:<?=$this->site_object_arr[formheight]?>px;background-image:url(<?=$this->gridimg?>);" id="fieldcontainer<?=$rec_i?>">
			<?php foreach($record_rows as $r):?>
			<?php
			   if($this->edit_object) $fbgcolor='#fff79f';

			   $setDHTMLstr.=',"divlabel'.$r[fieldname].'"';
			   $setDHTMLstr.=',"divfield'.$r[fieldname].'"';

			   $setlabelx=($r[canvas_label_x]?$r[canvas_label_x]:$labelx);
			   $setfieldx=($r[canvas_field_x]?$r[canvas_field_x]:$fieldx);

			   if($r[canvas_label_y])
			   {
				  $setlabely=$r[canvas_label_y];
			   }
			   else
			   {
				  $setlabely=$setlabely+40;
			   }

			   if($r[canvas_field_y])
			   {
				  $setfieldy=$r[canvas_field_y];
			   }
			   else
			   {
				  $setfieldy=$setfieldy+40;
			   }
			?>

			<?php if($this->edit_object):?>
			<input type="hidden" name="POS<?=$r[fieldname]?>canvas_label_x" value="<?=$setlabelx?>" id="POS<?=$r[fieldname]?>canvas_label_x" />
			<input type="hidden" name="POS<?=$r[fieldname]?>canvas_label_y" value="<?=$setlabely?>" id="POS<?=$r[fieldname]?>canvas_label_y" />
			<input type="hidden" name="POS<?=$r[fieldname]?>canvas_field_x" value="<?=$setfieldx?>" id="POS<?=$r[fieldname]?>canvas_field_x" />
			<input type="hidden" name="POS<?=$r[fieldname]?>canvas_field_y" value="<?=$setfieldy?>" id="POS<?=$r[fieldname]?>canvas_field_y" />


			<input type="hidden" name="FIELDS<?=$r[fieldname]?>" value="<?=$r[fieldname]?>" />
			<?php endif?>

			<div style="background-color:<?=$fbgcolor?>;padding:2px;position:absolute;left:<?=$setlabelx?>px;top:<?=$setlabely?>px;" id="div<?='label'.$r[fieldname]?>">

			   <?php if($this->edit_object && $r[editfieldlink]):?>
			   <a href="javascript:void(0);" onclick="parent.window.open('<?=$r[editfieldlink]?>' , 'poplang_code', 'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"><img src="<?=$this->img_edit?>" alt="" /></a>
			   <?php endif?> 

			   <span style="font-weight:bold"><?=$r[display_name]?>
			   <?php if($r[tooltip_mouseover]):?>
			   <?=//$r[field_help_info]?>
			   <!--<img src="<?=$this->tooltip_img?>" <?=$r[tooltip_mouseover]?> alt="" />-->
			   <?php endif?>

 
			   </span>
			  			</div>

			<div style="background-color:<?=$fbgcolor?>;position:absolute;left:<?=$setfieldx?>px;top:<?=$setfieldy?>px;" id="div<?='field'.$r[fieldname]?>"><?=$r[input]?>
			   <?php if($this->edit_object):?>
			   <img src="<?=$this->draghandle?>" alt="<?=$r[fieldname]?>" title="<?=$r[fieldname]?>" />
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
		 <table id="rec<?=$rec_i?>" style="border:dashed 1px #cccccc;margin-bottom:20px;" xxxonmousedown="recActive('rec<?=$rec_i?>')" align="" class="editrecordtable" style="border-spacing: 0px;" cellpadding="0" cellspacing="0"  >
			<?php if($this->edit_object):?>
			<tr>
			   <td class="propertiescell"></td>
			   <td class="propertiescell"></td>
			   <td class="propertiescell"><img src="<?=$GLOBALS[egw]->common->image('jinn','formicon22')?>" alt="<?=lang('Form')?>" title="<?=lang('Form')?>" /></td>
			   <td class="propertiescell"><img src="<?=$GLOBALS[egw]->common->image('jinn','listicon22')?>" alt="<?=lang('Form')?>" title="<?=lang('List')?>" /></td>
			   <td class="propertiescell"><?=lang("order")?></td>
			   <td class="propertiescell"></td>
			</tr>
			<?php endif?>

			<?php foreach($record_rows as $r):?>
			<?php $row_i++ ?>
			<tr id="TR<?=$row_i?>" xxxonmousedown="rowactive('TR<?=$row_i?>');">
			   <?php if($this->edit_object && $r[editfieldlink]):?>
			   <td style=""  class="propertiescell">
				  <a href="javascript:void(0);" onclick="parent.window.open('<?=$r[editfieldlink]?>' , 'poplang_code', 'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"><img src="<?=$this->img_edit?>" alt="" /></a>
			   </td>
			   <?php if($r['element_type']=='auto'):?>
			   <td id="enable<?=$r['fieldname']?>" class="propertiescell">
				  <?php if($r['disabled']=='disabled'):?>
				  <a href="javascript:void(0);" onclick="toggleEnabled('<?=$r[fieldname]?>','enable','<?=$r['parent_object']?>')"><img src="<?=$this->img_fld_disabled?>" alt="" /></a>
				  <?php else:?>
				  <a href="javascript:void(0);" onclick="toggleEnabled('<?=$r[fieldname]?>','disable','<?=$r[parent_object]?>')"><img src="<?=$this->img_fld_enabled?>" alt="" /></a>
				  <?php endif?>
			   </td>
			   <?php else:?>
			   <td id="delete<?=$r[fieldname]?>" class="propertiescell">
				  <a href="<?=$this->link_delete_element?>&field_name=<?=$r[fieldname]?>" onclick="return window.confirm('<?=lang('Are you sure you want to delete this element?')?>')"><img src="<?=$GLOBALS[egw]->common->image('phpgwapi','close')?>" alt="" /></a>
			   </td>
			   <?php endif?>

			   <td style="" id="visible<?=$r[fieldname]?>" class="propertiescell">
				  <?php if($r[visible]=='hide'):?>
				  <a href="javascript:void(0);" onclick="toggleVisible('<?=$r[fieldname]?>','visible','<?=$r[parent_object]?>')"><img src="<?=$this->img_eyehidden?>" alt="" /></a>
				  <?php else:?>
				  <a href="javascript:void(0);" onclick="toggleVisible('<?=$r[fieldname]?>','hide','<?=$r[parent_object]?>')"><img src="<?=$this->img_eyevisible?>" alt="" /></a>
				  <?php endif?>
			   </td>

			   <td style="" id="listvisible<?=$r[fieldname]?>" class="propertiescell">
				  <?php if($r[listvisible]=='hide'):?>
				  <a href="javascript:void(0);" onclick="toggleListVisible('<?=$r[fieldname]?>','visible','<?=$r[parent_object]?>')"><img src="<?=$this->img_eyehidden?>" alt="" /></a>
				  <?php else:?>
				  <a href="javascript:void(0);" onclick="toggleListVisible('<?=$r[fieldname]?>','hide','<?=$r[parent_object]?>')"><img src="<?=$this->img_eyevisible?>" alt="" /></a>
				  <?php endif?>
			   </td>
			   <td style="" class="propertiescell">
				  <a href="<?=$this->change_field_order_link?>&movefield=<?=$r['fieldname']?>&up=true"><img src="<?=$GLOBALS[egw]->common->image('phpgwapi','up2')?>" alt="<?=lang('move up')?>" title="<?=lang('move up')?>" /></a>
				  <a href="<?=$this->change_field_order_link?>&movefield=<?=$r['fieldname']?>&down=true"><img src="<?=$GLOBALS[egw]->common->image('phpgwapi','down2')?>" alt="<?=lang('move down')?>" title="<?=lang('move down')?>" /></a><?=//$r['form_listing_order'].' '.$r['orig_list_order']?>
			   </td>
			   <?php endif?>

			   <?php if($r[single_col]):?>
			   <td colspan="2" style="" valign="top" >
				  <?php if($r[label_visibility]==1 || $r[label_visibility]==null):?>
				  <span style="font-weight:bold"><?=$r[display_name]?></span><br/>
				  <?php endif?>
				  <?php if($r[field_help_info]):?>
				  <br/>
				  <div style=""><?=$r[field_help_info]?></div>
				  <!-- <img src="<?=$this->tooltip_img?>" <?=$r[tooltip_mouseover]?> alt="" />-->
				  <?php endif?>
				  <?=$r[input]?>
			   </td>
			   <?php else:?>
			   <td style="line-height:130%;width:130px;" valign="top" >
				  <?php if($r[label_visibility]!=0 || $r[label_visibility]==null):?>
				  <span style="font-weight:bold;"><?=$r[display_name]?></span>
				  <?php endif?>
				  <?php if($r[field_help_info]):?>
				  <br/>
				  <div style=""><?=$r[field_help_info]?></div>
				  <!-- <img src="<?=$this->tooltip_img?>" <?=$r[tooltip_mouseover]?> alt="" />-->
				  <?php endif?>
			   </td>
			   <td style=""><?=$r[input]?></td>
			   <?php endif?>
			</tr>
			<?php endforeach?>

		 </table>
		 <?php endif?>

	  </div>
	  <?php endforeach?>

	  <!-- ############################# edit record buttons ############################## -->
	  <?php if(!$this->edit_object):?>

	  <div style="clear:both;height:20px;"></div>

	  <?php if($this->readonly):?>
	  <div style="float:left;width:auto;"><input type="button" name="edit" onClick="location='<?=$this->edit_record_link?>'" class="egwbutton" value="<?=lang('Edit this Record')?>"></div>
	  <?php else:?>
	  <div style="float:left;width:auto;"><input type="submit" name="reopen" class="egwbutton" value="<?=lang('Save')?>"></div>

	  <?php //if($this->max_records)>1 && $this->num_records<$this->max_records):?>
	  <!--	  <div style="float:left;width:auto;"><input  class="egwbutton" type="submit" name="add_new" value="<?=lang('Save and Add New Record')?>"></div>-->
	  <?php //endif?>

	  <!--	  <input type="hidden" name="delete"> <input  class="egwbutton" type="submit"  name="delete" value="<?=lang('Delete')?>">-->
	  <?php endif?>

	  <?php if($this->max_records!=1):?>
	  <div style="float:left;width:auto;"><input type="button" onclick="location='<?=$this->listing_link?>'" name="reopen" class="egwbutton" value="<?=lang('Back to list')?>"></div>
	  <?php endif?>

	  <?php if(!$this->japie):?>
	  <div style="float:right;width:auto;"><input type="button" onclick="openhelp()" name="reopen" class="egwbutton" value="<?=lang('Help')?>"></div>
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
