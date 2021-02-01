<?php 
/**
 * 
 * @version 0.1.0
 * @since 2020-07-30
 */

namespace MA\Main;

use \Bitrix\Main\Loader;

class Helpers
{
	/**
	 * @param $userId ing - user id
	 * @return array - user's ids or empty array
	 */
	public static function getStaffByUser($userId) {
		if ((int)$userId <= 0 || !$userId)
			return [];

		$arUser = \Bitrix\Main\UserTable::getList([
			'filter' => ['ID' => $userId],
			'select' => ['ID', 'UF_STAFF'],
		])->Fetch();

		if ($arUser['ID'] == $userId) {
			if (!is_array($arUser['UF_STAFF']))
				$arUser['UF_STAFF'] = [];

			return $arUser['UF_STAFF'];
		} else {
			return [];
		}
	}

	/**
	 * @param $userId ing - user id
	 * @return int - user id or 0
	 */
	public static function getUserIdByStaff($userId) {
		if ((int)$userId <= 0 || !$userId)
			return 0;

		$arUser = \Bitrix\Main\UserTable::getList([
			'filter' => ['UF_STAFF' => $userId],
			'select' => ['ID'],
		])->Fetch();

		if ($arUser['ID'] > 0)
			return $arUser['ID'];
		else
			return 0;
	}
}