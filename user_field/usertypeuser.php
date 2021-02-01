<?php
/**
 * @version 1.0.0
 * @since 2019-06-28
 */

namespace MA\Main;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * user field "link to user"
 */
class UserTypeUser
{
	/**
	 * OnUserTypeBuildList event handler 
	 *
	 * @see https://bxapi.ru/src/?module_id=main&name=CUserTypeString%3A%3AGetUserTypeDescription
	 */
	public static function GetUserTypeDescription()
	{
		return array(
			'USER_TYPE_ID'	=> 'usertypeuser',
			'CLASS_NAME'	=> __CLASS__,
			'DESCRIPTION'	=> Loc::getMessage('USER_FIELD_BIND_USER_NAME'),
			'BASE_TYPE'		=> \CUserTypeManager::BASE_TYPE_INT,
		);
	}

	public static function GetDBColumnType($arUserField)
	{
		global $DB;

		switch (strtolower($DB->type)) {
			case 'mysql': return 'int(18)';
			case 'oracle': return 'number(18)';
			case 'mssql': return 'int';
		}
	}

	public static function PrepareSettings($arUserField)
	{
		return array();
	}

	public static function GetSettingsHTML($arUserField=false, $arHtmlControl, $bVarsFromForm)
	{
		return '';
	}

	public static function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		$sField = \FindUserID(
			$arHtmlControl['NAME'], # Имя поля для ввода ID пользователя
			$arHtmlControl['VALUE'], # Значение поля для ввода ID пользователя
			'',			# ID, логин, имя и фамилия пользователя, выводимые рядом с полем для ввода ID пользователя, сразу же после загрузки страницы
			'user_edit_form', # Имя формы, в которой находится поле для ввода ID пользователя
			'5',		# Ширина поля для ввода ID пользователя
			'',			# Максимальное количество символов в поле для ввода ID пользователя
			' ... ',	# Подпись на кнопке ведущей на страницу поиска пользователя
			'',			# CSS класс для поля ввода ID пользователя
			''			# CSS класс для кнопки ведущей на страницу поиска пользователя
		);
		return $sField;
	}

	public static function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return '';
	}

	public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		preg_match('/FIELDS\[([0-9]+)\]/', $arHtmlControl['NAME'], $a);

		if (0 < $a[1]) {
			require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/tools/prop_userid.php';

			return \CIBlockPropertyUserID::GetAdminListViewHTML(array(), $arHtmlControl, '');
		}

		return '&nbsp;';
	}

	public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		$sField = \FindUserID(
			$arHtmlControl['NAME'], # Имя поля для ввода ID пользователя
			$arHtmlControl['VALUE'], # Значение поля для ввода ID пользователя
			'',			# ID, логин, имя и фамилия пользователя, выводимые рядом с полем для ввода ID пользователя, сразу же после загрузки страницы
			'form_tbl_user', # Имя формы, в которой находится поле для ввода ID пользователя
			'5',		# Ширина поля для ввода ID пользователя
			'',			# Максимальное количество символов в поле для ввода ID пользователя
			' ... ',	# Подпись на кнопке ведущей на страницу поиска пользователя
			'',			# CSS класс для поля ввода ID пользователя
			''			# CSS класс для кнопки ведущей на страницу поиска пользователя
		);
		return $sField;
	}

	public static function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		return $aMsg;
	}

	public static function OnSearchIndex($arUserField)
	{
		return '';
	}

	public static function ConvertToDB($arProperty, $value)
	{
		return $value;
	}

	public static function ConvertFromDB($arProperty, $value)
	{
		return $value;
	}
}