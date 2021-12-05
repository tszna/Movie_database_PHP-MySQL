<?php

namespace App\Controller;

use App\JsonResponse;
use App\Helper\Redirect;

use App\Model\Film as FilmModel;
use App\Model\Category;
use App\Model\Director;
use App\View\View;


class Film
{
	/**
	 * Zwrócenie odpowiedzi do JavaScriptu
	 * z listą filmów okrojoną do okređlonej
	 * liczby (limit) oraz odpowiedznio przefiltrowaną.
	 *
	 * @return JsonResponse
	 */
	public function ajaxList()
	{
		try {
			// Filtry przysłane z JavaScriptu (w formie tablicy asocjacyjnej)
			$filters = $_POST['filtry'] ?? [];
			// Utworzenie modelu filmów
			$filmModel = new FilmModel();
			// Pobranie listy filmów zgodnie z zadanymi filtrami oraz ograniczeniami (limit, offset)
			$films = $filmModel->getAllWithRelations($filters, $_POST['limit'], $_POST['offset']);
			// Ustalenie całkowitej liczby strony dla obecnych filtrów (ile stron filmów przy obecnych filtrach jest)
			$numberOfPages = $filmModel->getNumberOfPages($filters, $_POST['limit']);

			return json([
				'films' => $films,
				'numberOfPages' => $numberOfPages
			]);
		} catch (\Exception $e) {
			return json(['error' => 'Wystąpił nieoczekiwany błąd serwera!'], 500);
		}
	}

	/**
	 * Znalezienie listy filmów do wyświetlenia pod inputem title
	 * @return JsonResponse
	 */
	public function ajaxFindTitle()
	{
		try {
			$filmModel = new FilmModel();

			$titles = $filmModel->findMatchTitles($_POST['value']);

			return json(['records' => $titles]);
		} catch (\Exception $e) {
			return json(['error' => 'Wystąpił nieoczekiwany błąd serwera!'], 500);
		}
	}

	/**
	 * Strona filmu.
	 *
	 * @return
	 */
	public function getFilmById()
	{
		$filmId = $_GET['id'];

		$model = new FilmModel();
		$data['film'] = $model->getFilm($filmId);

		if (!isset($data['film']['title'])) {
			return \App\Helper\Redirect::redirect('')->response();
		}

		$view = new View();

		return $view->template('film')->with($data);
	}
}
