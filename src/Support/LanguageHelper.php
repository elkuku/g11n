<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 29/06/18
 * Time: 13:24
 */

namespace ElKuKu\G11n\Support;


class LanguageHelper
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
	 * @param   string  $languageCode  The language code e.g. en-GB
	 *
	 * @return string 'ltr' or 'rtl'. Defaults to 'ltr'
	 */
	public static function getDirection($languageCode)
	{
		if (array_key_exists($languageCode, static::$languages))
		{
			return static::$languages[$languageCode]['direction'] ?? 'ltr';
		}

		return 'ltr';
	}
}
