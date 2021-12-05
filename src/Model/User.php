<?php

namespace App\Model;

use App\Helper\DB;


class User extends BaseModel
{
	public const TABLE = 'user';

	/**
	 * Zwarac tablicę wszystkich elementów bazy z tabeli category.
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT * FROM ' . self::TABLE;

		$stmt = $con->query($query);

		if ($stmt->execute()) {
			$data = $stmt->fetchAll();
		}

		return $data;
	}

	/**
	 * Dodanie użytkownika do bazy.
	 *
	 * @return int Indeks wstawionego wiersza
	 */
	public function insert(string $login, string $password): int
	{
		$id = -1;
		$con = DB::connection();

		$query = 'INSERT INTO ' . self::TABLE . ' VALUES (NULL, :login, :password)';

		$stmt = $con->prepare($query);

		$stmt->bindValue(':login', $login, \PDO::PARAM_STR);
		$stmt->bindValue(':password', $password, \PDO::PARAM_STR);

		if ($stmt->execute()) {
			$id = $con->lastInsertId();
		}

		return $id;
	}

	/**
	 * Zwraca dane użytkwonika.
	 *
	 * @return array
	 */
	public function find(int $id): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE id = :id';

		$stmt = $con->prepare($query);
		$stmt->bindValue(':id', $id, \PDO::PARAM_INT);

		if ($stmt->execute()) {
			$data = $stmt->fetchAll();
		}

		return $data[0] ?? [];
	}

	public function updateUserPermissions(int $userId, array $permissions)
	{
		$con = DB::connection();

		$deleteQuery = 'DELETE FROM `user_permission` WHERE user_id = :user_id';
		$insertQuery = 'INSERT INTO `user_permission` VALUES (NULL, :user_id, :permission_id)';

		$effect = 0;

		try {
			// Rozpoczęcie transakcji
			$con->beginTransaction();
			// Przygotowanie zapytania usuwającego dotychczasowe uprawnienia użytkwonika
			$stmt = $con->prepare($deleteQuery);
			// Bindowanie ID użtykownika
			$stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
			// Wykonanie zapytania SQL usuwającego
			$effect = $stmt->execute();
			// Jeśli nie udało się wykonać - wyjątek żeby zrobić rollback
			if ($effect === false) {
				throw new \Exception();
			}
			// Przygotwanie zapytania wstawiajacego uprawnienie
			$stmt = $con->prepare($insertQuery);
			// Bidnowanie id użytkownika
			$stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
			// Dla każdego uprawnienia, przed wykonaniem zapytania podstawiamy nowe id uprawnienia
			// dzięki temu wstawimy przy użyciu jednego zapytania wiele uprawnień, bo przy każdym wykonaniu inne będzie :permission_id
			foreach ($permissions as $permissionId) {
				$stmt->bindValue(':permission_id', $permissionId, \PDO::PARAM_INT);
				// Jeśli nie udało się wstawić wiersza - wyjątek -> rollback
				if ($stmt->execute() === false) {
					throw new \Exception();
				}
			}
			// Efektem działań będzie id ostatniego wpisu z tabeli user_permission
			$effect = $con->lastInsertId();
			// Kończymy transakcję funkcją commit() - dane zostają na stałe zapisane do bazy
			$con->commit();
		} catch (\Throwable $e) {
			// Wszystkie zmiany zrobione w transakcji na bazie danych są wycofywane (do stanu sprzed transakcji)
			$con->rollBack();
			$effect = -1;
		}
		// Zwracamy id ostatniego wstawionego wiersza lub 0 - jeśli nie udło się wykonać operacji
		return $effect;
	}

	/**
	 * Zwraca dane użytkwonika.
	 *
	 * @return array
	 */
	public function findByLogin(string $login): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE login = :login';

		$stmt = $con->prepare($query);
		$stmt->bindValue(':login', $login, \PDO::PARAM_STR);

		if ($stmt->execute()) {
			$data = $stmt->fetchAll();
		}

		return isset($data[0]) ? $data[0] : [];
	}

	public function getUserPermissions(int $id): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT * FROM `user_permission` WHERE user_id = :user_id';

		$stmt = $con->prepare($query);
		$stmt->bindValue(':user_id', $id, \PDO::PARAM_INT);

		if ($stmt->execute()) {
			$data = $stmt->fetchAll();
		}

		return array_map(
			function ($element) {
				return $element['permission_id'];
			},
			$data
		);
	}

	public function hasPermission(int $userId, int $permissionId): bool
	{
		$userPermissions = $this->getUserPermissions($userId);

		return in_array($permissionId, $userPermissions);
	}
}
