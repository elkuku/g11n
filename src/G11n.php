<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n;

use ElKuKu\G11n\Language\Parser\Code;
use ElKuKu\G11n\Language\Parser\Language;
use ElKuKu\G11n\Language\Storage;

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
	 * @var array
	 */
	protected static $strings = [];

	/**
	 * Array of defined strings for JavaScript and their translations
	 *
	 * @var array
	 */
	protected static $stringsJs = [];

	/**
	 * Array of defined plural strings for JavaScript and their translations
	 *
	 * @var array
	 */
	protected static $stringsJsPlural = [];

	/**
	 * Array of defined plural forms for PHP and their translations
	 *
	 * @var array
	 */
	protected static $stringsPlural = [];

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
	protected static $pluralFunction;

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
	protected static $events = [];

	/**
	 * List of loaded extensions.
	 *
	 * @var array
	 */
	protected static $extensionsLoaded = [];

	/**
	 *  For debugging purpose
	 *
	 * @var array
	 */
	private static $processedItems = [];

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
	public static function get(string $property)
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
	public static function loadLanguage(string $extension, string $domain, string $inputType = 'po', string $storageType = 'file_php') : void
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

		$handler = Storage::getHandler($inputType, $storageType);

		$store = $handler->retrieve(self::$lang, $extension, $domain);

		self::$strings       = array_merge(self::$strings, $store->getStrings());
		self::$stringsPlural = array_merge(self::$stringsPlural, $store->getStringsPlural());

		self::setPluralFunction($store->getPluralForms());

		self::$stringsJs       = array_merge(self::$stringsJs, $store->getStringsJs());
		self::$stringsJsPlural = array_merge(self::$stringsJsPlural, $store->getStringsJsPlural());

		if (self::$debug)
		{
			self::logEvent(
				array(
					'Lang'      => self::$lang,
					'Domain'    => $domain,
					'Extension' => $extension,
					'Ext'       => $inputType,
					'Store'     => $storageType,
					'$'         => \count($store->getStrings()),
					'$n'        => \count($store->getStringsPlural()),
					'JS-$'      => \count($store->getStringsJs()),
					'JS-$n'     => \count($store->getStringsJsPlural()),
					'File'      => $store->getLangPath(),
					'Cache'     => $store->getCachePath()
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
	public static function getDefault() : string
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
	public static function setDefault(string $lang) : string
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
	public static function getCurrent() : string
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
	 * @return string
	 */
	public static function setCurrent(string $lang) : string
	{
		// @todo check if language "exists"
		self::$lang = $lang;

		return $lang;
	}

	/**
	 * Try to translate a string.
	 *
	 * @param   string  $original    The string to translate.
	 * @param   array   $parameters  Replacement parameters.
	 *
	 * @return string Translated string or original if not found.
	 */
	public static function translate(string $original, array $parameters = []) : string
	{
		if (self::$debug)
		{
			return self::debugTranslate($original, $parameters);
		}

		$key = md5($original);

		if (isset(self::$strings[$key]) && self::$strings[$key])
		{
			return self::process(self::$strings[$key], $parameters);
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
	private static function debugTranslate(string $original, array $parameters) : string
	{
		$key = md5($original);

		if (isset(self::$strings[$key]) && self::$strings[$key])
		{
			self::recordTranslated($original, '+');

			return sprintf('+-%s-+', self::process(self::$strings[$key], $parameters));
		}

		self::recordTranslated($original, '-');

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
	public static function translatePlural(string $singular, string $plural, int $count, array $parameters) : string
	{
		if (!self::$pluralFunction)
		{
			// Set a pluralization
			self::setPluralFunction('X');
		}

		$key = $singular;

		$key = md5($key);

		$index = (int) \call_user_func(self::$pluralFunction, $count);

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
		$retVal = ($count === 1) ? $singular : $plural;

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
	public static function cleanStorage(string $extension, $domain = '', string $inputType = 'po', string $storageType = 'file_php') : void
	{
		if (!self::$lang)
		{
			self::detectLanguage();
		}

		Storage::getHandler($inputType, $storageType)
			->clean(self::$lang, $extension, $domain);
	}

	/**
	 * Switch the debugging feature on or off.
	 *
	 * Provided for 3pd use ore whatever..
	 *
	 * @param   boolean $debug Set true to turn the debugger on
	 *
	 * @return void
	 */
	public static function setDebug(bool $debug) : void
	{
		self::$debug = $debug;
	}

	/**
	 * Get recorded events.
	 *
	 * @since  2.0
	 * @return array
	 */
	public static function getEvents() : array
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
	public static function getParser(string $type, string $name)
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
	 * @param   string $type Parser type.
	 *
	 * @since  2.0
	 * @throws G11nException
	 * @return Code
	 */
	public static function getCodeParser(string $type) : Code
	{
		return self::getParser('code', $type);
	}

	/**
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param   string $type Parser type.
	 *
	 * @since  2.0
	 * @throws G11nException
	 * @return Language
	 */
	public static function getLanguageParser(string $type) : Language
	{
		return self::getParser('language', $type);
	}

	/**
	 * Set a plural function.
	 *
	 * @param   string  $pcrePluralForm  The PCRE plural form to be parsed.
	 *
	 * @return void
	 */
	protected static function setPluralFunction(string $pcrePluralForm) : void
	{
		if (!$pcrePluralForm || ';' === $pcrePluralForm)
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

		$jsFuncBody = 'plural = (' . $expression . ');'
			. ' return (plural <= ' . $nplurals . ')? plural : plural - 1;';

		self::$pluralForms = $nplurals;

		self::$pluralFunction = function ($n) use ($nplurals, $PHPexpression)
		{
			// This is a foo line...
			$plural = $n;

			// Note: eval is evil...
			eval('$plural = ' . $PHPexpression . ';');

			return ($plural <= $nplurals ) ? $plural : $plural - 1;
		};

		self::$pluralFunctionRaw = $expression;

		self::$pluralFunctionJsStr = "phpjs.create_function('n', '" . $jsFuncBody . "')";
	}

	/**
	 * Get the JavaScript declaration.
	 *
	 * @return  string
	 *
	 * @since  2.1
	 */
	public static function getJavaScript() : string
	{
		$js   = [];

		$js[] = '/* JavaScript translations */';
		$js[] = 'g11n.debug = \'' . self::$debug . '\';';
		$js[] = 'g11n.loadLanguageStrings(' . json_encode(self::$stringsJs) . ');';

		if (self::$pluralFunctionJsStr)
		{
			$js[] = 'g11n.loadPluralStrings(' . json_encode(self::$stringsJsPlural) . ');';
			$js[] = 'g11n.setPluralFunction(' . self::$pluralFunctionJsStr . ');';
		}

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
	private static function process(string $string, array $parameters) : string
	{
		$string = base64_decode($string);

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
	private static function detectLanguage() : void
	{
		// Get the environment language
		$envLang = getenv('LANG');

		$envLang = ('POSIX' !== $envLang) ? $envLang : '';

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
	 * Record translated and untranslated strings.
	 *
	 * @param   string   $string  The string to record
	 * @param   string   $mode    Parsing mode strict/legacy
	 * @param   integer  $level   The level where the function has been called (A GUESS !)
	 *
	 * @return void
	 */
	private static function recordTranslated(string $string, string $mode, int $level = 3) : void
	{
		$info           = new \stdClass;
		$info->string   = $string;
		$info->status   = $mode;
		$info->file     = '';
		$info->line     = 0;
		$info->function = '';
		$info->args     = [];
		$info->trace    = null;

		if (\function_exists('debug_backtrace'))
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

		self::$processedItems[] = $info;
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
	private static function logEvent(array $event) : void
	{
		$e = new \stdClass;

		foreach ($event as $k => $v)
		{
			$e->$k = $v;
		}

		self::$events[] = $e;
	}
}
