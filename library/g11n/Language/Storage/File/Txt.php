<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

// @codingStandardsIgnoreStart

namespace g11n\Language\Storage\File;

use g11n\G11nException;
use g11n\Language\Storage;

/**
 * g11nStorageFileTxt class
 *
 * @since  1
 */
class G11nStorageFileTxt extends Storage\File
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
	 * @return \g11n\Support\Store
	 */
	public function retrieve($lang, $extension, $domain = '')
	{
		if (self::$storage == 'off')
		{
			return false;
		}

		$profiler = JProfiler::getInstance('LangDebug');
		$profiler->mark('start: ' . $extension);

		jimport('joomla.filesystem.file');

		$path = self::$cacheDir . '/' . $lang . '.' . $extension . '.txt';

		if ( ! JFile::exists($path))
		{
			return false;
		}

		$strings = JFile::read($path);

		if ( ! $strings)
		{
			return false;
		}

		$strings = json_decode($strings, true);

		$profiler->mark('<span style="color: green;">*Loaded txt*</span>' . htmlentities($path));

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
	 * @return boolean
	 */
	public function store($lang, $extension, $domain = '')
	{
		if (self::$storage == 'off')
		{
			return false;
		}

		$profiler = JProfiler::getInstance('LangDebug');
		$profiler->mark('store: ' . $extension);

//        #		$fileNames = JFolder::files(JPATH_ADMINISTRATOR, '.sys.ini', false, true);

		$strings = self::parseFile($fileName);

		$path = self::$cacheDir . '/' . $lang . '.' . $extension . '.txt';
		$jsonString = json_encode($strings);

		if ( ! JFile::write($path, $jsonString))
		{
			throw new G11nException('Unable to write language storage file');
		}

		$profiler->mark('<span style="color: blue;">wrote file</span>: '
		. str_replace(JPATH_ROOT, 'J', $path)
		);

		$profiler->mark('store SUCCESS ++++: ' . $extension);

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
