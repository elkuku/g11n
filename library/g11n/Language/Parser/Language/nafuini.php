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
class g11nParserLanguageNafuIni
{
    protected $ext = 'nafuini';

    /**
     * Get the extension.
     *
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }//function

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)__CLASS__;
    }//function

    /**
     * Parse an ini style language file similar to gettext files.
     *
     * Example:
     * key
     * value
     *
     * @param string $fileName Absolute path to the file.
     *
     * @return array
     */
    public function parse($fileName)
    {
        //    ###public static function parse($fileName)
        $fileName = JPath::clean($fileName);

        if( ! file_exists($fileName))
        {
            return array();//@todo throw exception
        }

        $lines = explode("\n", JFile::read($fileName));

        if( ! $lines)
        {
            return array();//@todo throw exception
        }

        $header = '';
        $parsing = false;

        $fileInfo = new g11nFileInfo;
        $fileInfo->fileName = $fileName;

        $strings = array();
        $stringsPlural = array();

        $previous = '';

        foreach($lines as $line)
        {
            $line = trim($line);

            if(strpos($line, '#') === 0)
            {
                if( ! $parsing)//read header infos
                {
                    if(preg_match('/#@@@MODE:\s(\w+)/', $line, $matches))
                    $fileInfo->mode = $matches[1];

                    if(preg_match('/#@@@PLURALFORMS:\s(.*)/', $line, $matches))
                    $fileInfo->pluralForms = $matches[1];
                }

                continue;
            }

            if( ! $line)//empty
            {
                $parsing = true;//first run
                $previous = '';

                continue;
            }

            if($previous)//trying to match
            {
                if(preg_match('/@PLURALFORM:\s*(.*)/', $line, $matches))
                {
                    $stringsPlural[$previous] = array();

                    continue;
                }

                if(preg_match('/@PLURAL:(\d+)@\s*(.*)/', $line, $matches))
                {
                    $stringsPlural[$previous][$matches[1]] = $matches[2];

                    continue;
                }

                //-- Found a pair :)
                                        $e = new JObject;
                        $e->info = '';//$info;
                        $e->string = $line;

                $fileInfo->strings[$previous] = $e;

                continue;
            }

            //-- Nothing matched :(
            $previous = $line;
        }//foreach

        //        #if( ! $strings) JError::raiseWarning(0, 'No strings found :(');

        #$fileInfo->strings = $strings;
        $fileInfo->stringsPlural = $stringsPlural;

        return $fileInfo;
    }//function

    /**
     * Generate a language file.
     *
     * @return void
     */
    public function generate(LanguageCheckerHelper $checker, JObject $options)
    {
        $head = $checker->getHead();

        if($head)
        {
            echo $head;
        }
        else
       {
            echo '# @version SVN: $I'.'d$'.NL;
            echo NL;
        }

        $translations = $checker->getTranslations();

        foreach($checker->getStrings() as $key => $string)
        {
            if($options->get('includeLineNumbers'))
            {
                foreach($string->files as $f => $locs)
                {
                    foreach($locs as $loc)
                    {
                        echo '#: '.str_replace(JPATH_ROOT.DS, '', $f).':'.$loc.NL;
                    }//foreach
                }//foreach
            }

            $value = '';//@TRANSLATE: '.$key;//@TODO

            if(array_key_exists($key, $translations))
            {
                $value = $translations[$key]->string;
            }
            else
           {
                $test = strtoupper($key);

                if(array_key_exists($test, $translations))
                {
                    if($this->buildOpts->get('markKeyDiffers'))
                    {
                        echo '# @Key differs :('.NL;
                    }

                    $value = $translations[$test]->string;
                }
                else
              {
                    $value = "'";

                    if($this->buildOpts->get('markFuzzy'))
                    {
                        echo '#.fuzzy'.NL;
                    }
                }
            }

            echo htmlspecialchars($key).NL;
            echo htmlspecialchars($value).NL;
            echo NL;
        }//foreach
    }//function
}//class
