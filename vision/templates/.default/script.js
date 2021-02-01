function visionCalculator(id) {
	this.form = BX(id);
	this.url = this.form.action;
	this.resultNode = BX('result');
	this.topTextNode = BX('top_text');
	this.errorNode = BX('error');
	this.noteNode = BX('note');
	this.stepNode = BX('step');
	this.currentStep = 0;
	this.titleNode = BX('title');
	this.backButton = BX('back');
	this.nextButton = BX('next');
	this.restartButton = BX('restart');
	this.chainNode = BX('chain');
	this.result = null;
	this.curScreen = '';
	this.visionData = {};
	this.screens = [
		'type',
		'heating',
		'switch',
		'electrical_floorheating_devices',
		'central_touch_screen',
		'number_manifolds',
		'number_radiators',
		'heating_cooling',
		'devices_light',
		'rooms_number_floor_heating',
		'light_devices',
		'thermostat_add',
		'result',
	];

	if (!!this.backButton) {
		BX.bind(this.backButton, 'click', BX.delegate(this.sendRequest, this));
	}

	if (!!this.nextButton) {
		BX.bind(this.nextButton, 'click', BX.delegate(this.sendRequest, this));
	}

	if (!!this.restartButton) {
		BX.bind(this.restartButton, 'click', BX.delegate(this.restartVision, this));
	}

	this.setCurrentStep();
}

visionCalculator.prototype.setCurrentStep = function() {
	this.currentStep = this.stepNode.value;
};

visionCalculator.prototype.setCurrentStepValue = function(step) {
	this.stepNode.value = step;
	this.setCurrentStep();
};

visionCalculator.prototype.getCurrentStep = function() {
	return this.currentStep;
};

visionCalculator.prototype.setTitle = function(title) {
	this.titleNode.innerText = title;
};

visionCalculator.prototype.setBackButtonState = function(state) {
	if (!!this.backButton)
		this.backButton.disabled = state;

	if (state) {
		BX.addClass(this.backButton, 'hidden');
	} else {
		BX.removeClass(this.backButton, 'hidden');
	}

};

visionCalculator.prototype.setTopText = function(text) {
	this.topTextNode.innerHTML = text;
	if (text.length > 0) {
		this.topTextNode.style.display = 'block';
	} else {
		this.topTextNode.style.display = 'none';
	}
};

visionCalculator.prototype.setErrorText = function(text) {
	this.errorNode.innerHTML = text;
};

visionCalculator.prototype.setNoteText = function(text) {
	this.noteNode.innerHTML = text;
	if (text.length > 0) {
		this.noteNode.style.display = 'block';
	} else {
		this.noteNode.style.display = 'none';
	}
};

visionCalculator.prototype.getChain = function() {
	var currentChainObj = {},
		currentChain = this.chainNode.value;
	if (currentChain.length > 0) {
		try	{
			currentChainObj = JSON.parse(currentChain);
		} catch (e) {}
	}
	return currentChainObj;
};

visionCalculator.prototype.setDataChain = function(data) {
	var newData, currentChain, currentChainObj = [], newChain;
	newData = JSON.parse(JSON.stringify(data));
	delete newData.chain;

	currentChain = this.chainNode.value;
	if (currentChain.length > 0) {
		try	{
			currentChainObj = JSON.parse(currentChain);
		} catch (e) {}
	}
	if (newData.action == 'next') {
		currentChainObj.push(newData);
	} else if (newData.action == 'back') {
		currentChainObj.splice(length-1, 1);
	} else if (newData.action == 'restart') {
		currentChainObj = [];
	}
	newChain = JSON.stringify(currentChainObj);
	this.chainNode.value = newChain;
};

visionCalculator.prototype.restartVision = function(e) {
	this.stepNode.value = 1;
	this.setDataChain({action: 'restart'});
	this.visionData = {};
	var needInput;
	needInput = this.form.querySelector('input[name="need_central_touch_screen"]');
	if (!!needInput) {
		BX.remove(needInput);
	}
	this.sendRequest(e);
	this.nextButton.removeAttribute('style');
};

visionCalculator.prototype.sendRequest = function(e) {
	e.preventDefault();
	e.stopPropagation();

	var formData,
		_that = this,
		action = BX.data(e.target, 'value');

	formData = this.prepareFormData();

	if (!this.checkFormData(formData) && action == 'next') {
		this.showError();
		return;
	}

	this.hideError();

	formData.action = action;
	if (action != 'back')
		this.fillVisionData(formData);

	if (this.curScreen == 'number_manifolds' && action != 'restart' && action != 'back') {
		if (!this.checkManifoldsOptions(formData))
			return;

		if (!this.checkTermostatsTotalCount(formData))
			return;
	}

	if (action == 'next' || action == 'restart') {
		this.setDataChain(formData);
	}
	BX.showWait();
	BX.ajax({
		url: this.url,
		data: formData,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		async: true,
		processData: true,
		scriptsRunFirst: true,
		emulateOnload: true,
		start: true,
		cache: false,
		onsuccess: function(data){
			BX.closeWait();
			if (data.FINAL == 'Y') {
				_that.processFinalData(data);
			} else {
				_that.processData(data);
				if (action == 'back') {
					_that.setDataChain(formData);
					_that.reFillVisionData(formData);
				}
			}
		},
		onfailure: function(){}
	});
};

visionCalculator.prototype.prepareFormData = function() {
	var data, formData = {}, i, j;
	data = BX.ajax.prepareForm(this.form);
	for (i in data.data) {
		if (BX.util.in_array(i, this.screens)) {
			formData.field = i;
			formData.value = data.data[i];
		} else {
			formData[i] = data.data[i];
		}
	}
	return formData;
};

visionCalculator.prototype.checkManifoldsOptions = function(formData) {
	var select, manifoldsValue;
	select = this.resultNode.querySelector('[name="number_manifolds"');
	if (!!select) {
		manifoldsValue = select.value;
		if (manifoldsValue <= 3) {
			if (this.visionData.central_touch_screen == 1) {
				return true;
			} else if (!this.visionData.wall_reciever && this.visionData.type == 1) {
				this.showWallReceiverControl();
				return false;
			} else if (this.visionData.wall_reciever == 2 && this.visionData.central_touch_screen != 1) {
				this.resultNode.append(BX.create('input', {
					attrs: {
						type: 'hidden',
						name: 'need_central_touch_screen',
						value: 1,
					}
				}));
				formData.need_central_touch_screen = 1;
				return true;
			} else {
				return true;
			}
		} else {
			if (this.visionData.central_touch_screen == 1) {
				return true;
			} else {
				this.resultNode.append(BX.create('input', {
					attrs: {
						type: 'hidden',
						name: 'need_central_touch_screen',
						value: 1,
					}
				}));
				formData.need_central_touch_screen = 1;
				return true;
			}
		}
	}
};

visionCalculator.prototype.showWallReceiverControl = function() {
	var wallReceiver;
	wallReceiver = this.resultNode.querySelector('[data-name="wall_reciever"]');
	if (!!wallReceiver) {
		BX.removeClass(wallReceiver, 'hidden');
	}
};

visionCalculator.prototype.fillVisionData = function(data) {
	var visionData, i;
	visionData = JSON.parse(JSON.stringify(data));

	if (visionData.hasOwnProperty('field') && visionData.hasOwnProperty('value')) {
		this.visionData[visionData.field] = visionData.value;
	}

	if (visionData.hasOwnProperty('products')) {
		for (i in visionData.products) {
			if (visionData.products.hasOwnProperty(i)) {
				if (!this.visionData.hasOwnProperty('products'))
					this.visionData.products = {};
				this.visionData.products[i] = visionData.products[i];
			}
		}
	}

	if (visionData.hasOwnProperty('number_termostats')) {
		this.visionData.number_termostats = visionData.number_termostats;
	}

	if (visionData.hasOwnProperty('number_groups')) {
		this.visionData.number_groups = visionData.number_groups;
	}

	if (visionData.hasOwnProperty('need_central_touch_screen')) {
		this.visionData.need_central_touch_screen = visionData.need_central_touch_screen;
	}

	if (visionData.hasOwnProperty('wall_reciever')) {
		this.visionData.wall_reciever = visionData.wall_reciever;
	}

	if (visionData.hasOwnProperty('number_radiators')) {
		this.visionData.number_radiators = visionData.number_radiators;
	}

	if (visionData.hasOwnProperty('number_rooms')) {
		this.visionData.number_rooms = visionData.number_rooms;
	}

	if (visionData.hasOwnProperty('rooms_number_floor_heating')) {
		this.visionData.rooms_number_floor_heating = visionData.rooms_number_floor_heating;
	}

	if (visionData.hasOwnProperty('amps_per_room')) {
		this.visionData.amps_per_room = visionData.amps_per_room;
	}

	if (visionData.hasOwnProperty('temp_regulator10')) {
		this.visionData.temp_regulator10 = visionData.temp_regulator10;
	}

	if (visionData.hasOwnProperty('temp_regulator16')) {
		this.visionData.temp_regulator16 = visionData.temp_regulator16;
	}

	if (visionData.hasOwnProperty('external_sensor')) {
		this.visionData.external_sensor = visionData.external_sensor;
	}

	if (visionData.hasOwnProperty('rooms_number_infrared_panel')) {
		this.visionData.rooms_number_infrared_panel = visionData.rooms_number_infrared_panel;
	}

	if (visionData.hasOwnProperty('number_panels_room')) {
		this.visionData.number_panels_room = visionData.number_panels_room;
	}

	if (visionData.hasOwnProperty('panel_amps_per_room')) {
		this.visionData.panel_amps_per_room = visionData.panel_amps_per_room;
	}

	if (visionData.hasOwnProperty('panel_temp_regulator10')) {
		this.visionData.panel_temp_regulator10 = visionData.panel_temp_regulator10;
	}

	if (visionData.hasOwnProperty('panel_temp_regulator16')) {
		this.visionData.panel_temp_regulator16 = visionData.panel_temp_regulator16;
	}
};

visionCalculator.prototype.reFillVisionData = function(data) {
	var visionData, searchebleFields = [], _that, chain, i;
	visionData = JSON.parse(JSON.stringify(data));

	_that = this;

	try	{
		chain = JSON.parse(visionData.chain);
		for (i in chain) {
			if (chain.hasOwnProperty(i)) {
				searchebleFields.push(chain[i].field);
			}
		}
		if (visionData.hasOwnProperty('products') && this.visionData.hasOwnProperty('products')) {
			for (i in visionData.products) {
				if (visionData.products.hasOwnProperty(i) && this.visionData.products.hasOwnProperty(i)) {
					delete this.visionData.products[i];
				}
			}
		}

		for (i in _that.visionData) {
			if (_that.visionData.hasOwnProperty(i) && !BX.util.in_array(i, searchebleFields)) {
				// console.log('delete i ', i);
				delete _that.visionData[i];
			}
			if (i == 'number_manifolds' && _that.visionData.hasOwnProperty('need_central_touch_screen'))
				delete _that.visionData.need_central_touch_screen;
		}
	} catch(e) {
		console.error(e);
	}
};

visionCalculator.prototype.checkFormData = function(data) {
	var bChecked, i;
	bChecked = false;
	for (i in data) {
		if (data.hasOwnProperty(i)) {
			if (i == 'step' || i == 'action' || i == 'products' || i == 'chain')
				continue;

			bChecked = true;
		}
	}
	return bChecked;
};

visionCalculator.prototype.showError = function() {
	this.errorNode.style.display = 'block';
};

visionCalculator.prototype.hideError = function() {
	this.errorNode.style.display = 'none';
};

visionCalculator.prototype.processData = function(result) {
	var itemsNode, values, itemClass, select, i, j;

	this.result = result;
	this.curScreen = result.CUR_SCREEN;

	if (result.STEP) {
		this.setCurrentStepValue(result.STEP);

		if (result.STEP > 1) {
			this.setBackButtonState(false);
		} else {
			this.setBackButtonState(true);
		}
	}

	if (result.ITEMS.length > 0) {
		BX.cleanNode(this.resultNode);
		for (i = 0; i < result.ITEMS.length; i++) {
			if (result.ITEMS[i].FROM_IBLOCK == 'Y') {
				if (result.ITEMS[i].TYPE == 'radio') {
					values = this.getProductValuesForItem(result.ITEMS[i]);
				} else if (result.ITEMS[i].TYPE == 'select') {
					values = this.getValuesForItem(result.ITEMS[i]);
				}
			} else {
				values = this.getValuesForItem(result.ITEMS[i]);
			}

			itemClass = 'b-item js-item';

			if (result.ITEMS[i].HIDDEN == 'Y') {
				itemClass += ' hidden';
			}

			itemsNode = BX.create('div', {
				props: {
					className: itemClass,
				},
				dataset: {
					name: result.ITEMS[i].CONTROL_NAME,
				},
			});

			if (result.ITEMS[i].DESCRIPTION) {
				BX.append(BX.create('div', {
					props: {
						className: 'b-item-description',
					},
					html: result.ITEMS[i].DESCRIPTION,
				}), itemsNode);
			}

			if (values.length > 0) {
				for (j = 0; j < values.length; j++) {
					BX.append(values[j], itemsNode);
				}
			}

			BX.append(itemsNode, this.resultNode);

			if (result.ITEMS[i].TYPE == 'select') {
				select = itemsNode.querySelectorAll('select');
				// console.log('select', select);
				for (j = 0; j < select.length; j++) {
					this.createSelectControls(select[j]);
				}
			}
		}

		this.checkVisibleItems();
	} else {
		BX.cleanNode(this.resultNode);
	}

	if (result.TITLE) {
		this.setTitle(result.TITLE);
	}

	this.setTopText(result.TOP_TEXT);
	this.setErrorText(result.ERROR);
	this.setNoteText(result.NOTE);
	select = this.resultNode.querySelectorAll('select');
	if (select.length > 0) {
		for (i = 0; i < select.length; i++) {
			if (!$(select[i]).data('selectric')) {
				$(select[i]).selectric({
					onChange: function(element) {
						visionCalc.createSelectControls(element);
						visionCalc.checkTermostatsCount(element);
						visionCalc.toogleElectricalOptions(element);
						visionCalc.toogleElectricalPanelsOptions(element);
					},
				});
			}
		}
	}

};

visionCalculator.prototype.processFinalData = function(result) {
	var totalNode, header, product, i;

	BX.cleanNode(this.resultNode);
	if (Object.keys(result.ITEMS).length > 0) {
		totalNode = BX.create('div', {
			props: {
				className: 'b-item b-total',
			}
		});

		header = this.createTotalHeader(result.HEADERS);
		BX.append(header, totalNode);

		for (i in result.ITEMS) {
			if (result.ITEMS.hasOwnProperty(i)) {
				product = this.getProductRow(result.ITEMS[i]);
				BX.append(product, totalNode);
			}
		}

		BX.append(totalNode, this.resultNode);

		if (result.PDF_TEXT) {
			BX.append(BX.create('div', {
				props: {
					className: 'b-pdf-link-holder'
				},
				children: [
					BX.create('a', {
						props: {
							className: 'b-pdf-link'
						},
						attrs: {
							href: 'javascript:void(0)',
						},
						events: {
							click: BX.proxy(this.generatePdf, this),
						},
						text: result.PDF_TEXT
					}),
				]
			}), this.resultNode);
		}

		if (result.EMAIL_TEXT) {
			BX.append(BX.create('div', {
				props: {
					className: 'b-email-holder'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'b-email-text',
						},
						text: result.EMAIL_TEXT,
					}),
					BX.create('div', {
						props: {
							className: 'b-email-input',
						},
						children: [
							BX.create('input', {
								attrs: {
									type: 'text',
									name: 'email',
								},
								props: {
									className: 'b-vision-input',
								},
							}),
							BX.create('button', {
								props: {
									className: 'g-button',
								},
								text: result.EMAIL_BUTTON_TEXT,
								events: {
									click: BX.proxy(this.sendEmail, this),
								}
							}),
						]
					})
				]
			}), this.resultNode);
		}
	}

	if (result.TITLE) {
		this.setTitle(result.TITLE);
	}

	this.setBackButtonState(true);
	this.nextButton.style.display = 'none';
};

visionCalculator.prototype.generatePdf = function(e) {
	var formData, xhr, body, i;

	formData = this.prepareFormData();

	formData.action = 'generate_pdf';
	formData.is_ajax = 'Y';

	BX.showWait();
	BX.ajax({
		url: this.url,
		data: formData,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		async: true,
		processData: true,
		scriptsRunFirst: true,
		emulateOnload: true,
		start: true,
		cache: false,
		onsuccess: function(data){
			BX.closeWait();
			if (data.success == 'Y' && data.file.length > 0) {
				window.open(data.file);
			}
		},
		onfailure: function(){}
	});
};

visionCalculator.prototype.sendEmail = function (e) {
	e.preventDefault();
	e.stopPropagation();
	var formData, xhr, body;

	formData = this.prepareFormData();
	formData.action = 'send_email';

	if (formData.email.length > 0) {
		BX.showWait();
		BX.ajax({
			url: this.url,
			data: formData,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			async: true,
			processData: true,
			scriptsRunFirst: true,
			emulateOnload: true,
			start: true,
			cache: false,
			onsuccess: function(data){
				BX.closeWait();
				if (data.success == 'Y' && data.message.length > 0) {
					var parent;
					parent = BX.findParent(e.target, {className: 'b-email-holder'});
					if (!!parent) {
						BX.append(BX.create('div', {
							props: {
								className: 'b-email-answer',
							},
							text: data.message
						}), parent);
					}
				}
			},
			onfailure: function(){}
		});
	}

	return false;
};

visionCalculator.prototype.createTotalHeader = function(headers) {
	var header, i;
	header = BX.create('div', {
		props: {
			className: 'b-item-value header',
		},
	});

	for (i in headers) {
		if (headers.hasOwnProperty(i)) {
			BX.append(BX.create('div', {
				text: headers[i],
			}), header);
		}
	}

	return header;
};

visionCalculator.prototype.getProductRow = function(product) {
	var productNode;
	productNode = BX.create('div', {
		props: {
			className: 'b-item-value',
		},
		children: [
			BX.create('div', {
				props: {
					className: 'b-item-value-article',
				},
				text: product.ARTICLE,
			}),
			BX.create('div', {
				props: {
					className: 'b-item-value-main',
				},
				children: [
					BX.create('div', {
						props: {
							className: 'b-item-value-main-image'
						},
						children: [
							BX.create('a', {
								attrs: {
									href: product.DETAIL_PAGE_URL,
									target: '_blank',
								},
								children: [
									BX.create('img', {
										attrs: {
											src: product.PICTURE.SRC
										}
									})
								]
							})
						]
					}),
					BX.create('div', {
						props: {
							className: 'b-item-value-main-title'
						},
						children: [
							BX.create('a', {
								attrs: {
									href: product.DETAIL_PAGE_URL,
									target: '_blank',
								},
								text: product.TEXT,
							})
						]
					}),
				]
			}),
			BX.create('div', {
				props: {
					className: 'b-item-value-qty',
				},
				text: product.QUANTITY
			})
		]
	});
	return productNode;
};

visionCalculator.prototype.getValuesForItem = function(item) {
	var items = [], chain, step, key, value, checked, i, j;
	chain = this.getChain();
	step = this.getCurrentStep();
	for (i = 0; i < chain.length; i++) {
		if (chain[i].step == step) {
			for (j in chain[i]) {
				if (chain[i].hasOwnProperty(j)) {
					if (j == 'step' || j == 'action') {
						continue;
					} else {
						key = j;
						value = chain[i][j];
						break;
					}
				}
			}
			break;
		}
	}

	if (item.VALUES.length > 0) {
		switch (item.TYPE) {
			case 'radio':
				items = this.getRadioControl(item.VALUES, item.CONTROL_NAME, key, value);
				break;
			case 'select':
				items = [this.getSelectControl(item, item.CONTROL_NAME, key, value)];
				break;
			case 'hidden':
				items = [this.getHiddenControl(item, item.CONTROL_NAME)];
				break;
		}
	}
	return items;
};

visionCalculator.prototype.getRadioControl = function(values, name, currentKey, currentValue) {
	var items = [], checked, value, i;

	for (i = 0; i < values.length; i++) {

		value = values[i];
		checked = false;
		if (name == currentKey && value.VALUE == currentValue) {
			checked = true;
		} else if (value.SELECTED) {
			checked = true;
		}
		items.push(BX.create('div', {
			props: {
				className: 'b-item-value' + (value.READONLY ? ' disabled' : ''),
			},
			children: [
				BX.create('label', {
					props: {
						className: 'b-label',
					},
					children: [
						BX.create('input', {
							attrs: {
								name: name,
								value: value.VALUE,
								type: 'radio',
								checked: checked,
								disabled: (value.READONLY ? true : false),
							},
							events: {
								click: BX.delegate(this.inputHandler, this),
							}
						}),
						BX.create('span', {
							props: {
								className: 'b-radio-icon',
							},
						}),
						BX.create('span', {
							props: {
								className: 'b-label-text',
							},
							text: value.TEXT,
						}),
					],
				}),
			],
		}));
	}

	return items;
};

visionCalculator.prototype.getSelectControl = function(item, name, currentKey, currentValue, iterator1, iterator2) {
	var control, select, checked = false, value, i;

	if (name == 'number_termostats[#i#]') {
		if (this.visionData.number_termostats) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.number_termostats[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'number_groups[#i#]') {
		if (this.visionData.number_groups) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.number_groups[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'amps_per_room[#i#]') {
		if (this.visionData.amps_per_room) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.amps_per_room[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'temp_regulator0[#i#]') {
		if (this.visionData.temp_regulator0) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.temp_regulator0[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'temp_regulator10[#i#]') {
		if (this.visionData.temp_regulator10) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.temp_regulator10[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'temp_regulator16[#i#]') {
		if (this.visionData.temp_regulator16) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.temp_regulator16[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'amps_per_room[#i#]') {
		if (this.visionData.amps_per_room) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.amps_per_room[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'number_panels_room[#i#]') {
		if (this.visionData.number_panels_room) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.number_panels_room[iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'panel_amps_per_room[#j#][#i#]') {
		if (this.visionData.panel_amps_per_room && this.visionData.panel_amps_per_room[iterator2]) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.panel_amps_per_room[iterator2][iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'panel_temp_regulator0[#j#][#i#]') {
		if (this.visionData.panel_temp_regulator0 && this.visionData.panel_temp_regulator0[iterator2]) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.panel_temp_regulator0[iterator2][iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'panel_temp_regulator10[#j#][#i#]') {
		if (this.visionData.panel_temp_regulator10 && this.visionData.panel_temp_regulator10[iterator2]) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.panel_temp_regulator10[iterator2][iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name == 'panel_temp_regulator16[#j#][#i#]') {
		if (this.visionData.panel_temp_regulator16 && this.visionData.panel_temp_regulator16[iterator2]) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (item.VALUES[i].VALUE == this.visionData.panel_temp_regulator16[iterator2][iterator1]) {
					item.VALUES[i].SELECTED = true;
				} else {
					delete item.VALUES[i].SELECTED;
				}
			}
		}
	}

	if (name.indexOf('#i#') > -1 && iterator1 !== false) {
		name = name.replace('#i#', iterator1);
	}
	if (name.indexOf('#j#') > -1 && iterator2 !== false) {
		name = name.replace('#j#', iterator2);
	}
	select = BX.create('select', {
		attrs: {
			name: name,
		}
	});
	if (name == 'number_manifolds') {
		BX.bind(select, 'change', BX.delegate(this.createSelectControls, this));
	}
	if (name == 'number_termostats[#i#]') {
		BX.bind(select, 'change', BX.delegate(this.checkTermostatsCount, this));
	}

	for (i = 0; i < item.VALUES.length; i++) {
		if (item.VALUES[i].SELECTED) {
			checked = true;
		}
	}

	for (i = 0; i < item.VALUES.length; i++) {
		BX.append(BX.create('option', {
			attrs: {
				value: item.VALUES[i].VALUE,
				selected: checked ? (item.VALUES[i].SELECTED ? 'selected' : '') : (i == 0 ? 'selected' : ''),
			},
			text: item.VALUES[i].TEXT,
		}), select);
		if (i == 0) {
			select.value = item.VALUES[i].VALUE;
		}
	}
	if (item.FROM_IBLOCK == 'Y') {
		control = BX.create('div', {
			props: {
				className: 'b-item-value js-item-value',
			},
			dataset: {
				name: name,
			},
			children: [
				BX.create('label', {
					props: {
						className: 'b-label',
					},
					children: [
						BX.create('span', {
							props: {
								className: 'b-label-text item',
							},
							children: [
								BX.create('span', {
									props: {
										className: 'b-label-image item',
									},
									children: [
										BX.create('a', {
											props: {
												className: 'b-item-image',
											},
											attrs: {
												href: item.DETAIL_PAGE_URL,
												target: '_blank',
											},
											children: [
												BX.create('img', {
													attrs: {
														src: item.PICTURE.SRC,
													}
												}),
											]
										}),
									],
								}),
								BX.create('span', {
									props: {
										className: 'b-item-article',
									},
									html: item.ARTICLE,
								}),
								BX.create('span', {
									props: {
										className: 'b-item-name',
									},
									html: item.TEXT,
								}),
							],
						}),
						select,
					],
				}),
			],
		});
	} else {
		control = BX.create('div', {
			props: {
				className: 'b-item-value js-item-value',
			},
			dataset: {
				name: name,
			},
			children: [
				BX.create('label', {
					props: {
						className: 'b-label',
					},
					children: [
						BX.create('span', {
							props: {
								className: 'b-label-text select',
							},
							text: item.TEXT,
						}),
						select
					],
				}),
			],
		});
	}
	return control;
};

visionCalculator.prototype.getHiddenControl = function(item, name) {
	var control, value, i;
	for (i = 0; i < item.VALUES.length; i++) {
		value = item.VALUES[i];
	}
	control = BX.create('input', {
		attrs: {
			type: 'hidden',
			name: name,
			value: value,
		}
	});
	return control;
};

visionCalculator.prototype.createSelectControls = function(select) {
	var optionsNode, value, name, template, templateNode,
		ampSelect, ampValue, selects, templateOptNodes, sel, i;
	name = select.name;
	if (name == 'number_manifolds') {
		value = select.value;
		if (this.result.ITEMS && this.result.ITEMS.length > 0) {
			for (i = 0; i < this.result.ITEMS.length; i++) {
				if (this.result.ITEMS[i].TYPE == 'template') {
					template = this.result.ITEMS[i];
					break;
				}
			}
		}
		optionsNode = this.resultNode.querySelector('.b-item[data-name="'+template.CONTROL_NAME+'"]');
		if (!optionsNode) {
			optionsNode = this.getOptionsControlNode(template);
			BX.append(optionsNode, this.resultNode);
		} else {
			BX.cleanNode(optionsNode);
		}

		if (value > 0) {
			BX.append(this.createTemplateHeader(template), optionsNode);

			for (i = 0; i < value; i++) {
				BX.append(this.createItemByTemplate(template, i), optionsNode);
			}
			BX.removeClass(optionsNode, 'hidden');

			selects = optionsNode.querySelectorAll('select');
			if (selects.length > 0) {
				$(selects).selectric({
					onChange: function(element) {
						visionCalc.checkTermostatsCount(element);
					},
				});
				for (i = 0; i < selects.length; i++) {
					this.checkTermostatsCount(selects[i]);
				}
			}
		} else {
			BX.addClass(optionsNode, 'hidden');
		}
	}
	if (name == 'rooms_number_floor_heating') {
		value = select.value;
		if (this.result.ITEMS && this.result.ITEMS.length > 0) {
			for (i = 0; i < this.result.ITEMS.length; i++) {
				if (this.result.ITEMS[i].CONTROL_NAME == 'FLOOR_HEATING_OPTIONS') {
					template = this.result.ITEMS[i];
					break;
				}
			}
		}

		optionsNode = this.resultNode.querySelector('.b-item[data-name="'+template.CONTROL_NAME+'"]');
		if (!optionsNode) {
			optionsNode = this.getOptionsControlNode(template);
			BX.append(optionsNode, this.resultNode);
		} else {
			BX.cleanNode(optionsNode);
		}

		if (value > 0) {
			BX.append(this.createTemplateHeader(template), optionsNode);

			for (i = 0; i < value; i++) {
				templateNode = this.createItemByTemplate(template, i);
				ampSelect = templateNode.querySelector('select[name^="amps_per_room"]');
				ampValue = ampSelect.value;
				templateOptNodes = templateNode.querySelectorAll('.js-item-value');
				templateOptNodes.forEach(function(element) {
					if (BX.data(element, 'name').indexOf('temp_regulator') > -1 && BX.data(element, 'name') != 'temp_regulator'+ampValue+'['+i+']') {
						element.style.display = 'none';
						sel = element.querySelector('select');
						if (!!sel) {
							sel.disabled = true;
						}
					}
				});
				BX.append(templateNode, optionsNode);
			}

			BX.removeClass(optionsNode, 'hidden');

			selects = optionsNode.querySelectorAll('select');
			if (selects.length > 0) {
				$(selects).selectric({
					onChange: function(element) {
						visionCalc.toogleElectricalOptions(element);
					},
				});
				for (i = 0; i < selects.length; i++) {
					this.toogleElectricalOptions(selects[i]);
				}
			}
		} else {
			BX.addClass(optionsNode, 'hidden');
		}
	}
	if (name == 'rooms_number_infrared_panel') {
		value = select.value;
		if (this.result.ITEMS && this.result.ITEMS.length > 0) {
			for (i = 0; i < this.result.ITEMS.length; i++) {
				if (this.result.ITEMS[i].CONTROL_NAME == 'INFRARED_OPTIONS') {
					template = this.result.ITEMS[i];
					break;
				}
			}
		}
		optionsNode = this.resultNode.querySelector('.b-item[data-name="'+template.CONTROL_NAME+'"]');
		if (!optionsNode) {
			optionsNode = this.getOptionsControlNode(template);
			BX.append(optionsNode, this.resultNode);
		} else {
			BX.cleanNode(optionsNode);
		}

		if (value > 0) {
			BX.append(this.createTemplateHeader(template), optionsNode);

			for (i = 0; i < value; i++) {
				templateNode = this.createItemByTemplate(template, i);
				BX.append(templateNode, optionsNode);
			}

			BX.removeClass(optionsNode, 'hidden');

			selects = optionsNode.querySelectorAll('select');
			if (selects.length > 0) {
				$(selects).selectric({
					onChange: function(element) {
						visionCalc.toogleElectricalPanelsOptions(element);
					},
				});
				for (i = 0; i < selects.length; i++) {
					this.toogleElectricalPanelsOptions(selects[i]);
				}
			}
		} else {
			BX.addClass(optionsNode, 'hidden');
		}
	}
};

visionCalculator.prototype.checkTermostatsCount = function(select) {
	var name, value, actuatorSelect, options, i;
	name = select.name;
	if (name.indexOf('number_termostats') > -1) {
		value = parseInt(select.value);
		actuatorSelect = BX.findParent(select, {className: 'template'}).querySelector('select[name^="number_groups"]');
		if (!!actuatorSelect) {
			options = actuatorSelect.options;
			for (i = 0; i < options.length; i++) {
				if (parseInt(options[i].value) < value) {
					options[i].disabled = true;
					if (options[i].selected) {
						options[i].selected = false;
						options[i+1].selected = true;
					}
				} else {
					options[i].disabled = false;
				}
			}
			$(actuatorSelect).selectric('refresh');
		}
	}
};

visionCalculator.prototype.checkTermostatsTotalCount = function(data) {
	var count = 0, i;
	
	if (data.hasOwnProperty('number_termostats')) {
		for (i in data.number_termostats) {
			count += parseInt(data.number_termostats[i]);
		}
	}
	if (count > 50) {
		this.showError();
		return false;
	} else {
		this.hideError();
		return true;
	}
};

visionCalculator.prototype.toogleElectricalOptions = function(select) {
	var name, value, parent, tempRegulatorNodes, sel,
		bPanel, extSwitcher, input, ampRegulator, ampValue, 
		regValue, i;

	bPanel = false;
	name = select.name;
	value = select.value;
	if (name.indexOf('panel') > -1) bPanel = true;
	
	if (name.indexOf('amps_per_room') > -1) {
		parent = BX.findParent(select, {className: 'template'});
		ampValue = select.value;
		if (!!parent) {
			tempRegulatorNodes = parent.querySelectorAll('.js-item-value[data-name^="'+(bPanel ? 'panel_' : '')+'temp_regulator"]');
			for (i = 0; i < tempRegulatorNodes.length; i++) {
				tempRegulatorNodes[i].style.display = 'none';
				sel = tempRegulatorNodes[i].querySelector('select');
				if (!!sel) {
					sel.disabled = true;
				}
				if (BX.data(tempRegulatorNodes[i], 'name').indexOf('temp_regulator'+value) > -1) {
					tempRegulatorNodes[i].removeAttribute('style');
					sel = tempRegulatorNodes[i].querySelector('select');
					if (!!sel) {
						sel.disabled = false;
						$(sel).selectric('refresh');
						regValue = sel.value;
					}
				}
			}
			extSwitcher = BX.findNextSibling(parent);
			if (!!extSwitcher) {
				input = BX.findChildren(extSwitcher, {tagName: 'input'}, true);
				if ((ampValue == 10 && regValue == 'P06674') || (ampValue == 16 && regValue == 'P06675')) {
					extSwitcher.style.display = 'flex';
					for (i = 0; i < input.length; i++) {
						input[i].disabled = false;
					}
				} else {
					extSwitcher.style.display = 'none';
					for (i = 0; i < input.length; i++) {
						input[i].disabled = true;
					}
				}
			}
		}
	}
	if (name.indexOf('temp_regulator') > -1) {
		parent = BX.findParent(select, {className: 'template'});
		ampRegulator = parent.querySelector('select[name^="amps_per_room"]');
		if (!!ampRegulator) {
			ampValue = ampRegulator.value;
		}

		if (!!parent) {
			extSwitcher = BX.findNextSibling(parent);
			if (!!extSwitcher && select.disabled === false) {
				input = BX.findChildren(extSwitcher, {tagName: 'input'}, true);
				if ((ampValue == 10 && value == 'P06674') || (ampValue == 16 && value == 'P06675')) {
					extSwitcher.style.display = 'flex';
					for (i = 0; i < input.length; i++) {
						input[i].disabled = false;
					}
				} else {
					extSwitcher.style.display = 'none';
					for (i = 0; i < input.length; i++) {
						input[i].disabled = true;
					}
				}
			}
		}
	}
};

visionCalculator.prototype.toogleElectricalPanelsOptions = function(select) {
	var name, value, parent, tempRegulatorNodes, template, ampSelect, sel, iterator, i;
	name = select.name;
	value = select.value;

	iterator = name.match(/^.+\[(\d+)\]/);
	if (!!iterator) {
		iterator = parseInt(iterator[1]);
	}
	if (name.indexOf('number_panels_room') > -1) {
		parent = BX.findParent(select, {className: 'template'});
		if (!!parent) {
			if (this.result.ITEMS && this.result.ITEMS.length > 0) {
				for (i = 0; i < this.result.ITEMS.length; i++) {
					if (this.result.ITEMS[i].CONTROL_NAME == 'INFRARED_PANEL_OPTIONS') {
						template = this.result.ITEMS[i];
						break;
					}
				}
			}
			optionsNode = parent.querySelector('.b-item[data-name="'+template.CONTROL_NAME+'"]');
			if (!optionsNode) {
				optionsNode = this.getOptionsControlNode(template);
				BX.append(optionsNode, parent);
			} else {
				BX.cleanNode(optionsNode);
			}

			if (value > 0) {
				for (i = 0; i < value; i++) {
					templateNode = this.createItemByTemplate(template, i, iterator);
					ampSelect = templateNode.querySelector('select[name^="panel_amps_per_room"]');
					ampValue = ampSelect.value;
					templateOptNodes = templateNode.querySelectorAll('.js-item-value');
					templateOptNodes.forEach(function(element) {
						if (BX.data(element, 'name').indexOf('panel_temp_regulator') > -1 && BX.data(element, 'name') != 'panel_temp_regulator'+ampValue+'['+i+']') {
							element.style.display = 'none';
							sel = element.querySelector('select');
							if (!!sel) {
								sel.disabled = true;
							}
						}
					});
					BX.append(templateNode, optionsNode);
				}

				BX.removeClass(optionsNode, 'hidden');

				selects = optionsNode.querySelectorAll('select');
				if (selects.length > 0) {
					$(selects).selectric({
						onChange: function(element) {
							visionCalc.toogleElectricalOptions(element);
						},
					});
					for (i = 0; i < selects.length; i++) {
						visionCalc.toogleElectricalOptions(selects[i]);
					}
				}
			} else {
				BX.addClass(optionsNode, 'hidden');
			}
		}
	}
};

visionCalculator.prototype.getOptionsControlNode = function(template) {
	return BX.create('div', {
		props: {
			className: 'b-item js-item hidden',
		},
		dataset: {
			name: template.CONTROL_NAME,
		}
	});
};

visionCalculator.prototype.createTemplateHeader = function(template) {
	var item, bFirst, i;
	item = BX.create('div', {
		props: {
			className: 'b-item-value header',
		}
	});
	bFirst = false;
	if (template.VALUES && template.VALUES.length > 0) {
		for (i = 0; i < template.VALUES.length; i++) {
			if (bFirst || (template.CONTROL_NAME == 'FLOOR_HEATING_OPTIONS' && template.VALUES[i].CONTROL_NAME == 'external_sensor[]'))
				continue;

			BX.append(BX.create('div', {
				text: template.VALUES[i].TEXT
			}), item);
			if (template.CONTROL_NAME == 'FLOOR_HEATING_OPTIONS' && template.VALUES[i].TEXT == 'Temperature regulation') {
				bFirst = true;
			}
		}
	}
	return item;
};

visionCalculator.prototype.createItemByTemplate = function(template, i, iterator) {
	var item, control, checkboxControl, arChilds = [], j, k,
		tempControl, tempVal, ampControl, ampVal;
	item = BX.create('div', {
		props: {
			className: 'b-item-value template',
		}
	});
	if (template.VALUES && template.VALUES.length > 0) {
		for (j = 0; j < template.VALUES.length; j++) {
			checkboxControl = null;
			if (template.VALUES[j].TYPE == 'iterator') {
				BX.append(BX.create('div', {
					props: {
						className: 'b-item-value',
					},
					text: i+1,
				}), item);
			} else if (template.VALUES[j].TYPE == 'select') {
				control = this.getSelectControl(template.VALUES[j], template.VALUES[j].CONTROL_NAME, false, false, i, iterator);
				BX.remove(control.querySelector('.b-label-text'));
				BX.append(control, item);
			} else if (template.VALUES[j].TYPE == 'checkbox') {
				checkboxControl = this.getCheckboxControl(template.VALUES[j], i);
				if (template.VALUES[j].CONTROL_NAME.indexOf('external_sensor') > -1) {
					ampControl = item.querySelector('select[name^="amps_per_room"]');
					if (!!ampControl) {
						ampVal = ampControl.value;
					}

					tempControl = item.querySelector('select[name^="temp_regulator1"][disabled=false]');
					if (!!tempControl) {
						tempVal = tempControl.value;
					}

					if ((ampVal == 10 && tempVal == 'P06674') || (ampVal == 16 && tempVal == 'P06675')) {
						checkboxControl.style.display = 'flex';
					}
				}
			}
		}
	}
	arChilds.push(item);
	if (!!checkboxControl) {
		arChilds.push(checkboxControl);
	}
	return BX.create('div', {
		props: {
			className: 'b-item-value-holder',
		},
		children: arChilds,
	});
};

visionCalculator.prototype.getCheckboxControl = function(item, iterator) {
	var control, inputsHolder, label, input, isChecked, name, i;
	isChecked = false;
	if (item.CONTROL_NAME.indexOf('external_sensor') > -1) {
		name = item.CONTROL_NAME.replace('[#i#]', '');
		if (this.visionData[name] && this.visionData[name][iterator]) {
			for (i = 0; i < item.VALUES.length; i++) {
				if (this.visionData[name][iterator] == item.VALUES[i].VALUE) {
					isChecked = true;
				}
			}
		}
	}

	inputsHolder = BX.create('div', {
		props: {
			className: 'b-values-holder',
		},
	});
	for (i = 0; i < item.VALUES.length; i++) {
		label = BX.create('label', {
			children: [
				BX.create('input', {
					attrs: {
						type: 'checkbox',
						name: ((item.CONTROL_NAME.indexOf('#i#') > -1) ? item.CONTROL_NAME.replace('#i#', iterator) : item.CONTROL_NAME ),
						value: item.VALUES[i].VALUE,
						checked: isChecked,
					}
				}),
				BX.create('span', {
					props: {
						className: 'b-checkbox-icon',
					},
				}),
				BX.create('span', {
					props: {
						className: '',
					},
					text: item.VALUES[i].TEXT,
				}),
			],
		});
		BX.append(label, inputsHolder);
	}
	control = BX.create('div', {
		props: {
			className: 'b-item-value js-item-value checkbox',
		},
		dataset: {
			'name': item.CONTROL_NAME,
		},
		children: [
			BX.create('span', {
				props: {
					className: 'b-checkbox-title js-checkbox-title',
				},
				text: item.TEXT,
			}),
			inputsHolder,
		],
	});
	if (item.CONTROL_NAME.indexOf('external_sensor') > -1) {
		control.style.display = 'none';
		input = BX.findChildren(control, {tagName: 'input'}, true);
		for (i = 0; i < input.length; i++) {
			input[i].disabled = true;
		}
	}
	return control;
};

visionCalculator.prototype.getProductValuesForItem = function(item) {
	var items = [], chain, step, key, value, checked, i, j;
	chain = this.getChain();
	step = this.getCurrentStep();

	for (i = 0; i < chain.length; i++) {
		if (chain[i].step == step) {
			for (j in chain[i]) {
				if (chain[i].hasOwnProperty(j)) {
					if (j == 'step' || j == 'action') {
						continue;
					} else {
						key = j;
						value = chain[i][j];
						break;
					}
				}
			}
			break;
		}
	}
	if (item.VALUES.length > 0) {
		for (i = 0; i < item.VALUES.length; i++) {
			checked = false;
			if (item.CONTROL_NAME == key && item.VALUES[i].VALUE == value) {
				checked = true;
			} else if (item.VALUES[i].SELECTED) {
				checked = true;
			}
			items.push(BX.create('div', {
				props: {
					className: 'b-item-value js-item-value',
				},
				children: [
					BX.create('label', {
						props: {
							className: 'b-label',
						},
						children: [
							BX.create('input', {
								attrs: {
									name: item.CONTROL_NAME,
									value: item.VALUES[i].ID,
									type: 'radio',
									checked: checked,
									disabled: (item.VALUES[i].READONLY ? true : false),
								}
							}),
							BX.create('span', {
								props: {
									className: 'b-radio-icon',
								},
							}),
							BX.create('span', {
								props: {
									className: 'b-label-text item',
								},
								children: [
							BX.create('span', {
								props: {
									className: 'b-label-image item',
								},
								children: [
									BX.create('a', {
										props: {
											className: 'b-item-image',
										},
										attrs: {
											href: item.VALUES[i].DETAIL_PAGE_URL,
											target: '_blank',
										},
										children: [
											BX.create('img', {
												attrs: {
													src: item.VALUES[i].PICTURE.SRC,
												}
											}),
										]
									}),
								],
							}),
									BX.create('span', {
										props: {
											className: 'b-item-article',
										},
										html: item.VALUES[i].ARTICLE,
									}),
									BX.create('span', {
										props: {
											className: 'b-item-name',
										},
										html: item.VALUES[i].TEXT,
									}),
								],
							}),
						],
					}),
				],
			}));
		}
	}
	return items;
};

visionCalculator.prototype.getProductValue = function(item) {
	var items = [], chain, step, key, value, checked, i, j;
	chain = this.getChain();
	step = this.getCurrentStep();

	for (i = 0; i < chain.length; i++) {
		if (chain[i].step == step) {
			for (j in chain[i]) {
				if (chain[i].hasOwnProperty(j)) {
					if (j == 'step' || j == 'action') {
						continue;
					} else {
						key = j;
						value = chain[i][j];
						break;
					}
				}
			}
			break;
		}
	}
	if (item.VALUES.length > 0) {
		for (i = 0; i < item.VALUES.length; i++) {
			checked = false;
			if (item.CONTROL_NAME == key && item.VALUES[i].VALUE == value) {
				checked = true;
			} else if (item.VALUES[i].SELECTED) {
				checked = true;
			}
			items.push(BX.create('div', {
				props: {
					className: 'b-item-value js-item-value',
				},
				children: [
					BX.create('label', {
						props: {
							className: 'b-label',
						},
						children: [
							BX.create('input', {
								attrs: {
									name: item.CONTROL_NAME,
									value: item.VALUES[i].ID,
									type: 'radio',
									checked: checked,
									disabled: (item.VALUES[i].READONLY ? true : false),
								}
							}),
							BX.create('span', {
								props: {
									className: 'b-radio-icon',
								},
							}),
							BX.create('span', {
								props: {
									className: 'b-label-text item',
								},
								children: [
									BX.create('span', {
										props: {
											className: 'b-item-article',
										},
										html: item.VALUES[i].ARTICLE,
									}),
									BX.create('span', {
										props: {
											className: 'b-item-name',
										},
										html: item.VALUES[i].TEXT,
									}),
								],
							}),
							BX.create('span', {
								props: {
									className: 'b-label-image item',
								},
								children: [
									// BX.create('span', {
									BX.create('a', {
										props: {
											className: 'b-item-image',
										},
										attrs: {
											href: item.VALUES[i].DETAIL_PAGE_URL,
											target: '_blank',
										},
										children: [
											BX.create('img', {
												attrs: {
													src: item.VALUES[i].PICTURE.SRC,
												}
											}),
										]
									}),
								],
							}),
						],
					}),
				],
			}));
		}
	}
	return items;
};

visionCalculator.prototype.inputHandler = function(e) {
	var input;
	input = e.target;
	this.toggleProductsVisibility(input);
};

visionCalculator.prototype.toggleProductsVisibility = function(input) {
	var prodNode, inputs, i;
	if (!!input) {
		if (input.name == 'central_touch_screen') {
			prodNode = document.querySelector('.js-item[data-name="products[central_touch_screen]"]');
			inputs = prodNode.querySelectorAll('input');
			if (input.value == 1 && input.checked) {
				BX.removeClass(prodNode, 'hidden');
				for (i = 0; i < inputs.length; i++) {
					inputs[i].disabled = false;
				}
			} else {
				BX.addClass(prodNode, 'hidden');
				for (i = 0; i < inputs.length; i++) {
					inputs[i].disabled = true;
				}
			}
		}
	}
};

visionCalculator.prototype.checkVisibleItems = function() {
	var items, selectItem, inputs, productItem, activeInputs, active, input, i;
	items = this.resultNode.querySelectorAll('.js-item');
	for (i = 0; i < items.length; i++) {
		if (BX.data(items[i], 'name').indexOf('products') > -1) {
			productItem = items[i];
		} else {
			selectItem = items[i];
		}
	}

	if (!!selectItem) {
		inputs = selectItem.querySelectorAll('input');
		activeInputs = 0;
		for (i = 0; i < inputs.length; i++) {
			if (!inputs[i].disabled) {
				activeInputs++;
				input = inputs[i];

				if (inputs[i].checked)
					active = inputs[i];
			}
		}
		if (activeInputs == 1) {
			this.toggleProductsVisibility(input);
		} else if (this.result.PARAMS.action == 'back') {
			this.toggleProductsVisibility(active);
		}
	}
};