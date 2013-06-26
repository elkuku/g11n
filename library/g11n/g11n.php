<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n;

use g11n\Language\Debugger;
use g11n\Language\Storage;
use g11n\Support\ExtensionHelper;

require_once __DIR__ . '/Language/methods.php';

/**
 * The g11n - "globalization" class.
 *
 * Language handling class.
 */
abstract class g11n
{
	/** Language tag - e.g.en-GB, de-DE
	 *
	 * @var string
	 */
	protected static $lang = '';

	/** Fallback language tag - e.g.en-GB, de-DE
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
	 * Array of defined plural forms for PHP and their translations
	 *
	 * @var array
	 */
	protected static $stringsPlural = array();

	/**
	 * Plural form for a specific language.
	 *
	 * @var string
	 */
	protected static $pluralForms = '';

	/**
	 * Cache function that chooses plural forms.
	 *
	 * @var callable
	 */
	protected static $pluralFunction = null;

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

	/** This is for, well... debugging =;)
	 *
	 * @var boolean
	 */
	protected static $debug = false;

	protected static $events = array();

	protected static $extensionsLoaded = array();

	/**
	 * Provide access to everything we have inside ;).
	 *
	 * Provided for 3pd use or whatever..
	 *
	 * @param   string $property  Property name
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
	 * @param string $extension    E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param string $domain       The language domain.
	 * @param string $inputType    The input type e.g. "ini" or "po"
	 * @param string $storageType  The store type - e.g. 'file_php'
	 *
	 * @throws g11nException
	 * @return void
	 */
	public static function loadLanguage($extension, $domain, $inputType = 'po', $storageType = 'file_php')
	{
		$key = $extension . '.' . $domain;

		if (array_key_exists($key, self::$extensionsLoaded))
			return;

		if (!self::$lang)
			self::detectLanguage();

		if (!self::$docType)
			self::detectDocType();

		$handler = Storage::getHandler($inputType, $storageType);

		$store = $handler->retrieve(self::$lang, $extension, $domain);

		self::$strings = array_merge(self::$strings, $store->get('strings'));

		self::$stringsPlural = $store->get('stringsPlural');

		self::setPluralFunction($store->get('pluralForms'));

		self::addJavaScript($store->get('stringsJs'), $store->get('stringsJsPlural'));

		$dbgMsg = '';

		if (self::$debug)
		{
			$dbgMsg = sprintf(
				'Found %d strings',
				count($store->get('strings'))
			);
		}

		self::logEvent(__METHOD__, $extension, $domain, $inputType, $storageType, $dbgMsg, self::$lang);

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
	 * @param string $lang The language tag.
	 *
	 * @return string
	 */
	public static function setDefault($lang)
	{
		self::$defaultLang = $lang;
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
			self::detectLanguage();

		return self::$lang;
	}

	/**
	 * Set the current language.
	 *
	 * @param string $lang The language tag.
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
	 * @param string $original The string to translate.
	 *
	 * @return string Translated string or original if not found.
	 */
	public static function translate($original)
	{
		if (self::$debug)
			return self::debugTranslate($original);

		$key = md5($original);

		// Translation found
		if (isset(self::$strings[$key]) && self::$strings[$key])
			return self::process(self::$strings[$key]);

		// No translation found !
		if ('html' == self::$docType)
			$original = str_replace(array("\n", "\\n"), '<br />', $original);

		return $original;
	}

	/**
	 * Translation in debug mode.
	 *
	 * @param string $original Original string to be translated
	 *
	 * @return string
	 */
	private static function debugTranslate($original)
	{
		$key = md5($original);

		if (isset(self::$strings[$key]) && self::$strings[$key])
		{
			// Translation found
			self::recordTranslated($original, '+');

			return sprintf('+-%s-+', self::process(self::$strings[$key]));
		}

		// No translation found !

		self::recordTranslated($original, '-');

		return sprintf('¿-%s-¿', str_replace(array("\n", "\\n"), '<br />', $original));
	}

	/**
	 * Try to translate a plural string.
	 *
	 * @param string  $singular Singular form
	 * @param string  $plural   Plural form
	 * @param integer $count    How many times..
	 *
	 * @return string
	 */
	public static function translatePlural($singular, $plural, $count)
	{
		$key = $singular;

		$key = md5($key);

		$index = (int) call_user_func(self::$pluralFunction, $count);

		if (array_key_exists($key, self::$stringsPlural)
			&& array_key_exists($index, self::$stringsPlural[$key]))
		{
			if (self::$debug)
			{
				self::recordTranslated($singular . ' / ' . $plural, '+', 2);

				return sprintf('+-%s-+', self::process(self::$stringsPlural[$key][$index]));
			}

			return self::process(self::$stringsPlural[$key][$index]);
		}

		// Fallback - english: singular == 1
		$retVal = ($count == 1) ? $singular : $plural;

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
	 * @param string      $extension   E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param bool|string $domain      Set true for administrator
	 * @param string      $inputType   The input type e.g. "ini" or "po"
	 * @param string      $storageType The story type
	 *
	 * @throws g11nException
	 * @return void
	 */
	public static function cleanStorage($extension, $domain = '', $inputType = 'po', $storageType = 'file_php')
	{
		if (!self::$lang)
			self::detectLanguage();

		Storage::getHandler($inputType, $storageType)->clean(self::$lang, $extension, $domain);
	}

	/**
	 * Switch the debugging feature on or off.
	 *
	 * Provided for 3pd use ore whatever..
	 *
	 * @param boolean $bool Set true to turn the debugger on
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
	 * @param boolean $untranslatedOnly Set true to output only untranslated strings
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
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param string $type Parser type
	 * @param string $name Parser name
	 *
	 * @deprecated Use getCodeParser() or getLanguageParser()
	 *
	 * @throws g11nException
	 * @return Parser of a specific type
	 */
	public static function getParser($type, $name)
	{
		$class = '\\g11n\\Language\\Parser\\' . ucfirst($type) . '\\' . ucfirst($name);

		if (!class_exists($class))
			throw new g11nException('Required class not found: ' . $class);

		return new $class;
	}

	/**
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param string $type Parser type
	 *
	 * @throws g11nException
	 * @return \g11n\Language\Parser\Code of a specific type
	 */
	public static function getCodeParser($type)
	{
		$class = '\\g11n\\Language\\Parser\\Code\\' . ucfirst($type);

		if (!class_exists($class))
			throw new g11nException('Required class not found: ' . $class);

		return new $class;
	}

	/**
	 * For 3PD use.
	 *
	 * You may use this function for manipulation of language files.
	 * Parsers support parsing and generating language files.
	 *
	 * @param string $type Parser type
	 *
	 * @throws g11nException
	 * @return \g11n\Language\Parser\Language of a specific type
	 */
	public static function getLanguageParser($type)
	{
		$class = '\\g11n\\Language\\Parser\\Language\\' . ucfirst($type);

		if (!class_exists($class))
			throw new g11nException('Required class not found: ' . $class);

		return new $class;
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
		ExtensionHelper::addDomainPath($domain, $path);
	}

	/**
	 * Set the cache directory.
	 *
	 * @param   string  $path  A valid path.
	 *
	 * @throws \RuntimeException
	 * @return void
	 */
	public static function setCacheDir($path)
	{
		ExtensionHelper::setCacheDir($path);
	}

	/**
	 * Set a plural function.
	 *
	 * @param string $pcrePluralForm The PCRE plural form to be parsed.
	 *
	 * @return void
	 */
	protected static function setPluralFunction($pcrePluralForm)
	{
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

		$func_body = '$plural = (' . $PHPexpression . ');'
			. ' return ($plural <= ' . $nplurals . ')? $plural : $plural - 1;';

		$js_func_body = 'plural = (' . $expression . ');'
			. ' return (plural <= ' . $nplurals . ')? plural : plural - 1;';

		self::$pluralFunction = create_function('$n', $func_body);

		self::$pluralFunctionJsStr = "phpjs.create_function('n', '" . $js_func_body . "')";
	}

	/**
	 * Add the strings designated to JavaScript to the page <head> section.
	 *
	 * @param array $strings       These strings will be added to the HTML source of your page
	 * @param array $stringsPlural The plural strings
	 *
	 * @return void
	 */
	protected static function addJavaScript($strings, $stringsPlural)
	{
		// @todo disabled.

		return;

		static $hasBeenAdded = false;

		// To be called only once
		if (!$hasBeenAdded)
		{
			$path     = 'libraries/g11n/language/javascript';
			$document = null;//self::getApplication()->getDocument();

			$document->addScript(JURI::root(true) . '/' . $path . '/methods.js');
			$document->addScript(JURI::root(true) . '/' . $path . '/language.js');
			$document->addScript(JURI::root(true) . '/' . $path . '/phpjs.js');

			$document->addScriptDeclaration("g11n.debug = '" . self::$debug . "'\n");

			$hasBeenAdded = true;
		}

		// Add the strings to the page <head> section
		$js   = array();
		$js[] = '<!--';
		$js[] = '/* JavaScript translations */';
		$js[] = 'g11n.loadLanguageStrings(' . json_encode($strings) . ');';

		if (self::$pluralFunctionJsStr)
		{
			$js[] = 'g11n.loadPluralStrings(' . json_encode($stringsPlural) . ');';

			if (!$hasBeenAdded)
				$js[] = 'g11n.setPluralFunction(' . self::$pluralFunctionJsStr . ')';
		}

		$js[] = '-->';

		self::$stringsJs = array_merge(self::$stringsJs, $strings);

		// @ self::getApplication()->getDocument()->addScriptDeclaration(implode("\n", $js));
	}

	/**
	 * Processes the final translation. Decoding and converting \n to <br /> if necessary.
	 *
	 * @param string $string The string to process
	 *
	 * @return string
	 */
	private static function process($string)
	{
		$string = base64_decode($string);

		if ('html' == self::$docType)
		{
			$string = str_replace(array("\n", '\n'), '<br />', $string);
		}

		return $string;
	}

	/**
	 * Try to detect the current language.
	 *
	 * @throws g11nException
	 * @return void
	 */
	private static function detectLanguage()
	{
/*		self::$lang = self::getApplication()->input->get('lang');

		if (self::$lang)
		{
			// @todo CHECK if language exists..

			self::getApplication()->getSession()->set('lang', self::$lang);

			return;
		}

		// Get the language from session
		self::$lang = self::getApplication()->getSession()->get('lang');

		if (self::$lang)
		{
			return;
		}*/

		// Get the environment language
		$envLang = getenv('LANG');

		$envLang = ('POSIX' != $envLang) ? $envLang : '';

		if ($envLang)
		{
			$envLang = str_replace('_', '-', $envLang);

			if (strpos($envLang, '.'))
				$envLang = substr($envLang, 0, strpos($envLang, '.'));

			// Map with fallback languages.
			$map = array(
				'en-US' => 'en-GB'
			);

			if (array_key_exists($envLang, $map))
				$envLang = $map[$envLang];

			self::$lang = $envLang;

			return;
		}

		// Nothing found. Fall back to the default language.
		if (!self::$lang)
			self::$lang = self::$defaultLang;
	}

	/**
	 * Try to detect the current document type.
	 *
	 * This is done with a little help .. from JFactory::getLanguage()
	 *
	 * @throws g11nException
	 * @return void
	 */
	private static function detectDocType()
	{
		// @todo hard set to HTML

		// JFactory::getDocument()->getType();
		self::$docType = 'html';

		if (!self::$docType)
			throw new g11nException('Unable to detect the document type :(');
	}

	/**
	 * Record translated and untranslated strings.
	 *
	 * @param string $string The string to record
	 * @param string $mode   Parsing mode strict/legacy
	 * @param int    $level  The level where the function has been called (A GUESS !)
	 *
	 * @return void
	 */
	private static function recordTranslated($string, $mode, $level = 3)
	{
		// Already recorded
		if (array_key_exists($string, self::$processedItems))
			return;

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
	 * @return void
	 */
	private static function logEvent()
	{
		$args = func_get_args();

		$e = new \stdClass;

		foreach ($args as $k => $v)
		{
			$e->$k = $v;
		}

		self::$events[] = $e;
	}
}
