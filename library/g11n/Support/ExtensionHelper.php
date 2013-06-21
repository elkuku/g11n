<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Support;

/**
 * Extension helper class.
 *
 * @package g11n
 */
class ExtensionHelper
{
    protected static $extensionTypes = array(
    'com' => 'component'
    , 'mod' => 'module'
    , 'tpl' => 'template'
    , 'plg' => 'plugin'
    , 'lib' => 'library'
    , 'cli' => 'cli_language');

    public static $langDirName = 'g11n';

    /**
     * Set a custom directory name for language files.
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

        if(array_key_exists($extension, $dirs))
        return $dirs[$extension];

        if('joomla' == $extension)
        return;

        $prfx_extension = $extension;

        $parts = self::split($extension);

        $subType = '';

        if(count($parts) === 1)
        {
            $parts = self::split($extension, '_');
            $prefix = $parts[0];
            $extensionName = $parts[1];
        }
        else//
        {
            //-- We have a subType
            $subType = $parts[1];

            $prfx_extension  = $parts[0];

            $parts = self::split($parts[0], '_');
            $prefix = $parts[0];
            $extensionName = $parts[1];
        }

        if( ! array_key_exists($prefix, self::$extensionTypes))
        throw new g11nException(sprintf('Undefined extension type: %s', $prefix));

        if('tpl' == $prefix)
        {
            //-- Templates reside in a directory *without* the prefix 'tpl'
            $extensionDir = self::$extensionTypes[$prefix].'s/'.$extensionName;
        }
        else if('plg' == $prefix)
        {
            $parts = explode('_', $extensionName);

            if( ! isset($parts[1]))
            throw new g11nException('Unable to parse plugin name');

            $extensionDir = self::$extensionTypes[$prefix].'s/'.$parts[0].'/'.$parts[1];
        }
        else//
        {
            $extensionDir = self::$extensionTypes[$prefix].'s/'.$prfx_extension;
        }

        $dirs[$extension] = $extensionDir;

        return $extensionDir;
    }//function

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

        return $path.'/'.self::$langDirName;
    }//function

    /**
     * @static
     *
     * @param string $extension The extension name, e.g. com_easycreator
     * @param string $scope     The extension scope, e.g. admin
     *
     * @return bool
     */
    public static function isExtension($extension, $scope = 'admin')
    {
        try
        {
            $extensionPath = self::getExtensionPath($extension);
            $scopePath = self::getScopePath($scope);

            return is_dir($scopePath.'/'.$extensionPath);
        }
        catch(\Exception $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }//try
    }//function

    /**
     * Get the scope path.
     *
     * @static
     *
     * @param string $scope The extension scope, e.g. admin
     *
     * @return string
     */
    public static function getScopePath($scope)
    {
        return ($scope == 'admin') ? JPATH_ADMINISTRATOR : JPATH_SITE;
    }//function

    /**
     * Get known extension types.
     *
     * @static
     * @return array
     */
    public static function getExtensionTypes()
    {
        return self::$extensionTypes;
    }//function

    /**
     * Searches the system for language files.
     *
     * @param string $lang      Language
     * @param string $extension Extension
     * @param string $scope     The extension scope, e.g. admin
     * @param string $type      Language file type - e.g. 'ini', 'po' etc.
     *
     * @return mixed Full path to file | false if none found
     *
     */
    public static function findLanguageFile($lang, $extension, $scope = '', $type = 'po')
    {
        if($scope == '')
        {
            $base =(JFactory::getApplication()->isAdmin())
            ? JPATH_ADMINISTRATOR : JPATH_SITE;
        }
        else
        {
            $base = g11nExtensionHelper::getScopePath($scope);
        }

        if('joomla' == $extension)
        {
                $fileName = $lang.'.'.$type;
        }
        else
        {
                $fileName = $lang.'.'.$extension.'.'.$type;
        }

        $extensionDir = self::getExtensionPath($extension);

        $extensionLangDir = self::getExtensionLanguagePath($extension);

        //-- First try our special dir
        $path = JPath::clean("$base/$extensionLangDir/$lang/$fileName");

        if(file_exists($path))
        return $path;

        //-- Next try extension/language directory
        $path = JPath::clean("$base/$extensionDir/language/$lang/$fileName");

        if(file_exists($path))
        return $path;

        //-- Now try the base language dir
        $path = JPath::clean("$base/language/$lang/$fileName");

        if(file_exists($path))
        return $path;

        //-- Found nothing :(

        /* @Do_NOT_Translate */
        //        JError::raiseNotice(0, 'No language files found for [lang] [extension] [scope] [type]');
        //        JError::raiseNotice(0, sprintf('[%s] [%s] [%s] [%s]', $lang, $extension, $JAdmin, $type));

        return false;

        //throw new Exception('No language files found');//@Do_NOT_Translate
    }//function

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

        if('mod' == $parts[0]
        || 'plg' == $parts[0]
        || 'tpl' == $parts[0])
        {
            $parts = array();

            $pos = strpos($string, '_');
            $parts[0] = substr($string, 0, $pos);
            $parts[1] = substr($string, $pos + 1);

            return $parts;
        }

        if(count($parts) < 1
        || count($parts) > 2)
        throw new g11nException('Invalid type - must be xx'.$delimiter.'[xx]: '.$string);

        return $parts;
    }//function
}//class
