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
	protected static $domainPaths = [];

	/**
	 * @var string
	 */
	protected static $cacheDir = '/tmp';

	/**
	 * @var string
	 */
	public static $langDirName = 'g11n';

	/**
	 * Set a custom directory name for language files.
	 *
	 * @param   string  $name  The directory name.
	 *
	 * @return void
	 */
	public static function setDirName(string $name) : void
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
	public static function setCacheDir(string $path) : void
	{
		if (false === is_dir($path))
		{
			throw new G11nException('Invalid cache dir');
		}

		$path .= '/g11n';

		if (false === is_dir($path))
		{
			$tmp = umask(0);
			$result = mkdir($path, 0777, true);
			umask($tmp);

			if (false === $result)
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
	public static function getCacheDir() : string
	{
		return self::$cacheDir;
	}

	/**
	 * Clean the cache dir.
	 *
	 * @throws \DomainException
	 * @return void
	 */
	public static function cleanCache() : void
	{
		$filesystem = new Filesystem(new Local(self::$cacheDir));

		foreach ($filesystem->listContents() as $path)
		{
			if ('dir' === $path['type'] && false === $filesystem->deleteDir($path['path']))
			{
				throw new \DomainException('Can not clean the cache.');
			}
		}
	}

	/**
	 * Get the extension path.
	 *
	 * @param   string $extension The extension name, e.g. com_easycreator
	 *
	 * @return string
	 * @throws G11nException
	 */
	public static function getExtensionPath(string $extension) : string
	{
		static $dirs = [];

		if (array_key_exists($extension, $dirs))
		{
			return $dirs[$extension];
		}

		$extensionDir = $extension;

		$parts = self::split($extension);

		if (\count($parts) > 1)
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
	 * @param   string $extension The extension name, e.g. com_easycreator
	 *
	 * @return string
	 * @throws G11nException
	 */
	public static function getExtensionLanguagePath(string $extension) : string
	{
		$path = self::getExtensionPath($extension);

		return $path . '/' . self::$langDirName;
	}

	/**
	 * Check that the extension is valid.
	 *
	 * @param   string $extension The extension name, e.g. com_easycreator
	 * @param   string $domain    The extension scope, e.g. admin
	 *
	 * @return boolean
	 * @throws G11nException
	 */
	public static function isExtension(string $extension, string $domain = '') : bool
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
	public static function getDomainPath(string $domain) : string
	{
		if (array_key_exists($domain, self::$domainPaths))
		{
			return self::$domainPaths[$domain];
		}

		throw new G11nException('Undefined domain: ' . $domain);
	}

	/**
	 * Get registered domain paths.
	 *
	 * @return array
	 */
	public static function getDomainPaths(): array
	{
		return self::$domainPaths;
	}

	/**
	 * Searches the system for language files.
	 *
	 * @param   string $lang      Language
	 * @param   string $extension Extension
	 * @param   string $domain    The extension scope, e.g. admin
	 * @param   string $type      Language file type - e.g. 'ini', 'po' etc.
	 *
	 * @return mixed Full path to file | false if none found
	 * @throws G11nException
	 */
	public static function findLanguageFile(string $lang, string $extension, string $domain = '', string $type = 'po')
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
	public static function split(string $string, string $delimiter = '.') : array
	{
		$parts = explode($delimiter, $string);

		$c = \count($parts);

		if ($c < 1 || $c > 2)
		{
			throw new G11nException('Invalid type - must be xx' . $delimiter . '[xx]: ' . $string);
		}

		return $parts;
	}

	/**
	 * Set the default domain path.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public static function setDomainPath(string $path): void
	{
		self::addDomainPath('default', $path);
	}

	/**
	 * Add a path to search for language files.
	 *
	 * @param   string  $domain  The domain name.
	 * @param   string  $path    A path to search for language files.
	 *
	 * @return void
	 */
	public static function addDomainPath(string $domain, string $path) : void
	{
		self::$domainPaths[$domain] = $path;
	}


	/**
	 * @param string $extension
	 * @param string $domain
	 *
	 * @return array
	 * @throws G11nException
	 */
	public static function getLanguages(string $extension = 'default', string $domain = 'default'): array
	{
		$root = self::getDomainPath($domain);
		$path = self::getExtensionLanguagePath($extension);

		$langs = [];

		foreach (new \DirectoryIterator($root . '/' . $path) as $item)
		{
			if ($item->isDot())
			{
				continue;
			}

			$langs[] = $item->getFilename();
		}

		return $langs;
	}
}
