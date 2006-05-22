<tr>
<td valign="top" class="<?=$rowval?>"><?=$this->cval[label]?></td><td valign="top" class="<?=$rowval?>">
<?php foreach($this->cval[checkbox_arr] as $optval => $optname):?>
<?php 
   unset($checked);
   if(is_array($this->set_val))
   {
	  if(in_array($optval,$this->set_val)) $checked='checked="checked"';
   }
?>
<input name="<?=$this->cval[fname]?>[<?=$optval?>]" type="checkbox" value="<?=$optval?>" <?=$checked?> /><?=$optname?><br/>

<?php endforeach?>
</td></tr>
