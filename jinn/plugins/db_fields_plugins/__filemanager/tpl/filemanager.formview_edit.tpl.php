<style>
   .fmfieldset
   {
		 padding:10px;
		 margin:5px 0px 5px 0px;
		 width:170px;
		 border-width:1px;
		 border-style:solid;
		 border-color:#aaaaaa;
		 text-align:center;
   }
</style>
<input type="hidden" name="<?=$this->field_name?>" value="">
<?php if($this->value):?>
<input name="<?=$this->prefix.'_IMG_ORG_'.$this->stripped_name?>" type="hidden" value="<?=$this->value?>" />
<?php endif?>

<?=$this->fullrows?>
<?=$this->empt_rows?>

<?php if($this->config[Allow_more_then_max_files]=='True'):?>

<span id="writeroot<?=$this->field_name?>"></span>
<div style="clear:both">
   <input style="margin:5px 0px 5px 0px;" class="egwbutton"  type="button" value="<?=lang('add slot')?>" onClick="moreFields('<?=$this->field_name?>')">
</div>
<input id="counter<?=$this->field_name?>" name="counter<?=$this->field_name?>" type="hidden" value="<?=$this->counter?>" />


<fieldset id="templbox<?=$this->field_name?>" class="fmfieldset" style="display:none;overflow:hidden;">
   <legend id="label<?=$this->field_name?>"></legend>

   <input type="hidden" value="" id="<?=$this->prefix.'_IMG_EDIT_'.$this->stripped_name?>" name="<?=$this->prefix.'_IMG_EDIT_'.$this->stripped_name?>">

   <div style="padding-top:10px;">
	  <img id="<?=$this->prefix.'_IMG_'.$this->stripped_name?>" src="jinn/plugins/db_fields_plugins/__filemanager/img/spacer.gif"><span id="<?=$this->prefix.'_PATH_'.$this->stripped_name?>"></span>
   </div>
   <div style="padding-top:10px;">
	  <input type="button"  class="egwbutton" value="<?=lang('delete')?>" onClick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);lowerCounter('<?=$this->field_name?>');" src="jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/edit_trash.gif">
	  <input onClick="onBrowseServer2('<?=$this->prefix?>', '<?=$this->stripped_name?>',this);" type="button"  class="egwbutton" value="<?=lang('browse')?>" id="BROWSEBUTTON_">
   </div>
</fieldset>
<?php endif?>
