<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;

class SecElemTreeMenu extends CBitrixComponent
{
    
    /**
     * Рекурсивно создаёт массив пунктов меню
     * @param array &$menuItems Массив всех пунктов меню 
     * @param array $arParent Родительский массив
     * @param int $depthLevel Глубина вложенности
     * @return array
     */
    private function buildMenuArray(&$menuItems, $arParent, $depthLevel)
    {
        foreach($arParent as $item){
            $isParent = ($item['IS_SECTION']&&isset($menuItems[$item['ID']]));
            $res[] = array(
                htmlspecialchars($item['~NAME']),
                $item['LINK'],
                array(),
                array(
                    'FROM_IBLOCK' => true,
                    'IS_PARENT' => $isParent,
                    'DEPTH_LEVEL' => $depthLevel,
                ),
            );
            if ($isParent){
                $resJoin = $this->buildMenuArray($menuItems,$menuItems[$item['ID']],$depthLevel+1);
                $res = array_merge($res, $resJoin);
            }
        }
        return $res;
    }

    public function executeComponent()
    {   
        if(!isset($this->arParams['CACHE_TIME']))
        	$this->arParams['CACHE_TIME'] = 36000000;

        $this->arParams['IBLOCK_ID'] = intval($this->arParams['IBLOCK_ID']);

        $this->arParams['DEPTH_LEVEL'] = intval($this->arParams['DEPTH_LEVEL']);
        $this->arParams['DEPTH_LEVEL'] = ($this->arParams['DEPTH_LEVEL'] <= 0) ? 1 : $this->arParams['DEPTH_LEVEL'];

        $cache = Bitrix\Main\Data\Cache::createInstance();
        $cacheId = 'SecElemTreeMenu'.$this->arParams['IBLOCK_ID'].$this->arParams['DEPTH_LEVEL'];
		$cacheDir = 'SecElemTreeMenu';
        if($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)){
			$this->arResult = $cache->getVars();
		}
		elseif($cache->startDataCache()){
			$result = array();
		    Loader::includeModule("iblock");
            $arSectionId = array();
            $arFilter = array(
                'IBLOCK_ID'=>$this->arParams['IBLOCK_ID'],
                'GLOBAL_ACTIVE'=>'Y',
                'ACTIVE'=>'Y',
                '<=DEPTH_LEVEL' => $this->arParams['DEPTH_LEVEL'],
            );
            $arOrder = array(
                'SORT'=>'ASC',
            );
            $rsSections = CIBlockSection::GetList($arOrder, $arFilter, false, array(
                'ID',
                'DEPTH_LEVEL',
                'NAME',
                'SECTION_PAGE_URL',
                'IBLOCK_SECTION_ID',
            ));
            $menuItems = array();
            while($arSection = $rsSections->GetNext()){
                $arSection['IS_SECTION'] = 1;
                $arSection['LINK'] = $arSection['SECTION_PAGE_URL'];
                if ($arSection['IBLOCK_SECTION_ID']){
                    $menuItems[$arSection['IBLOCK_SECTION_ID']][] = $arSection;
                } else {
                    $menuItems['ROOT'][] = $arSection;
                }
                $arSectionId[] = $arSection['ID'];
            }
            //Получим элементы
            $arSelect = Array('ID', 'NAME','DETAIL_PAGE_URL', 'IBLOCK_SECTION_ID');
            $arFilter = Array(
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y',
                array(
                'LOGIC' => 'OR',
                    array('SECTION_ID' => $arSectionId),
                    array('SECTION_ID' => false),
                ),
            );
            $arOrder = Array('SORT' => 'ASC');
            $res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);  
            while ($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();
                $arFields['IS_SECTION'] = 0;
                $arFields['LINK'] = $arFields['DETAIL_PAGE_URL'];
                if ($arFields['IBLOCK_SECTION_ID']){
                    $menuItems[$arFields['IBLOCK_SECTION_ID']][] = $arFields;
                } else {
                    $menuItems['ROOT'][] = $arFields;
                }
            }
            //Рекурсивно сформируем итоговый массив для меню
            $startLevel = (intval($this->arParams['START_DEPTH_LEVEL'])) ? $this->arParams['START_DEPTH_LEVEL'] : 1;
            $this->arResult = $this->buildMenuArray($menuItems,$menuItems['ROOT'],$startLevel);
            if(!is_array($this->arResult)){
				$cache->abortDataCache();
			}
			$cache->endDataCache($this->arResult);
		}        
        //т.к. формируется массив для menu_ext,то шаблон не подключаем
        //$this->includeComponentTemplate();
        return $this->arResult;
    }
}
?>