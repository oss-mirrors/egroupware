<tr>
<td valign="top" class="<?=$rowval?>"><?=$this->cval[label]?></td><td valign="top" class="<?=$rowval?>">
<?php
unset($site_files);
//// Merk op dat !== niet bestond tot 4.0.0-RC2
if ($handle = @opendir($this->jinn_sitefile_path.SEP.$this->cval[subdir])) 
{
   while (false !== ($file = readdir($handle))) 
   { 
	  if ($file != "." && $file != "..") 
	  { 
		 $site_files[]= $file;
	  } 
   }
   closedir($handle); 
}
?>
<input type="hidden" name="<?=$this->cval[fname]?>" value="none" />
<select name="<?=$this->cval[fname]?>" />
<?php if($this->cval[allowempty]):?>
<option <?=$selected?> value=''><?=lang('none')?></option>
<?php endif?>
<?php

?>
<?php if(is_array($site_files)):?>
<?php foreach($site_files as $file):?>
<?php
   unset($selected);
   if($this->set_val==$file) $selected='selected="selected"';
?>
<option <?=$selected?> value='<?=$file?>'><?=$file?></option>
<?php endforeach?>
<?php endif?>
</select>

<input type="submit" onclick="document.popfrm.uploaddelete.value='true';" name="<?=$this->cval[fname]?>_plgdelete" value="<?=lang('delete file')?>" /> 
<br/>
<!--<input type="file" name="<?=$this->cval[fname]?>[value]" /> -->
<input type="file" name="<?=$this->cval[fname]?>" />
<input type="submit" onclick="document.popfrm.uploaddelete.value='true';" name="<?=$this->cval[fname]?>_plgupload" value="<?=lang('upload')?>" /> 
</td></tr>
