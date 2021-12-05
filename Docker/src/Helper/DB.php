<?php

namespace App\Helper;

use PDO;

/**
 *
 */
class DB
{
	/**
	 * Uchwyt do bazy danych.
	 *
	 * @var PDO
	 */
	public static $connection;

	/**
	 * Zwraca uchwyt do bazy danych.
	 *
	 * @return PDO
	 */
	public static function connection(): PDO
	{
		if (!isset(self::$connection)) {
			self::$connection = new PDO(
				'mysql:host=mov-mysql;port=3306;dbname=film;charset=utf8',
				'film',
				'film',
				[\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]
			);
		}

		return self::$connection;
	}
}
