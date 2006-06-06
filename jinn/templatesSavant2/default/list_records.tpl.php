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

   //-->
</script>

<!-- SEARCH & FILTERS BLOCK -->
<?php if(!$this->japie || $this->japie_functions['search']):?>

<table cellpadding="0" cellspacing="0" style="border:solid 1px #cccccc">
   <tr>
	  <td align="center" style="padding-left:20px;">
		 <form action="<?=$this->menu_action ?>" method="post"><?=lang('search for string') ?>&nbsp;<input style="<?=$this->quick_filter_bgcolor ?>" type="text" size="20" name="quick_filter" value="<?=$this->search_string ?>">
			<input type="hidden" name="quick_filter_hidden" value="1">
			<input type='submit'  value='<?=lang('Search'); ?>' class="egwbutton">
		 </form>	
	  </td>
	  <td align="center" style="padding-left:20px;">
		 <form name="filterform" action="<?=$this->filter_action ?>" method="post"><?=$this->filter_text ?>&nbsp;
			<select name="filtername" style="<?=$this->adv_filter_bgcolor ?>" onChange="document.filterform.action = document.filterform.refresh_url.value; submit();">
			   <?=$this->filter_list ?>
			</select>
			<input type="hidden" name="refresh_url" value="<?=$this->refresh_url ?>">
			<input type="submit" value="<?=$this->filter_edit ?>" class="egwbutton">
		 </form>	
	  </td>
   </tr>
</table>

<?php endif?>
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
<input type="button" value="<?=lang('Add new Record') ?>" onClick="location.href='<?=$this->newrec_link ?>'" style="width:150px;"/>
<?=$this->walklistblock?>
<div class="jinnListBlock">
   <table border="0" style="border-spacing:1px;" align="center" width="100%" >
	  <tr>
		 <td style="padding:2px;border-bottom:solid 1px #006699" align="left">
			<table cellspacing="0" cellpadding="0" width="100%">
			   <tr>
				  <td style="padding:5px;font-size:16px;font-weight:bold;" colspan="3"><?=$this->table_title ?></td>
			   </tr>
			   <tr>
				  <td style="padding:5px;font-size:12px;font-weight:normal;"><?=$this->table_descr ?></td>
				  <td style="font-size:10px;font-weight:normal" align="center"><?=$this->pager ?></td>
				  <td style="font-size:10px;font-weight:normal" align="right"><?=$this->total_records ?> - <?=$this->rec_per_page ?></td>
			   </tr>
			</table>
		 </td>
	  </tr>
   </table>

   <form name="frm" action="<?=$this->list_form_action ?>" method="post">
	  <input type="hidden" name="action" value="none">
	  <table border="0" cellspacing="1" cellpadding="0" width="100%" style="padding-bottom:3px;border-bottom:solid 1px #006699">
		 <tr>
			<td bgcolor="<?=$this->th_bg ?>" colspan="5"  valign="top" style="width:1%;font-weight:bold;padding:3px 5px 3px 5px;"><?=lang('Actions')?></td>

			<?php if(is_array($this->colnames)):?>
			<?php foreach($this->colnames as $colname):?> 

			<td bgcolor="<?=$colname['colhead_bg_color'] ?>" style="font-weight:bold;padding:3px;" align="center"><a href="<?=$colname['colhead_order_link'] ?>"><?=$colname['colhead_name'] ?>&nbsp;<?=$colname['colhead_order_by_img'] ?></a><?=$colname['tipmouseover'] ?></td>

			<?php endforeach?>
			<?php endif?>
		 </tr>

		 <?php if(count($this->records_rows_arr)>0):?>
		 <?php foreach($this->records_rows_arr as $recrow_arr):?>

		 <!-- BEGIN row -->
		 <tr valign="top">

			<td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><input style="border-style:none;" type="checkbox" name="<?=$recrow_arr['colfield_check_name'] ?>" value="<?=$recrow_arr['colfield_check_val'] ?>"/></td>
			<td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('view') ?>" href="<?=$recrow_arr['colfield_view_link'] ?>"><img width="16" src="<?=$this->colfield_view_img_src ?>" alt="<?=lang('view') ?>" /></a></td>

			<td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('edit')?>" href="<?=$recrow_arr['colfield_edit_link'] ?>"><img width="16" src="<?=$this->colfield_edit_img_src ?>" alt="<?=lang('edit')?>" /></a></td>

			<td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('copy record') ?>" href="<?=$recrow_arr['colfield_copy_link'] ?>" onClick="return window.confirm('<?=lang('Do you want to copy this record?') ?>')"><img width="19" src="<?=$this->colfield_copy_img_src ?>" alt="<?=lang('copy record') ?>" /></a></td>

			<td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" align="left"><a title="<?=lang('delete') ?>" href="<?=$recrow_arr['colfield_delete_link'] ?>" onClick="return window.confirm('<?=$this->colfield_lang_confirm_delete_one ?>')"><img width="16" src="<?=$this->colfield_delete_img_src ?>" alt="<?=lang('delete') ?>" /></a></td>

			<?php foreach($recrow_arr['fields'] as $field_arr):?>
			<!-- BEGIN column_field -->
			<td bgcolor="<?=$recrow_arr['colfield_bg_color'] ?>" valign="top" style="padding:0px 2px 0px 2px"><?=$field_arr['value'] ?></td>
			<!-- END column_field -->
			<?php endforeach?>

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

			<td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><input title="<?=lang('toggle all above checkboxes') ?>" type="checkbox" name="CHECKALL" id="CHECKALL" value="TRUE" onclick="doCheckAll(this)" /></td>
			<td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('view all selected records') ?>" href="javascript:submit_multi('view')"><img width="16" src="<?=$this->colfield_view_img_src ?>" alt="<?=lang('view all selected records') ?>" /></a></td>

			<td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('edit all selected records') ?>" href="javascript:submit_multi('edit')"><img width="16" src="<?=$this->colfield_edit_img_src ?>" alt="<?=lang('edit all selected records') ?>" /></a></td>

			<td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('delete all selected records') ?>" href="javascript:submit_multi('del')" ><img width="16" src="<?=$this->colfield_delete_img_src ?>" alt="<?=lang('delete all selected records') ?>" /></a></td>

			<td width="1%" bgcolor="<?=$this->colhead_bg_color ?>" align="left"><a title="<?=lang('export all selected records') ?>" href="javascript:submit_multi('export')" ><img width="16" src="<?=$this->colfield_export_img_src ?>" alt="<?=lang('export all selected records') ?>" /></a></td>

			<td >&nbsp;<?=lang('Actions to apply on all selected record')?></td>
		 </tr>

		 <?php else:?>
		 <tr valign="top" bgcolor="<?=$this->colhead_bg_color ?>"><td >&nbsp;</td></tr>

		 <?php endif?>

	  </table>
   </form>
</div>
