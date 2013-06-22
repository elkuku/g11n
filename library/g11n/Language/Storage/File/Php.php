<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language\Storage\File;

use g11n\g11nException;
use g11n\Support\ExtensionHelper;
use g11n\Language\Storage;
use g11n\Support\Store;

/**
 * Storage handler for PHP files.
 *
 * @package g11n
 */
class Php extends Storage
{
    public $fileInfo = null;

    protected $parser = null;

    protected $ext = '.php';

	/**
	 * Constructor.
	 *
	 * @param string $inputType The input type
	 *
	 * @throws \g11n\g11nException
	 */
    public function __construct($inputType)
    {
        $parserName = '\\g11n\\Language\\Parser\\Language\\'.ucfirst($inputType);

        if(! class_exists($parserName))
            throw new g11nException('Required parser class not found: '.$parserName);

        $this->parser = new $parserName;
    }

	/**
	 * Stores the strings into a storage.
	 *
	 * @param string $lang      E.g. de-DE, es-ES etc.
	 * @param string $extension E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param string $scope     Must be 'admin' or 'site'.
	 *
	 * @throws \g11n\g11nException
	 * @return void
	 */
    public function store($lang, $extension, $scope = '')
    {
        $ext = $this->parser->getExt();

        /*
         * Parse language files
         */
        $fileName = ExtensionHelper::findLanguageFile($lang, $extension, $scope, $ext);

        $fileInfo = $this->parser->parse($fileName);

        $this->fileInfo = $fileInfo;

        /*
         * "Normal" strings
         */
        $stringsArray = array();

        foreach($fileInfo->strings as $key => $value)
        {
            $key = md5($key);
            $value = base64_encode($value->string);

            $stringsArray[] = "'".$key."'=>'".$value."'";
        }

        /*
         * Plural strings
         */
        $pluralsArray = array();

        foreach($fileInfo->stringsPlural as $key => $plurals)
        {
            $key = md5($key);
            $ps = array();

            foreach($plurals->forms as $keyP => $plural)
            {
                $value = base64_encode($plural);
                $ps[] = "'".$keyP."'=>'".$value."'";
            }

            $value = base64_encode($value);
            $pluralsArray[] = "'".$key."'=> array(".implode(',', $ps).")";
        }

        /*
         * JavaScript strings
         */
        $jsArray = array();
        $jsPluralsArray = array();

        try
        {
            $jsFileName = ExtensionHelper::findLanguageFile($lang, $extension, $scope, 'js.'.$ext);

            $jsInfo = $this->parser->parse($jsFileName);

            foreach($jsInfo->strings as $key => $value)
            {
                $key = md5($key);
                $value = base64_encode($value->string);
                $jsArray[] = "'".$key."'=>'".$value."'";
            }

            $jsPluralsArray = array();

            foreach($jsInfo->stringsPlural as $key => $plurals)
            {
                $key = md5($key);
                $ps = array();

                foreach($plurals as $keyP => $plural)
                {
                    $value = base64_encode($plural);
                    $ps[] = "'".$keyP."'=>'".$value."'";
                }

                $value = base64_encode($value);
                $jsPluralsArray[] = "'".$key."'=> array(".implode(',', $ps).")";
            }
        }
        catch(Exception $e)
        {
            //-- We did not found the javascript files...
            //-- Do nothing - for now..@todo do something :P
            echo '';
        }

        /* Process the results - Construct an ""array string""
         * Result:
         * '<?php $strings = array('a'=>'b', ...); ?>'
         */
        $resultString = '';
        $resultString .= '<?php ';
        $resultString .= '$info=array('
            ."'mode'=>'".$fileInfo->mode."'"
            .",'pluralForms'=>'".$this->translatePluralForms($fileInfo->pluralForms)."'"
            .");";
        $resultString .= ' $strings=array('.implode(',', $stringsArray).');';
        $resultString .= ' $stringsPlural=array('.implode(',', $pluralsArray).');';
        $resultString .= ' $stringsJs=array('.implode(',', $jsArray).');';
        $resultString .= ' $stringsJsPlural=array('.implode(',', $jsPluralsArray).');';
        /*$resultString .= ' ?>';*/

        $storePath = $this->getPath($lang, $extension, $scope).$this->ext;

        if(! file_put_contents($storePath, $resultString))
            throw new g11nException('Unable to write language storage file to '.$storePath);
        //@Do_NOT_Translate
    }

    /**
     * Retrieve the storage content.
     *
     * @param string $lang      E.g. de-DE, es-ES etc.
     * @param string $extension E.g. joomla, com_weblinks, com_easycreator etc.
     * @param string $scope     Must be 'admin' or 'site'.
     *
     * @return boolean
     */
    public function retrieve($lang, $extension, $scope = '')
    {
        $parts = ExtensionHelper::split($extension, '_');

        $prefix = $parts[0];

        if('joomla' != $prefix)
        {
            if(! array_key_exists($prefix, ExtensionHelper::getExtensionTypes()))
                throw new g11nException('Unknown extension type: '.$prefix);
            //@Do_NOT_Translate

            $extensionName = $parts[1];
        }

        # $parts = $this->split($extensionName, '.');

        $path = $this->getPath($lang, $extension, $scope).$this->ext;
//        $path = JPath::clean($path);

        //-- File has not being cached
        if(! file_exists($path))
        {
            //-- Try to store
            $this->store($lang, $extension, $scope);

            //-- Failed ?
            if(! file_exists($path))
                throw new g11nException('Unable to retrieve the strings');
            //@Do_NOT_Translate
        }

        /*
         * Include the file
         * This file should contain the arrays
         * # $info()
         * # $strings()
         * # $jsStrings()
         */
        include $path;

        $store = new Store;

        if(isset($info))
        {
//            $this->fileInfo->mode = null;
//
//            if(isset($info['mode'])
//            && $info['mode'])
//            $this->fileInfo->mode = $info['mode'];//Legacy ?

            if(isset($info['pluralForms']))
                $store->set('pluralForms', $info['pluralForms']);
        }

        if(! empty($strings))
            $store->set('strings', $strings);

        if(! empty($stringsPlural))
            $store->set('stringsPlural', $stringsPlural);

        if(! empty($stringsJs))
            $store->set('stringsJs', $stringsJs);

        if(! empty($stringsJsPlural))
            $store->set('stringsJsPlural', $stringsJsPlural);

        return $store;
    }

	/**
	 * Cleans the storage.
	 *
	 * @param string $lang      E.g. de-DE, es-ES etc.
	 * @param string $extension E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param string $scope     Must be 'admin' or 'site'.
	 *
	 * @throws \g11n\g11nException
	 * @return void
	 */
    public function clean($lang, $extension, $scope = '')
    {
	    jimport('joomla.filesystem.file');

        $storePath = $this->getPath($lang, $extension, $scope).$this->ext;

        if(! file_exists($storePath))
            return;
        //-- Storage file does not exist

        if(!unlink($storePath))
            throw new g11nException('Unable to clean storage in: '.$storePath);
        //@Do_NOT_Translate
    }
}
