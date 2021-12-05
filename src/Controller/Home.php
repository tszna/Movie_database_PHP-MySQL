<?php

namespace App\Controller;

use App\View\View;


class Home
{
	public function index()
	{
		$view = new View();


		return $view->template('baseTemplate')
			->js([
				'Filters',
				'FilmEntry',
				'FilmBox',
				'Filter',
				'Filters/InputFilter',
				'Filters/YearFilter',
				'Filters/TitleFilter',
				'Filters/SelectFilter',
				'Filters/CategoryFilter',
				'Filters/DirectorFilter',
				'PaginateButton',
				'Pagination'
			]);
	}
}
