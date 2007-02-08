<h1><?=lang('Select objects from Site File to import.') ?></h1>
<strong><?=lang('Site File:');?></strong> <?=$this->newtempfilename?><br/>
<form name="frm" action="<?=$this->form_action ?>" method="post" enctype="multipart/form-data">
   <input name="import_into" type="hidden" value="<?=$this->import_into?>" />
   <input name="newtemp" type="hidden" value="<?=$this->newtemp?>" />
   <input name="objects_selected" type="hidden" value="true" />

   <div style="margin-left:100px;">
	  <?php if(is_array($this->import_site_objects)):?>
	  <?php foreach($this->import_site_objects as $oneobject):?>
	  <input checked="checked" type="checkbox" name="<?=$oneobject['object_id']?>"><?=$oneobject['name']?><br/>
	  <?php endforeach?>
	  <?php endif?>
	  
	  <br/>
	  <input title="<?=lang('toggle all checkboxes')?>" name="CHECKALL" id="CHECKALL" checked="checked" value="TRUE" onclick="doCheckAll(this)" type="checkbox" />
	  <?=lang('toggle all checkboxes')?>
	  <br/>
	  <input class="egwbutton"  type="submit" value="<?=lang('submit and import')?>">
   </div>
</form>
