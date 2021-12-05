<?php

namespace App\Model;

use App\Helper\DB;
use App\Helper\Redirect;


class Film extends BaseModel
{
	public const TABLE = 'film';

	/**
	 * Zwraca tablicę wszystkich elementów bazy z tabeli film.
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

		$this->supplyDirForImages($data);

		return $data;
	}

	/**
	* Pobranie listy filmów zgodnie z
	* zadanymi filtrami oraz
	* ograniczeniami (limit, offset).
	*
	 * @param  array $filters Tablica filtrów
	 * @param  int   $limit   Liczba filmów do pobrania z bazy
	 * @param  int   $offset  Miejsce od którego zaczynamy pobierać filmy z bazy (np. od 5. filmu)
	 * @return array          Filmy
	 */
	public function getAllWithRelations(array $filters, int $limit, int $offset): array
	{
		// Tablica na dane
		$data = [];
		// Tablica na bindy (zmienne w zapytaniu SQL)
		$binds = [];
		// Połączenie z bazą danych
		$con = DB::connection();
		// Tworzymy zapytanie (ta część jest identyczna niezależnie od filtrów, limitów itd.)
		$query = 'SELECT f.id, f.img, f.title, f.original_title, f.year FROM `' . self::TABLE . '` f'
			. ' JOIN `film_category` fc ON fc.film_id = f.id'
			. ' JOIN `film_director` fd ON fd.film_id = f.id';


		// Część zapytania zawierająca filtry (będzie na koniec dodana do części niezmiennej (tej powyżej))
		$filtersQueryPart = '';
		// Jeśli długość tekstu zapisanego pod indeksem "year" (rok produkcji filmu) jest różna od 0 (czyli czy jest coś w ten filtr wpisane)
		if (isset($filters['year']) && strlen($filters['year']) !== 0) {
			// Jeśli jest już coś wpisane w część zapytania SQl dot. filtrów - zaczynamy od " AND " żeby odseparować się od tego co dotychczas się zebrało
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			// Dodanie w SQL filtru odnośnie roku produkcji filmu
			$filtersQueryPart .= "f.year LIKE :year";
			// Zapisanie bindy w tablicy bind
			$binds[':year'] = '%' . $filters['year'] . '%';
		}
		// Jeśli jest coś w filtrze tytułu filmu - będziemy filtrować wyniki po tytule
		if (isset($filters['title']) && strlen($filters['title']) !== 0) {
			// Jeśli jest już coś wpisane w część SQL dot. filtrów (np. filtowanie po roku prod.) - odseparowywujemy się od tego słówkiem " AND "
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			// Dodanie w SQL filtru po tytule filmu
			$filtersQueryPart .= "f.title LIKE :title";
			// Dodanie bindy w tbalicy bind
			$binds[':title'] = '%' . $filters['title'] . '%';
		}
		// Jeśli jest coś w filtrze reżyserów - będziemy filtrować
		if (isset($filters['director']) && strlen($filters['director']) !== 0) {
			// Jęsli już coś zapisaliśmy w SQL filtrów - dokładamy AND
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			// DOdanie do SQL filtru po reżyserze
			$filtersQueryPart .= "fd.director_id = :director";
			// Dodanie bindy
			$binds[':director'] = $filters['director'];
		}
		// Jęsli jest coś w filtrze kategorii - filtrujemy
		if (isset($filters['category']) && is_array($filters['category'])) {
			// Jęsli jest coś w SQL filtrów - dokładamy AND
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			// Filtr kategorii może zawierać jeden lub więcej identyfikatorów kategorii (id)
			// Dlatego też nie zrobimy category_id = wartość, tylko category_id IN (wart1, wart2, ...)
			// - żeby móc filtrówać po kilku identyfikatorach
			$filtersQueryPart .= 'fc.category_id IN (';
			// Przejście po liście kategorii po których mamy filtrować
			foreach ($filters['category'] as $key => $category) {
				// Do SQl dodanie ":category_0, " tyle razy ile jest kategorii w filtrze (raz lub więcej)
				$filtersQueryPart .= ":category_$key, ";
				// Dodanie bindy
				$binds[":category_$key"] = $category;
			}
			// Ucinamy z części SQL dot. filtrów ostatnie 2 znaki (", ") oraz zamykamy nawias (tak żeby category_id IN () miało nawias zamykający)
			$filtersQueryPart = substr($filtersQueryPart, 0, -2) . ')';
		}
		// Jeśli część SQL dot. filtrów (jej długość) jest większa od 0 (czyli czy jest chociażby jeden filtr)
		if (strlen($filtersQueryPart) > 0) {
			// Dokładamy tę część do zapytanie dpoisująć przed słówko WHERE
			$query .= ' WHERE ' . $filtersQueryPart;
		}
		// Dodanie grupowania wynikó po tytułach (aby jeden film nie wystąpił więcej niż raz - np. ze względu na to że ma przypisane dwie kategorie - wtedy wystąpił by dwa razy)
		$query .= ' GROUP BY f.title'
			. ' ORDER BY f.id ASC'
			. ' LIMIT :limit'
			. ' OFFSET :offset';

		// Przygotowanie zapytania
		$stmt = $con->prepare($query);
		// Bindowanie daych (bindujemy to co udało nam się zebrać - ew. nic jeśli nie było filtrów)
		foreach ($binds as $key => $value) {
			$stmt->bindValue($key, $value);
		}
		// Bindowanie stałych bind - limit, offset
		$stmt->bindValue(':limit', intval($limit), \PDO::PARAM_INT);
		$stmt->bindValue(':offset', intval($offset), \PDO::PARAM_INT);
		// Wykonanie zapytania
		if ($stmt->execute()) {
			// Zpisanie wyników do tablicy
			$data = $stmt->fetchAll();
		}
		// Uzupełnienie ścieżek do obrazkó filmów o część http://localhost.../film/
		$this->supplyDirForImages($data);
		// Uzupełnienie danych o relację
		$this->supplyRelations($data);
		// Zwrócenie danych
		return $data;
	}

	/**
	 * Dodanie adresu do aplikacji do ścieżek obazów filmów.
	 *
	 * @param array $data
	 */
	private function supplyDirForImages(array &$data): void
	{
		foreach ($data as &$v) {
			$v['img'] = Redirect::APP_URL . $v['img'];
		}
	}

	/**
	 * Funkcja dodaje dane relacyjne do filmów
	 * (takie gdzie do jednego filmu może być
	 * przypisanych wiele wartości, np. kategorie).
	 *
	 * @param array $data Lista filmów
	 * @return array Filmy z uzupełnionymi relacjami
	 */
	private function supplyRelations(array &$data): void
	{
		// Tablica na relacje
		$relations = [];
		// Połączenie z bazą danych
		$con = DB::connection();
		// Niezależna od filtrów część zapytania
		// Pobranie id filmu, id kategorii, nazwę kategorii, id reżysera,
		// nazwisko reżysera z tabeli filmów połączonej z tabelą łączącą
		// filmy z kategoriami, filmy z reżyserami, oraz samymi tabeliami
		// kategorii i reżyseró (żeby pobrać ich dane - nazwy, nzawiska)
		$query = 'SELECT f.id, c.id as category_id, c.name as category_name, d.id as director_id, d.name as director_name  FROM ' . self::TABLE . ' f'
			. ' JOIN `film_category` fc ON fc.film_id = f.id'
			. ' JOIN `film_director` fd ON fd.film_id = f.id'
			. ' JOIN `category` c ON c.id = fc.category_id'
			. ' JOIN `director` d ON d.id = fd.director_id WHERE f.id IN (';
		// Pobieramy tylko te relacje, któe dotycxą obecnych filmów
		// - dlatego ograniczymy wyniki tylko do tych, których id filmu
		// jest jednym z id filmow z tablicy $data (wynik filtrów)
		foreach ($data as $v) {
			$query .= $v['id'] . ', ';
		}
		// Ucięcie ", " i zamnkięcie nawiasu
		$query = \substr($query, 0, -2) . ')';
		// s($query);
		// Przygotowanie zapytania
		$stmt = $con->prepare($query);
		// Wykonaine zapytania
		if ($stmt->execute()) {
			// Zapisanie danych do tablicy
			$relations = $stmt->fetchAll();
		}
		// Tablica reżyserów
		$directors = [];
		// Tablica kategorii
		$categories = [];
		// Przepisanie danych wynikowych z bazy na osobne tablice kategorii i reżyserów
			foreach ($relations as $k => $v) {
			$directors[$v['id']][$v['director_id']] = $v['director_name'];
			$categories[$v['id']][$v['category_id']] = $v['category_name'];
		}
		// Przypisujemy do indeksów kategorii i reżyserów całę wartości z tablic powyżej, czyli każdy film będzie miał swoje kategorie wg wzoru:
		// [
		//     id_kategroii => "nazwa kategorii",
		//     id_kategroii2 => "nazwa kategorii 2",
		//     ...
		// ]
		// Tak samo w przypadku reżyserów
		foreach ($data as &$film) {
			$film['director'] = $directors[$film['id']];
			$film['category'] = $categories[$film['id']];
		}
	}

	/**
	 * Ustalenie całkowitej liczby strony dla
	 * obecnych filtrów (ile stron filmów przy
	 * obecnych filtrach jest).
	 *
	 * @param  array $filters Filtry z JavaScript
	 * @param  int   $limit   Limit filmów na stronę
	 * @return int            Liczba stron
	 */
	public function getNumberOfPages(array $filters, int $limit): int
	{
		$data = [];
		$binds = [];
		$con = DB::connection();

		$query = 'SELECT f.id FROM ' . self::TABLE . ' f'
			. ' JOIN `film_category` fc ON fc.film_id = f.id'
			. ' JOIN `film_director` fd ON fd.film_id = f.id'
			. ' JOIN `category` c ON c.id = fc.category_id'
			. ' JOIN `director` d ON d.id = fd.director_id';

		// Część zapytania zawierająca filtry
		$filtersQueryPart = '';

		if (isset($filters['year']) && strlen($filters['year']) !== 0) {
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			$filtersQueryPart .= "f.year LIKE :year";
			$binds[':year'] = '%' . $filters['year'] . '%';
		}

		if (isset($filters['title']) && strlen($filters['title']) !== 0) {
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			$filtersQueryPart .= "f.title LIKE :title";
			$binds[':title'] = '%' . $filters['title'] . '%';
		}

		if (isset($filters['director']) && strlen($filters['director']) !== 0) {
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			$filtersQueryPart .= "fd.director_id = :director";
			$binds[':director'] = $filters['director'];
		}

		if (isset($filters['category']) && is_array($filters['category'])) {
			if (strlen($filtersQueryPart) > 0) {
				$filtersQueryPart .= ' AND ';
			}
			$filtersQueryPart .= 'fc.category_id IN (';

			foreach ($filters['category'] as $key => $category) {
				$filtersQueryPart .= ":category_$key, ";
				$binds[":category_$key"] = $category;
			}

			$filtersQueryPart = substr($filtersQueryPart, 0, -2) . ')';
		}

		if (strlen($filtersQueryPart) > 0) {
			$query .= ' WHERE ' . $filtersQueryPart;
		}

		$query .= ' GROUP BY f.title';

		$stmt = $con->prepare($query);

		foreach ($binds as $key => $value) {
			$stmt->bindValue($key, $value);
		}

		if ($stmt->execute()) {
			$data = $stmt->fetchAll();
		}
		// Liczymy (zaokrąglając do pierwszej pełnej większej liczby (np. 1.2 daje 2))
		// ile stron filmów będzie przy obecnych filtrach
		return ceil(count($data) / $limit);
	}

	/**
	 * Zwraca tablicę wszystkich elementów bazy z tabeli film.
	 *
	 * @return array
	 */
	public function findMatchTitles(string $title): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT `id`, `title` FROM ' . self::TABLE . ' WHERE `title` LIKE :title';

		$stmt = $con->prepare($query);
		$stmt->bindValue(':title', '%' . \strtolower($title) . '%', \PDO::PARAM_STR);

		if ($stmt->execute()) {
			$tmp = $stmt->fetchAll();
		}

		$data = [];

		foreach ($tmp as &$row) {
			$data[$row['id']] = $row['title'];
		}

		return $data;
	}

	/**
	 * Zwrócenie filmu wraz z wszytskimi danymi o nim.
	 *
	 * @param  int   $id Id filmu
	 * @return array     Dane filmu
	 */
	public function getFilm(int $id): array
	{
		$data = [];
		$con = DB::connection();

		$query = 'SELECT f.*, c.name as `category`, d.name as `director`, c.id as `category_id`, d.id as `director_id`'
			. ' FROM ' . self::TABLE . ' f'
			. ' LEFT JOIN `film_category` fc ON fc.film_id = f.id'
			. ' LEFT JOIN `film_director` fd ON fd.film_id = f.id'
			. ' LEFT JOIN `category` c ON c.id = fc.category_id'
			. ' LEFT JOIN `director` d ON d.id = fd.director_id'
			. ' WHERE f.`id` = :id';

		$stmt = $con->prepare($query);
		$stmt->bindValue(':id', $id, \PDO::PARAM_INT);

		if ($stmt->execute()) {
			$tmp = $stmt->fetchAll();
		}

		$data = [
			'img' => Redirect::APP_URL . $tmp[0]['img'],
			'title' => $tmp[0]['title'],
			'original_title' => $tmp[0]['original_title'],
			'year' => $tmp[0]['year'],
			'category' => [
				$tmp[0]['category_id'] => $tmp[0]['category']
			],
			'director' => [
				$tmp[0]['director_id'] => $tmp[0]['director']
			],
		];

		unset($tmp[0]);

		foreach ($tmp as $v) {
			$data['category'][$v['category_id']] = $v['category'];
			$data['director'][$v['director_id']] = $v['director'];
		}

		return $data;
	}
}
