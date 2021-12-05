<?php

namespace App\Model;



abstract class BaseModel
{
	/**
	 * Zwrócenie danych jako klucz wartość:
	 * @param  array  $data   [description]
	 * @param  string $key    [description]
	 * @param  array  $values [description]
	 * @return [type]         [description]
	 */
	public function toAssoc(array $data, array $values = ['name'], string $separator = ' ', string $key = 'id')
	{
		$arr = [];

		foreach ($data as $v) {
			$arr[$v[$key]] = '';

			foreach ($values as $value) {
				$arr[$v[$key]] .= $v[$value] . $separator;
			}

			$arr[$v[$key]] = substr($arr[$v[$key]], 0, - strlen($separator));
		}

		return $arr;
	}
}
