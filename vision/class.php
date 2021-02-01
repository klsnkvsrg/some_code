<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Vision extends CBitrixComponent {
	private $iblockId = 19;
	
	/**
	 * articles for touch screen selection
	 */
	private $arTouchScreenArticles_EN = [
		'P07255',
		'P07256',
	];

	/**
	 * additional articles for touch screen
	 */
	private $arTouchScreenAdd_EN = [
		'P06402',
	];

	/**
	 * articles for light devices
	 */
	private $arLightDevices_EN = [
		'P07296',
		// 'P07296',
	];

	/**
	 * articles for temperature regulators for 10 amperes
	 */
	private $arTempRegulators10_EN = [
		'P06674',
		'P06675',
		'P06676',
	];

	/**
	 * articles for temperature regulators for 16 amperes
	 */
	private $arTempRegulators16_EN = [
		'P06675',
		'P06676',
	];

	/**
	 * articles for external floor sensor
	 */
	private $arExternalSensor_EN = [
		'P01228',
	];

	/**
	 * articles of temperature modules if termostats count 1-6
	 */
	private $arTempModules_0_EN = [
		// 'P06678'
		'P06676'
	];

	/**
	 * articles of temperature modules if termostats count 7-10
	 */
	private $arTempModules_1_EN = [
		// 'P06678',
		'P06676',
		'P06680',
	];

	/**
	 * articles of temperature modules if termostats count 7-12
	 */
	private $arTempModules_2_EN = [
		// 'P06678',
		'P06676',
		'P06679',
	];

	/**
	 * articles of digital termostats for manifolds
	 */
	private $arDigitalThermostats_EN = [
		'P07710',
		'P07930',
		'P01228',
	];

	/**
	 * articles of actuators for termostats
	 */
	private $arActuators_EN = [
		'10029671',
	];

	/**
	 * articles of termostats
	 */
	private $arThermostats_EN = [
		// 'P06671',
		'P07710',
		'P07930',
	];

	/**
	 * articles of termostats actuators for each radiator
	 */
	private $arThermostatsActuators_EN = [
		'P06681',
	];

	/**
	 * articles of termostats in rooms with multiple radiators
	 */
	private $arThermostatsMultiple_EN = [
		// 'P06671',
		'P07710',
	];

	/**
	 * articles of heat and cool modules
	 */
	private $arHeatCoolModules_EN = [
		'P06066',
	];

	/**
	 * @param $step int - step of vision calculator
	 * @param $value int - value of prev step
	 * @return array - items for form
	 */
	public function getTitle($step, $field = '', $value = false) {
		$formData = $this->arResult['FORM_DATA'];
		if ($formData['need_central_touch_screen'] && $formData['central_touch_screen'] == 2) {
			return 'Choice central touch screen';
		} else {
			switch ($field) {
				case 'type':
					switch ($value) {
						case 1:
							return 'Choice heating';
						case 2:
							return 'Choice switch';
						case 3:
							return 'Choice central touch screen';
					}
				case 'heating':
					if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['heating'] == 2 && !isset($formData['central_touch_screen'])) {
						return 'Choice central touch screen';
					} else if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['heating'] == 2 && $formData['central_touch_screen'] == 2) {
						return 'Choice central touch screen';
					} else if ($formData['type'] == 2 && $formData['switch'] > 0) {
						return 'Choice manifolds';
					} else {
						switch ($value) {
							case 1:
								return 'Choice central touch screen';
							case 2:
								return 'Choice central touch screen';
							case 3:
								return 'Choice central touch screen';
						}
					}
				case 'central_touch_screen':
					if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['central_touch_screen'] == 1 && $formData['heating'] == 2) {
						return 'Choice manifolds';
					} else if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['central_touch_screen'] == 1) {
						return 'Choice heating';
					} else if ($formData['type'] == 2 && $formData['switch'] == 1 && $formData['central_touch_screen'] == 1) {
						return 'Choice heating';
						// return 'Choice manifolds';
					} else if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['central_touch_screen'] == 2) {
						// return 'Choice manifolds';
						return 'Choice heating';
					} else if ($formData['type'] == 3) {
						switch ($value) {
							case 1:
							case 2:
								return 'Choice electrical floor heating/devices';
						}
					} else {
						switch ($value) {
							case 1:
								if (isset($formData['number_manifolds'])) {
									return 'Choice thermostat';
								} else {
									return 'Choice manifolds';
								}
							case 2:
								return 'Choice manifolds';
						}
					}
				case 'number_manifolds':
					if ($formData['heating'] == 2) {
						return 'Choice radiators';
					} else {
						if ($formData['number_manifolds'] > 0 && !isset($formData['PRODUCTS']['thermostat'])) {
							return 'Choice thermostat';
						} else {
							return 'Devices on/off/light';
						}
					}
				case 'number_radiators':
					if ($formData['number_manifolds'] > 0 && !isset($formData['PRODUCTS']['thermostat'])) {
						return 'Choice thermostat';
					} else {
						return 'Devices on/off/light';
					}
				case 'switch':
					return 'Choice central touch screen';
				case 'rooms_number_floor_heating':
					// if ($formData['number_manifolds'] > 0) {
					if (!isset($formData['PRODUCTS']['thermostat_add'])) {
						return 'Choice thermostat';
					} else {
						return 'Devices on/off/light';
					}
				case 'thermostat_add':
					return 'Devices on/off/light';
				default:
					// return 'Choice type';
					return 'Choice kind';
			}
		}
	}

	/**
	 * @param $step int - step of vision calculator
	 * @param $value int - value of prev step
	 * @return array - items for form
	 */
	public function getItemByStep($step, $field = '', $value = false) {
		$formData = $this->arResult['FORM_DATA'];
		$arItems = [];
		if (($formData['need_central_touch_screen'] && $formData['central_touch_screen'] == 2) || ($formData['need_central_touch_screen'] && $formData['central_touch_screen'] == 1 && $this->arResult['PARAMS']['action'] == 'back')) {
			$arItems = $this->getItemsForTouchScreen();
			$this->disableTouchScreenItems($arItems);
		} else {
			switch ($field) {
				case 'type':
					switch ($value) {
						case 1:
							$arItems = $this->getItemsForHeating();
							break;
						case 2:
							$arItems = $this->getItemsForSwitch();
							break;
						case 3:
							$arItems = $this->getItemsForTouchScreen();
							break;
					}
					break;
				case 'heating':
					if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['heating'] == 2 && !isset($formData['central_touch_screen'])) {
						$arItems = $this->getItemsForTouchScreen();
						$this->disableTouchScreenItems($arItems);
						break;
					} else if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['heating'] == 2 && $formData['central_touch_screen'] == 2) {
						$arItems = $this->getItemsForTouchScreen();
						$this->disableTouchScreenItems($arItems);
						break;
					} else if ($formData['type'] == 2 && $formData['switch'] > 0) {
						$arItems = $this->getItemsForChoiceManifolds();
						break;
					} else {
						switch ($value) {
							case 1:
								$arItems = $this->getItemsForTouchScreen();
								break;
							case 2:
								$arItems = $this->getItemsForTouchScreen();
								$this->disableTouchScreenItems($arItems);
								break;
							case 3:
								$arItems = $this->getItemsForTouchScreen();
								$this->disableTouchScreenItems($arItems);
								break;
						}
					}
					break;
				case 'switch':
					switch ($value) {
						case 1:
							$arItems = $this->getItemsForTouchScreen();
							$this->disableTouchScreenItems($arItems);
							break;
						case 2:
							$arItems = $this->getItemsForTouchScreen();
							break;
					}
					break;
				case 'central_touch_screen':
					if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['central_touch_screen'] == 1 && $formData['heating'] == 2) {
						$arItems = $this->getItemsForChoiceManifolds();
						break;
					} else if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['central_touch_screen'] == 1) {
						$arItems = $this->getItemsForHeating();
						$this->disableRadiatorsItem($arItems);
						break;
					} else if ($formData['type'] == 2 && $formData['switch'] == 1 && $formData['central_touch_screen'] == 1) {
						$arItems = $this->getItemsForHeating();
						$this->disableRadiatorsItem($arItems);
						break;
					} else if ($formData['type'] == 2 && $formData['switch'] == 2 && $formData['central_touch_screen'] == 2) {
						$arItems = $this->getItemsForHeating();
						$this->disableRadiatorsItem($arItems);
						break;
					} else if ($formData['type'] == 3) {
						switch ($value) {
							case 1:
							case 2:
								$arItems = $this->getItemsForElectricalHeating();
								break;
						}
					} else {
						switch ($value) {
							case 1:
								if ($formData['type'] == 1 && $formData['heating'] == 3) {
									$arItems = $this->getItemsForRadiators();
								} else {
									if (isset($formData['number_manifolds']) && $this->arResult['PARAMS']['action'] != 'back') {
										$arItems = $this->getItemsForTermostats();
									} else {
										$arItems = $this->getItemsForChoiceManifolds();
									}
								}
								break;
							case 2:
								$arItems = $this->getItemsForChoiceManifolds();
								break;
						}
					}
					break;
				case 'number_manifolds':
					if ($formData['heating'] == 2) {
						$arItems = $this->getItemsForRadiators();
					} else {
						if ($formData['number_manifolds'] > 0 && !isset($formData['PRODUCTS']['thermostat'])) {
							$arItems = $this->getItemsForTermostats();
						} else {
							$arItems = $this->getItemsForLightDevices();
						}
					}
					break;
				case 'number_radiators':
					if ($formData['number_manifolds'] > 0 && !isset($formData['PRODUCTS']['thermostat'])) {
						$arItems = $this->getItemsForTermostats();
					} else {
						$arItems = $this->getItemsForLightDevices();
					}
					break;
				case 'rooms_number_floor_heating':
					if (!isset($formData['PRODUCTS']['thermostat_add'])) {
						$arItems = $this->getItemsForTermostats();
					} else {
						$arItems = $this->getItemsForLightDevices();
					}
					break;
				case 'thermostat_add':
					$arItems = $this->getItemsForLightDevices();
					break;
				default:
					$arItems = $this->getItemsForType();
			}
		}
		return $arItems;
	}

	/**
	 * @param $step int - step of vision calculator
	 * @param $value int - value of prev step
	 * @return array - items for form
	 */
	public function getErrorByStep($step, $field = '', $value = false) {
		$formData = $this->arResult['FORM_DATA'];
		switch ($field) {
			case 'type':
				switch ($value) {
					case 1:
						return 'Choose a heating type';
					case 2:
						return 'Choose the switch';
					case 3:
						return 'Maak een keuze';
				}
				break;
			case 'heating':
				switch ($value) {
					case 1:
						return 'Choice central touch screen';
					case 2:
						return 'Choice central touch screen';
					case 3:
						return 'Choice central touch screen';
				}
				break;
			case 'switch':
				return 'Choice central touch screen';
			case 'central_touch_screen':
				if (!isset($formData['number_manifolds'])) {
					return 'This configuration has more than 50 thermostats, please contact Watts Benelux.';
				}
			default:
				return 'Choose a type';
				break;
		}
	}

	/**
	 * @param $step int - step of vision calculator
	 * @param $value int - value of prev step
	 * @return array - items for form
	 */
	public function getTopTextByStep($step, $field = '', $value = false) {
		return '';
		switch ($field) {
			case 'type':
				switch ($value) {
					case 1:
						return '';
					case 2:
						return '';
					case 3:
						return '';
				}
				break;
			case 'heating':
				switch ($value) {
					case 1:
						return 'Choice central touch screen';
					case 2:
						return 'Which central unit?';
					case 3:
						return 'Choice central touch screen';
				}
				break;
			default:
				return '';
				break;
		}
	}

	/**
	 * @param $step int - step of vision calculator
	 * @param $value int - value of prev step
	 * @return array - items for form
	 */
	public function getNoteByStep($step, $field = '', $value = false) {
		$formData = $this->arResult['FORM_DATA'];
		switch ($field) {
			case 'type':
				switch ($value) {
					case 1:
						return '';
					case 2:
						return '';
					case 3:
						return 'Note: a touchscreen is required for some configurations.<br>Enables remote control from Smartphone App for iOS and Android';
				}
				break;
			case 'heating':
				switch ($value) {
					case 1:
						return 'Note: a touchscreen is required for some configurations.<br>Enables remote control from Smartphone App for iOS and Android';
					case 2:
						return 'Comment: a Central unit should be used when combining Floor and radiator';
					case 3:
						return 'Choice central touch screen';
				}
				break;
			case 'central_touch_screen':
				if ($this->arResult['FORM_DATA']['type'] == 2 && $this->arResult['FORM_DATA']['switch'] > 0) {
					return '';
				} else if ($formData['type'] == 3) {
					return '';
				} else {
					switch ($value) {
						case 1:
							if (!isset($formData['number_manifolds']) || ($this->arResult['PARAMS']['action'] == 'back' && isset($formData['number_manifolds']))) {
								return '* With more than 12 thermostats per manifold: Contact your sales representative<br>** To control circuits loops';
							}
						case 2:
							if (!isset($formData['number_manifolds']) || ($this->arResult['PARAMS']['action'] == 'back' && isset($formData['number_manifolds']))) {
								return '* With more than 12 thermostats per manifold: Contact your sales representative<br>** To control circuits loops';
							}
					}
				}
				break;
			case 'number_manifolds':
				if ($formData['heating'] == 2) {
					return '*We recommend to install a thermostat to control multiple TH in a room';
				} else {
					return '';
				}
				break;
			case 'switch':
				switch ($value) {
					case 1:
						return 'Note: a touchscreen is required for some configurations.<br>Enables remote control from Smartphone App for iOS and Android';
						break;
					case 2:
						return 'Note: a touchscreen is required for some configurations.<br>Enables remote control from Smartphone App for iOS and Android';
						break;
				}
			default:
				return '';
		}
	}

	public function getPdfText() {
		return 'Download this list in PDF format';
	}

	public function getEmailText() {
		return 'Enter your email address to receive the results';
	}

	public function getEmailButtonText() {
		return 'Send';
	}

	public function getCurrentScreen() {
		if (!empty($this->arResult['ITEMS'])) {
			foreach ($this->arResult['ITEMS'] as $key => $arItem) {
				if (in_array($arItem['CONTROL_NAME'], $this->arResult['SCREENS'])) {
					return $arItem['CONTROL_NAME'];
				}
			}
		}
	}

	public function checkFinalStep() {
		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] == 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}
		
		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] == 0 &&
			$this->arResult['FORM_DATA']['wall_reciever'] == 1 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			$this->arResult['FORM_DATA']['wall_reciever'] == 1 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] == 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['wall_reciever'] == 2 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['wall_reciever'] == 2 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 2 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] == 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['number_radiators'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 2 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['number_radiators'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 3 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['number_radiators'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			$this->arResult['FORM_DATA']['wall_reciever'] == 2 &&
			$this->arResult['FORM_DATA']['need_central_touch_screen'] == 1
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			$this->arResult['FORM_DATA']['wall_reciever'] == 2 &&
			$this->arResult['FORM_DATA']['need_central_touch_screen'] == 1
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 1 &&
			$this->arResult['FORM_DATA']['heating'] == 1 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] > 0 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen']) &&
			$this->arResult['FORM_DATA']['number_manifolds'] > 3 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']) &&
			$this->arResult['FORM_DATA']['need_central_touch_screen'] == 1
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 2 &&
			$this->arResult['FORM_DATA']['switch'] > 0 &&
			$this->arResult['FORM_DATA']['heating'] > 0 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 2 &&
			$this->arResult['FORM_DATA']['switch'] == 2 &&
			$this->arResult['FORM_DATA']['heating'] > 0 &&
			$this->arResult['FORM_DATA']['central_touch_screen'] == 1 &&
			isset($this->arResult['FORM_DATA']['number_manifolds']) &&
			$this->arResult['FORM_DATA']['need_central_touch_screen'] == 1 &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['central_touch_screen'])
		) {
			return true;
		}

		if (
			$this->arResult['FORM_DATA']['type'] == 3 &&
			isset($this->arResult['FORM_DATA']['rooms_number_floor_heating']) &&
			isset($this->arResult['FORM_DATA']['rooms_number_infrared_panel']) &&
			!empty($this->arResult['FORM_DATA']['PRODUCTS']['light_devices'] &&
			isset($this->arResult['FORM_DATA']['PRODUCTS']['thermostat_add']))
		) {
			return true;
		}

		return false;
	}

	public function getFinalItems() {
		$arItems = [];
		$arProducts = [];
		$arArticles = [];
		$lang = $this->getLanguage();
		$formData = $this->arResult['FORM_DATA'];

		if ($formData['type'] == 2) {
			foreach ($this->{'arHeatCoolModules_'.$lang} as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => 1,
				];
				$arArticles[] = $article;
			}
		}
		if ($formData['PRODUCTS']['central_touch_screen']) {
			$arProducts[] = [
				'ARTICLE' => $formData['PRODUCTS']['central_touch_screen'],
				'QUANTITY' => 1,
			];
			$arArticles[] = $formData['PRODUCTS']['central_touch_screen'];
		}
		if ($formData['number_termostats'] && $formData['number_groups']) {
			$totalTermostatsCount = 0;
			$totalActuatorsCount = 0;
			$termostatsInfo = [];
			foreach ($formData['number_groups'] as $key => $count) {
				if ($count >= 1 && $count <= 6) {
					$int = 0;
				} else if ($count >= 7 && $count <= 10) {
					$int = 1;
				} else if ($count >= 7 && $count <= 12) {
					$int = 2;
				}

				foreach ($this->{'arTempModules_'.$int.'_'.$lang} as $article) {
					if (!isset($termostatsInfo[$article]))
						$termostatsInfo[$article] = 0;
					
					$arArticles[] = $article;
					$termostatsInfo[$article] += 1;
				}

				$totalTermostatsCount += $count;
				$totalActuatorsCount += $formData['number_groups'][$key];
			}

			if (!empty($termostatsInfo)) {
				foreach ($termostatsInfo as $article => $count) {
					$arProducts[] = [
						'ARTICLE' => $article,
						'QUANTITY' => $count,
					];
					$arArticles[] = $article;
				}
			}

			if ($totalTermostatsCount > 0) {
				$thermostatArticle = isset($formData['PRODUCTS']['thermostat'])?
					$formData['PRODUCTS']['thermostat'] :
					(isset($formData['PRODUCTS']['thermostat_add']) ?
						$formData['PRODUCTS']['thermostat_add'] :
						'');

				foreach ($this->{'arThermostats_'.$lang} as $article) {
					if ($thermostatArticle == $article) {
						$arProducts[] = [
							'ARTICLE' => $article,
							'QUANTITY' => $totalTermostatsCount,
						];
						$arArticles[] = $article;
					}
				}
			}
			if ($totalActuatorsCount > 0) {
				foreach ($this->{'arActuators_'.$lang} as $article) {
					$arProducts[] = [
						'ARTICLE' => $article,
						'QUANTITY' => $totalActuatorsCount,
					];
					$arArticles[] = $article;
				}
			}
		}
		if ($formData['PRODUCTS']['light_devices']) {
			foreach ($formData['PRODUCTS']['light_devices'] as $article => $count) {
				if ($count > 0) {
					$arProducts[] = [
						'ARTICLE' => $article,
						'QUANTITY' => $count,
					];
					$arArticles[] = $article;
				}
			}
		}
		if ($formData['PRODUCTS']['thermostat']) {
			if ($formData['PRODUCTS']['thermostat'] == 'P01228') {
				$arProducts[] = [
					'ARTICLE' => 'P07930',
					'QUANTITY' => 1,
				];
				$arArticles[] = 'P07930';
			}
			$arArticles[] = $formData['PRODUCTS']['thermostat'];
		}
		if ($formData['number_radiators']) {
			foreach ($this->{'arThermostatsActuators_'.$lang} as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => $formData['number_radiators'],
				];
				$arArticles[] = $article;
			}
		}
		if ($formData['number_rooms']) {
			foreach ($this->{'arThermostatsMultiple_'.$lang} as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => $formData['number_rooms'],
				];
				$arArticles[] = $article;
			}
		}
		if ($formData['temp_regulator10']) {
			foreach ($formData['temp_regulator10'] as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => 1,
				];
				$arArticles[] = $article;
			}
		}
		if ($formData['temp_regulator16']) {
			foreach ($formData['temp_regulator16'] as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => 1,
				];
				$arArticles[] = $article;
			}
		}
		if ($formData['external_sensor']) {
			foreach ($formData['external_sensor'] as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => 1,
				];
				$arArticles[] = $article;
			}
		}
		if ($formData['panel_temp_regulator10']) {
			foreach ($formData['panel_temp_regulator10'] as $block) {
				foreach ($block as $article) {
					$arProducts[] = [
						'ARTICLE' => $article,
						'QUANTITY' => 1,
					];
					$arArticles[] = $article;
				}
			}
		}
		if ($formData['panel_temp_regulator16']) {
			foreach ($formData['panel_temp_regulator16'] as $block) {
				foreach ($block as $article) {
					$arProducts[] = [
						'ARTICLE' => $article,
						'QUANTITY' => 1,
					];
					$arArticles[] = $article;
				}
			}
		}
		if ($formData['rooms_number_floor_heating']) {
			foreach ($this->{'arThermostats_'.$lang} as $article) {
				if ($article == $formData['PRODUCTS']['thermostat_add']) {
					$arProducts[] = [
						'ARTICLE' => $article,
						'QUANTITY' => $formData['rooms_number_floor_heating'],
					];
					$arArticles[] = $article;
				}
			}
		}
		if ($formData['number_panels_room']) {
			foreach ($formData['number_panels_room'] as $key => $count) {
				foreach ($this->{'arThermostats_'.$lang} as $article) {
					if ($article == $formData['PRODUCTS']['thermostat_add']) {
						$arProducts[] = [
							'ARTICLE' => $article,
							'QUANTITY' => $count,
						];
						$arArticles[] = $article;
					}
				}
			}
		}
		if ($formData['wall_reciever'] == 1) {
			foreach ($this->{'arLightDevices_'.$lang} as $article) {
				$arProducts[] = [
					'ARTICLE' => $article,
					'QUANTITY' => 1,
				];
				$arArticles[] = $article;
			}
		}
		$arArticles = array_unique($arArticles);
		foreach ($arProducts as $k_1 => $arProduct_1) {
			foreach ($arProducts as $k_2 => $arProduct_2) {
				if ($arProduct_1['ARTICLE'] == $arProduct_2['ARTICLE'] && $k_1 < $k_2) {
					$arProducts[$k_1]['QUANTITY'] += $arProduct_2['QUANTITY'];
					unset($arProducts[$k_2]);
				}
			}
		}

		if (!empty($arArticles)) {
			$arFilter = [
				'IBLOCK_ID' => $this->iblockId,
				'PROPERTY_ARTICLE_'.$lang => $arArticles,
			];

			$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_ARTICLE_'.$lang];

			$arIblockProducts = $this->getProducts($arFilter, $arSelect);

			if (!empty($arIblockProducts)) {
				foreach ($arIblockProducts as $key => $arItem) {
					foreach ($arProducts as $pKey => $arProduct) {
						if ($arProduct['ARTICLE'] == $arItem['ARTICLE']) {
							$arItem['QUANTITY'] = $arProduct['QUANTITY'];
							$arItems[$pKey] = $arItem;
							break;
						}
					}
				}
			}
		}

		return $arItems;
	}

	public function getFinalHeaders() {
		return [
			'Article',
			'Description',
			'Qt.',
		];
	}

	/**
	 * @return array - items for form
	 */
	private function getItemsForType() {
		return [
			[
				'CONTROL_NAME' => 'type',
				'TYPE' => 'radio',
				'VALUES' => [
					[
						'VALUE' => '1',
						'TEXT' => 'Heating',
					],
					[
						'VALUE' => '2',
						'TEXT' => 'Heating / cooling',
					],
					[
						'VALUE' => '3',
						'TEXT' => 'Electrical (floor) heating and devices',
					],
				],
			],
		];
	}

	private function getItemsForHeating() {
		return [
			[
				'CONTROL_NAME' => 'heating',
				'TYPE' => 'radio',
				'VALUES' => [
					[
						'VALUE' => '1',
						'TEXT' => 'Floor heating',
					],
					[
						'VALUE' => '2',
						'TEXT' => 'Floor heating / radiators',
					],
					[
						'VALUE' => '3',
						'TEXT' => 'Radiators',
					],
				],
			],
		];
	}

	private function getItemsForSwitch() {
		return [
			[
				'CONTROL_NAME' => 'switch',
				'TYPE' => 'radio',
				'VALUES' => [
					[
						'VALUE' => '1',
						'TEXT' => 'Manual Heat & Cool switch made by end user',
					],
					[
						'VALUE' => '2',
						'TEXT' => 'Automatic switch made by the system (for example district heating, heat pump or central system)',
					],
				],
			],
		];
	}

	private function getItemsForTouchScreen() {
		$lang = $this->getLanguage();
		$arItems = [
			[
				'CONTROL_NAME' => 'central_touch_screen',
				'TYPE' => 'radio',
				'VALUES' => [
					[
						'VALUE' => '1',
						'TEXT' => 'Yes',
					],
					[
						'VALUE' => '2',
						'TEXT' => 'No',
					],
				],
			],
		];

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $this->{'arTouchScreenArticles_'.$lang}
		];

		$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_ARTICLE_'.$lang];

		$arProducts = $this->getProducts($arFilter, $arSelect);

		$arItems[] = [
			'CONTROL_NAME' => 'products[central_touch_screen]',
			'FROM_IBLOCK' => 'Y',
			'HIDDEN' => 'Y',
			'DESCRIPTION' => 'Which central unit?',
			'TYPE' => 'radio',
			'VALUES' => $arProducts,
		];

		return $arItems;
	}

	private function disableTouchScreenItems(&$arItems) {
		foreach ($arItems as $key => $arField) {
			if ($arField['FROM_IBLOCK'] != 'Y') {
				foreach ($arField['VALUES'] as $fKey => $value) {
					if ($value['VALUE'] == 1) {
						$arItems[$key]['VALUES'][$fKey]['SELECTED'] = true;
					} else {
						$arItems[$key]['VALUES'][$fKey]['READONLY'] = true;
					}
				}
			}
		}
	}

	private function getItemsForChoiceManifolds() {
		$arItems = [];
		$arItem = [
			'CONTROL_NAME' => 'number_manifolds',
			'TYPE' => 'select',
			'TEXT' => 'Number of manifolds',
			'VALUES' => [],
		];
		for ($i = 0; $i <= 50; $i++) {
			$arItem['VALUES'][] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}
		$arItems[] = $arItem;

		$numberTermostatsValues = [];
		for ($i = 1; $i <= 12; $i++) {
			$numberTermostatsValues[] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}

		$numberGroupsValues = [];
		for ($i = 1; $i <= 24; $i++) {
			$numberGroupsValues[] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}

		$arItems[] = [
			'CONTROL_NAME' => 'MANIFOLDS_OPTIONS',
			'HIDDEN' => 'Y',
			'TYPE' => 'template',
			'VALUES' => [
				[
					'CONTROL_NAME' => 'number',
					'TYPE' => 'iterator',
					'TEXT' => 'Manifold #',
				],
				[
					'CONTROL_NAME' => 'number_termostats[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Number of thermostats *',
					'VALUES' => $numberTermostatsValues,
				],
				[
					'CONTROL_NAME' => 'number_groups[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Number of actuators **',
					'VALUES' => $numberGroupsValues,
				],
			],
		];

		if ($this->arResult['FORM_DATA']['type'] == 1) {
			$arItems[] = [
				'CONTROL_NAME' => 'wall_reciever',
				'TYPE' => 'radio',
				'TEXT' => 'If you cannot wire the boiler / HP to the connecting box, you can choose a wireless wall receiver',
				'DESCRIPTION' => 'If you cannot wire the boiler / HP to the connecting box, you can choose a wireless wall receiver',
				'HIDDEN' => 'Y',
				'VALUES' => [
						[
							'VALUE' => '1',
							'TEXT' => 'Yes',
						],
						[
							'VALUE' => '2',
							'TEXT' => 'No',
						],
				],
			];
		}

		if ($this->arResult['FORM_DATA']['type'] == 2) {
			$lang = $this->getLanguage();

			$arFilter = [
				'IBLOCK_ID' => $this->iblockId,
				'PROPERTY_ARTICLE_'.$lang => $this->{'arDigitalThermostats_'.$lang}
			];
			$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_ARTICLE_'.$lang];

			$arProducts = $this->getProducts($arFilter, $arSelect);

			$parentKey = 0;
			foreach ($arProducts as $key => $arProduct) {
				if ($arProduct['ARTICLE'] == 'P07710') {
					$parentKey = $key;
					break;
				}
			}

			foreach ($arProducts as $key => $arProduct) {
				if ($arProduct['ARTICLE'] == 'P01228') {
					$arProducts[$key]['ARTICLE'] = $arProducts[$parentKey]['ARTICLE'];
					$arProducts[$key]['PICTURE'] = $arProducts[$parentKey]['PICTURE'];
					$arProducts[$key]['TEXT'] = $arProducts[$parentKey]['ARTICLE'].' '.$arProducts[$parentKey]['TEXT'].' + '.$arProduct['ARTICLE'].' '.$arProduct['TEXT'];
				}
			}

			$arItems[] = [
				'CONTROL_NAME' => 'products[thermostat]',
				'FROM_IBLOCK' => 'Y',
				'DESCRIPTION' => 'Which thermostat?',
				'TYPE' => 'radio',
				'VALUES' => $arProducts,
			];

		}

		return $arItems;
	}

	private function getItemsForLightDevices() {
		$arItems = [];

		$arItem = [
			'CONTROL_NAME' => 'light_devices',
			'TYPE' => 'hidden',
			'TEXT' => '',
			'DESCRIPTION' => '',
			'VALUES' => ['Y'],
		];

		$arItems[] = $arItem;

		$lang = $this->getLanguage();

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $this->{'arLightDevices_'.$lang}
		];

		$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_ARTICLE_'.$lang];

		$arProducts = $this->getProducts($arFilter, $arSelect);

		foreach ($arProducts as $key => $arItem) {
			$values = [];
			for ($i = 0; $i <= 50; $i++) {
				$values[] = [
					'VALUE' => $i,
					'TEXT' => $i,
				];
			}

			$arItem = [
				'CONTROL_NAME' => 'products[light_devices]['.$arItem['ID'].']',
				'FROM_IBLOCK' => 'Y',
				'DESCRIPTION' => 'In addition to climate control, lighting and other devices can also be switched.',
				'TYPE' => 'select',
				'VALUES' => $values,
				'TEXT' => $arItem['TEXT'],
				'ARTICLE' => $arItem['ARTICLE'],
				'PICTURE' => $arItem['PICTURE'],
				'DETAIL_PAGE_URL' => $arItem['DETAIL_PAGE_URL'],
				'PRODUCT_ID' => $arItem['ID'],
				'ID' => $arItem['ARTICLE'],
			];

			$arItems[] = $arItem;
		}

		return $arItems;
	}

	private function getProducts($arFilter, $arSelect) {
		$arProducts = [];
		$lang = $this->getLanguage();

		$res = CIBlockElement::GetList(
			['SORT' => 'ASC'],
			$arFilter,
			false,
			false,
			$arSelect
		);

		$arProducts = [];
		$bFirst = true;
		while ($ar = $res->GetNext()) {
			if ($ar['DETAIL_PICTURE'] > 0) {
				$ar['DETAIL_PICTURE'] = CFile::GetFileArray($ar['DETAIL_PICTURE']);
			}

			if (is_array($ar['DETAIL_PICTURE'])) {
				$arFilter = '';
				
				$arFileTmp = CFile::ResizeImageGet(
					$ar['DETAIL_PICTURE'],
					array('width' => 200, 'height' => 200),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true, 
					$arFilter
				);

				$ar['DETAIL_PICTURE'] = array(
					'SRC' => $arFileTmp["src"],
					'WIDTH' => $arFileTmp["width"],
					'HEIGHT' => $arFileTmp["height"],
				);
			}

			$arProd = [
				'ID' => $ar['PROPERTY_ARTICLE_'.$lang.'_VALUE'],
				'PRODUCT_ID' => $ar['ID'],
				'ARTICLE' => $ar['PROPERTY_ARTICLE_'.$lang.'_VALUE'],
				'PICTURE' => $ar['DETAIL_PICTURE'],
				'TEXT' => $ar['NAME'],
				'DETAIL_PAGE_URL' => $ar['DETAIL_PAGE_URL'],
			];
			if ($bFirst) {
				$arProd['SELECTED'] = true;
				$bFirst = false;
			}
			$arProducts[] = $arProd;
		}
		return $arProducts;
	}

	private function getSelectProducts($arFilter, $arSelect) {
		$arProducts = [];
		$lang = $this->getLanguage();

		$res = CIBlockElement::GetList(
			['SORT' => 'ASC'],
			$arFilter,
			false,
			false,
			$arSelect
		);

		$arProducts = [];

		while ($ar = $res->GetNext()) {
			$arProd = [
				'VALUE' => $ar['PROPERTY_ARTICLE_'.$lang.'_VALUE'],
				'TEXT' => $ar['PROPERTY_ARTICLE_'.$lang.'_VALUE'].' '.$ar['NAME'],
			];
			$arProducts[] = $arProd;
		}
		return $arProducts;
	}

	private function getItemsForRadiators() {
		$arItems = [];

		$arItem = [
			'CONTROL_NAME' => 'number_radiators',
			'TYPE' => 'select',
			'TEXT' => 'Number of radiators',
			'VALUES' => [],
		];
		for ($i = 1; $i <= 50; $i++) {
			$arItem['VALUES'][] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}
		$arItems[] = $arItem;

		$arItem = [
			'CONTROL_NAME' => 'number_rooms',
			'TYPE' => 'select',
			'TEXT' => 'Number of rooms with multiple radiators',
			'VALUES' => [],
		];
		for ($i = 0; $i <= 50; $i++) {
			$arItem['VALUES'][] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}
		$arItems[] = $arItem;

		return $arItems;
	}

	private function disableRadiatorsItem(&$arItems) {
		foreach ($arItems as $itemKey => $arItem) {
			if (!empty($arItem['VALUES'])) {
				foreach ($arItem['VALUES'] as $valKey => $arValue) {
					if ($arValue['VALUE'] == 3) {
						$arItems[$itemKey]['VALUES'][$valKey]['READONLY'] = true;
					}
				}
			}
		}
	}

	private function getItemsForElectricalHeating() {
		$arItems = [];
		$lang = $this->getLanguage();
		$arItem = [
			'CONTROL_NAME' => 'rooms_number_floor_heating',
			'TYPE' => 'select',
			'TEXT' => 'Number of rooms**',
			'DESCRIPTION' => 'Floor heating',
			'VALUES' => [],
		];
		for ($i = 0; $i <= 50; $i++) {
			$arItem['VALUES'][] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}
		$arItems[] = $arItem;

		$arArticles10 = $this->{'arTempRegulators10_'.$lang};
		foreach ($arArticles10 as $key => $article) {
			if ($article == 'P06676')
				unset($arArticles10[$key]);
		}

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $arArticles10,
		];
		$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_ARTICLE_'.$lang];
		$arProducts10 = $this->getSelectProducts($arFilter, $arSelect);

		$arArticles16 = $this->{'arTempRegulators16_'.$lang};
		foreach ($arArticles16 as $key => $article) {
			if ($article == 'P06676')
				unset($arArticles16[$key]);
		}

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $arArticles16,
		];
		$arProducts16 = $this->getSelectProducts($arFilter, $arSelect);

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $this->{'arExternalSensor_'.$lang},
		];
		$arProductsExternal = $this->getSelectProducts($arFilter, $arSelect);

		$arItems[] = [
			'CONTROL_NAME' => 'FLOOR_HEATING_OPTIONS',
			'HIDDEN' => 'Y',
			'TYPE' => 'template',
			'VALUES' => [
				[
					'CONTROL_NAME' => 'number',
					'TYPE' => 'iterator',
					'TEXT' => '#',
				],
				[
					'CONTROL_NAME' => 'amps_per_room[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Amps per room',
					'VALUES' => [
						[
							'VALUE' => 0,
							'TEXT' => '[Choose]',
						],
						[
							'VALUE' => 10,
							'TEXT' => 'Up to 10 Amps',
						],
						[
							'VALUE' => 16,
							'TEXT' => 'Up to 16 Amps',
						],
					],
				],
				[
					'CONTROL_NAME' => 'temp_regulator0[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Temperature regulation',
					'VALUES' => [
						[
							'VALUE' => 0,
							'TEXT' => '[First choose the Amps per room]',
						],
					],
				],
				[
					'CONTROL_NAME' => 'temp_regulator10[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Temperature regulation',
					'VALUES' => $arProducts10,
				],
				[
					'CONTROL_NAME' => 'temp_regulator16[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Temperature regulation',
					'VALUES' => $arProducts16,
				],
				[
					'CONTROL_NAME' => 'external_sensor[#i#]',
					'TYPE' => 'checkbox',
					'TEXT' => 'Choice option',
					'VALUES' => $arProductsExternal,
				],
			],
		];

		$arItem = [
			'CONTROL_NAME' => 'rooms_number_infrared_panel',
			'TYPE' => 'select',
			'TEXT' => 'Number of rooms**',
			'DESCRIPTION' => 'Infrared panels/electrical panels',
			'VALUES' => [],
		];
		for ($i = 0; $i <= 50; $i++) {
			$arItem['VALUES'][] = [
				'VALUE' => $i,
				'TEXT' => $i,
			];
		}

		$arItems[] = $arItem;

		$arItems[] = [
			'CONTROL_NAME' => 'INFRARED_OPTIONS',
			'HIDDEN' => 'Y',
			'TYPE' => 'template',
			'VALUES' => [
				[
					'CONTROL_NAME' => 'number',
					'TYPE' => 'iterator',
					'TEXT' => '#',
				],
				[
					'CONTROL_NAME' => 'number_panels_room[#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Number of panels',
					'VALUES' => [
						['VALUE' => 0, 'TEXT' => '[Choose]',],
						['VALUE' => 1, 'TEXT' => 1,],
						['VALUE' => 2, 'TEXT' => 2,],
						['VALUE' => 3, 'TEXT' => 3,],
						['VALUE' => 4, 'TEXT' => 4,],
						['VALUE' => 5, 'TEXT' => 5,],
						['VALUE' => 6, 'TEXT' => 6,],
						['VALUE' => 7, 'TEXT' => 7,],
						['VALUE' => 8, 'TEXT' => 8,],
						['VALUE' => 9, 'TEXT' => 9,],
						['VALUE' => 10, 'TEXT' => 10,],
					],
				],
				[
					'CONTROL_NAME' => 'number',
					'TYPE' => 'header',
					'TEXT' => 'Amps per panel room',
				],
				[
					'CONTROL_NAME' => 'number',
					'TYPE' => 'header',
					'TEXT' => 'Temperature regulation',
				],
			],
		];

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $this->{'arTempRegulators10_'.$lang},
		];
		$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_ARTICLE_'.$lang];
		$arProducts10 = $this->getSelectProducts($arFilter, $arSelect);

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $this->{'arTempRegulators16_'.$lang},
		];
		$arProducts16 = $this->getSelectProducts($arFilter, $arSelect);

		$arItems[] = [
			'CONTROL_NAME' => 'INFRARED_PANEL_OPTIONS',
			'TYPE' => 'template',
			'HIDDEN' => 'Y',
			'VALUES' => [
				[
					'CONTROL_NAME' => 'panel_amps_per_room[#j#][#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Amps per panel room',
					'VALUES' => [
						[
							'VALUE' => 0,
							'TEXT' => '[Choose]',
						],
						[
							'VALUE' => 10,
							'TEXT' => 'Up to 10 Amps',
						],
						[
							'VALUE' => 16,
							'TEXT' => 'Up to 16 Amps',
						],
					],
				],
				[
					'CONTROL_NAME' => 'panel_temp_regulator0[#j#][#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Temperature regulation',
					'VALUES' => [
						[
							'VALUE' => 0,
							'TEXT' => '[First choose the Amps per room]',
						],
					],
				],
				[
					'CONTROL_NAME' => 'panel_temp_regulator10[#j#][#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Temperature regulation',
					'VALUES' => $arProducts10,
				],
				[
					'CONTROL_NAME' => 'panel_temp_regulator16[#j#][#i#]',
					'TYPE' => 'select',
					'TEXT' => 'Temperature regulation',
					'VALUES' => $arProducts16,
				],
			]
		];

		return $arItems;
	}

	private function getItemsForTermostats() {
		$arItems = [];

		$arItem = [
			'CONTROL_NAME' => 'thermostat_add',
			'TYPE' => 'hidden',
			'TEXT' => '',
			'DESCRIPTION' => '',
			'VALUES' => ['Y'],
		];

		$arItems[] = $arItem;

		$lang = $this->getLanguage();

		$arFilter = [
			'IBLOCK_ID' => $this->iblockId,
			'PROPERTY_ARTICLE_'.$lang => $this->{'arThermostats_'.$lang}
		];

		$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_ARTICLE_'.$lang];

		$arProducts = $this->getProducts($arFilter, $arSelect);

		$arItems[] = [
			'CONTROL_NAME' => 'products[thermostat_add]',
			'FROM_IBLOCK' => 'Y',
			// 'HIDDEN' => 'Y',
			'DESCRIPTION' => 'Which thermostat?',
			'TYPE' => 'radio',
			'VALUES' => $arProducts,
		];

		return $arItems;
	}

	public function modifyItems(&$arItems, $arSelect) {
		foreach ($arItems as $key => $arItem) {
			if ($arItem['CONTROL_NAME'] == $arSelect['field']) {
				foreach ($arItem['VALUES'] as $vKey => $arValue) {
					if ($arValue['VALUE'] == $arSelect['value']) {
						$arItems[$key]['VALUES'][$vKey]['SELECTED'] = true;
					}
				}
			} elseif (isset($arSelect[$arItem['CONTROL_NAME']])) {
				foreach ($arItem['VALUES'] as $vKey => $arValue) {
					if ($arSelect[$arItem['CONTROL_NAME']] == $arValue['VALUE']) {
						$arItems[$key]['VALUES'][$vKey]['SELECTED'] = true;
					}
				}
			}

			if (!empty($arSelect['products']) && stripos($arItem['CONTROL_NAME'], 'products') !== false) {
				preg_match('/products\[([a-zA-Z0-9_-]+)\]/', $arItem['CONTROL_NAME'], $matches);
				$productControl = $matches[1];
				if ($productControl && isset($arSelect['products'][$productControl])) {
					foreach ($arItem['VALUES'] as $vKey => $arValue) {
						if ($arValue['ARTICLE'] == $arSelect['products'][$productControl]) {
							$arItems[$key]['VALUES'][$vKey]['SELECTED'] = true;
						} else {
							unset($arItems[$key]['VALUES'][$vKey]['SELECTED']);
						}
					}
				}
			}
			unset($productControl);
		}
	}

	private function getLanguage() {
		return strtoupper(LANGUAGE_ID);
	}
}