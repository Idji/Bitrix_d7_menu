<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__); 

$arComponentDescription = array(
	"NAME" => Loc::GetMessage("IJ_SECELEMTREEMENU_NAME"),
	"DESCRIPTION" => Loc::GetMessage("IJ_SECELEMTREEMENU_DESCRIPTION"),
	"PATH" => array(
		"ID" => "ib-menu",
		"NAME" => "ИБ-меню"
	),
	"COMPLEX" => "N"
);
?>