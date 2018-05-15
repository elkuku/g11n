<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n;

use ElKuKu\G11n\Language\Debugger;
use ElKuKu\G11n\Language\Storage;
use ElKuKu\G11n\Support\ExtensionHelper;

require_once __DIR__ . '/Language/methods.php';

/**
 * The G11n - "Globalization" class.
 *
 * Language handling class.
 * 
 * @since  1.0
 */
abstract class G11n
{
	/**
	 * Language tag - e.g.en-GB, de-DE
	 *
	 * @var string
	 */
	protected static $lang = '';

	/**
	 * Fallback language tag - e.g.en-GB, de-DE
	 *
	 * @var string
	 */
	protected static $defaultLang = 'en-GB';

	/**
	 * Array of defined strings for PHP and their translations
	 *
	 * @var array()
	 */
	protected static $strings = array();

	/**
	 * Array of defined strings for JavaScript and their translations
	 *
	 * @var array()
	 */
	protected static $stringsJs = array();

	/**
	 * Array of defined plural strings for JavaScript and their translations
	 *
	 * @var array()
	 */
	protected static $stringsJsPlural = array();

	/**
	 * Array of defined plural forms for PHP and their translations
	 *
	 * @var array
	 */
	protected static $stringsPlural = array();

	/**
	 * Number of plural forms for a specific language.
	 *
	 * @var integer
	 */
	protected static $pluralForms = 0;

	/**
	 * Cache function that chooses plural forms.
	 *
	 * @var callable
	 */
	protected static $pluralFunction = null;

	/**
	 * Cache function that chooses plural forms (human readable).
	 *
	 * @var string
	 */
	protected static $pluralFunctionRaw = '';

	/**
	 * The pluralization function for Javascript as a string.
	 *
	 * @var string
	 */
	protected static $pluralFunctionJsStr = '';

	/**
	 * The type of the document to be rendered. E.g. html, json, console, etc.
	 * According to the doctype \n will be converted to <br /> - or not.
	 *
	 * @var string
	 */
	protected static $docType = '';

	/**
	 *  For debugging purpose
	 *
	 * @var array()
	 */
	protected static $processedItems = array();

	/**
	 * This is for, well... debugging =;)
	 *
	 * @var boolean
	 */
	protected static $debug = false;

	/**
	 * Array of recorded events.
	 *
	 * @var array
	 */
	protected static $events = array();

	/**
	 * List of loaded extensions.
	 *
	 * @var array
	 */
	protected static $extensionsLoaded = array();

	/**
	 * Provide access to everything we have inside ;).
	 *
	 * Provided for 3pd use or whatever..
	 *
	 * @param   string  $property  Property name
	 *
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	public static function get($property)
	{
		if (isset(self::$$property))
		{
			return self::$$property;
		}

		throw new \UnexpectedValueException('Undefined property ' . __CLASS__ . '::' . $property);
	}

	/**
	 * Load the language.
	 *
	 * @param   string  $extension    E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain       The language domain.
	 * @param   string  $inputType    The input type e.g. "ini" or "po"
	 * @param   string  $storageType  The store type - e.g. 'file_php'
	 *
	 * @throws G11nException
	 * @return void
	 */
	public static function loadLanguage($extension, $domain, $inputType = 'po', $storageType = 'file_php')
	{
		$key = $extension . '.' . $domain;

		if (array_key_exists($key, self::$extensionsLoaded))
		{
			return;
		}

		if (!self::$lang)
		{
			self::detectLanguage();
		}

		if (!self::$docType)
		{
			self::detectDocType();
		}

		$handler = Storage::getHandler($inputType, $storageType);

		$store = $handler->retrieve(self::$lang, $extension, $domain);

		self::$strings       = array_merge(self::$strings, $store->get('strings'));
		self::$stringsPlural = array_merge(self::$stringsPlural, $store->get('stringsPlural'));

		self::setPluralFunction($store->get('pluralForms'));

		self::$stringsJs       = array_merge(self::$stringsJs, $store->get('stringsJs'));
		self::$stringsJsPlural = array_merge(self::$stringsJsPlural, $store->get('stringsJsPlural'));

		if (self::$debug)
		{
			self::logEvent(
				array(
					'Lang'      => self::$lang,
					'Domain'    => $domain,
					'Extension' => $extension,
					'Ext'       => $inputType,
					'Store'     => $storageType,
					'$'         => count($store->get('strings')),
					'$n'        => count($store->get('stringsPlural')),
					'JS-$'      => count($store->get('stringsJs')),
					'JS-$n'     => count($store->get('stringsJsPlural')),
					'File'      => defined('JPATH_ROOT') ? str_replace(JPATH_ROOT, '', $store->get('langPath')) : $store->get('langPath'),
					'Cache'     => defined('JPATH_ROOT') ? str_replace(JPATH_ROOT, '', $store->get('cachePath')) : $store->get('cachePath')
				)
			);
		}

		self::$extensionsLoaded[$key] = 1;
	}

	/**
	 * Get the default language tag.
	 *
	 * @return string
	 */
	public static function getDefault()
	{
		return self::$defaultLang;
	}

	/**
	 * Set the default language tag.
	 *
	 * @param   string  $lang  The language tag.
	 *
	 * @return string
	 */
	public static function setDefault($lang)
	{
		self::$defaultLang = $lang;

		return $lang;
	}

	/**
	 * Get the current language.
	 *
	 * @since  2.0
	 * @return string
	 */
	public static function getCurrent()
	{
		if (!self::$lang)
		{
			self::detectLanguage();
		}

		return self::$lang;
	}

	/**
	 * Set the current language.
	 *
	 * @param   string  $lang  The language tag.
	 *
	 * @since  2.0
	 * @return void
	 */
	public static function setCurrent($lang)
	{
		// @todo check if language "exists"
		self::$lang = $lang;
	}

	/**
	 * Try to translate a string.
	 *
	 * @param   string  $original    The string to translate.
	 * @param   array   $parameters  Replacement parameters.
	 *
	 * @return string Translated string or original if not found.
	 */
	public static function translate($original, array $parameters = [])
	{
		if (self::$debug)
		{
			return self::debugTranslate($original, $parameters);
		}

		$key = md5($original);

		// Translation found
		if (isset(self::$strings[$key]) && self::$strings[$key])
		{
			return self::process(self::$strings[$key], $parameters);
		}

		// No translation found !
		if ('html' == self::$docType)
		{
			$original = str_replace(array("\n", "\\n"), '<br />', $original);
		}

		return $parameters ? strtr($original, $parameters) : $original;
	}

	/**
	 * Translation in debug mode.
	 *
	 * @param   string  $original    Original string to be translated
	 * @param   array   $parameters  Replacement parameters.
	 *
	 * @return string
	 */
	private static function debugTranslate($original, array $parameters)
	{
		$key = md5($original);

		if (isset(self::$strings[$key]) && self::$strings[$key])
		{
			// Translation found
			self::recordTranslated($original, '+');

			return sprintf('+-%s-+', self::process(self::$strings[$key], $parameters));
		}

		// No translation found !

		self::recordTranslated($original, '-');

		if ('html' == self::$docType)
		{
			$original = str_replace(array("\n", "\\n"), '<br />', $original);
		}

		$original = $parameters ? strtr($original, $parameters) : $original;

		return sprintf('¿-%s-¿', $original);
	}

	/**
	 * Try to translate a plural string.
	 *
	 * @param   string   $singular    Singular form
	 * @param   string   $plural      Plural form
	 * @param   integer  $count       How many times..
	 * @param   array    $parameters  Replacement parameters.
	 *
	 * @return string
	 */
	public static function translatePlural($singular, $plural, $count, array $parameters)
	{
		if (!self::$pluralFunction)
		{
			// Set a pluralization
			self::setPluralFunction('X');
		}

		$key = $singular;

		$key = md5($key);

		$index = (int) call_user_func(self::$pluralFunction, $count);

		if (array_key_exists($key, self::$stringsPlural)
			&& array_key_exists($index, self::$stringsPlural[$key]))
		{
			if (self::$debug)
			{
				self::recordTranslated($singular . ' / ' . $plural, '+', 2);

				return sprintf('+-%s-+', self::process(self::$stringsPlural[$key][$index], $parameters));
			}

			return self::process(self::$stringsPlural[$key][$index], $parameters);
		}

		// Fallback - english: singular == 1
		$retVal = ($count == 1) ? $singular : $plural;

		$retVal = $parameters ? strtr($retVal, $parameters) : $retVal;

		if (self::$debug)
		{
			self::recordTranslated($singular . ' / ' . $plural, '-', 2);

			return sprintf('¿-%s-¿', $retVal);
		}

		return $retVal;
	}

	/**
	 * Clean the storage device.
	 *
	 * @param   string       $extension    E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   bool|string  $domain       Set true for administrator
	 * @param   string       $inputType    The input type e.g. "ini" or "po"
	 * @param   string       $storageType  The story type
	 *
	 * @throws G11nException
	 * @return void
	 */
	public static function cleanStorage($extension, $domain = '', $inputType = 'po', $storageType = 'file_php')
	{
		if (!self::$lang)
		{
			self::detectLanguage();
		}

		Storage::getHandler($inputType, $storageType)->clean(self::$lang, $extension, $domain);
	}

	/**
	 * Switch the debugging feature on or off.
	 *
	 * Provided for 3pd use ore whatever..
	 *
	 * @param   boolean  $bool  Set true to turn the debugger on
	 *
	 * @return void
	 */
	public static function setDebug($bool)
	{
		self::$debug = (bool) $bool;
	}

	/**
	 * Debug output translated and untranslated items.
	 *
	 * @param   boolean  $untranslatedOnly  Set true to output only untranslated strings
	 *
	 * @return void
	 */
	public static function debugPrintTranslateds($untranslatedOnly = false)
	{
		Debugger::debugPrintTranslateds($untranslatedOnly);
	}

	/**
	 * Print out recorded events.
	 *
	 * @return void
	 */
	public static function printEvents()
	{
		foreach (self::$events as $e)
		{
			var_dump($e);
		}
	}

	/**
	 * Get recorded events.
	 *
	 * @since  2.0
	 * @return array
	 */
	public static function getEvents()
	{
		return self::$events;
	}

	/**
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param   string  $type  Parser type
	 * @param   string  $name  Parser name
	 *
	 * @throws G11nException
	 * @return \ElKuKu\G11n\Language\Parser\Code|\ElKuKu\G11n\Language\Parser\Language Parser of a specific type.
	 */
	public static function getParser($type, $name)
	{
		$class = '\\ElKuKu\G11n\\Language\\Parser\\' . ucfirst($type) . '\\' . ucfirst($name);

		if (!class_exists($class))
		{
			throw new G11nException('Required class not found: ' . $class);
		}

		return new $class;
	}

	/**
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param   string  $name  Parser type.
	 *
	 * @since  2.0
	 * @throws G11nException
	 * @return \ElKuKu\G11n\Language\Parser\Code
	 */
	public static function getCodeParser($name)
	{
		return self::getParser('code', $name);
	}

	/**
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param   string  $name  Parser type.
	 *
	 * @since  2.0
	 * @throws G11nException
	 * @return \ElKuKu\G11n\Language\Parser\Language
	 */
	public static function getLanguageParser($name)
	{
		return self::getParser('language', $name);
	}

	/**
	 * Add a path to search for language files.
	 *
	 * @param   string  $domain  The domain name.
	 * @param   string  $path    A path to search for language files.
	 *
	 * @deprecated Use ExtensionHelper::addDomainPath($domain, $path)
	 *
	 * @since  2.0
	 * @return void
	 */
	public static function addDomainPath($domain, $path)
	{
		ExtensionHelper::addDomainPath($domain, $path);
	}

	/**
	 * Set the cache directory.
	 *
	 * @param   string  $path  A valid path.
	 *
	 * @deprecated Use ExtensionHelper::setCacheDir($path)
	 *
	 * @since  2.0
	 * @return void
	 */
	public static function setCacheDir($path)
	{
		ExtensionHelper::setCacheDir($path);
	}

	/**
	 * Clean the cache directory.
	 *
	 * @deprecated Use ExtensionHelper::cleanCache()
	 *
	 * @since  2.1
	 * @return void
	 */
	public static function cleanCache()
	{
		ExtensionHelper::cleanCache();
	}

	/**
	 * Set a plural function.
	 *
	 * @param   string  $pcrePluralForm  The PCRE plural form to be parsed.
	 *
	 * @return void
	 */
	protected static function setPluralFunction($pcrePluralForm)
	{
		if (!$pcrePluralForm || ';' == $pcrePluralForm)
		{
			return;
		}

		if (preg_match("/nplurals\s*=\s*(\d+)\s*\;\s*plural\s*=\s*(.*?)\;+/", $pcrePluralForm, $matches))
		{
			$nplurals   = $matches[1];
			$expression = $matches[2];

			$PHPexpression = str_replace('n', '$n', $expression);
		}
		else
		{
			$nplurals      = 2;
			$expression    = 'n == 1 ? 0 : 1';
			$PHPexpression = '$' . $expression;
		}

		$js_func_body = 'plural = (' . $expression . ');'
			. ' return (plural <= ' . $nplurals . ')? plural : plural - 1;';

		self::$pluralForms = $nplurals;

		self::$pluralFunction = function ($n) use ($nplurals, $PHPexpression)
		{
			$plural = 0;
			eval('$plural = ' . $PHPexpression . ';');

			return ($plural <= $nplurals ) ? $plural : $plural - 1;
		};

		self::$pluralFunctionRaw = $expression;

		self::$pluralFunctionJsStr = "phpjs.create_function('n', '" . $js_func_body . "')";
	}

	/**
	 * Get the JavaScript declaration.
	 *
	 * @return  string
	 *
	 * @since  2.1
	 */
	public static function getJavaScript()
	{
		$js   = array();

		$js[] = '<!--';
		$js[] = '/* JavaScript translations */';
		$js[] = 'g11n.debug = \'' . self::$debug . '\'';
		$js[] = 'g11n.loadLanguageStrings(' . json_encode(self::$stringsJs) . ');';

		if (self::$pluralFunctionJsStr)
		{
			$js[] = 'g11n.loadPluralStrings(' . json_encode(self::$stringsJsPlural) . ');';
			$js[] = 'g11n.setPluralFunction(' . self::$pluralFunctionJsStr . ')';
		}

		$js[] = '-->';

		return implode("\n", $js);
	}

	/**
	 * Processes the final translation. Decoding and converting \n to <br /> if necessary.
	 *
	 * @param   string  $string      The string to process
	 * @param   array   $parameters  Replacement parameters.
	 *
	 * @return string
	 */
	private static function process($string, array $parameters)
	{
		$string = base64_decode($string);

		if ('html' == self::$docType)
		{
			$string = str_replace(array("\n", '\n'), '<br />', $string);
		}

		if ($parameters)
		{
			$string = strtr($string, $parameters);
		}

		return $string;
	}

	/**
	 * Try to detect the current language.
	 *
	 * @return void
	 */
	private static function detectLanguage()
	{
		// Get the environment language
		$envLang = getenv('LANG');

		$envLang = ('POSIX' != $envLang) ? $envLang : '';

		if ($envLang)
		{
			$envLang = str_replace('_', '-', $envLang);

			if (strpos($envLang, '.'))
			{
				$envLang = substr($envLang, 0, strpos($envLang, '.'));
			}

			// Map with fallback languages.
			$map = array(
				'en-US' => 'en-GB'
			);

			if (array_key_exists($envLang, $map))
			{
				$envLang = $map[$envLang];
			}

			self::$lang = $envLang;

			return;
		}

		// Nothing found. Fall back to the default language.
		self::$lang = self::$lang ?: self::$defaultLang;
	}

	/**
	 * Try to detect the current document type.
	 *
	 * This is done with a little help .. from JFactory::getLanguage()
	 *
	 * @throws G11nException
	 * @return void
	 */
	private static function detectDocType()
	{
		// @todo hard set to HTML

		// JFactory::getDocument()->getType();
		self::$docType = 'html';

		if (!self::$docType)
		{
			throw new G11nException('Unable to detect the document type :(');
		}
	}

	/**
	 * Record translated and untranslated strings.
	 *
	 * @param   string   $string  The string to record
	 * @param   string   $mode    Parsing mode strict/legacy
	 * @param   integer  $level   The level where the function has been called (A GUESS !)
	 *
	 * @return void
	 */
	private static function recordTranslated($string, $mode, $level = 3)
	{
		// Already recorded
		if (array_key_exists($string, self::$processedItems))
		{
			return;
		}

		$info           = new \stdClass;
		$info->status   = $mode;
		$info->file     = '';
		$info->line     = 0;
		$info->function = '';
		$info->args     = array();
		$info->trace    = null;

		if (function_exists('debug_backtrace'))
		{
			$trace = debug_backtrace();

			// Also store the whole trace for further investigation =;)
			$info->trace = $trace;

			// Element no. 3 must be our jgettext() caller - s/be/not be...
			$trace = $trace[$level];

			$info->file     = $trace['file'];
			$info->line     = $trace['line'];
			$info->function = $trace['function'];
			$info->args     = $trace['args'];
		}

		self::$processedItems[$string] = $info;
	}

	/**
	 * Logs events.
	 *
	 * Accepts multiple arguments
	 *
	 * @param   array  $event  The event.
	 *
	 * @return void
	 */
	private static function logEvent(array $event)
	{
		$e = new \stdClass;

		foreach ($event as $k => $v)
		{
			$e->$k = $v;
		}

		self::$events[] = $e;
	}
}
