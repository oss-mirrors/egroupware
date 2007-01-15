<style>
   label
   {
		 margin: 5px;
   }
   input
   {
		 color:#000;
		 padding-right:5px;
		 padding-left:5px;
		 padding-top:2px;
		 padding-bottom:2px;
   }
   .rij1
   {
		 background-color:#e8f0f0;
   }
   .rij2
   {
		 background-color:#dddddd;
   }
   td
   {
		 padding:5px;
   }
</style>

<script>
   <?=$this->report_insertfield_js?>
</script>
<?php
   if(is_array($this->val))
   {

	  if($this->val['report_html'] ==1)
	  {
		 $checked='checked';
	  }
	  else
	  {
		 $checked='';
	  }
   }
   else
   {
	  $checked='checked';
   }
?>
<style>
   .instructions
   {
		 padding:10px;
		 background-color:#dedede;
		 border:dashed #aaa 1px;

   }
</style>

<form method="post" name="frm" action="<?=$this->form_action;?>" enctype="multipart/form-data">
   <input type="hidden" value="<?=$this->obj_id;?>" name="obj_id">
   <input type="hidden" value="<?=$this->report_type_name;?>" name="report_type_name">
   <?php if($this->val['report_id'] != ''):?>
   <input type='hidden' value="<?=$this->val['report_id']?>" name="report_id">
   <?php endif ?>
   <table style="width:700px;" class="rij1">
	  <tr valign='top' >
		 <td colspan="2">
		 <div class="instructions">
			<?= lang('Place fields to merge in your report as %%fieldname%%.')?>
		 </div>
		 </td>
	  </tr>
	  <tr valign='top' >
		 <td><label><?=lang('Type');?></label></td>
		 <td><strong><?=$this->report_type_name;?></strong></td>
	  </tr>
	  <tr valign='top' >
		 <td><label><?=lang('Name');?></label></td>
		 <td><input type='text' name='name' id='name' value='<?=$this->val['report_naam'];?>'></td>
	  </tr>

   	  <?=$this->extra_config?>
   </table>

   <table style="width:700px;" >
	  <tr valign='top' class='rij1'>

		 <td><label><?=lang('Header');?></label></td>
		 <td><?=$this->report_header;?></td>
		 <td>
			<select name="sel1" multiple="multiple" size="6" id='sel1'>
			   <?=$this->attibutes?>
			</select><br><br>
			<input class="egwbutton"  type='button' value='<<' onclick="insertValue('1')">

		 </td>
	  </tr>
	  <tr valign='top' class='rij2'>
		 <td><label><?=lang('Body');?></label></td>
		 <td><?=$this->report_body;?></td>
		 <td>
			<select name="sel2" multiple="multiple" size="6" id='sel2'>
			   <?=$this->attibutes?>
			</select>
			<br><br>
			<input type='button' value='<<' onclick="insertValue('2')">
		 </td>
	  </tr>
	  <tr valign='top' class='rij1'>
		 <td><label><?=lang('Footer');?></label></td>
		 <td><?=$this->report_footer;?></td>
		 <td>
			<select name="sel3" multiple="multiple" size="6" id='sel3'>
			   <?=$this->attibutes?>
			</select>
			<br><br>
			<input type='button' value='<<' onclick="insertValue('3')">

		 </td>
	  </tr>
	  <tr valign='top' class='rij2'>
		 <td></td><td> 
			<input type="submit" name="reportsubmit" value='<?=lang('Save');?>' >
			<input type="button" value="<?= lang("Return to list")?>" onClick="document.location.href='<?=$this->returnlink?>'">
		 </td>
		 <td></td>
	  </tr>
   </table>
</form>
