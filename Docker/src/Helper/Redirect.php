<?php

namespace App\Helper;

use App\ResponseInterface;
/**
 *
 */
class Redirect implements ResponseInterface
{
	/**
	 * Adres do aplikacji.
	 *
	 * @var string
	 */
	const APP_URL = 'http://localhost:2300/';

	/**
	 * Adres na który nastąpi przekierowanie.
	 *
	 * @var string
	 */
	protected $_url;

	/**
	 * Prywatna instancja obiektu (singleton).
	 *
	 * @var self
	 */
	protected static $_instance;

	/**
	 * Funkcja przekierowywująca na wskazay adres
	 * lub adres główny aplikacji.
	 *
	 * @param  string|null $url Adres URL (tylko od początku aplikacji, np. 'home/' - ponieważ reszta zostanie dołożona za nas)
	 * @return self
	 */
	public static function redirect(string $url = null): self
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance->setUrl($url);
	}

	/**
	 * Ustawia adres URL przekierowania.
	 *
	 * @param  [type] $url [description]
	 * @return self        [description]
	 */
	public function setUrl(string $url = null): self
	{
		if (null === $url || false === is_string($url) || strlen($url) === 0) {
			$this->_url = self::APP_URL;
		} elseif (0 === strpos($url, 'http')) {
			$this->_url = $url;
		} else {
			$this->_url = self::APP_URL . $url;
		}

		return $this;
	}

	/**
	 * Zwraca adres URL.
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		if (!isset($this->_url)) {
			$this->setUrl();
		}

		return $this->_url;
	}

	/**
	 * Wykonanie przekierowania.
	 *
	 * @return void
	 */
	public function response(): void
	{
		header('Location: ' . $this->getUrl());
		exit();
	}
}
