<script>
   var text = '<?=$this->field_name?>';
   function change_selection(val,id)
   {
		 for(i=1;i<=<?php echo(count($this->config));?>;i++)
		 {
			   document.getElementById(i).style.border="0px solid black";
			}
		 document.getElementById(id).style.border="1px solid black";
		 document.getElementById(text).value=val;
   }
   
</script>
<div style="margin-top:5px;border:solid 1px black ;display:table; background-color:white;width:auto;padding:5px;">
   <input type="hidden" name="<?=$this->field_name?>" value="<?=$this->value?>" id ="<?=$this->field_name?>"/>
   <?php $i=1;?>
   <?php if(is_array($this->config)):?>
   <?php foreach($this->config as $config):?>
   <?if($this->value == $config['option_value']):?>
   <img style="margin:10px;border:1px solid black" src="<?php echo($this->upload_url);?>/visual_selection/<?php echo($config[imgfile]['value']);?>" onclick="change_selection('<?php echo($config['option_value']);?>',<?php echo($i);?>);" id="<?php echo($i);?>">
   
   <?else:?>
   <img style="margin:10px;" src="<?php echo($this->upload_url);?>/visual_selection/<?php echo($config[imgfile][value]);?>" onclick="change_selection('<?php echo($config['option_value']);?>',<?php echo($i);?>);" id="<?php echo($i);?>">
   <?endif?>
<?php $i++;?>
   <?php endforeach?>
   <?endif?>
</div>
