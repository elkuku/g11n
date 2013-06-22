<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Support;

use g11n\g11nException;

/**
 * Extension helper class.
 *
 * @package g11n
 */
class ExtensionHelper
{
	/**
	 * Known domain paths.
	 * @var array
	 */
	protected static $domainPaths = array();

	public static $langDirName = 'g11n';

	/**
	 * Set a custom directory name for language files.
	 *
	 * @static
	 *
	 * @param $name
	 */
	public static function setDirName($name)
	{
		self::$langDirName = $name;
	}

	/**
	 * Get the extension path.
	 *
	 * @static
	 *
	 * @param string $extension The extension name, e.g. com_easycreator
	 *
	 * @return string
	 * @throws g11nException
	 */
	public static function getExtensionPath($extension)
	{
		static $dirs = array();

		if (array_key_exists($extension, $dirs))
			return $dirs[$extension];

		/*        if('joomla' == $extension)
				return;
		*/
		$extensionDir = $extension;

		$parts = self::split($extension);

		if (count($parts) > 1)
		{
			//-- We have a subType

			$extensionDir = $parts[0];
		}

		$dirs[$extension] = $extensionDir;

		return $extensionDir;
	}

	/**
	 * Get the extensions language path.
	 *
	 * @static
	 *
	 * @param string $extension The extension name, e.g. com_easycreator
	 *
	 * @return string
	 */
	public static function getExtensionLanguagePath($extension)
	{
		$path = self::getExtensionPath($extension);

		return $path . '/' . self::$langDirName;
	}

	/**
	 * @static
	 *
	 * @param string $extension The extension name, e.g. com_easycreator
	 * @param string $domain     The extension scope, e.g. admin
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
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	public static function getDomainPath($domain)
	{
		if (array_key_exists($domain, self::$domainPaths))
		{
			return self::$domainPaths[$domain];
		}

		throw new \UnexpectedValueException('Undefined domain: ' . $domain);
	}

	/**
	 * Searches the system for language files.
	 *
	 * @param string $lang       Language
	 * @param string $extension  Extension
	 * @param string $domain     The extension scope, e.g. admin
	 * @param string $type       Language file type - e.g. 'ini', 'po' etc.
	 *
	 * @return mixed Full path to file | false if none found
	 *
	 */
	public static function findLanguageFile($lang, $extension, $domain = '', $type = 'po')
	{
		$base = ExtensionHelper::getDomainPath($domain);

		$fileName = $lang . '.' . $extension . '.' . $type;

		$extensionDir = self::getExtensionPath($extension);

		$extensionLangDir = self::getExtensionLanguagePath($extension);

		//-- First try our special dir
		$path = "$base/$extensionLangDir/$lang/$fileName";

		if (file_exists($path))
			return $path;

		//-- Next try extension/language directory
		$path = "$base/$extensionDir/language/$lang/$fileName";

		if (file_exists($path))
			return $path;

		//-- Now try the base language dir
		$path = "$base/language/$lang/$fileName";

		if (file_exists($path))
			return $path;

		//-- Found nothing :(

		//throw new Exception('No language files found');//@Do_NOT_Translate
		return false;
	}

	/**
	 * Splits a string by a separator.
	 *
	 * Expects exactly two parts. Otherwise it will fail.
	 *
	 * @param string $string    The string to split
	 * @param string $delimiter The delimiter character
	 *
	 * @throws g11nException
	 * @return array
	 *
	 */
	public static function split($string, $delimiter = '.')
	{
		$parts = explode($delimiter, $string);

		if (count($parts) < 1
			|| count($parts) > 2
		)
			throw new g11nException('Invalid type - must be xx' . $delimiter . '[xx]: ' . $string);

		return $parts;
	}

	/**
	 * @param $domain
	 * @param $path
	 */
	public static function setDomainPath($domain, $path)
	{
		self::$domainPaths[$domain] = $path;
	}
}
