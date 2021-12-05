<?php

namespace App\View;

use App\ResponseInterface;
use Exception;
use App\Helper\Redirect;
use Twig\TwigFunction;
use App\Helper\FilterPermissions;
/**
 *
 */
class View implements ResponseInterface
{
	/**
	 * Rozszerzenie szablonów.
	 *
	 * @var string
	 */
	const TEMPLATE_EXTENSION = '.html.twig';

	/**
	 * Ścieżka do wszystkich szablonów.
	 *
	 * @var string
	 */
	const TEMPLATES_PATH = 'src/View/templates/';

	/**
	 * Nazwa szablonu (wraz z ewentualną ścieżką).
	 *
	 * @var string
	 */
	protected $_template;

	/**
	 * Prywatna instancja obiektu (singleton).
	 *
	 * @var self
	 */
	protected static $_instance;

	/**
	 * Tablica assetów.
	 *
	 * @var array
	 */
	protected $_assets;

	/**
	 * Tablica danych.
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * Konstruktor.
	 */
	function __construct()
	{
		// Utworzenie klasy Twig wczytującej pliki szablonów z zadanego katalogu
        $loader = new \Twig_Loader_Filesystem(self::TEMPLATES_PATH);
        // Utworzenie klasy Twig przechowywującej konfigurację
        $this->twig = new \Twig_Environment($loader);
		$canFunction = new TwigFunction('can', [FilterPermissions::class, 'can']);
		$this->twig->addFunction($canFunction);

		$this->_assets = [
			0 => 'assets/css/ext/fa-all.css',
			1 => 'assets/css/ext/select2.min.css',
			2 => 'assets/js/ext/jquery-3.3.1.min.js',
			3 => 'assets/js/ext/bootstrap.bundle.min.js',
			4 => 'assets/js/ext/select2.full.min.js',
			5 => 'assets/css/custom.css',
			6 => 'assets/css/main.css'
		];

		$this->_data = [
			'appurl' => Redirect::APP_URL,
			'loggedInUser' => isset($_SESSION['logged-in-user']) ? $_SESSION['logged-in-user'] : null,
			'hello' => 'Witaj',
		];
	}

	/**
	 * Inicjalizacja widoku.
	 *
	 * @return self
	 */
	public function init(): self
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
	}

	/**
	 * Ustawienie szablonu.
	 *
	 * @param  string $template Nazwa szablonnu (np. 'User/index', 'baseTemplate', ..)
	 * @return self
	 */
	public function template(string $template): self
	{
		$template .= self::TEMPLATE_EXTENSION;  //baseTemplate .= .html.twig

		if (false === is_file(self::TEMPLATES_PATH . $template)) {
			throw new Exception('Brak szablonu');
		}

		$this->_template = $template;

		return $this;
	}

	/**
	 * Dodanie danych do widoku.
	 *
	 * @param  array $data Dane
	 * @return self
	 */
	public function with(array $data): self
	{
		$this->_data = array_merge($this->_data, $data);
		return $this;
	}

	/**
	 * Dodanie assetu/ów do zbioru.
	 *
	 * @param  string|array $asset Asset/y
	 * @param  string       $type  Typ assetu (css/js/img)
	 * @return self
	 */
	public function asset($asset, string $type): self
	{
		if (false === is_array($asset)) {
			$asset = "assets/$type/$asset.$type"; // assets/js/core/main.js

			if (is_file($asset)) {
				$this->_assets[] = $asset;
			}
		} else {
			foreach ($asset as $a) {
				$this->asset($a, $type);
			}
		}

		return $this;
	}

	/**
	 * Dodanie pliku js do assetów.
	 *
	 * @param  string|array $path Plik/i
	 * @return self
	 */
	public function js($path): self
	{
		if (false === is_array($path)) {
			$this->asset($path, 'js');
		} else {
			foreach ($path as $asset) {
				$this->js($asset);
			}
		}

		return $this;
	}

	/**
	 * Dodanie pliku css do assetów.
	 *
	 * @param  string|array $path Plik/i
	 * @return self
	 */
	public function css($path): self
	{
		if (false === is_array($path)) {
			$this->asset($path, 'css');
		} else {
			foreach ($path as $asset) {
				$this->css($asset);
			}
		}

		return $this;
	}

	/**
	 * Dodanie pliku img do assetów.
	 *
	 * @param  string|array $path Plik/i
	 * @return self
	 */
	public function img($path): self
	{
		if (false === is_array($path)) {
			$this->asset($path, 'img');
		} else {
			foreach ($path as $asset) {
				$this->img($asset);
			}
		}

		return $this;
	}

	/**
	 * Wyświetlenie widoku.
	 *
	 * @return void
	 */
	public function response(): void
	{
		if (isset($_SESSION['message'])) {
			$msg = $_SESSION['message'];
			unset($_SESSION['message']);
		} else {
			$msg = [];
		}

		$this->with([
			'assets' => $this->_assets,
			'message' => $msg
		]);

		echo $this->twig->render(
			$this->_template,
			$this->_data
		);
	}
}
