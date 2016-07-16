<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Support;

use ElKuKu\G11n\G11nException;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Extension helper class.
 *
 * @since  1.0
 */
abstract class ExtensionHelper
{
	/**
	 * Known domain paths.
	 *
	 * @var array
	 */
	protected static $domainPaths = array();

	protected static $cacheDir = '/tmp';

	public static $langDirName = 'g11n';

	/**
	 * Set a custom directory name for language files.
	 *
	 * @param   string  $name  The directory name.
	 *
	 * @return void
	 */
	public static function setDirName($name)
	{
		self::$langDirName = $name;
	}

	/**
	 * Set the cache directory.
	 *
	 * @param   string  $path  A valid path.
	 *
	 * @throws G11nException
	 * @return void
	 */
	public static function setCacheDir($path)
	{
		if (false == is_dir($path))
		{
			throw new G11nException('Invalid cache dir');
		}

		$path .= '/g11n';

		if (false == is_dir($path))
		{
			$tmp = umask(0);
			$result = mkdir($path, 0777, true);
			umask($tmp);

			if (false == $result)
			{
				throw new G11nException('Can not create the cache directory');
			}
		}

		self::$cacheDir = $path;
	}

	/**
	 * Get the cache dir path.
	 *
	 * @return string
	 */
	public static function getCacheDir()
	{
		return self::$cacheDir;
	}

	/**
	 * Clean the cache dir.
	 *
	 * @throws \DomainException
	 * @return void
	 */
	public static function cleanCache()
	{
		$filesystem = new Filesystem(new Local(self::$cacheDir));

		foreach ($filesystem->listContents() as $path)
		{
			if ('dir' == $path['type'])
			{
				if (false == $filesystem->deleteDir($path['path']))
				{
					throw new \DomainException('Can not clean the cache.');
				}
			}
		}
	}

	/**
	 * Get the extension path.
	 *
	 * @param   string  $extension  The extension name, e.g. com_easycreator
	 *
	 * @return string
	 */
	public static function getExtensionPath($extension)
	{
		static $dirs = array();

		if (array_key_exists($extension, $dirs))
		{
			return $dirs[$extension];
		}

		$extensionDir = $extension;

		$parts = self::split($extension);

		if (count($parts) > 1)
		{
			// We have a subType
			$extensionDir = $parts[0];
		}

		$dirs[$extension] = $extensionDir;

		return $extensionDir;
	}

	/**
	 * Get the extensions language path.
	 *
	 * @param   string  $extension  The extension name, e.g. com_easycreator
	 *
	 * @return string
	 */
	public static function getExtensionLanguagePath($extension)
	{
		$path = self::getExtensionPath($extension);

		return $path . '/' . self::$langDirName;
	}

	/**
	 * Check that the extension is valid.
	 *
	 * @param   string  $extension  The extension name, e.g. com_easycreator
	 * @param   string  $domain     The extension scope, e.g. admin
	 *
	 * @return bool
	 */
	public static function isExtension($extension, $domain = '')
	{
		$extensionPath = self::getExtensionPath($extension);
		$scopePath     = self::getDomainPath($domain);

		return is_dir($scopePath . '/' . $extensionPath);
	}

	/**
	 * Get a domain path.
	 *
	 * @param   string  $domain  The extension domain.
	 *
	 * @throws G11nException
	 * @return string
	 */
	public static function getDomainPath($domain)
	{
		if (array_key_exists($domain, self::$domainPaths))
		{
			return self::$domainPaths[$domain];
		}

		throw new G11nException('Undefined domain: ' . $domain);
	}

	/**
	 * Searches the system for language files.
	 *
	 * @param   string  $lang       Language
	 * @param   string  $extension  Extension
	 * @param   string  $domain     The extension scope, e.g. admin
	 * @param   string  $type       Language file type - e.g. 'ini', 'po' etc.
	 *
	 * @return mixed Full path to file | false if none found
	 */
	public static function findLanguageFile($lang, $extension, $domain = '', $type = 'po')
	{
		$base = self::getDomainPath($domain);

		$fileName = $lang . '.' . $extension . '.' . $type;

		$extensionDir = self::getExtensionPath($extension);

		$extensionLangDir = self::getExtensionLanguagePath($extension);

		// First try our special dir
		$path = "$base/$extensionLangDir/$lang/$fileName";

		if (file_exists($path))
		{
			return $path;
		}

		// Next try extension/language directory
		$path = "$base/$extensionDir/language/$lang/$fileName";

		if (file_exists($path))
		{
			return $path;
		}

		// Now try the base language dir
		$path = "$base/" . self::$langDirName . "/$lang/$fileName";

		if (file_exists($path))
		{
			return $path;
		}

		// Found nothing :(

		// @ throw new Exception('No language files found');
		return false;
	}

	/**
	 * Splits a string by a separator.
	 *
	 * Expects exactly one or two parts. Otherwise it will fail.
	 *
	 * @param   string  $string     The string to split
	 * @param   string  $delimiter  The delimiter character
	 *
	 * @throws G11nException
	 * @return array
	 */
	public static function split($string, $delimiter = '.')
	{
		$parts = explode($delimiter, $string);

		if (count($parts) < 1
			|| count($parts) > 2)
		{
			throw new G11nException('Invalid type - must be xx' . $delimiter . '[xx]: ' . $string);
		}

		return $parts;
	}

	/**
	 * Add a path to search for language files.
	 *
	 * @param   string  $domain  The domain name.
	 * @param   string  $path    A path to search for language files.
	 *
	 * @return void
	 */
	public static function addDomainPath($domain, $path)
	{
		self::$domainPaths[$domain] = $path;
	}
}
