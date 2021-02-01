<?php

namespace MA\Iblock;

/**
 * @version 1.0
 * @since 26.01.2021
 * @author Media Army
 */

use \Bitrix\Highloadblock as HL,
	\Bitrix\Main\Loader;

class Handlers {
	private static $CATALOG_IB = 18; // catalog iblock ID
	private static $SECTION_HLBL = 6; // sections hl block ID
	private static $ELEMENT_HLBL = 7; // elements hl block ID

	/**
	 * set section code from directory
	 *
	 * @param $arFields array - section's fields
	 */
	public static function setSectionCodeFromDirectory(&$arFields) {
		if ($arFields['IBLOCK_ID'] == self::$CATALOG_IB) {
			if (isset($arFields['XML_ID']) && !empty($arFields['XML_ID'])) {
				Loader::includeModule('highloadblock');
				$hlblock = HL\HighloadBlockTable::getById(self::$SECTION_HLBL)->fetch();
				$entity = HL\HighloadBlockTable::compileEntity($hlblock);
				$entity_data_class = $entity->getDataClass();
				$arSectionInfo = $entity_data_class::getList([
					'filter' => [
						'UF_SECTION_XML_ID' => $arFields['XML_ID'],
					],
				])->fetch();

				if ($arSectionInfo['UF_SECTION_CODE']) {
					$arFields['CODE'] = $arSectionInfo['UF_SECTION_CODE'];
				}
			}
		}
	}

	/**
	 * set element code from directory
	 *
	 * @param $arFields array - element's fields
	 */
	public static function setElementCodeFromDirectory(&$arFields) {
		if ($arFields['IBLOCK_ID'] == self::$CATALOG_IB) {
			if (isset($arFields['XML_ID']) && !empty($arFields['XML_ID'])) {
				Loader::includeModule('highloadblock');
				$hlblock = HL\HighloadBlockTable::getById(self::$ELEMENT_HLBL)->fetch();
				$entity = HL\HighloadBlockTable::compileEntity($hlblock);
				$entity_data_class = $entity->getDataClass();
				$arSectionInfo = $entity_data_class::getList([
					'filter' => [
						'UF_ELEMENT_XML_ID' => $arFields['XML_ID'],
					],
				])->fetch();

				if ($arSectionInfo['UF_ELEMENT_CODE']) {
					$arFields['CODE'] = $arSectionInfo['UF_ELEMENT_CODE'];
				}
			}
		}
	}
}