<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$arResult = [];

$arResult['STEP'] = 1;
$arResult['SCREENS'] = [
	'type',
	'heating',
	'switch',
	'electrical_floorheating_devices',
	'central_touch_screen',
	'number_manifolds',
	'radiators',
	'heating_cooling',
	'devices_light',
	'thermostat_add',
	'light_devices',
	'result',
];

$arResult['ITEMS'] = [];

$arResult['ITEMS'] = $this->getItemByStep($arResult['STEP']);
$arResult['TITLE'] = $this->getTitle($arResult['STEP']);
$arResult['ERROR'] = $this->getErrorByStep($arResult['STEP']);
$arResult['TOP_TEXT'] = $this->getTopTextByStep($arResult['STEP']);
$arResult['NOTE'] = $this->getNoteByStep($arResult['STEP']);
$arResult['CUR_SCREEN'] = $this->getCurrentScreen();

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$arRequest = $request->toArray();
if ($request->isAjaxRequest() || $arRequest['is_ajax'] == 'Y') {
	$APPLICATION->RestartBuffer();
	\Bitrix\Main\Loader::includeModule('iblock');
	$value = $arRequest['data']['type'];

	$step = $arRequest['step'];
	$action = $arRequest['action'];
	$products = $arRequest['products'];
	$chain = $arRequest['chain'];
	$field = $arRequest['field'];
	$value = $arRequest['value'];

	if ($chain) {
		try {
			$arChain = json_decode($chain, true);
			$arChain[] = $arRequest;
		} catch (Exception $e) {}
	}

	$formData = [
		'PRODUCTS' => [],
	];

	if ($action == 'back') {
		array_pop($arChain);
	}

	foreach ($arChain as $chainItem) {
		unset($chainItem['step'], $chainItem['action'], $chainItem['chain']);
		$formData[$chainItem['field']] = $chainItem['value'];
		if (isset($chainItem['products']) && is_array($chainItem['products'])) {
			foreach ($chainItem['products'] as $k => $v) {
				$formData['PRODUCTS'][$k] = $v;
			}
		}
		if (isset($chainItem['number_termostats'])) {
			$formData['number_termostats'] = $chainItem['number_termostats'];
		}
		if (isset($chainItem['number_groups'])) {
			$formData['number_groups'] = $chainItem['number_groups'];
		}
		if (isset($chainItem['need_central_touch_screen'])) {
			$formData['need_central_touch_screen'] = $chainItem['need_central_touch_screen'];
		}
		if (isset($chainItem['wall_reciever'])) {
			$formData['wall_reciever'] = $chainItem['wall_reciever'];
		}
		if (isset($chainItem['number_radiators'])) {
			$formData['number_radiators'] = $chainItem['number_radiators'];
		}
		if (isset($chainItem['number_rooms'])) {
			$formData['number_rooms'] = $chainItem['number_rooms'];
		}
		if (isset($chainItem['rooms_number_floor_heating'])) {
			$formData['rooms_number_floor_heating'] = $chainItem['rooms_number_floor_heating'];
		}
		if (isset($chainItem['amps_per_room'])) {
			$formData['amps_per_room'] = $chainItem['amps_per_room'];
		}
		if (isset($chainItem['temp_regulator10'])) {
			$formData['temp_regulator10'] = $chainItem['temp_regulator10'];
		}
		if (isset($chainItem['temp_regulator16'])) {
			$formData['temp_regulator16'] = $chainItem['temp_regulator16'];
		}
		if (isset($chainItem['external_sensor'])) {
			$formData['external_sensor'] = $chainItem['external_sensor'];
		}
		if (isset($chainItem['rooms_number_infrared_panel'])) {
			$formData['rooms_number_infrared_panel'] = $chainItem['rooms_number_infrared_panel'];
		}
		if (isset($chainItem['number_panels_room'])) {
			$formData['number_panels_room'] = $chainItem['number_panels_room'];
		}
		if (isset($chainItem['panel_amps_per_room'])) {
			$formData['panel_amps_per_room'] = $chainItem['panel_amps_per_room'];
		}
		if (isset($chainItem['panel_temp_regulator10'])) {
			$formData['panel_temp_regulator10'] = $chainItem['panel_temp_regulator10'];
		}
		if (isset($chainItem['panel_temp_regulator16'])) {
			$formData['panel_temp_regulator16'] = $chainItem['panel_temp_regulator16'];
		}
	}

	$arResult['FORM_DATA'] = $formData;

	if ($action == 'next') {
		$step++;
	} elseif ($action == 'restart') {
		$step = 1;
		$field = '';
		$value = '';
	} elseif ($action == 'back') {
		if (is_array($arChain)) {
			$arSelected = array_pop($arChain);
			$arRequest = array_pop($arChain);

			if ($arSelected['field'] == 'number_manifolds') {
				unset($arSelected['need_central_touch_screen'], $arSelected['wall_reciever']);
				unset($arResult['FORM_DATA']['need_central_touch_screen'], $arResult['FORM_DATA']['wall_reciever']);
				unset($arRequest['need_central_touch_screen']);
			}

			$step = $arSelected['step'];
			$products = $arRequest['products'];
			$chain = $arRequest['chain'];
			$field = $arRequest['field'];
			$value = $arRequest['value'];
		}
		if (!$step) {
			$step = 1;
		}
	}

	$arResult['PARAMS'] = [
		'step' => $step,
		'action' => $action,
		'field' => $field,
		'value' => $value,
	];

	$arResult['STEP'] = $step;

	$final = $this->checkFinalStep();

	if ($final && $action != 'back') {
		$arResult['TITLE'] = 'Result';
		$arResult['FINAL'] = 'Y';
		$arResult['ITEMS'] = $this->getFinalItems();
		$arResult['HEADERS'] = $this->getFinalHeaders();
		$arResult['ERROR'] = '';
		$arResult['TOP_TEXT'] = '';
		$arResult['NOTE'] = '';
		$arResult['CUR_SCREEN'] = 'result';
		$arResult['PDF_TEXT'] = $this->getPdfText();
		$arResult['EMAIL_TEXT'] = $this->getEmailText();
		$arResult['EMAIL_BUTTON_TEXT'] = $this->getEmailButtonText();
	} else {
		$arResult['TITLE'] = $this->getTitle($arResult['STEP'], $field, $value);
		$arResult['ITEMS'] = $this->getItemByStep($arResult['STEP'], $field, $value);
		$arResult['ERROR'] = $this->getErrorByStep($arResult['STEP'], $field, $value);
		$arResult['TOP_TEXT'] = $this->getTopTextByStep($arResult['STEP'], $field, $value);
		$arResult['NOTE'] = $this->getNoteByStep($arResult['STEP'], $field, $value);
		$arResult['CUR_SCREEN'] = $this->getCurrentScreen();

		if ($action == 'back') {
			$this->modifyItems($arResult['ITEMS'], $arSelected);
		}
	}

	$arResult['REQUEST'] = $arRequest;

	if ($request->getPost('action') == 'generate_pdf' || $request->getPost('action') == 'send_email') {
		$this->setTemplateName('pdf');
		$this->IncludeComponentTemplate();
	} else {
		echo CUtil::PhpToJsObject($arResult, true, true);
	}
	exit;
}

CUtil::InitJSCore(array('selectric'));
$this->IncludeComponentTemplate();

// delete old files
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
preg_match("/^.*\/$/", $documentRoot, $matches);
if (!empty($matches)) {
	$documentRoot = substr($documentRoot, 0, -1);
}
$folder = '/upload/vision';

$curTime = time();
$day = 60*60*24;
$day = 60;
if ($dh = opendir($documentRoot.$folder)) {
	while (($file = readdir($dh)) !== false) {
		if ($file == '.' || $file == '..')
			continue;

		if (($curTime - filectime($documentRoot.$folder.'/'.$file)) > $day)
			unlink($documentRoot.$folder.'/'.$file);
	}
}