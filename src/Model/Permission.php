<?php

namespace App\Model;

use App\Helper\DB;


class Permission extends BaseModel
{
	public const TABLE = 'permission';

	/**
	 * Zwraca tablicę wszystkich elementów bazy z tabeli director.
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT * FROM ' . self::TABLE;

		$stmt = $con->query($query, \PDO::FETCH_ASSOC);

		if ($stmt->execute()) {
			$data = $stmt->fetchAll();
		}

		return $this->toAssoc($data);
	}

	/**
	 * Zwraca tablicę wszystkich uprawnień użytkownika.
	 *
	 * @param int $userId Id użytkownika
	 * @return array
	 */
	public function getUserPerms(int $userId): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT * FROM user_' . self::TABLE . ' WHERE user_id = :uid';

		$stmt = $con->prepare($query);
		$stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);

		if ($stmt->execute()) {
			$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		return $this->toAssoc($data, ['user_id'], ' ', 'permission_id');
	}
}
