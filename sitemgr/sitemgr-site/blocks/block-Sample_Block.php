<?php
/* blockconfig: <title>Sample block</title> */
/* blockconfig: <description>This is just a sample</description> */
/* blockconfig: <view>0</view> (everybody) */
/************************************************************************/
/* PHP-NUKE: Web Portal System                                          */
/* ===========================                                          */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi                                */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

if (eregi("block-Sample_Block.php",$PHP_SELF)) {
    Header("Location: index.php");
    die();
}

$content = "Here goes the content you want in your new block";

?>