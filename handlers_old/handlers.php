<?php
/**
 * @version 2.0.0
 * @since 2020.04.09
 * @author Media Army
 *
 * OnGetOptimalPrice 
 * OnBuildGlobalMenu
 * PartnerySaytaOnAfterAdd
 * PartnerySaytaOnAfterUpdate
 * KontragentyOnAfterAdd
 * KontragentyOnAfterUpdate
 * OnBeforeEventSend
 * OnAfterUserUpdate
 * OnSaleBasketItemEntitySaved
 * OnSalePropertyValueEntitySaved
 * OnSaleOrderSaved
 */

use \Bitrix\Main\Loader,
	\Bitrix\Highloadblock,
	\Bitrix\Main\Entity,
	\Bitrix\Main\EventManager,
	\Bitrix\Main\UserTable,
	\Bitrix\Catalog\GroupTable,
	\Bitrix\Sale\Internals\OrderPropsTable,
	\Bitrix\Sale,
	\Bitrix\Main;

$eventManager = \Bitrix\Main\EventManager::getInstance();
// добавление в корзину по нужной цене
global $arTmBrands;
$arTmBrands = array(
	2777, // аллюр
	2775, // стандарт
	2867, // авангард
);
if (!function_exists('MyGetOptimalPrice')) {
	$eventManager->addEventHandler("catalog", "OnGetOptimalPrice", "MyGetOptimalPrice");

	function MyGetOptimalPrice(
		$intProductID,
		$quantity = 1,
		$arUserGroups = array(),
		$renewal = "N",
		$arPrices = array(),
		$siteID = false,
		$arDiscountCoupons = false
	) {
		global $USER;
		global $arTmBrands;
		if ($USER->IsAuthorized() && empty($arPrices)) {
			$result = array();

			$arUser = \CUser::GetByID($USER->GetID())->fetch();

			if ($arUser['UF_PRICE_TYPE_TM']) {
				$enumRes = \CUserFieldEnum::GetList(array(), array('ID' => $arUser['UF_PRICE_TYPE_TM']));
				$arEnum = $enumRes->fetch();
				$_SESSION['PRICE_TYPE_TM_ID'] = $arEnum['XML_ID'];
			} else {
				$_SESSION['PRICE_TYPE_TM_ID'] = 12;
			}

			if ($arUser['UF_PRICE_TYPE_OTHER']) {
				$enumRes = \CUserFieldEnum::GetList(array(), array('ID' => $arUser['UF_PRICE_TYPE_OTHER']));
				$arEnum = $enumRes->fetch();
				$_SESSION['PRICE_TYPE_OTHER_ID'] = $arEnum['XML_ID'];
			} else {
				$_SESSION['PRICE_TYPE_OTHER_ID'] = 12;
			}

			if ($_SESSION['PRICE_TYPE_TM_ID'] > 0 || $_SESSION['PRICE_TYPE_OTHER_ID'] > 0) {
				$arSelect = array(
					'ID',
					'IBLOCK_ID',
					'PROPERTY_CML2_MANUFACTURER'
				);
				if ($_SESSION['PRICE_TYPE_TM_ID'] > 0) {
					$arSelect[] = 'CATALOG_GROUP_'.$_SESSION['PRICE_TYPE_TM_ID'];
				}
				if ($_SESSION['PRICE_TYPE_OTHER_ID'] > 0) {
					$arSelect[] = 'CATALOG_GROUP_'.$_SESSION['PRICE_TYPE_OTHER_ID'];
				}
				$resElement = \CIBlockElement::GetList(array(), array('ID' => $intProductID), false, false, $arSelect);
				$arElement = $resElement->Fetch();
				if (in_array($arElement['PROPERTY_CML2_MANUFACTURER_ENUM_ID'], $arTmBrands) && $_SESSION['PRICE_TYPE_TM_ID'] > 0 && $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_TM_ID']] > 0) {
					$result = array(
						'RESULT_PRICE' => array(
							'PRICE_TYPE_ID' => $_SESSION['PRICE_TYPE_TM_ID'],
							'BASE_PRICE' => $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_TM_ID']],
							'DISCOUNT_PRICE' => $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_TM_ID']],
							'CURRENCY' => $arElement['CATALOG_CURRENCY_'.$_SESSION['PRICE_TYPE_TM_ID']],
							'DISCOUNT' => 0,
							'PERCENT' => 0,
							'VAT_RATE' => $vat['RATE'],
							'VAT_INCLUDED' => $vat['VAT_INCLUDED'],
							'PRICE_ID' => $arElement['CATALOG_PRICE_ID_'.$_SESSION['PRICE_TYPE_TM_ID']],
						),
						'DISCOUNT_PRICE' => $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_TM_ID']],
						'DISCOUNT' => array(),
						'DISCOUNT_LIST' => array(),
						'PRODUCT_ID' => $arElement['ID'],
					);
				} elseif (!in_array($arElement['PROPERTY_CML2_MANUFACTURER_ENUM_ID'], $arTmBrands) && $_SESSION['PRICE_TYPE_OTHER_ID'] > 0 && $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_OTHER_ID']] > 0) {
					$result = array(
						'RESULT_PRICE' => array(
							'PRICE_TYPE_ID' => $_SESSION['PRICE_TYPE_OTHER_ID'],
							'BASE_PRICE' => $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_OTHER_ID']],
							'DISCOUNT_PRICE' => $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_OTHER_ID']],
							'CURRENCY' => $arElement['CATALOG_CURRENCY_'.$_SESSION['PRICE_TYPE_OTHER_ID']],
							'DISCOUNT' => 0,
							'PERCENT' => 0,
							'VAT_RATE' => $vat['RATE'],
							'VAT_INCLUDED' => $vat['VAT_INCLUDED'],
							'PRICE_ID' => $arElement['CATALOG_PRICE_ID_'.$_SESSION['PRICE_TYPE_OTHER_ID']],
						),
						'DISCOUNT_PRICE' => $arElement['CATALOG_PRICE_'.$_SESSION['PRICE_TYPE_OTHER_ID']],
						'DISCOUNT' => array(),
						'DISCOUNT_LIST' => array(),
						'PRODUCT_ID' => $arElement['ID'],
					);
				}

				if (!empty($result)) {
					return $result;
				}
			}
		}
		return true;
	}
}

// добавляем пункт меню
if (!function_exists('AddMenuItems')) {
	$eventManager->addEventHandler("main", "OnBuildGlobalMenu", "AddMenuItems");
	function AddMenuItems(&$adminMenu, &$moduleMenu) {
		global $APPLICATION;
		$APPLICATION->SetAdditionalCss("/bitrix/tools/ma/menu_style.css");
		$moduleMenu[] = array(
			'parent_menu' => 'global_menu_settings',
			'sort' => 90,
			// 'menu_id' => 'ma_tools',
			'text' => 'Импорт пользователей из HL блока',
			'title' => 'Импорт пользователей из HL блока',
			'url' => 'import_user_hl_block.php?lang=ru',
			'icon' => 'ma_user_import',
		);
	}
}

// добавление, изменение элемента в HL блоке PartnerySayta, добавление, изменение пользователей сайта и профилей
// партнеры begin
$eventManager->addEventHandler('', 'PartnerySaytaOnAfterAdd', 'PartnerySaytaOnAfterAdd');
$eventManager->addEventHandler('', 'PartnerySaytaOnAfterUpdate', 'PartnerySaytaOnAfterUpdate');

function PartnerySaytaOnAfterAdd (\Bitrix\Main\Entity\Event $event) {
	$arFields = $event->getParameter("fields");

	if ($arFields['UF_KOD']) {

		$userId = 0;
		$INN = 0;
		$KPP = 0;
		$FORM = 0;
		$FORM_LIST = array();
		$ERRORS = array();
		$user = new \CUser;
		$arUserFields = array();

		$arPrices = MaGetPrices();

		Loader::includeModule('sale');
		$arProperties = OrderPropsTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('ACTIVE' => 'Y', 'CODE' => array('INN', 'FORM', 'KPP')),
		))->FetchAll();
		foreach ($arProperties as $propKey => $arProperty) {
			if ($arProperty['CODE'] == 'INN')
				$INN = $arProperty['ID'];
			if ($arProperty['CODE'] == 'KPP')
				$KPP = $arProperty['ID'];
			if ($arProperty['CODE'] == 'FORM')
				$FORM = $arProperty['ID'];
		}
		if ($FORM) {
			$resForms = \CSaleOrderPropsVariant::GetList(array('ID' => 'ASC'), array('ORDER_PROPS_ID' => $FORM));
			while ($arForm = $resForms->fetch()) {
				$FORM_LIST[$arForm['ID']] = $arForm;
			}
		}

		$arUserFields['LAST_NAME'] = $arFields['UF_NAME'];

		$arUserFields['EMAIL'] = trim($arFields['UF_ELEKTRONNAYAPOCHT']);
		$arUserFields['PERSONAL_PHONE'] = $arFields['UF_TELEFON'];
		$arUserFields['UF_PRICE_TYPE_TM'] = $arPrices[$arFields['UF_TIPTSENYTM']]['TM_ID'];
		$arUserFields['UF_PRICE_TYPE_OTHER'] = $arPrices[$arFields['UF_TIPTSENYPROCHEE']]['OTHER_ID'];

		// $userLogin = '';
		// do {
		// 	$userLogin = 'user_'.randString(6,array('0123456789'));
		// 	$res = UserTable::getList(array(
		// 		'select' => array('ID'),
		// 		'filter' => array(
		// 			'LOGIN' => $userLogin,
		// 		),
		// 	));
		// } while ($res->getSelectedRowsCount() > 0);
		$password = randString(12);
		// $arUserFields['LOGIN'] = $userLogin;
		$arUserFields['LOGIN'] = trim($arFields['UF_ELEKTRONNAYAPOCHT']);
		$arUserFields['PASSWORD'] = $password;
		$arUserFields['CONFIRM_PASSWORD'] = $password;
		$arUserFields['GROUP_ID'] = array(3,4,6);
		$arUserFields['UF_HLBLOCK_USER_ID'] = $arFields['UF_KOD'];
		$arUserFields['LID'] = 's1';

		if (!empty($arFields['UF_OSNOVNOYMENEDZHER'])) {
			$hlManagersBlock = Highloadblock\HighloadBlockTable::getById(9)->fetch();
			$managersEntity = Highloadblock\HighloadBlockTable::compileEntity($hlManagersBlock);
			$managersEntityClass = $managersEntity->getDataClass();

			$arManager = $managersEntityClass::getList(array(
				'filter' => array(
					'UF_IDENTIFIKATORMENE' => $arFields['UF_OSNOVNOYMENEDZHER'],
				),
				'select' => array('ID', 'UF_IDENTIFIKATORMENE'),
			))->Fetch();
			if (0 < $arManager['ID']) {
				$arUserFields['UF_MANAGEGER_HL'] = $arManager['ID'];
			}
		}

		$userId = $user->Add($arUserFields);

		if (0 < $userId && $arFields['UF_IDENTIFIKATORPART']) {
			$arAuthResult = \CUser::SendPassword($arUserFields['LOGIN'], $arUserFields['EMAIL'], $arUserFields['LID']);
			
			$arUser = UserTable::getList(array(
				'filter' => array(
					'ID' => $userId,
				),
				'select' => array('ID', 'LID'),
			))->Fetch();
			// $user->SendUserInfo($userId, $arUser['LID']);
			$hlProfilesBlock = Highloadblock\HighloadBlockTable::getById(6)->fetch();
			$profilesEntity = Highloadblock\HighloadBlockTable::compileEntity($hlProfilesBlock);
			$profilesEntityClass = $profilesEntity->getDataClass();

			$arHlProfiles = array();
			$profilesHlRes = $profilesEntityClass::getList(array(
				'filter' => array('UF_PARTNER' => $arFields['UF_IDENTIFIKATORPART']),
				'select' => array('*'),
				'order' => array('ID' => 'ASC'),
			));
			while ($profileRow = $profilesHlRes->fetch()) {
				if (!$profileRow['UF_INN'])
					continue;

				$arHlProfiles[] = $profileRow;
			}
			if (0 < count($arHlProfiles)) {
				$arUserProfiles = array();
				$profilesRes = \CSaleOrderUserProps::GetList(
					array(),
					array(
						'USER_ID' => (int)($userId)
					)
				);
				while ($arProfile = $profilesRes->fetch()) {
					$arProperties = Sale\OrderUserProperties::getProfileValues((int)$arProfile['ID']);
					$arProfile['PROPERTIES'] = $arProperties;

					$arUserProfiles[] = $arProfile;
				}
				if (0 < count($arUserProfiles)) {
					foreach ($arHlProfiles as $profKey => $arHlProfile) {
						$arOrderPropsValues = array();
						$arProfiles = array_filter(
							$arUserProfiles,
							function ($arUserProfile) use ($arHlProfile, $INN) {
								if ($arHlProfile['UF_INN'] == $arUserProfile['PROPERTIES'][$INN])
									return true;
								return false;
							}
						);
						if (0 < count($arProfiles)) {
							$curKey = key($arProfiles);
							$arProfile = current($arProfiles);
							$arOrderPropsValues = $arProfile['PROPERTIES'];
							if ($arHlProfile['UF_NAME']) {
								$arProfile['NAME'] = $arHlProfile['UF_NAME'];
							}
							$arOrderPropsValues[$KPP] = $arHlProfile['UF_KPP'];
							\CSaleOrderUserProps::DoSaveUserProfile(
								$arProfile['USER_ID'],
								$arProfile['ID'],
								$arProfile['NAME'],
								$arProfile['PERSON_TYPE_ID'],
								$arOrderPropsValues,
								$ERRORS
							);
							unset($arHlProfiles[$profKey], $arUserProfiles[$curKey]);
						}
					}
				}
				if (0 < count($arHlProfiles)) {
					unset($profKey, $arProfile);
					foreach ($arHlProfiles as $profKey => $arProfile) {
						$arOrderPropsValues = array();
						if (10 == strlen($arProfile['UF_INN']) || (12 == strlen($arProfile['UF_INN']) && 'ИндивидуальныйПредприниматель' == $arProfile['UF_YURFIZLITSO'])) {
							$personTypeId = 2;
						} elseif (12 == strlen($arProfile['UF_INN']) || 'ФизЛицо' == $arProfile['UF_YURFIZLITSO']) {
							$personTypeId = 1;
						}
						if ($personTypeId == 2) {
							$arOrderPropsValues[$KPP] = $arProfile['UF_KPP'];
							$arOrderPropsValues[$INN] = $arProfile['UF_INN'];
							$arOrderPropsValues[8] = $arProfile['UF_DESCRIPTION'];
							foreach ($FORM_LIST as $fKey => $arForm) {
								if (('ЮрЛицо' == $arProfile['UF_YURFIZLITSO'] && 'ООО' == $arForm['VALUE']) ||
								    ('ИндивидуальныйПредприниматель' == $arProfile['UF_YURFIZLITSO'] && 'ИП' == $arForm['VALUE'])
								   ) {
									$arOrderPropsValues[$FORM] = $arForm['ID'];
									break;
								}
							}
						} elseif ($personTypeId == 1) {
							$arOrderPropsValues[1] = $arProfile['UF_DESCRIPTION'];
						}
						$profileId = \CSaleOrderUserProps::DoSaveUserProfile(
							$userId,
							0,
							$arProfile['UF_NAME'],
							$personTypeId,
							$arOrderPropsValues,
							$ERRORS
						);
					}
				}
			}
		} else {
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', __FILE__.':'.__LINE__.' '.$user->LAST_ERROR.PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', __FILE__.':'.__LINE__.' + $arFields:'.PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', print_r($arFields, true).PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', __FILE__.':'.__LINE__.' + $arUserFields:'.PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', print_r($arUserFields, true).PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}
}

function PartnerySaytaOnAfterUpdate (\Bitrix\Main\Entity\Event $event) {
	// $id = $event->getParameter("id");
	// $id = $id["ID"];
	// $entity = $event->getEntity();
	// $entityDataClass = $entity->GetDataClass();
	// $eventType = $event->getEventType();
	$arFields = $event->getParameter("fields");

	if ($arFields['UF_KOD']) {

		$userId = 0;
		$INN = 0;
		$KPP = 0;
		$FORM = 0;
		$FORM_LIST = array();
		$ERRORS = array();
		$user = new \CUser;
		$arUserFields = array();

		$arPrices = MaGetPrices();

		Loader::includeModule('sale');
		$arProperties = OrderPropsTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('ACTIVE' => 'Y', 'CODE' => array('INN', 'FORM', 'KPP')),
		))->FetchAll();
		foreach ($arProperties as $propKey => $arProperty) {
			if ($arProperty['CODE'] == 'INN')
				$INN = $arProperty['ID'];
			if ($arProperty['CODE'] == 'KPP')
				$KPP = $arProperty['ID'];
			if ($arProperty['CODE'] == 'FORM')
				$FORM = $arProperty['ID'];
		}
		if ($FORM) {
			$resForms = \CSaleOrderPropsVariant::GetList(array('ID' => 'ASC'), array('ORDER_PROPS_ID' => $FORM));
			while ($arForm = $resForms->fetch()) {
				$FORM_LIST[$arForm['ID']] = $arForm;
			}
		}

		$arUserFields['LAST_NAME'] = $arFields['UF_NAME'];

		$arUserFields['EMAIL'] = trim($arFields['UF_ELEKTRONNAYAPOCHT']);
		$arUserFields['LOGIN'] = trim($arFields['UF_ELEKTRONNAYAPOCHT']);
		$arUserFields['PERSONAL_PHONE'] = $arFields['UF_TELEFON'];
		$arUserFields['UF_PRICE_TYPE_TM'] = $arPrices[$arFields['UF_TIPTSENYTM']]['TM_ID'];
		$arUserFields['UF_PRICE_TYPE_OTHER'] = $arPrices[$arFields['UF_TIPTSENYPROCHEE']]['OTHER_ID'];

		if (!empty($arFields['UF_OSNOVNOYMENEDZHER'])) {
			$hlManagersBlock = Highloadblock\HighloadBlockTable::getById(9)->fetch();
			$managersEntity = Highloadblock\HighloadBlockTable::compileEntity($hlManagersBlock);
			$managersEntityClass = $managersEntity->getDataClass();

			$arManager = $managersEntityClass::getList(array(
				'filter' => array(
					'UF_IDENTIFIKATORMENE' => $arFields['UF_OSNOVNOYMENEDZHER'],
				),
				'select' => array('ID', 'UF_IDENTIFIKATORMENE'),
			))->Fetch();
			if (0 < $arManager['ID']) {
				$arUserFields['UF_MANAGEGER_HL'] = $arManager['ID'];
			}
		}

		$userRes = UserTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'UF_HLBLOCK_USER_ID' => $arFields['UF_KOD'],
			),
		));
		if ($arr = $userRes->fetch()) {
			$userId = $arr['ID'];
		}
		if (0 < $userId) {
			$user->Update($userId, $arUserFields);

			if (0 < $userId && $arFields['UF_IDENTIFIKATORPART']) {
				$hlProfilesBlock = Highloadblock\HighloadBlockTable::getById(6)->fetch();
				$profilesEntity = Highloadblock\HighloadBlockTable::compileEntity($hlProfilesBlock);
				$profilesEntityClass = $profilesEntity->getDataClass();

				$arHlProfiles = array();
				$profilesHlRes = $profilesEntityClass::getList(array(
					'filter' => array('UF_PARTNER' => $arFields['UF_IDENTIFIKATORPART']),
					'select' => array('*'),
					'order' => array('ID' => 'ASC'),
				));
				while ($profileRow = $profilesHlRes->fetch()) {
					if (!$profileRow['UF_INN'])
						continue;

					$arHlProfiles[] = $profileRow;
				}
				if (0 < count($arHlProfiles)) {
					$arUserProfiles = array();
					$profilesRes = \CSaleOrderUserProps::GetList(
						array(),
						array(
							'USER_ID' => (int)($userId)
						)
					);
					while ($arProfile = $profilesRes->fetch()) {
						$arProperties = Sale\OrderUserProperties::getProfileValues((int)$arProfile['ID']);
						$arProfile['PROPERTIES'] = $arProperties;

						$arUserProfiles[] = $arProfile;
					}
					if (0 < count($arUserProfiles)) {
						foreach ($arHlProfiles as $profKey => $arHlProfile) {
							$arOrderPropsValues = array();
							$arProfiles = array_filter(
								$arUserProfiles,
								function ($arUserProfile) use ($arHlProfile, $INN) {
									if ($arHlProfile['UF_INN'] == $arUserProfile['PROPERTIES'][$INN])
										return true;
									return false;
								}
							);
							if (0 < count($arProfiles)) {
								$curKey = key($arProfiles);
								$arProfile = current($arProfiles);
								$arOrderPropsValues = $arProfile['PROPERTIES'];
								if ($arHlProfile['UF_NAME']) {
									$arProfile['NAME'] = $arHlProfile['UF_NAME'];
								}
								$arOrderPropsValues[$KPP] = $arHlProfile['UF_KPP'];
								\CSaleOrderUserProps::DoSaveUserProfile(
									$arProfile['USER_ID'],
									$arProfile['ID'],
									$arProfile['NAME'],
									$arProfile['PERSON_TYPE_ID'],
									$arOrderPropsValues,
									$ERRORS
								);
								unset($arHlProfiles[$profKey], $arUserProfiles[$curKey]);
							}
						}
					}
					if (0 < count($arHlProfiles)) {
						unset($profKey, $arProfile);
						foreach ($arHlProfiles as $profKey => $arProfile) {
							$arOrderPropsValues = array();
							if (10 == strlen($arProfile['UF_INN']) || (12 == strlen($arProfile['UF_INN']) && 'ИндивидуальныйПредприниматель' == $arProfile['UF_YURFIZLITSO'])) {
								$personTypeId = 2;
							} elseif (12 == strlen($arProfile['UF_INN']) || 'ФизЛицо' == $arProfile['UF_YURFIZLITSO']) {
								$personTypeId = 1;
							}
							if ($personTypeId == 2) {
								$arOrderPropsValues[$KPP] = $arProfile['UF_KPP'];
								$arOrderPropsValues[$INN] = $arProfile['UF_INN'];
								$arOrderPropsValues[8] = $arProfile['UF_DESCRIPTION'];
								foreach ($FORM_LIST as $fKey => $arForm) {
									if (('ЮрЛицо' == $arProfile['UF_YURFIZLITSO'] && 'ООО' == $arForm['VALUE']) ||
									    ('ИндивидуальныйПредприниматель' == $arProfile['UF_YURFIZLITSO'] && 'ИП' == $arForm['VALUE'])
									   ) {
										$arOrderPropsValues[$FORM] = $arForm['ID'];
										break;
									}
								}
							} elseif ($personTypeId == 1) {
								$arOrderPropsValues[1] = $arProfile['UF_DESCRIPTION'];
							}
							$profileId = \CSaleOrderUserProps::DoSaveUserProfile(
								$userId,
								0,
								$arProfile['UF_NAME'],
								$personTypeId,
								$arOrderPropsValues,
								$ERRORS
							);
						}
					}
				}
			}
		} else {
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', __FILE__.':'.__LINE__.' '.$user->LAST_ERROR.PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', __FILE__.':'.__LINE__.' + $arFields:'.PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', print_r($arFields, true).PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', __FILE__.':'.__LINE__.' + $arUserFields:'.PHP_EOL, FILE_APPEND | LOCK_EX);
			file_put_contents(__DIR__.'/log/error-'.date('Ymd').'.log', print_r($arUserFields, true).PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}
}
// партнеры end

// контрагенты begin
$eventManager->addEventHandler('', 'KontragentyOnAfterAdd', 'KontragentyOnAfterAdd');
$eventManager->addEventHandler('', 'KontragentyOnAfterUpdate', 'KontragentyOnAfterUpdate');

function KontragentyOnAfterAdd (\Bitrix\Main\Entity\Event $event) {
	$arFields = $event->getParameter("fields");

	if ($arFields['UF_INN'] && $arFields['UF_PARTNER']) {
		$userId = 0;
		$INN = 0;
		$KPP = 0;
		$FORM = 0;
		$FORM_LIST = array();
		$ERRORS = array();

		Loader::includeModule('sale');
		$arProperties = OrderPropsTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('ACTIVE' => 'Y', 'CODE' => array('INN', 'FORM', 'KPP')),
		))->FetchAll();
		foreach ($arProperties as $propKey => $arProperty) {
			if ($arProperty['CODE'] == 'INN')
				$INN = $arProperty['ID'];
			if ($arProperty['CODE'] == 'KPP')
				$KPP = $arProperty['ID'];
			if ($arProperty['CODE'] == 'FORM')
				$FORM = $arProperty['ID'];
		}
		if ($FORM) {
			$resForms = \CSaleOrderPropsVariant::GetList(array('ID' => 'ASC'), array('ORDER_PROPS_ID' => $FORM));
			while ($arForm = $resForms->fetch()) {
				$FORM_LIST[$arForm['ID']] = $arForm;
			}
		}

		$hlUsersBlock = Highloadblock\HighloadBlockTable::getById(7)->fetch();
		$usersEntity = Highloadblock\HighloadBlockTable::compileEntity($hlUsersBlock);
		$usersEntityClass = $usersEntity->getDataClass();

		$arHlUser = $usersEntityClass::getList(array(
			'filter' => array('UF_IDENTIFIKATORPART' => $arFields['UF_PARTNER']),
			'select' => array('*'),
			'order' => array('ID' => 'ASC'),
		))->Fetch();
		if ($arHlUser['UF_KOD']) {
			$userRes = UserTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'UF_HLBLOCK_USER_ID' => $arHlUser['UF_KOD'],
				),
			));
			if ($arr = $userRes->fetch()) {
				$userId = $arr['ID'];
			}
			if ($userId > 0) {
				$arOrderPropsValues = array();
				if (10 == strlen($arFields['UF_INN']) || (12 == strlen($arFields['UF_INN']) && 'ИндивидуальныйПредприниматель' == $arFields['UF_YURFIZLITSO'])) {
					$personTypeId = 2;
				} elseif (12 == strlen($arFields['UF_INN']) || 'ФизЛицо' == $arFields['UF_YURFIZLITSO']) {
					$personTypeId = 1;
				}
				if ($personTypeId == 2) {
					$arOrderPropsValues[$KPP] = $arFields['UF_KPP'];
					$arOrderPropsValues[$INN] = $arFields['UF_INN'];
					$arOrderPropsValues[8] = $arFields['UF_DESCRIPTION'];
					foreach ($FORM_LIST as $fKey => $arForm) {
						if (('ЮрЛицо' == $arFields['UF_YURFIZLITSO'] && 'ООО' == $arForm['VALUE']) ||
						    ('ИндивидуальныйПредприниматель' == $arFields['UF_YURFIZLITSO'] && 'ИП' == $arForm['VALUE'])
						   ) {
							$arOrderPropsValues[$FORM] = $arForm['ID'];
							break;
						}
					}
				} elseif ($personTypeId == 1) {
					$arOrderPropsValues[1] = $arFields['UF_DESCRIPTION'];
				}
				$profileId = \CSaleOrderUserProps::DoSaveUserProfile(
					$userId,
					0,
					$arFields['UF_NAME'],
					$personTypeId,
					$arOrderPropsValues,
					$ERRORS
				);
			}
		}
	}
}

function KontragentyOnAfterUpdate (\Bitrix\Main\Entity\Event $event) {
	$arFields = $event->getParameter("fields");

	if ($arFields['UF_INN'] && $arFields['UF_PARTNER']) {
		$userId = 0;
		$INN = 0;
		$KPP = 0;
		$FORM = 0;
		$FORM_LIST = array();
		$ERRORS = array();

		Loader::includeModule('sale');
		$arProperties = OrderPropsTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('ACTIVE' => 'Y', 'CODE' => array('INN', 'FORM', 'KPP')),
		))->FetchAll();
		foreach ($arProperties as $propKey => $arProperty) {
			if ($arProperty['CODE'] == 'INN')
				$INN = $arProperty['ID'];
			if ($arProperty['CODE'] == 'KPP')
				$KPP = $arProperty['ID'];
			if ($arProperty['CODE'] == 'FORM')
				$FORM = $arProperty['ID'];
		}
		if ($FORM) {
			$resForms = \CSaleOrderPropsVariant::GetList(array('ID' => 'ASC'), array('ORDER_PROPS_ID' => $FORM));
			while ($arForm = $resForms->fetch()) {
				$FORM_LIST[$arForm['ID']] = $arForm;
			}
		}

		$hlUsersBlock = Highloadblock\HighloadBlockTable::getById(7)->fetch();
		$usersEntity = Highloadblock\HighloadBlockTable::compileEntity($hlUsersBlock);
		$usersEntityClass = $usersEntity->getDataClass();

		$arHlUser = $usersEntityClass::getList(array(
			'filter' => array('UF_IDENTIFIKATORPART' => $arFields['UF_PARTNER']),
			'select' => array('*'),
			'order' => array('ID' => 'ASC'),
		))->Fetch();
		if ($arHlUser['UF_KOD']) {
			$userRes = UserTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'UF_HLBLOCK_USER_ID' => $arHlUser['UF_KOD'],
				),
			));
			if ($arr = $userRes->fetch()) {
				$userId = $arr['ID'];
			}
			if ($userId > 0) {
				$arUserProfiles = array();
				$profilesRes = \CSaleOrderUserProps::GetList(
					array(),
					array(
						'USER_ID' => (int)($userId)
					)
				);
				while ($arProfile = $profilesRes->fetch()) {
					$arProperties = Sale\OrderUserProperties::getProfileValues((int)$arProfile['ID']);
					$arProfile['PROPERTIES'] = $arProperties;

					$arUserProfiles[] = $arProfile;
				}
				if (0 < count($arUserProfiles)) {
					$arOrderPropsValues = array();
					$arProfiles = array_filter(
						$arUserProfiles,
						function ($arUserProfile) use ($arFields, $INN) {
							if ($arFields['UF_INN'] == $arUserProfile['PROPERTIES'][$INN])
								return true;
							return false;
						}
					);
					if (0 < count($arProfiles)) {
						$curKey = key($arProfiles);
						$arProfile = current($arProfiles);
						$arOrderPropsValues = $arProfile['PROPERTIES'];
						if ($arFields['UF_NAME']) {
							$arProfile['NAME'] = $arFields['UF_NAME'];
						}
						$arOrderPropsValues[$KPP] = $arFields['UF_KPP'];
						\CSaleOrderUserProps::DoSaveUserProfile(
							$arProfile['USER_ID'],
							$arProfile['ID'],
							$arProfile['NAME'],
							$arProfile['PERSON_TYPE_ID'],
							$arOrderPropsValues,
							$ERRORS
						);
					} else {
						$arOrderPropsValues = array();
						if (10 == strlen($arFields['UF_INN']) || (12 == strlen($arFields['UF_INN']) && 'ИндивидуальныйПредприниматель' == $arFields['UF_YURFIZLITSO'])) {
							$personTypeId = 2;
						} elseif (12 == strlen($arFields['UF_INN']) || 'ФизЛицо' == $arFields['UF_YURFIZLITSO']) {
							$personTypeId = 1;
						}
						if ($personTypeId == 2) {
							$arOrderPropsValues[$KPP] = $arFields['UF_KPP'];
							$arOrderPropsValues[$INN] = $arFields['UF_INN'];
							$arOrderPropsValues[8] = $arFields['UF_DESCRIPTION'];
							foreach ($FORM_LIST as $fKey => $arForm) {
								if (('ЮрЛицо' == $arFields['UF_YURFIZLITSO'] && 'ООО' == $arForm['VALUE']) ||
								    ('ИндивидуальныйПредприниматель' == $arFields['UF_YURFIZLITSO'] && 'ИП' == $arForm['VALUE'])
								   ) {
									$arOrderPropsValues[$FORM] = $arForm['ID'];
									break;
								}
							}
						} elseif ($personTypeId == 1) {
							$arOrderPropsValues[1] = $arFields['UF_DESCRIPTION'];
						}
						$profileId = \CSaleOrderUserProps::DoSaveUserProfile(
							$userId,
							0,
							$arFields['UF_NAME'],
							$personTypeId,
							$arOrderPropsValues,
							$ERRORS
						);
					}
				}
			}
		}
	}
}
// контрагенты end

// в почтовое событие добавляем e-mail менеджера
$eventManager->addEventHandler('main', 'OnBeforeEventSend', 'addManagerEmailToMail');
function addManagerEmailToMail(&$arFields, &$arTemplate) {
	$arStatuses = array(
		'SALE_STATUS_CHANGED_N',
		'SALE_STATUS_CHANGED_S',
		'SALE_STATUS_CHANGED_P',
		'SALE_STATUS_CHANGED_F',
		'SALE_ORDER_CANCEL',
		'SALE_NEW_ORDER',
	);
	if (in_array($arTemplate['EVENT_NAME'], $arStatuses)) {
		if (0 < $arFields['ORDER_REAL_ID']) {
			\Bitrix\Main\Loader::includeModule('sale');
			$order = \Bitrix\Sale\Order::load($arFields['ORDER_REAL_ID']);
			$userId = $order->getUserId();
			$arUser = \Bitrix\Main\UserTable::getList([
				'filter' => [
					'ID' => $userId,
				],
				'select' => [
					'ID', 'UF_MANAGEGER_HL'
				]
			])->Fetch();
			if (0 < $arUser['UF_MANAGEGER_HL']) {
				$hlManagersBlock = Highloadblock\HighloadBlockTable::getById(9)->fetch();
				$managersEntity = Highloadblock\HighloadBlockTable::compileEntity($hlManagersBlock);
				$managersEntityClass = $managersEntity->getDataClass();
				$arManager = $managersEntityClass::getList(array(
					'filter' => array(
						'ID' => $arUser['UF_MANAGEGER_HL'],
					),
					'select' => array('ID', 'UF_NAME', 'UF_ELEKTRONNAYAPOCHT'),
				))->Fetch();
				if ($arManager['UF_ELEKTRONNAYAPOCHT']) {
					$arFields['MANAGER_EMAIL'] = $arManager['UF_ELEKTRONNAYAPOCHT'];
				}
				if ($arTemplate['EVENT_NAME'] == 'SALE_NEW_ORDER' && $arManager['UF_NAME']) {
					$arFields['MANAGER_NAME'] = 'Ваш менеджер: '.$arManager['UF_NAME'].'<br>';
				}
			} else {
				$arFields['MANAGER_EMAIL'] = '';
				$arFields['MANAGER_NAME'] = '';
			}
			$propertyCollection = $order->getPropertyCollection();
			$addrPropValue = $propertyCollection->getItemByOrderPropertyId(19);
			if ($addrPropValue->getValue()) {
				$arFields['DELIVERY_ADDRESS'] = 'Адрес доставки: '.$addrPropValue->getValue().'<br>';
			} else {
				$arFields['DELIVERY_ADDRESS'] = '';
			}
			$arFields['ORDER_ID'] = $order->getField('ACCOUNT_NUMBER');
		}
	}
}

// при изменении пользоватля нужно оповестить менеджера пользователя
$eventManager->addEventHandler('main', 'OnAfterUserUpdate', 'OnAfterUserUpdateManager');
function OnAfterUserUpdateManager($arFields) {
	if ($arFields['RESULT'] && 0 < $arFields['ID'] && 's1' == $arFields['LID']) {
		$arUser = Bitrix\Main\UserTable::getList([
			'filter' => ['ID' => $arFields['ID']],
			'select' => ['ID', 'UF_MANAGER', 'UF_MANAGEGER_HL'],
		])->Fetch();
		$arEventFields = array(
			'USER_ID' => $arFields['ID'],
			'LOGIN' => $arFields['LOGIN'],
			'NAME' => $arFields['NAME'],
			'LAST_NAME' => $arFields['LAST_NAME'],
			'EMAIL' => $arFields['EMAIL'],
			'PERSONAL_PHONE' => $arFields['PERSONAL_PHONE'],
		);
		if (0 < $arUser['UF_MANAGER'] && Bitrix\Main\Loader::includeModule('iblock')) {
			$res = \CIBlockElement::GetList(
				[],
				[
					'ID' => $arUser['UF_MANAGER'],
				],
				false,
				false,
				['ID', 'IBLOCK_ID', 'PROPERTY_EMAIL']
			);
			$arManager = $res->Fetch();
			if ($arManager['PROPERTY_EMAIL_VALUE']) {
				$arEventFields['EMAIL_TO'] = $arManager['PROPERTY_EMAIL_VALUE'];
			}
		} else if (0 < $arUser['UF_MANAGEGER_HL'] && Bitrix\Main\Loader::includeModule('highloadblock')) {
			$hlManagersBlock = Highloadblock\HighloadBlockTable::getById(9)->fetch();
			$managersEntity = Highloadblock\HighloadBlockTable::compileEntity($hlManagersBlock);
			$managersEntityClass = $managersEntity->getDataClass();
			$arManager = $managersEntityClass::getList(array(
				'filter' => array(
					'ID' => $arUser['UF_MANAGEGER_HL'],
				),
				'select' => array('ID', 'UF_ELEKTRONNAYAPOCHT'),
			))->Fetch();
			if ($arManager['UF_ELEKTRONNAYAPOCHT']) {
				$arEventFields['EMAIL_TO'] = $arManager['UF_ELEKTRONNAYAPOCHT'];
			}
		}
		if (isset($arEventFields['EMAIL_TO']) && !empty($arEventFields['EMAIL_TO'])) {
			\CEvent::Send('USER_UPDATE', SITE_ID, $arEventFields, 'N', 175);
		}
	}
}

// при изменении заказа оповещаем менеджера
$eventManager->addEventHandler('sale', 'OnSaleBasketItemEntitySaved', array('OrderHandlers', 'OnSaleBasketItemEntitySavedManager'));
$eventManager->addEventHandler('sale', 'OnSalePropertyValueEntitySaved', array('OrderHandlers', 'OnSaleSalePropertyValueEntitySavedManager'));
$eventManager->addEventHandler('sale', 'OnSaleOrderSaved', array('OrderHandlers', 'OnSaleOrderSavedManager'));

class OrderHandlers {
	protected static $isChanged = false;

	public static function OnSaleBasketItemEntitySavedManager(Main\Event $event) {
		if (!self::$isChanged)
			self::$isChanged = true;
	}

	public static function OnSaleSalePropertyValueEntitySavedManager(Main\Event $event) {
		if (!self::$isChanged)
			self::$isChanged = true;
	}

	public static function OnSaleOrderSavedManager(Main\Event $event) {
		global $USER;

		$isNew = $event->getParameter("IS_NEW");

		if (self::$isChanged && !$isNew) {
			$arUser = Bitrix\Main\UserTable::getList([
				'filter' => ['ID' => $USER->GetID()],
				'select' => ['ID', 'UF_MANAGER'],
			])->Fetch();

			if (0 < $arUser['UF_MANAGER'] && \Bitrix\Main\Loader::includeModule('iblock')) {
				$res = \CIBlockElement::GetList(
					[],
					[
						'ID' => $arUser['UF_MANAGER'],
					],
					false,
					false,
					['ID', 'IBLOCK_ID', 'PROPERTY_EMAIL']
				);
				$arManager = $res->Fetch();
				if ($arManager['PROPERTY_EMAIL_VALUE']) {
					$order = $event->getParameter("ENTITY");
					$basket = $order->getBasket();
					$basketItems = $basket->getBasketItems();
					$orderList = '';

					foreach ($basketItems as $basketItem) {
						$price = $basketItem->getPrice();
						$fullPrice = $basketItem->getFinalPrice();
						$quantity = $basketItem->getQuantity();
						$orderList .= $basketItem->getField('NAME') . ' - ' . $quantity . ' шт &times; ' . number_format($price, 2, '.', ' ') . ' ' . CurrencyFormat($fullPrice, $basketItem->getCurrency()) . '<br>';
					}

					$arEventFields = array(
						'ORDER_ID' => $order->getId(),
						'ORDER_ACCOUNT_NUMBER_ENCODE' => $order->getField('ACCOUNT_NUMBER'),
						'ORDER_REAL_ID' => $order->getField('ID'),
						'ORDER_DATE' => $order->getDateInsert()->format('d.m.Y H:i:s'),
						'ORDER_USER' => $USER->GetFullName(),
						'PRICE' => $order->getPrice(),
						'EMAIL' => $USER->GetEmail(),
						'SALE_EMAIL' => Bitrix\Main\Config\Option::get("sale", "order_email"),
						'ORDER_LIST' => $orderList,
						'EMAIL_TO' => $arManager['PROPERTY_EMAIL_VALUE'],
						'BCC' => '',
					);
					\CEvent::Send('SALE_USER_CHANGE_ORDER', SITE_ID, $arEventFields, 'N', 177);
				}
			}
		}
	}
}

// установка координат на карте по адресу партнера
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', array('IblockHandlers', 'SetCoordinatesByAddress'));
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', array('IblockHandlers', 'UpdateCoordinatesByAddress'));

class IblockHandlers {
	private static $parthersIblockIds = [
		8, // замочная компания
		44 // стандарт
	];

	function SetCoordinatesByAddress (&$arFields) {
		if (in_array($arFields['IBLOCK_ID'], self::$parthersIblockIds)) {
			$resProps = \Bitrix\Iblock\PropertyTable::getList([
				'filter' => [
					'IBLOCK_ID' => $arFields['IBLOCK_ID'],
					'ACTIVE' => 'Y',
					'CODE' => ['ADDRESS', 'LOCATION'],
				],
				'select' => ['ID', 'CODE'],
			]);
			while ($arProp = $resProps->Fetch()) {
				if ('ADDRESS' == $arProp['CODE']) $postAddressId = $arProp['ID'];
				elseif ('LOCATION' == $arProp['CODE']) $locationId = $arProp['ID'];
			}
			$postAddress = current($arFields['PROPERTY_VALUES'][$postAddressId]);
			$postAddressValue = $postAddress['VALUE'];
			if (0 < strlen($postAddressValue) && 0 < $locationId) {
				$obResult = GetCoordinatesByAddress($postAddressValue);

				if ('object' == gettype($obResult)) {
					$response = $obResult->response;
					$geoCollection = $response->GeoObjectCollection;
					$resultObjects = $geoCollection->featureMember;
					if (!empty($resultObjects)) {
						$resultObject = $resultObjects[0]->GeoObject;
						$point = $resultObject->Point;
						$coordinates = $point->pos;
					}
					$arCoordinates = explode(' ', $coordinates);

					$arFields['PROPERTY_VALUES'][$locationId] = array(
						array(
							'VALUE' => $arCoordinates[1].','.$arCoordinates[0],
						),
					);
				}
			}
		}
	}

	function UpdateCoordinatesByAddress (&$arFields) {
		if (in_array($arFields['IBLOCK_ID'], self::$parthersIblockIds)) {
			$elementId = $arFields['ID'];

			$resProps = \Bitrix\Iblock\PropertyTable::getList([
				'filter' => [
					'IBLOCK_ID' => $arFields['IBLOCK_ID'],
					'ACTIVE' => 'Y',
					'CODE' => ['ADDRESS', 'LOCATION'],
				],
				'select' => ['ID', 'CODE'],
			]);
			while ($arProp = $resProps->Fetch()) {
				if ('ADDRESS' == $arProp['CODE']) $postAddressId = $arProp['ID'];
				elseif ('LOCATION' == $arProp['CODE']) $locationId = $arProp['ID'];
			}

			$arLastAddress = \MA\Iblock\ElementPropertyTable::getList([
				'filter' => [
					'PROPERTY.IBLOCK_ID' => $arFields['IBLOCK_ID'],
					'PROPERTY.ID' => [$postAddressId],
				]
			])->Fetch();
			$lastAddressValue = $arLastAddress['VALUE'];

			$currentAddress = current($arFields['PROPERTY_VALUES'][$postAddressId]);
			$currentAddressValue = $currentAddress['VALUE'];

			if (($currentAddressValue != $lastAddressValue) && 0 < $locationId) {
				$obResult = GetCoordinatesByAddress($currentAddressValue);

				if ('object' == gettype($obResult)) {
					$response = $obResult->response;
					$geoCollection = $response->GeoObjectCollection;
					$resultObjects = $geoCollection->featureMember;
					if (!empty($resultObjects)) {
						$resultObject = $resultObjects[0]->GeoObject;
						$point = $resultObject->Point;
						$coordinates = $point->pos;
					}
					$arCoordinates = explode(' ', $coordinates);

					$arFields['PROPERTY_VALUES'][$locationId] = array(
						array(
							'VALUE' => $arCoordinates[1].','.$arCoordinates[0],
						),
					);
				}
			}
		}
	}
}

// при изменении полей пользователя нужно проверить e-mail и логин
$eventManager->addEventHandler('main', 'OnBeforeUserUpdate', array('MainHandlers', 'CheckUserLoginByEmail'));

class MainHandlers {
	public static function CheckUserLoginByEmail(&$arFields) {
		if (isset($arFields['ID']) && $arFields['ID'] > 100) {
			if ($arFields['LOGIN'] !== $arFields['EMAIL']) {
				$arFields['LOGIN'] = $arFields['EMAIL'];
			}
		}
	}
}