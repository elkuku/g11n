<?php
/**
 * @package    g11n
 * @subpackage Base
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 19-Sep-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

spl_autoload_register('g11n::loader');

require __DIR__.'/language/methods.php';

/**
 * The g11n - "globalization" class.
 *
 * Language handling class.
 *
 * @package g11n
 */
abstract class g11n //-- Joomla!'s Alternative Language Handler oO
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

    /**
     * Provide access to everything we have inside ;).
     *
     * Provided for 3pd use or whatever..
     *
     * @param string $property Property name
     *
     * @return mixed
     */
    public static function get($property)
    {
        if(isset(self::$$property))
            return self::$$property;

        JFactory::getApplication()->enqueueMessage('Undefined property '.__CLASS__.'::'.$property, 'error');
    }

    /**
     * Load the language.
     *
     * @param string $extension   E.g. joomla, com_weblinks, com_easycreator etc.
     * @param string $scope       Must be 'admin' or 'site' / empty to use the actual.
     * @param string $inputType   The input type e.g. "ini" or "po"
     * @param string $storageType The store type - e.g. 'file_php'
     *
     * @throws g11nException
     * @return void
     */
    public static function loadLanguage($extension = '', $scope = ''
        , $inputType = 'po', $storageType = 'file_php')
    {
        if(! $extension
            && ! $extension = JRequest::getCmd('option')
        )
            throw new g11nException('Invalid extension');

        if(empty($scope))
            $scope = JFactory::getApplication()->isAdmin()
                ? 'admin' : 'site';

        $key = $extension.'.'.$scope;

        if(array_key_exists($key, self::$extensionsLoaded))
            return;

        if(! self::$lang)
            self::detectLanguage();

        if(! self::$docType)
            self::detectDocType();

        $handler = g11nStorage::getHandler($inputType, $storageType);

        $store = $handler->retrieve(self::$lang, $extension, $scope);

        self::$strings = array_merge(self::$strings, $store->get('strings'));

        self::$stringsPlural = $store->get('stringsPlural');

        self::setPluralFunction($store->get('pluralForms'));

        self::addJavaScript($store->get('stringsJs'), $store->get('stringsJsPlural'));

        $dbgMsg = '';

        if(self::$debug)
        {
            $dbgMsg = sprintf('Found %d strings'
                , count($store->get('strings')));
        }

        self::logEvent(__METHOD__, $extension, $scope, $inputType, $storageType, $dbgMsg, self::$lang);

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
        if(! self::$lang)
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
        if(self::$debug)
            return self::debugTranslate($original);

        $key = md5($original);

        if(isset(self::$strings[$key])
            && self::$strings[$key]
        )
        {
            //-- Translation found
            return self::process(self::$strings[$key]);
        }

        //-- Search for alternatives - L for legacy
        if(self::$flexibility == 'mixed'
            || (! self::$flexibility)
        )
        {
            $key = md5(strtoupper($original));

            if(isset(self::$strings[$key]))
            {
                //-- Translation found - key is upper cased, requested string is not..
                return self::process(self::$strings[$key]);
            }
        }

        //-- Worst case - No translation found !

        if(self::$docType == 'html')
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

        $index = (int)call_user_func(self::$pluralFunction, $count);

        $format = '%s';

        if(array_key_exists($key, self::$stringsPlural)
            && array_key_exists($index, self::$stringsPlural[$key])
        )
        {
            if(self::$debug)
            {
                self::recordTranslated($singular.' / '.$plural, '+', 2);

                return sprintf('+-%s-+', self::process(self::$stringsPlural[$key][$index]));
            }

            return self::process(self::$stringsPlural[$key][$index]);
        }

        //-- Fallback - english: singular == 1
        $retVal = ($count == 1) ? $singular : $plural;

        if(self::$debug)
        {
            self::recordTranslated($singular.' / '.$plural, '-', 2);

            return sprintf('¿-%s-¿', $retVal);
        }

        return $retVal;
    }

    /**
     * Clean the storage device.
     *
     * @param string      $extension   E.g. joomla, com_weblinks, com_easycreator etc.
     * @param bool|string $JAdmin      Set true for administrator
     * @param string      $inputType   The input type e.g. "ini" or "po"
     * @param string      $storageType The story type
     *
     * @throws g11nException
     * @return void
     */
    public static function cleanStorage($extension = '', $JAdmin = ''
        , $inputType = 'po', $storageType = 'file_php')
    {
        if(! self::$lang)
            self::detectLanguage();

        if(! $extension
            && ! $extension = JFactory::getApplication()->input->get('option')
        )
            throw new g11nException('Invalid extension');

        if($JAdmin == '')
            $JAdmin = JFactory::getApplication()->isAdmin()
                ? true : false;

        g11nStorage::getHandler($inputType, $storageType)->clean(self::$lang, $extension, $JAdmin);
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
        self::$debug = (bool)$bool;
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
        g11nDebugger::debugPrintTranslateds($untranslatedOnly);
    }

    /**
     * Print out recorded events.
     *
     * @return void
     */
    public static function printEvents()
    {
        foreach(self::$events as $e)
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
        if(! jimport('g11n.language.parsers.'.$type.'.'.$name))
            throw new g11nException('Can not get the parser '.$type.'.'.$name);
        //@Do_NOT_Translate

        $parserName = 'g11nParser'.ucfirst($type).ucfirst($name);

        if(! class_exists($parserName))
            throw new g11nException('Required class not found: '.$parserName);
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

        if(isset(self::$strings[$key])
            && self::$strings[$key]
        )
        {
            //-- Translation found
            self::recordTranslated($original, '+');

            return sprintf('+-%s-+', self::process(self::$strings[$key]));
        }
        else if(self::$flexibility == 'mixed'
            || (! self::$flexibility)
        )
        {
            //-- Search for alternatives - upper cased key
            $key = md5(strtoupper($original));

            if(isset(self::$strings[$key]))
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
        if(preg_match("/nplurals\s*=\s*(\d+)\s*\;\s*plural\s*=\s*(.*?)\;+/", $pcrePluralForm, $matches))
        {
            $nplurals = $matches[1];
            $expression = $matches[2];

            $PHPexpression = str_replace('n', '$n', $expression);
        }
        else //
        {
            $nplurals = 2;
            $expression = 'n == 1 ? 0 : 1';
            $PHPexpression = '$n == 1 ? 0 : 1';
        }

        $func_body = '$plural = ('.$PHPexpression.');'
            .' return ($plural <= '.$nplurals.')? $plural : $plural - 1;';

        $js_func_body = 'plural = ('.$expression.');'
            .' return (plural <= '.$nplurals.')? plural : plural - 1;';

        self::$pluralFunction = create_function('$n', $func_body);

        self::$pluralFunctionJsStr = "phpjs.create_function('n', '".$js_func_body."')";
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
        static $hasBeenAdded = false;

        //-- To be called only once
        if(! $hasBeenAdded)
        {
            $path = 'libraries/g11n/language/javascript';
            $document = JFactory::getDocument();

            $document->addScript(JURI::root(true).'/'.$path.'/methods.js');
            $document->addScript(JURI::root(true).'/'.$path.'/language.js');
            $document->addScript(JURI::root(true).'/'.$path.'/phpjs.js');

            $document->addScriptDeclaration("g11n.debug = '".self::$debug."'\n");

            $hasBeenAdded = true;
        }

        //-- Add the strings to the page <head> section
        $js = array();
        $js[] = '<!--';
        $js[] = '/* JavaScript translations */';
        $js[] = 'g11n.loadLanguageStrings('.json_encode($strings).');';
        $js[] = "g11n.legacy = '".self::$flexibility."'";

        if(self::$pluralFunctionJsStr)
        {
            $js[] = 'g11n.loadPluralStrings('.json_encode($stringsPlural).');';

            if(! $hasBeenAdded)
                $js[] = 'g11n.setPluralFunction('.self::$pluralFunctionJsStr.')';
        }

        $js[] = '-->';

        self::$stringsJs = array_merge(self::$stringsJs, $strings);

        JFactory::getDocument()->addScriptDeclaration(implode("\n", $js));
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

        if(self::$docType == 'html')
        {
            $string = str_replace(array("\n", "\\n"), '<br />', $string);
        }

        return $string;
    }

    /**
     * Try to detect the current language.
     *
     * This is done with a little help .. from JFactory::getLanguage()
     *
     * @throws g11nException
     * @return void
     */
    private static function detectLanguage()
    {
        self::$lang = JFactory::getApplication()->input->get('lang');

        if(self::$lang != '')
        {
            //@todo CHECKif language exists..
            //self::$lang = $reqLang;

            JFactory::getApplication()->setUserState('lang', self::$lang);

            return;
        }

        self::$lang = JFactory::getApplication()->getUserState('lang');

        if(self::$lang != '')
        {
            //@todo CHECKif language exists..
            //self::$lang = $stateLang;

            return;
        }

        $env = getenv('LANG');
        self::$lang = ('POSIX' != $env) ? $env : '';

        if(self::$lang)
        {
            self::$lang = str_replace('_', '-', self::$lang);

            //-- We're british..
            if('en-US' == self::$lang)
                self::$lang = 'en-GB';

            return;
        }

        //-- OK.. let's do a
        self::$lang = JFactory::getLanguage()->getTag();

        //-- That should be enough.. british or die.
        if(! self::$lang)
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
        self::$docType = JFactory::getDocument()->getType();

        if(! self::$docType)
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
        if(array_key_exists($string, self::$processedItems))
            return;
        //-- Already recorded

        $info = new stdClass();
        $info->status = $mode;
        $info->file = '';
        $info->line = 0;
        $info->function = '';
        $info->args = array();

        if(function_exists('debug_backtrace'))
        {
            $trace = debug_backtrace();

            //-- Element no. 3 must be our jgettext() caller - s/be/not be...
            $trace = $trace[$level];

            $info->file = $trace['file'];
            $info->line = $trace['line'];
            $info->function = $trace['function'];
            $info->args = $trace['args'];
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

        $e = new stdClass();

        foreach($args as $k => $v)
        {
            $e->$k = $v;
        }

        self::$events[] = $e;
    }

    /**
     * The g11n autoloader.
     *
     * @static
     *
     * @param string $className The class to load.
     *
     * @return mixed
     */
    public static function loader($className)
    {
        if(0 !== strpos($className, 'g11n'))
            return;

        $file = strtolower(substr($className, 4)).'.php';

        $path = __DIR__.'/'.$file;

        if(file_exists($path))
        {
            include $path;

            return;
        }

        $path = __DIR__.'/language/'.$file;

        if(file_exists($path))
        {
            include $path;

            return;
        }

        $parts = preg_split('/(?<=[a-z])(?=[A-Z])/x', substr($className, 4));

        $path = __DIR__.'/language/'.strtolower(implode('/', $parts)).'.php';

        //-- @TODO change
        $path = str_replace('parser', 'parsers', $path);

        if(file_exists($path))
        {
            include $path;

            return;
        }
    }
}//class
