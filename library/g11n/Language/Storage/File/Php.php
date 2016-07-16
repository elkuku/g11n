<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language\Storage\File;

use g11n\Language\Storage;
use g11n\Support\ExtensionHelper;
use g11n\Support\FileInfo;
use g11n\Support\Store;
use g11n\G11nException;

/**
 * Storage handler for PHP files.
 *
 * @since  1.0
 */
class Php extends Storage\File
{
	/**
	 * @var FileInfo
	 */
	public $fileInfo = null;

	/**
	 * @var string
	 */
	protected $ext = '.php';

	/**
	 * Constructor.
	 *
	 * @param   string  $type  The input type
	 *
	 * @throws G11nException
	 */
	public function __construct($type)
	{
		parent::__construct($type);

		self::$cacheDir = ExtensionHelper::getCacheDir() . '/' . ExtensionHelper::$langDirName;
	}

	/**
	 * Stores the strings into a storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 * @return string  The path where the language file has been found / empty if it is read from cache.
	 */
	public function store($lang, $extension, $domain = '')
	{
		$ext = $this->parser->getExt();

		/*
		 * Parse language files
		 */
		$fileName = ExtensionHelper::findLanguageFile($lang, $extension, $domain, $ext);

		$fileInfo = $this->parser->parse($fileName);

		$this->fileInfo = $fileInfo;

		/*
		 * "Normal" strings
		 */

		$stringsArray = array();
		$value        = '';

		foreach ($fileInfo->strings as $key => $value)
		{
			$key   = md5($key);
			$value = base64_encode($value->string);

			$stringsArray[] = "'" . $key . "'=>'" . $value . "'";
		}

		/*
		 * Plural strings
		 */

		$pluralsArray = array();

		foreach ($fileInfo->stringsPlural as $key => $plurals)
		{
			$key = md5($key);
			$ps  = array();

			foreach ($plurals->forms as $keyP => $plural)
			{
				$value = base64_encode($plural);
				$ps[]  = "'" . $keyP . "'=>'" . $value . "'";
			}

			$value          = base64_encode($value);
			$pluralsArray[] = "'" . $key . "'=> array(" . implode(',', $ps) . ")";
		}

		/*
		 * JavaScript strings
		 */

		$jsArray        = array();
		$jsPluralsArray = array();

		try
		{
			$jsFileName = ExtensionHelper::findLanguageFile($lang, $extension, $domain, 'js.' . $ext);

			$jsInfo = $this->parser->parse($jsFileName);

			foreach ($jsInfo->strings as $key => $value)
			{
				$key       = md5($key);
				$value     = base64_encode($value->string);
				$jsArray[] = "'" . $key . "'=>'" . $value . "'";
			}

			$jsPluralsArray = array();

			foreach ($jsInfo->stringsPlural as $key => $plurals)
			{
				$key = md5($key);
				$ps  = array();

				foreach ($plurals as $keyP => $plural)
				{
					$value = base64_encode($plural);
					$ps[]  = "'" . $keyP . "'=>'" . $value . "'";
				}

				$value            = base64_encode($value);
				$jsPluralsArray[] = "'" . $key . "'=> array(" . implode(',', $ps) . ")";
			}
		}
		catch (\Exception $e)
		{
			// We did not found the javascript files...
			// Do nothing - for now..@todo do something :P
			echo '';
		}

		/*
		 * Process the results - Construct an ""array string""
		 * Result:
		 * '<?php $strings = array('a'=>'b', ...); ?>'
		 */

		$resultString = '<?php '
			. '$info=array('
			. "'mode'=>'" . $fileInfo->mode . "'"
			. ",'pluralForms'=>'" . $this->translatePluralForms($fileInfo->pluralForms) . "'"
			. ");"
			. ' $strings=array(' . implode(',', $stringsArray) . ');'
			. ' $stringsPlural=array(' . implode(',', $pluralsArray) . ');'
			. ' $stringsJs=array(' . implode(',', $jsArray) . ');'
			. ' $stringsJsPlural=array(' . implode(',', $jsPluralsArray) . ');';

		$storePath = $this->getPath($lang, $extension, $domain) . $this->ext;

		if (false == is_dir(dirname($storePath)))
		{
			$tmp = umask(0);
			$result = mkdir(dirname($storePath), 0777, true);
			umask($tmp);

			if (false == $result)
			{
				throw new G11nException('Can not create the cache directory');
			}
		}

		if ( ! file_put_contents($storePath, $resultString))
		{
			throw new G11nException('Unable to write language storage file to ' . $storePath);
		}

		return $fileName;
	}

	/**
	 * Retrieve the storage content.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 * @return \g11n\Support\Store
	 */
	public function retrieve($lang, $extension, $domain = '')
	{
		$path = $this->getPath($lang, $extension, $domain) . $this->ext;

		$langPath = '---';

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

		/*
		 * Include the "cache" file containing the language strings.
		 * This file should contain the arrays:
		 * # $info[]
		 * # $strings[]
		 * # $stringsPlural[]
		 * # $jsStrings[]
		 * # $stringsJsPlural[]
		 */
		include $path;

		$store = new Store;

		$store->set('langPath', $langPath);
		$store->set('cachePath', $path);

		if (isset($info['pluralForms']))
		{
			$store->set('pluralForms', $info['pluralForms']);
		}

		if (!empty($strings))
		{
			$store->set('strings', $strings);
		}

		if (!empty($stringsPlural))
		{
			$store->set('stringsPlural', $stringsPlural);
		}

		if (!empty($stringsJs))
		{
			$store->set('stringsJs', $stringsJs);
		}

		if (!empty($stringsJsPlural))
		{
			$store->set('stringsJsPlural', $stringsJsPlural);
		}

		return $store;
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
