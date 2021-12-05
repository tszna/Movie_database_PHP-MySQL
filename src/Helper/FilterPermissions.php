<?php

namespace App\Helper;


class FilterPermissions
{
	/**
	 * @var \App\Model\User
	 */
	protected static $userModel;

	/**
	 * @var array
	 */
	protected static $permissions;

	/**
	 * Sprwdzenie czy użytkownik może wykorzystywać filtr.
	 *
	 * @param  string $filter Nazwa filtru
	 * @return bool           Czy możę?
	 */
	public static function can(string $filter): bool
	{
		if (!isset($_SESSION['logged-in-user'])) {
			return false;
		}

		if (!isset(self::$permissions)) {
			$permsModel = new \App\Model\Permission();
			self::$permissions = \array_flip($permsModel->getAll());
		}

		if (!isset(self::$userModel)) {
			self::$userModel = new \App\Model\User();
		}

		$permissionId = self::$permissions[$filter];
		$userId = $_SESSION['logged-in-user']['id'];

		return self::$userModel->hasPermission($userId, $permissionId);
	}
}
