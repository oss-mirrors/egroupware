<form name="filterform" action="<?=$this->form_action?>" method="post">
   <div style="margin:10px 0px 20px 0px">
	  <span style="font-weight:bold"><?=lang('Operator to use between comparisons');?></span>
	  &nbsp;
	  <input type="radio" name="ANDOR" <?=$this->andor_and_chk?> value="AND" /><?=lang('AND');?>
	  &nbsp;
	  &nbsp;
	  <input type="radio" name="ANDOR" <?=$this->andor_or_chk?> value="OR" /><?=lang('OR');?>

   </div>
   <table cellpadding="0" cellspacing="0" style="border:solid 0px #cccccc">
	  <tr>
		 <td style="font-weight:bold" align="center"></td>
		 <td style="font-weight:bold" align="center"><?=lang('Delete')?></td>
		 <td style="font-weight:bold" align="center"><?=lang('Column')?></td>
		 <td style="font-weight:bold" align="center"><?=lang('Filter Operator');?></td>
		 <td style="font-weight:bold" align="center"><?=lang('Criterium');?></td>
	  </tr>
	  <tr>
		 <td colspan="3">&nbsp;</td>
	  </tr>
	  <?php foreach($this->filterdata_elements as $element):?>
	  <tr>
		 <?php if($element['set']):?>
		 <td align="center" style="width:10px">
			<?=$element['element']?>
		 </td>
		 <td align="center" style="width:10px">
			<input type="checkbox" name="delete_<?=$element['element']?>" value="true" />
		 </td>
		 <?php else:?>
		 <td colspan="2" align="center" style="width:10px">
			<?= lang('New')?>
		 </td>
		 <?php endif?>
		 <td align="center" style="padding-left:20px;">
			<select name="field_<?=$element['element']?>">
			   <?=$element['fields']?>
			</select>
		 </td>
		 <td align="center" style="padding-left:20px;">
			<select name="operator_<?=$element['element']?>">
			   <?=$element['operators']?>
			</select>
		 </td>
		 <td align="center" style="padding-left:20px;">
			<input type="text" name="value_<?=$element['element']?>" value="<?=$element['value']?>"/>
		 </td>
	  </tr>
	  <?php endforeach?>
   </tr>
</table>
<table>
   <tr>
	  <td colspan="3">&nbsp;</td>
   </tr>
   <tr>
	  <td align="right"><?=lang('Save as');?>:</td>
	  <td align="center" style="padding-left:20px;">
		 <input type="text" name="filtername" value="<?=$this->filtername?>"/>
	  </td>
	  <td align="center" style="padding-left:20px;">
		 <input class="egwbutton"  type="submit" name="submit" value="<?=lang('Store Filter');?>"/>
	  </td>
   </tr>
   <tr height="50">
	  <td colspan="3">&nbsp;</td>
   </tr>
   <tr>
	  <td colspan="3" align="left">
		 <input type="hidden" name="listurl" value="<?=$this->list_url?>"/>
		 <input type="hidden" name="deleteurl" value="<?=$this->delete_url?>"/>
		 <input class="egwbutton"  type="submit" name="submit" value="<?=lang('Delete Filter');?>" onClick="return onDelete();"/>
		 <input class="egwbutton"  type="submit" name="submit" value="<?=lang('Activate Filters and Return');?>" onClick="document.filterform.action = document.filterform.listurl.value;"/>
	  </td>
   </tr>
</table>
</form>
<script language="javascript">
   <!--
   function onDelete()
   {
		 if(document.filterform.filtername.value == 'sessionfilter')
		 {
			   alert('<?=lang('you cannot delete the session filter')?>');
			   return false;
		 }
		 else
		 {
			   if(confirm('<?=lang('are you sure you want to delete this filter?')?>'))
			   {
					 document.filterform.action = document.filterform.deleteurl.value; 
					 return true;
			   } 
			   else 
			   {
					 return false;
			   }
		 }
   }
   -->	
</script>
