<div style="border:solid 1px #CCCCCC; overflow:auto;background-color:#ffffff;padding:2px 10px 2px 10px; margin:0px 0px 5px 0px;">
   <?php if (is_array($this->msg_arr['error'])):?>
   <?php foreach($this->msg_arr['error'] as $error_str):?>
   <div style="margin:1px;color:red;"><?=$error_str?></div><br/>
   <?php endforeach?>
   <?php elseif($this->msg_arr['error']):?>
   <div style="margin:1px;color:red;"><?=$this->msg_arr['error']?></div><br/>
   <?php endif?>

   <?php if (is_array($this->msg_arr['info'])):?>
   <?php foreach($this->msg_arr['info'] as $info_str):?>
   <div style="margin:1px;color:green;"><?=$info_str?></div><br/>
   <?php endforeach?>
   <?php elseif($this->msg_arr['info']):?>
   <div style="margin:1px;color:green;"><?=$this->msg_arr['info']?></div><br/>
   <?php endif?>

   <?php if (is_array($this->msg_arr['help'])):?>
   <?php foreach($this->msg_arr['help'] as $help_str):?>
   <div style="margin:1px;color:blue;"><?=$help_str?></div><br/>
   <?php endforeach?>
   <?php elseif($this->msg_arr['help']):?>
   <div style="margin:1px;color:blue;"><?=$this->msg_arr['help']?></div><br/>
   <?php endif?>

   <?php if (is_array($this->msg_arr['debug'])):?>
   <input name="debugbutton" type="button" onclick="window.open('<?=$this->debugwindowlink?>,this.target,'width=800,height=600,scrollbars=yes,resizable=yes'); 
   		 return false;" value="<?=lang('Open debugging window.');?>" />
   <?php foreach($this->msg_arr['debug'] as $debug_arr):?>
   
   <?php if($debug_arr[line]):?><div style="margin:1px;color:#561800;">Line: <?=$debug_arr['line']?></div><br/><?php endif?>
   <?php if($debug_arr[line]):?><div style="margin:1px;color:#561800;">File: <?=$debug_arr['file']?></div><br/><?php endif?>
   <?php if($debug_arr[sql]):?><div style="margin:1px;color:#561800;">SQL: <?=$debug_arr[sql]?></div><br/><?php endif?>
   <?php if($debug_arr[other]):?><div style="margin:1px;color:#561800;">Other: <?=$debug_arr[other]?></div><br/><?php endif?>
   <?php if($debug_arr[post]):?><div style="margin:1px;color:#561800;">Post: <?=$debug_arr[post]?></div><br/><?php endif?>
   <?php if($debug_arr[get]):?><div style="margin:1px;color:#561800;">Get: <?=$debug_arr[get]?></div><br/><?php endif?>
   <?php if($debug_arr[session]):?><div style="margin:1px;color:#561800;">Session: <?=$debug_arr[session]?></div><br/><?php endif?>
   <?php if($debug_arr[site_arr]):?><div style="margin:1px;color:#561800;">Site_arr: <?=$debug_arr[site_arr]?></div><br/><?php endif?>
   <?php if($debug_arr[object_arr]):?><div style="margin:1px;color:#561800;">Object_arr: <?=$debug_arr[object_arr]?></div><br/><?php endif?>
   
   <?php endforeach?>
   <?php elseif($this->msg_arr['debug']):?>
   <div style="margin:1px;color:#561800;"><?=$this->msg_arr['debug']?></div><br/>
   <?php endif?>
</div>

