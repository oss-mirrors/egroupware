<?=$this->devtoolbar?>
<!-- edit this record button -->
<?php if(!$this->edit_object && !$this->japie):?>
<div style="text-align:right;"><a href="<?=$this->edit_object_link?>"><img src="<?=$this->img_edit?>" alt="" /></a></div>
<?php endif?>

<script language=JavaScript src="jinn/js/jinn/display_func.js" type=text/javascript></script>
<script language="javascript" type="text/javascript">
   <!--
   function submit_multi(action)
   {
		 if(countSelectedCheckbox()==0)
		 {
			   alert('<?=lang('You must select one or more records for this function.')?>');
		 }
		 else
		 {
			   if(action=='del')
			   {
					 if(window.confirm('<?=lang('Are you sure you want to delete these multiple records?') ?>'))
					 {
						   document.frm.action.value='del';
						   document.frm.submit();
					 }
					 else
					 {
						   document.frm.action.value='none';
					 }
			   }
			   else
			   {
					 document.frm.action.value=action;
					 document.frm.submit();
			   }
		 }
   }

   function img_popup(img,pop_width,pop_height,attr)
   {
		 options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
		 parent.window.open("<?=$this->popuplink ?>&path="+img+"&attr="+attr, "pop", options);
   }

   var curr_live=Array();

   function callLiveFieldEdit(cell,wherestring,field,object_id)
   {	 
		 if(curr_live['cell']!=cell)
		 {
			   callLiveFieldSave()	
		 }
		 //check if we can use ajax
		 if(document.getElementById('fieldinfo_noajax_'+field).value=='')
		 {
			   xajax_doXMLHTTP("jinn.ajaxjinn.editSingleField",cell,wherestring,field,object_id);
			   
			   curr_live['cell']=cell;
			   curr_live['wherestring']=wherestring;
			   curr_live['field']=field;
			   curr_live['object_id']=object_id;

		 }
		 else
		 {
		 }

		 //call ajax to edit cell
		 //else open popup to edit cell
		 //set new value in cell
   }

   function callLiveFieldSave()
   {
		 /*
		 if(document.getElementsByName('FLDXXX'+curr_live['cell'])!='undefined')
		 {
			   _arr=document.getElementsByName('FLDXXX'+curr_live['cell']);
			   xajax_doXMLHTTP("jinn.ajaxjinn.saveSingleField",_arr[0].value,curr_live['wherestring'],curr_live['field'],curr_live['object_id']);
			   document.getElementById(curr_live['cell']).innerHTML=_arr[0].value;

			   curr_live=Array();
		 }
		 */
   }

   function hoverCell(cell,inout,offcolor)
   {
		 if(inout=='in')
		 {
			   cell.style.backgroundColor='#ffff00';
		 }
		 else
		 {
			   cell.style.backgroundColor=offcolor;
		 }
   }

   //-->
</script>

<!-- SEARCH & FILTERS BLOCK -->

<table cellpadding="0" cellspacing="0" style="border:solid 0px #cccccc">
   <tr>
	  <td align="" style="padding-left:0px;">
		 <?php if($this->enable_simple_search):?>
		 <form name="simpleform" action="<?=$this->menu_action ?>" method="post"><?=lang('search for string') ?>&nbsp;<input id="quick_filter_input" style="<?=$this->quick_filter_bgcolor ?>" type="text" size="20" name="quick_filter" value="<?=$this->search_string ?>">
			<input type="hidden" name="quick_filter_hidden" value="1">
			<input type='submit'value='<?=lang('Search'); ?>' class="egwbutton">&nbsp;
			<input type='submit'  <?=($this->search_string?'':'disabled="disabled"')?>  onclick="document.getElementById('quick_filter_input').value='';" value='<?=lang('Remove Search Filter'); ?>' class="egwbutton">&nbsp;&nbsp;&nbsp;
		 </form>	
		 <?php endif?>
	  </td>
	  <td align="center" style="padding-left:0px;">
		 <?php if($this->enable_filters):?>
		 <form name="filterform" action="<?=$this->filter_action ?>" method="post"><?=$this->filter_text ?>&nbsp;
			<select name="filtername" style="<?=$this->adv_filter_bgcolor ?>" onChange="document.filterform.action = document.filterform.refresh_url.value; submit();">
			   <?=$this->filter_list ?>
			</select>
			<input type="hidden" name="refresh_url" value="<?=$this->refresh_url ?>">
			<input type="submit" value="<?=$this->filter_edit ?>" class="egwbutton">
		 </form>	
		 <?php endif?>
	  </td>
   </tr>
</table>

<!-- END SEARCH & FILTERS BLOCK -->

<?=$this->reportblock?>

<!-- BEGIN header_end -->

<br/>
<style>
   .jinnListBlock
   {
		 margin:3px 0px 3px 0px;
		 background-color:#ffffff;
		 border-top:solid 1px #cccccc; 
		 border-bottom:solid 1px #cccccc; 
		 border-left:solid 1px #cccccc; 
		 border-right:solid 1px #cccccc; 
   }
</style>
<br/>
<?php if($this->enable_create_rec):?>
<input type="button" value="<?=lang('Add new Record') ?>" onClick="location.href='<?=$this->newrec_link ?>'" style="width:150px;"/>
<?php endif?>
<?=$this->walklistblock?>

<div class="jinnListBlock">
   <table border="0" style="border-spacing:1px;" align="center" width="100%" >
	  <tr>
		 <td style="padding:2px;border-bottom:solid 1px #006699" align="left">
			<table cellspacing="0" cellpadding="0" width="100%">
			   <tr>
				  <td style="padding:5px;font-size:16px;font-weight:bold;" colspan="2"><?=$this->table_title ?></td>
				  <td style="text-align:right;padding:5px;">
					 <?=lang('Records per page');?>
					 <?php
						$recperpagechecked[$this->rec_per_page]='selected="selected"';
					 ?>
					 <select id="recperpage" name="recperpage" onchange="location.href='<?=$this->menu_action?>&recperpage='+document.getElementById('recperpage').value">
						<option <?=$recperpagechecked[10] ?>>10</option>
						<option <?=$recperpagechecked[25] ?>>25</option>
						<option <?=$recperpagechecked[50] ?>>50</option>
						<option <?=$recperpagechecked[100] ?>>100</option>
					 </select>&nbsp;&nbsp;&nbsp;

					 <?php if(!$this->japie):?>
					 <a href="<?=$this->config_columns_link?>" title="<?=lang('Show/Hide Columns')?>">&gt;&gt;</a>
					 <?php endif?>
					 
				  </td>
			   </tr>
			   <tr>
				  <td style="padding:5px;font-size:12px;font-weight:normal;"><?=$this->table_descr ?></td>
				  <td style="font-size:10px;font-weight:normal" align="center"><?=$this->pager ?></td>
				  <td style="font-size:10px;font-weight:normal" align="right"><?=$this->total_records ?></td>
			   </tr>
			</table>
		 </td>
	  </tr>
   </table>
   
   <div style="padding:2px 10px 2px 10px;background-color:#ffcccc;display:none;" id="warnscrolls"><?=lang('Scrollbar activated')?></div>
   <div style="overflow:auto;" id="recordscontentdiv">
	  <form name="frm" action="<?=$this->list_form_action ?>" method="post">
		 <input type="hidden" name="action" value="none">
		 <table id="recordscontenttable" border="0" cellspacing="1" cellpadding="0" width="100%" style="padding-bottom:3px;border-bottom:solid 1px #006699">
			<tr>
			   <td bgcolor="<?=$this->th_bg ?>" colspan="<?=($this->action_colspan+$this->runonrec_amount)?>"  valign="top" style="width:1%;font-weight:bold;padding:3px 5px 3px 5px;"></td>

			   <?php if(is_array($this->colnames)):?>
			   <?php $colidx=0;?>
			   <?php foreach($this->colnames as $colname):?> 
			   <input type="hidden" id="fieldinfo_noajax_<?=$colname['fieldname']?>" value="<?=$colname['noajax']?>">

			   <td bgcolor="<?=$colname['colhead_bg_color'] ?>" style="font-weight:bold;padding:3px;" align="center"><a href="<?=$colname['colhead_order_link'] ?>"><?=$colname['colhead_name'] ?>&nbsp;<?=$colname['colhead_order_by_img'] ?></a><?=$colname['tipmouseover'] ?></td>

			   <?php endforeach?>
			   <?php endif?>
			</tr>

			<?php if(count($this->records_rows_arr)>0):?>
			<?php
				$rowidx=0;
			?>
			<?php foreach($this->records_rows_arr as $recrow_arr):?>
			<?php
			   $rowidx++;
			?>

			<!-- BEGIN row -->
			<tr valign="top">
			   <?php if($this->enable_multi):?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><input style="border-style:none;" type="checkbox" name="<?=$recrow_arr['colfield_check_name'] ?>" value="<?=$recrow_arr['colfield_check_val'] ?>"/></td>
			   <?php endif?>

			   <?php if($this->enable_view_rec):?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('view') ?>" href="<?=$recrow_arr['colfield_view_link'] ?>"><img width="16" src="<?=$this->colfield_view_img_src ?>" alt="<?=lang('view') ?>" /></a></td>
			   <?php endif?>

			   <?php if($this->enable_edit_rec):?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('edit')?>" href="<?=$recrow_arr['colfield_edit_link'] ?>"><img width="16" src="<?=$this->colfield_edit_img_src ?>" alt="<?=lang('edit')?>" /></a></td>
			   <?php endif?>

			   <?php if($this->enable_copy_rec):?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('copy record') ?>" href="<?=$recrow_arr['colfield_copy_link'] ?>" onClick="return window.confirm('<?=lang('Do you want to copy this record?') ?>')"><img width="19" src="<?=$this->colfield_copy_img_src ?>" alt="<?=lang('copy record') ?>" /></a></td>
			   <?php endif?>

			   <?php if($this->enable_del):?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('delete') ?>" href="<?=$recrow_arr['colfield_delete_link'] ?>" onClick="return window.confirm('<?=$this->colfield_lang_confirm_delete_one ?>')"><img width="16" src="<?=$this->colfield_delete_img_src ?>" alt="<?=lang('delete') ?>" /></a></td>
			   <?php endif?>

			   <!-- RunOnRec Icons -->
			   <?php if(is_array($recrow_arr['runonrec_arr'])):?>
			   <?php foreach($recrow_arr['runonrec_arr'] as $runonrec_arr):?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" valign="top"><?=$runonrec_arr ?></td>
			   <?php endforeach?>
			   <?php endif?>

			   <!-- Field values -->
			   <?php if(is_array($recrow_arr['fields'])):?>
			   <?php
				  $colidx=0;
			   ?>
			   <?php foreach($recrow_arr['fields'] as $field_arr):?>
			   <?php
				  $colidx++;
			   ?>
			   <td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" id="cell<?=$rowidx?>x<?=$colidx?>" onmouseout="hoverCell(this,'out','')" onmouseover="hoverCell(this,'in')" onclick="callLiveFieldEdit('cell<?=$rowidx?>x<?=$colidx?>','<?=$recrow_arr['colfield_wherestring']?>','<?=$field_arr['name'] ?>','<?=$this->object_id?>')" valign="top" style="padding:0px 2px 0px 2px"><?php //_debug_array($field_arr);?><?=$field_arr['value'] ?></td>
			   <?php endforeach?>
			   <?php endif?>

			</tr>
			<!-- END row -->
			<?php endforeach?>
			<?php else:?>

			<tr><td colspan="<?=(count($this->colnames)+5)?>" style="padding:20px 10px 20px 10px;font-weight:bold;text-align:center;vertical-align:middle;">&nbsp;<?=lang('No records found') ?></td></tr>		   
			<?php endif?>

		 </table>

		 <!-- BEGIN table footer --> 
		 <table width="100%" cellspacing="1" cellpadding="0">

			<?php if(count($this->records_rows_arr)>0):?>
			<tr valign="top" bgcolor="<?=$this->colhead_bg_color ?>">

			   <?php if($this->enable_multi):?>
			   <td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><input title="<?=lang('toggle all above checkboxes') ?>" type="checkbox" name="CHECKALL" id="CHECKALL" value="TRUE" onclick="doCheckAll(this)" /></td>

			   <?php if($this->enable_view_rec):?>
			   <td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('view all selected records') ?>" href="javascript:submit_multi('view')"><img width="16" src="<?=$this->colfield_view_img_src ?>" alt="<?=lang('view all selected records') ?>" /></a></td>
			   <?php endif?>

			   <?php if($this->enable_edit_rec):?>
			   <td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('edit all selected records') ?>" href="javascript:submit_multi('edit')"><img width="16" src="<?=$this->colfield_edit_img_src ?>" alt="<?=lang('edit all selected records') ?>" /></a></td>
			   <?php endif?>

			   <?php if($this->enable_del):?>
			   <td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('delete all selected records') ?>" href="javascript:submit_multi('del')" ><img width="16" src="<?=$this->colfield_delete_img_src ?>" alt="<?=lang('delete all selected records') ?>" /></a></td>
			   <?php endif?>

			   <?php if($this->enable_export):?>
			   <td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('export all selected records') ?>" href="javascript:submit_multi('export')" ><img width="16" src="<?=$this->colfield_export_img_src ?>" alt="<?=lang('export all selected records') ?>" /></a></td>
			   <?php endif?>

			   <!--< ? p h p / / if($this->enable_multi):?>-->
			   <td>&nbsp;<?=lang('Actions to apply on all selected record')?></td>
			   <?php else:?>
			   <td>&nbsp;</td>
			   <?php endif?>
			</tr>

			<?php else:?>
			<tr valign="top" bgcolor="<?=$this->colhead_bg_color ?>"><td >&nbsp;</td></tr>

			<?php endif?>

		 </table>
	  </form>
	  <script>
		 if(document.getElementById('recordscontenttable').offsetWidth>document.getElementById('recordscontentdiv').offsetWidth)
		 {
			   document.getElementById('warnscrolls').style.display='block';	
		 }
	  </script>
   </div>
</div>
