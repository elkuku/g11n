<?php
/**
 * @copyright  2018 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Support;

/**
 * Class LanguageHelper
 * @package ElKuKu\G11n\Support
 * @since 1.0
 */
abstract class LanguageHelper
{
	/**
	 * List of known languages.
	 *
	 * @var array
	 */
	protected static $languages = [];

	/**
	 * Get the defined direction for a language.
	 *
	 * @param   string $languageCode The language code e.g. en-GB
	 *
	 * @return string 'ltr' or 'rtl'. Defaults to 'ltr'
	 */
	public static function getDirection(string $languageCode): string
	{
		if (array_key_exists($languageCode, static::$languages))
		{
			return static::$languages[$languageCode]['direction'] ?? 'ltr';
		}

		return 'ltr';
	}

	/**
	 * Get a language tag by code.
	 *
	 * @param   string $languageCode The language code.
	 *
	 * @return string
	 */
	public static function getLanguageTagByCode(string $languageCode): string
	{
		return array_key_exists($languageCode, static::$languages) ? static::$languages[$languageCode]['iso'] : '';
	}

	/**
	 * Get an array with language codes (e.g. en-GB)
	 *
	 * @return array
	 */
	public static function getLanguageCodes(): array
	{
		return array_keys(static::$languages);
	}

	/**
	 * Get an array containing information about languages.
	 *
	 * @return array
	 */
	public static function getLanguages(): array
	{
		return static::$languages;
	}

	/**
	 * Get an array containing information about languages.
	 * Sorted by display name.
	 *
	 * @return array
	 */
	public static function getLanguagesSortedByDisplayName(): array
	{
		$languages = static::$languages;

		uasort(
			$languages, function ($a, $b) {
				return strcmp($a['display'], $b['display']);
			}
		);

		return $languages;
	}
}
