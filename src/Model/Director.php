<?php

namespace App\Model;

use App\Helper\DB;


class Director extends BaseModel
{
	public const TABLE = 'director';

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
}
