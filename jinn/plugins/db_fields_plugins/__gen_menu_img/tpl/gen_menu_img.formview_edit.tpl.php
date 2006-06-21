<div style="margin:0px 0px 4px 0px">
   <input type="hidden" name="<?=$this->field_name?>" value="<?=$this->value?>"/>
   <input type="text" value="<?=$this->text?>" name="<?=$this->input_text_name?>" />
</div>
<?php if(is_array($this->img_src_arr)):?>
   <div style="margin-top:5px;border:solid 1px black ;display:table; background-color:white;width:auto;padding:5px;">
	  <?php foreach($this->img_src_arr as $img_name):?>
	  <?php $i++;?>
	  <div style="margin:0px 1px 4px 0px;">
		 <div style="color:#aaaaaa;width:5px:text-align:right;float:left;font-size:120%;font-weight:bold;margin:-3px 5px 0px 0px"><?=$i?>.</div>
		 <div ><img style="background-image:url(<?=$this->transbggrid?>);" src="<?=$this->upload_url?><?=$img_name?>" alt=""/></div>
	  </div>
	  <?php endforeach?>
   </div>
<?php endif?>


