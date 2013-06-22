<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

/**
 * Enter description here ...
 *
 * @package    g11n
 */
class g11nStorageFileTxt extends g11nStorage
{
    /**
     * Retrieve the storage content.
     *
     * @param string $extension Extension
     * @param string $lang Language
     * @param string $fileName The file name
     *
     * @return boolean
     */
    protected function retrieve($extension, $lang, $fileName)
    {
        if(self::$storage == 'off')
        return false;

        $profiler = JProfiler::getInstance('LangDebug');//@@debug
        $profiler->mark('start: '.$extension);//@@debug

        jimport('joomla.filesystem.file');

        $path = self::$cacheDir.'/'.$lang.'.'.$extension.'.txt';

        if( ! JFile::exists($path))
        return false;

        $strings = JFile::read($path);

        if( ! $strings)
        return false;

        $strings = json_decode($strings, true);

        $profiler->mark('<span style="color: green;">*Loaded txt*</span>'.htmlentities($path));//@@debug

        $this->strings = array_merge($this->strings, $strings);

        // language overrides
        $this->strings = array_merge($this->strings, $this->override);

        $this->paths[$extension][$fileName] = true;

        return true;
    }

    /**
     * Stores the strings into a storage.
     *
     * Should be moved..
     *
     * @param string $extension E.g. joomla, com_weblinks, com_easycreator etc.
     * @param string $lang E.g. de-DE, es-ES etc.
     * @param string $fileName File name of the original (ini) file.
     *
     * @return boolean true on success.
     * @throws Exception
     */
    protected function store($extension, $lang, $fileName)
    {
        if(self::$storage == 'off') return false;

        $profiler = JProfiler::getInstance('LangDebug');//@@debug
        $profiler->mark('store: '.$extension);//@@debug

//        #		$fileNames = JFolder::files(JPATH_ADMINISTRATOR, '.sys.ini', false, true);

        $strings = self::parseFile($fileName);

        $path = self::$cacheDir.'/'.$lang.'.'.$extension.'.txt';
        $jsonString = json_encode($strings);

        if( ! JFile::write($path, $jsonString))
        throw new g11nException('Unable to write language storage file');//@Do_NOT_Translate

        $profiler->mark('<span style="color: blue;">wrote file</span>: '
        .str_replace(JPATH_ROOT, 'J', $path));//@@debug

        $profiler->mark('store SUCCESS ++++: '.$extension);//@@debug

        return true;
    }
}
