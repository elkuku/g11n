<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Support;

/**
 * The G11n store description class.
 *
 * @since  1
 */
class Store
{
	/**
	 * @var array
	 */
	private $strings = [];

	/**
	 * @var array
	 */
	private $stringsPlural = [];

	/**
	 * @var array
	 */
	private $stringsJs = [];

	/**
	 * @var array
	 */
	private $stringsJsPlural = [];

	/**
	 * @var string
	 */
	private $pluralForms = '';

	/**
	 * @var string
	 */
	private $langPath = '';

	/**
	 * @var string
	 */
	private $cachePath = '';

	/**
	 * @param   array  $strings  Strings
	 *
	 * @return Store
	 */
	public function setStrings(array $strings): Store
	{
		$this->strings = $strings;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getStrings(): array
	{
		return $this->strings;
	}

	/**
	 * @param   array  $stringsPlural  Plural strings
	 *
	 * @return Store
	 */
	public function setStringsPlural(array $stringsPlural): Store
	{
		$this->stringsPlural = $stringsPlural;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getStringsPlural(): array
	{
		return $this->stringsPlural;
	}

	/**
	 * @param   string  $pluralForms  Plural forms
	 *
	 * @return Store
	 */
	public function setPluralForms(string $pluralForms): Store
	{
		$this->pluralForms = $pluralForms;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPluralForms(): string
	{
		return $this->pluralForms;
	}

	/**
	 * @param   array  $stringsJs  JS strings
	 *
	 * @return Store
	 */
	public function setStringsJs(array $stringsJs): Store
	{
		$this->stringsJs = $stringsJs;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getStringsJs(): array
	{
		return $this->stringsJs;
	}

	/**
	 * @param   array  $stringsJsPlural JS plural strings
	 *
	 * @return Store
	 */
	public function setStringsJsPlural(array $stringsJsPlural): Store
	{
		$this->stringsJsPlural = $stringsJsPlural;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getStringsJsPlural(): array
	{
		return $this->stringsJsPlural;
	}

	/**
	 * @param   string  $langPath  The language path.
	 *
	 * @return Store
	 */
	public function setLangPath(string $langPath): Store
	{
		$this->langPath = $langPath;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLangPath(): string
	{
		return $this->langPath;
	}

	/**
	 * @param   string  $cachePath  The cache path
	 *
	 * @return Store
	 */
	public function setCachePath(string $cachePath): Store
	{
		$this->cachePath = $cachePath;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCachePath(): string
	{
		return $this->cachePath;
	}
}
