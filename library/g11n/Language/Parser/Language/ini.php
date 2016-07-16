<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

// @codingStandardsIgnoreStart

/**
 * Enter description here ...
 *
 * @package    g11n
 */
class g11nParserLanguageIni
{
    protected $ext = 'ini';

    /**
     * Get the extension.
     *
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)__CLASS__;
    }

    /**
     * Parse an ini style language file with *few restrictions*.
     *
     * Example:
     * key = value
     *
     * @param string $fileName Absolute path to the file.
     *
     * @return array
     */
    public function parse($fileName)
    {
        $fileInfo = new g11nFileInfo;

        if( ! $fileName)
        return $fileInfo;

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

        $fileInfo->fileName = $fileName;
        $fileInfo->format = 'ini';
        $fileInfo->mode = '';
        $fileInfo->strings = array();

        $info = '';
        $parsing = false;

        foreach($lines as $line)
        {
            $line = trim($line);

            if( ! $line)
            {
                //-- First empty line stops head parsing
                $parsing = true;

                continue;
            }

            if(strpos($line, ';') === 0
            || strpos($line, '#') === 0)
            {
                if($parsing)
                {
                    $info .= $line."\n";
                }
                else
                {
                    $fileInfo->head .= $line."\n";
                }

                continue;
            }

            $pos = strpos($line, '=');

            if( ! $pos)
            {
                $info .= $line."\n";

                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1), ' "');

            $e = new stdClass;
            $e->info = $info;
            $e->string = $value;

            $info = '';

            $fileInfo->strings[$key] = $e;
        }

        return $fileInfo;
    }

    /**
     * Generate a language file.
     *
     * @return string
     */
    #public function generate(LanguageCheckerHelper $checker, JObject $options)
    public function generate(g11nFileInfo $fileInfo, JObject $options)
    {
        $out = array();

        # $fileInfo = $checker->fileInfo;
        #$head = $checker->getHead();
        $head = $fileInfo->head;

        if($head)
        {
            $out[] = $head;
            //            $out[] = '';
        }
        else
        {
            $out[] = '# @version SVN: $I'.'d$';
            $out[] = '';
        }

        #        $translations = $checker->getTranslations();
        $translations = $fileInfo->strings;

        #foreach($checker->getStrings() as $key => $string)
        foreach($translations as $key => $string)
        {
            //            if($string->isTranslatedInCore
            //            && $this->includeCoreLanguage)
            //            continue;

            if($string->info)
            {
                $out[] = $string->info;
            }

            if($options->get('includeLineNumbers'))
            {
                $out[] = '';

                foreach($string->files as $f => $locs)
                {
                    foreach($locs as $loc)
                    {
                        $out[] = '#: '.str_replace(JPATH_ROOT.DS, '', $f).':'.$loc;
                    }
                }
            }

            $value = '';

            if(array_key_exists($key, $translations))
            {
                $value = $translations[$key]->string;
            }
            else//
            {
                $test = strtoupper($key);

                if(array_key_exists($test, $translations))
                {
                    if($this->buildOpts->get('markKeyDiffers'))
                    {
                        $out[] = '# @Key differs :(';
                    }

                    $value = $this->translations[$test]->string;
                }
                else if($this->buildOpts->get('markFuzzy'))
                {
                    $out[] = '#.fuzzy';
                }
            }

            if('1.6' == $options->get('langFileVersion'))
            {
                $out[] = htmlspecialchars($key).' = "'.htmlspecialchars($value).'"';
            }
            else//
            {
                $out[] = htmlspecialchars($key).'='.htmlspecialchars($value);
            }
        }

        return implode("\n", $out);
    }
}
