<h1><?=lang('Select objects from Site File to import.') ?></h1>
<strong><?=lang('Site File:');?></strong> <?=$this->newtempfilename?><br/>
<form name="formimport" action="<?=$this->form_action ?>" method="post" enctype="multipart/form-data">
   <input name="import_into" type="hidden" value="<?=$this->import_into?>" />
   <input name="newtemp" type="hidden" value="<?=$this->newtemp?>" />
   <input name="objects_selected" type="hidden" value="true" />

   <div style="margin-left:100px;">
	  <?php if(is_array($this->import_site_objects)):?>
	  <?php foreach($this->import_site_objects as $oneobject):?>
	  <input checked="checked" type="checkbox" name="<?=$oneobject['object_id']?>"><?=$oneobject['name']?><br/>
	  <?php endforeach?>
	  <?php endif?>
   	  <input class="egwbutton"  type="submit" value="<?=lang('submit and import')?>">
   </div>
   <!--
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
	  </table>
   </fieldset>
   <div style="clear:both">
   </div>
</form>-->
