<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language\Storage\File;

use ElKuKu\G11n\G11nException;
use ElKuKu\G11n\Language\Storage;
use ElKuKu\G11n\Support\Store;

/**
 * g11nStorageFileTxt class
 *
 * @since  1
 */
class Txt extends Storage\File
{
	/**
	 * @var string
	 */
	protected $ext = '.php';

	/**
	 * Retrieve the storage content.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 *
	 * @return Store
	 */
	public function retrieve(string $lang, string $extension, string $domain = '') : Store
	{
		$path = $this->getPath($lang, $extension, $domain) . $this->ext;

		// File has not being cached
		if (!file_exists($path))
		{
			// Try to store
			$langPath = $this->store($lang, $extension, $domain);

			// Failed ?
			if (!file_exists($path))
			{
				throw new G11nException('Unable to retrieve the strings');
			}
		}

		$strings = JFile::read($path);

		if (! $strings)
		{
			return false;
		}

		$strings = json_decode($strings, true);

		$this->strings = array_merge($this->strings, $strings);

		// Language overrides
		$this->strings = array_merge($this->strings, $this->override);

		$this->paths[$extension][$fileName] = true;

		return true;
	}

	/**
	 * Stores the strings into a storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 *
	 * @return boolean
	 */
	public function store(string $lang, string $extension, string $domain = '') : string
	{
		$strings = self::parseFile($fileName);

		$path = self::$cacheDir . '/' . $lang . '.' . $extension . '.txt';
		$jsonString = json_encode($strings);

		if (! JFile::write($path, $jsonString))
		{
			throw new G11nException('Unable to write language storage file');
		}

		return true;
	}

	/**
	 * Cleans the storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 * @return void
	 */
	public function clean($lang, $extension, $domain = '')
	{
		$storePath = $this->getPath($lang, $extension, $domain) . $this->ext;

		// Storage file does not exist
		if (!file_exists($storePath))
		{
			return;
		}

		// @Do_NOT_Translate
		if (!unlink($storePath))
		{
			throw new G11nException('Unable to clean storage in: ' . $storePath);
		}
	}
}
