<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language;

use g11n\g11nException;
use g11n\Support\ExtensionHelper;

/**
 * The g11n storage base class.
 *
 * @package g11n
 */
class Storage
{
	protected static $handler = '';

	protected static $cacheDir = 'cache/g11n';

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		self::$cacheDir = 'cache/' . ExtensionHelper::langDirName;
	}

	/**
	 * Get a storage handler.
	 *
	 * @param string $inputType   A valid input type
	 * @param string $storageType A valid storage type
	 *
	 * @throws g11nException
	 * @return Storage
	 */
	public static function getHandler($inputType, $storageType)
	{
		//$fileName = __DIR__ . '/storages/' . $storageType . '.php';

		$parts = explode('_', $storageType);

		if (count($parts) != 2)
		{
			throw new \RuntimeException('Storage type must be in format [type][_subtype]');
		}

		$className = '\\g11n\\Language\\Storage\\' . ucfirst($parts[0]) . '\\' . ucfirst($parts[1]);

		if (false == class_exists($className))
		{
			throw new \RuntimeException('Invalid storage class: ' . $className);
		}
//		if (!file_exists($fileName))
//			throw new g11nException('Can not get the storage handler ' . $storageType . ' - ' . $fileName);

//		require_once $fileName;

/*		$parts       = ExtensionHelper::split($storageType, '_');
		$storageName = 'g11nStorage' . ucfirst($parts[0]) . ucfirst($parts[1]);

		if (!class_exists($storageName))
			throw new g11nException('Required class not found: ' . $storageName);*/

		return new $className($inputType);
		//return new $storageName($inputType);
	}

	/**
	 * Get the cache directory.
	 *
	 * @static
	 * @return string
	 */
	public static function getCacheDir()
	{
		return self::$cacheDir;
	}

	/**
	 * Get the path of a storage file.
	 *
	 * @param string $lang      Language tag e.g. en-GB.
	 * @param string $extension Extension name e.g. com_component.
	 * @param string $scope     Must be 'admin' or 'site' / blank to use actual.
	 *
	 * @return string
	 */
	protected function getPath($lang, $extension, $domain = '')
	{
/*		if (empty($scope))
		{
			$path = (JFactory::getApplication()->isAdmin())
				? JPATH_ADMINISTRATOR : JPATH_SITE;
		}
		else //
		{
			$path = ('admin' == $scope) ? JPATH_ADMINISTRATOR : JPATH_SITE;
		}
*/

		// @todo TEMP
		$path = __DIR__;

		$parts = ExtensionHelper::split($extension, '.');

		$dirName = $extension;

		if (count($parts) != 1)
		{
			$dirName = $parts[0];
		}

		$path .= '/' . self::$cacheDir . '/' . $dirName;
		$path .= '/' . $lang . '.' . $extension;

		return $path;
	}

	/**
	 * Test is a language template exists.
	 *
	 * @static
	 *
	 * @param $extension
	 * @param $scope
	 *
	 * @return bool
	 */
	public static function templateExists($extension, $scope)
	{
		return (file_exists(self::getTemplatePath($extension, $scope))) ? true : false;
	}

	/**
	 * Get the language template path.
	 *
	 * @static
	 *
	 * @param $extension
	 * @param $scope
	 *
	 * @return string
	 */
	public static function getTemplatePath($extension, $scope)
	{
		static $templates = array();

		if (array_key_exists($extension, $templates)
			&& array_key_exists($scope, $templates[$extension])
		)
			return $templates[$extension][$scope];

		$base = ExtensionHelper::getScopePath($scope);

		$parts = ExtensionHelper::split($extension);

		$subType = '';

		if (count($parts) == 1)
		{
			$parts  = ExtensionHelper::split($extension, '_');
			$prefix = $parts[0];
		}
		else //
		{
			//-- We have a subType
			$subType = $parts[1];

			$parts  = ExtensionHelper::split($parts[0], '_');
			$prefix = $parts[0];
		}

		$fileName = $extension . '.pot';

		$extensionDir = ExtensionHelper::getExtensionPath($extension);

		return "$base/$extensionDir/"
		. ExtensionHelper::$langDirName . "/templates/$fileName";
	}

	/**
	 * Translate a gettext PluralForms string to pcre.
	 *
	 * E.g.: nplurals=2; plural=(n != 1)
	 *
	 * @param string $gettextPluralForms Gettext format
	 *
	 * @return string pcre type PluralForms
	 */
	protected static function translatePluralForms($gettextPluralForms)
	{
		$expr = $gettextPluralForms . ';';
		$res  = '';
		$p    = 0;

		for ($i = 0; $i < strlen($expr); $i++)
		{
			$ch = $expr[$i];

			switch ($ch)
			{
				case '?':
					$res .= ' ? (';
					$p++;
					break;
				case ':':
					$res .= ') : (';
					break;
				case ';':
					$res .= str_repeat(')', $p) . ';';
					$p = 0;
					break;
				default:
					$res .= $ch;
			}
		}

		return $res;
	}
}
