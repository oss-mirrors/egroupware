<form name="formimport" action="<?=$this->form_action ?>" method="post" enctype="multipart/form-data">
   <div style="width:300px;float:left;line-height:150%;">
	  <strong><?=lang('JiNN Site File') ?></strong><br/>
	  <?=lang('Select a .JiNN or .jsxl file to import.') ?><br/>
	  <input name="importfile" type="file">
	  <input class="egwbutton"  type="submit" value="<?=lang('submit and import')?>">
   </div>
   <fieldset style="width:250px;">
	  <legend><?=lang('options')?></legend>
	  <table style="border-spacing:20px;" >
		 <tr>
			<td><?=lang('Replace existing Site with the same name');?></td>
			<td><input name="replace_existing" type="checkbox"></td>
		 </tr>
		 <tr>
			<td><?=lang('Don\'t allow unsafe old .JiNN files.');?></td>
			<td><input name="disallow_oldjinn" type="checkbox"></td>
		 </tr>
		 <tr>
			<td><?=lang('Keep object id\'s and replace existing objects with the same id');?></td>
			<td><input name="keep_object_ids" type="checkbox"></td>
		 </tr>
	  </table>
   </fieldset>
   <div style="clear:both">
   </div>
</form>
