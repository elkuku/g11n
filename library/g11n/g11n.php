<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n;

use g11n\Language\Storage;

require_once __DIR__ . '/Language/methods.php';

/**
 * The g11n - "globalization" class.
 *
 * Language handling class.
 *
 *  //-- Joomla!'s Alternative Language Handler oO
 *
 * @package g11n
 */
abstract class g11n
{
	/** Language tag - e.g.en-GB, de-DE
	 *
	 * @var string
	 */
	protected static $lang = '';

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
	 * @var object
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

	/**
	 * This handels how different cases (upper/lower) are treated in ini files
	 *
	 * @var string
	 */
	protected static $flexibility = '';

	/** This is for, well... debugging =;)
	 *
	 * @var boolean
	 */
	protected static $debug = false;

	protected static $events = array();

	protected static $extensionsLoaded = array();

	private static $application;

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
	public static function loadLanguage($extension, $domain = '', $inputType = 'po', $storageType = 'file_php')
	{
		$key = $extension . ($domain ? '.' . $domain : '');

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
			$dbgMsg = sprintf('Found %d strings'
				, count($store->get('strings')));
		}

		self::logEvent(__METHOD__, $extension, $domain, $inputType, $storageType, $dbgMsg, self::$lang);

		self::$extensionsLoaded[$key] = 1;
	}

	/**
	 * Get the default language tag.
	 *
	 * @static
	 * @return string
	 */
	public static function getDefault()
	{
		if (!self::$lang)
			self::detectLanguage();

		return self::$lang;
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

		if (isset(self::$strings[$key])
			&& self::$strings[$key]
		)
		{
			//-- Translation found
			return self::process(self::$strings[$key]);
		}

		//-- Search for alternatives - L for legacy
		if (self::$flexibility == 'mixed'
			|| (!self::$flexibility)
		)
		{
			$key = md5(strtoupper($original));

			if (isset(self::$strings[$key]))
			{
				//-- Translation found - key is upper cased, requested string is not..
				return self::process(self::$strings[$key]);
			}
		}

		//-- Worst case - No translation found !

		if (self::$docType == 'html')
		{
			$original = str_replace(array("\n", "\\n"), '<br />', $original);
		}

		return $original;
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

		$format = '%s';

		if (array_key_exists($key, self::$stringsPlural)
			&& array_key_exists($index, self::$stringsPlural[$key])
		)
		{
			if (self::$debug)
			{
				self::recordTranslated($singular . ' / ' . $plural, '+', 2);

				return sprintf('+-%s-+', self::process(self::$stringsPlural[$key][$index]));
			}

			return self::process(self::$stringsPlural[$key][$index]);
		}

		//-- Fallback - english: singular == 1
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
	 * Set the application object.
	 *
	 * @param   object  $application  The application object.
	 *
	 * @return $this
	 */
	public static function setApplication($application)
	{
		self::$application = $application;
	}

	/**
	 * Get the application object.
	 *
	 * @throws \RuntimeException
	 * @return mixed
	 */
	public static function getApplication()
	{
		if (!self::$application)
		{
			throw new \RuntimeException('No application set');
		}

		return self::$application;
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
	 * @throws g11nException
	 * @return g11nParser of a specific type
	 */
	public static function getParser($type, $name)
	{
		if (!jimport('g11n.language.parsers.' . $type . '.' . $name))
			throw new g11nException('Can not get the parser ' . $type . '.' . $name);
		//@Do_NOT_Translate

		$parserName = 'g11nParser' . ucfirst($type) . ucfirst($name);

		if (!class_exists($parserName))
			throw new g11nException('Required class not found: ' . $parserName);

		//@Do_NOT_Translate

		return new $parserName;
	}

	/**
	 * Translation in debug mode.
	 *
	 * @param string $original Original string to be translated
	 *
	 * @return string
	 */
	protected static function debugTranslate($original)
	{
		$key = md5($original);

		if (isset(self::$strings[$key])
			&& self::$strings[$key]
		)
		{
			//-- Translation found
			self::recordTranslated($original, '+');

			return sprintf('+-%s-+', self::process(self::$strings[$key]));
		}
		else if (self::$flexibility == 'mixed'
			|| (!self::$flexibility)
		)
		{
			//-- Search for alternatives - upper cased key
			$key = md5(strtoupper($original));

			if (isset(self::$strings[$key]))
			{
				//-- Translation found - key is upper cased, value is not..
				self::recordTranslated($original, 'L');

				return sprintf('L-%s-L', self::process(self::$strings[$key]));
			}
		}

		//-- Worst case - No translation found !

		self::recordTranslated($original, '-');

		return sprintf('¿-%s-¿', str_replace(array("\n", "\\n"), '<br />', $original));
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
		else //
		{
			$nplurals      = 2;
			$expression    = 'n == 1 ? 0 : 1';
			$PHPexpression = '$n == 1 ? 0 : 1';
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

		//-- To be called only once
		if (!$hasBeenAdded)
		{
			$path     = 'libraries/g11n/language/javascript';
			$document = self::getApplication()->getDocument();

			$document->addScript(JURI::root(true) . '/' . $path . '/methods.js');
			$document->addScript(JURI::root(true) . '/' . $path . '/language.js');
			$document->addScript(JURI::root(true) . '/' . $path . '/phpjs.js');

			$document->addScriptDeclaration("g11n.debug = '" . self::$debug . "'\n");

			$hasBeenAdded = true;
		}

		//-- Add the strings to the page <head> section
		$js   = array();
		$js[] = '<!--';
		$js[] = '/* JavaScript translations */';
		$js[] = 'g11n.loadLanguageStrings(' . json_encode($strings) . ');';
		$js[] = "g11n.legacy = '" . self::$flexibility . "'";

		if (self::$pluralFunctionJsStr)
		{
			$js[] = 'g11n.loadPluralStrings(' . json_encode($stringsPlural) . ');';

			if (!$hasBeenAdded)
				$js[] = 'g11n.setPluralFunction(' . self::$pluralFunctionJsStr . ')';
		}

		$js[] = '-->';

		self::$stringsJs = array_merge(self::$stringsJs, $strings);

		self::getApplication()->getDocument()->addScriptDeclaration(implode("\n", $js));
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

		if (self::$docType == 'html')
		{
			$string = str_replace(array("\n", "\\n"), '<br />', $string);
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
		self::$lang = self::getApplication()->input->get('lang');

		if (self::$lang != '')
		{
			//@todo CHECKif language exists..
			//self::$lang = $reqLang;

			//self::getApplication()->input->get('lang', self::$lang);

			return;
		}

		$env        = getenv('LANG');
		self::$lang = ('POSIX' != $env) ? $env : '';

		if (self::$lang)
		{
			self::$lang = str_replace('_', '-', self::$lang);

			if (strpos(self::$lang, '.'))
				self::$lang = substr(self::$lang, 0, strpos(self::$lang, '.'));

			//-- We're british..
			if ('en-US' == self::$lang)
				self::$lang = 'en-GB';

			return;
		}

		//-- OK.. let's do a
		//self::$lang = JFactory::getLanguage()->getTag();

		//-- That should be enough.. british or die.
		if (!self::$lang)
			self::$lang = 'en-GB';

//        throw new g11nException('Something wrong with JLanguage :(');
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
	 * @param int    $level  The level where the function has been called
	 *
	 * @return void
	 */
	private static function recordTranslated($string, $mode, $level = 3)
	{
		if (array_key_exists($string, self::$processedItems))
			return;
		//-- Already recorded

		$info           = new \stdClass;
		$info->status   = $mode;
		$info->file     = '';
		$info->line     = 0;
		$info->function = '';
		$info->args     = array();

		if (function_exists('debug_backtrace'))
		{
			$trace = debug_backtrace();

			//-- Element no. 3 must be our jgettext() caller - s/be/not be...
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