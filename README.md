# Bitrix_d7_menu
Menu from sections and elements

Component to use in menuname_ext.php file.
usage like:

<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION; 
$aMenuLinksExt = [];//массив из пунктов меню

$aMenuLinksExt=$APPLICATION->IncludeComponent(
  "ij:d7menu.sections.elements",
   "", 
   array(
    "IBLOCK_TYPE" => "content",
    "IBLOCK_ID" => "23",
    "DEPTH_LEVEL" => "4",
    "CACHE_TYPE" => "A",
    "CACHE_TIME" => "36000000",
    "START_DEPTH_LEVEL" => 1,
  ),
  false
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>
