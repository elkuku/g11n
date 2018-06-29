<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language;

use ElKuKu\G11n\G11nException;
use ElKuKu\G11n\Support\ExtensionHelper;
use ElKuKu\G11n\Language\Parser;
use ElKuKu\G11n\Support\Store;

/**
 * The G11n storage base class.
 *
 * @since  1
 */
abstract class Storage
{
	/**
	 * @var string
	 */
	protected static $handler = '';

	/**
	 * @var Parser\Language
	 */
	protected $parser;

	/**
	 * Constructor.
	 *
	 * @param   string  $type  The input type (e.g. ini, po)
	 *
	 * @throws G11nException
	 */
	protected function __construct(string $type)
	{
		$class = '\\ElKuKu\\G11n\\Language\\Parser\\Language\\' . ucfirst($type);

		if (!class_exists($class))
		{
			throw new G11nException('Required parser class not found: ' . $class);
		}

		$this->parser = new $class;
	}

	/**
	 * Get a storage handler.
	 *
	 * @param   string  $inputType    A valid input type
	 * @param   string  $storageType  A valid storage type
	 *
	 * @throws \RuntimeException
	 *
	 * @return Storage
	 */
	public static function getHandler(string $inputType, string $storageType) : Storage
	{
		$parts = explode('_', $storageType);

		if (\count($parts) !== 2)
		{
			throw new \RuntimeException('Storage type must be in format [type][_subtype]');
		}

		$className = '\\ElKuKu\\G11n\\Language\\Storage\\' . ucfirst($parts[0]) . '\\' . ucfirst($parts[1]);

		if (false === class_exists($className))
		{
			throw new \RuntimeException('Invalid storage class: ' . $className);
		}

		return new $className($inputType);
	}

	/**
	 * Get the path of a storage file.
	 *
	 * @param   string  $lang       Language tag e.g. en-GB.
	 * @param   string  $extension  Extension name e.g. com_component.
	 * @param   string  $domain     The domain name.
	 *
	 * @throws G11nException
	 *
	 * @return string
	 */
	protected function getPath(string $lang, string $extension, string $domain) : string
	{
		$parts = ExtensionHelper::split($extension, '.');

		$dirName = (1 === \count($parts))
			? $extension
			: $parts[0];

		return ExtensionHelper::getCacheDir() . '/' . $domain . '/' . $dirName . '/' . $lang . '.' . $extension;
	}

	/**
	 * Test is a language template exists.
	 *
	 * @param   string  $extension  The extension name.
	 * @param   string  $scope      The scope name.
	 *
	 * @throws G11nException
	 *
	 * @return boolean
	 */
	public static function templateExists(string $extension, string $scope) : bool
	{
		return file_exists(self::getTemplatePath($extension, $scope)) ? true : false;
	}

	/**
	 * Get the language template path.
	 *
	 * @param   string  $extension  Extension name.
	 * @param   string  $scope      Extension scope
	 *
	 * @throws G11nException
	 *
	 * @return string
	 */
	public static function getTemplatePath(string $extension, string $scope) : string
	{
		static $templates = [];

		if (array_key_exists($extension, $templates)
			&& array_key_exists($scope, $templates[$extension]))
		{
			return $templates[$extension][$scope];
		}

		$base = ExtensionHelper::getDomainPath($scope);

		$fileName = $extension . '.pot';

		$extensionDir = ExtensionHelper::getExtensionPath($extension);

		return "$base/$extensionDir/" . ExtensionHelper::$langDirName . "/templates/$fileName";
	}

	/**
	 * Translate a gettext PluralForms string to pcre.
	 *
	 * E.g.: nplurals=2; plural=(n != 1)
	 *
	 * @param   string  $gettextPluralForms  Gettext format.
	 *
	 * @return string pcre type PluralForms
	 */
	protected static function translatePluralForms(string $gettextPluralForms) : string
	{
		$expr = $gettextPluralForms . ';';
		$exprLen = \strlen($expr);
		$res  = '';
		$p    = 0;

		for ($i = 0; $i < $exprLen; $i++)
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

	/**
	 * Stores the strings into a storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 *
	 * @return string  The path where the language file has been found / empty if it is read from cache.
	 */
	abstract public function store(string $lang, string $extension, string $domain = '') : string;

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
	abstract public function retrieve(string $lang, string $extension, string $domain = '') : Store;

	/**
	 * Cleans the storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 *
	 * @return void
	 */
	abstract public function clean(string $lang, string $extension, string $domain = '') : void;
}
