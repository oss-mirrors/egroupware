<?php if($this->popup):?>
<a href="javascript:<?=$this->popup?>"><img <?php if(!$this->is_thumb):?>width="150"<?php endif?> id="<?=$this->name?>" src="<?=$this->imglink?>" alt="preview" /></a>
<?php else:?>
<img id="<?=$this->name?>" src="<?=$this->imglink?>" alt="preview" />
<?php endif ?>
<a id="<?=$this->linkid?>" href="<?=$this->filelink?>"><span id="<?=$this->span_id?>"><?=$this->file_name?></span></a>
