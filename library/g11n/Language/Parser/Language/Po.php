<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language\Parser\Language;

use g11n\Language\Parser\Language;
use g11n\Support\FileInfo;

/**
 * Parser for po language files.
 *
 * @package g11n
 */
class Po extends Language
{
    /**
     * File extension.
     *
     * @var string
     */
    protected $ext = 'po';

    /**
     * Parse a po style language file.
     *
     * @param string $fileName Absolute path to the language file.
     *
     * @return FileInfo
     */
    public function parse($fileName)
    {
        $fileInfo = new FileInfo;

        $fileInfo->fileName = $fileName;

        if(! file_exists($fileName))
        {
            return $fileInfo; //@todo throw exception
        }

        $lines = file($fileName);

        if(! $lines)
        {
            return $fileInfo; //@todo throw exception
        }

        $msgid = '';
        $msgstr = '';
        $msg_plural = '';
        $msg_plurals = array();

        $head = '';
        $info = '';
        $state = - 1;

        foreach($lines as $line)
        {
            $line = trim($line);

            if(0 === strpos($line, '#~'))
                continue;

            $match = array();

            switch($state)
            {
                case - 1 :
                    // Start parsing
                    if(! $line)
                    {
                        // First empty line stops header
                        $state = 0;
                    }
                    else
                    {
                        $head .= $line."\n";
                    }
                    break;

                case 0 :
                    // Waiting for msgid
                    if(preg_match('/^msgid "(.*)"$/', $line, $match))
                    {
                        $msgid = stripcslashes($match[1]);
                        $state = 1;
                    }
                    else
                    {
                        $info .= $line."\n";
                    }
                    break;

                case 1:
                    // Reading msgid, waiting for msgstr
                    if(preg_match('/^msgstr "(.*)"$/', $line, $match))
                    {
                        $msgstr = stripcslashes($match[1]);
                        $state = 2;
                    }
                    else if(preg_match('/^msgid_plural "(.*)"$/', $line, $match))
                    {
                        $msg_plural = stripcslashes($match[1]);
                        $state = 1;
                    }
                    else if(preg_match('/^msgstr\[(\d+)\] "(.*)"$/', $line, $match))
                    {
                        $msg_plurals[stripcslashes($match[1])] = stripcslashes($match[2]);
                        $state = 1;
                    }
                    else if(preg_match('/^"(.*)"$/', $line, $match))
                    {
                        $msgid = stripcslashes($match[1]);
                    }
                    break;

                case 2:
                    // Reading msgstr, waiting for blank
                    if(preg_match('/^"(.*)"$/', $line, $match))
                    {
                        $msgstr = stripcslashes($match[1]);
                    }
                    else if(empty($line))
                    {
                        if($msgstr)
                        {
                            // We have a complete entry
                            $e = new \stdClass;
                            $e->info = $info;
                            $e->string = $msgstr;
                            $fileInfo->strings[$msgid] = $e; //$msgstr;
                        }

                        $state = 0;
                        $info = '';
                    }
                    break;
            }

            // Comment or blank line?
            if(empty($line)
                || preg_match('/^#/', $line)
            )
            {
                if($msg_plural)
                {
                    if($msg_plurals[0])
                    {
                        $t = new \stdClass;
                        $t->plural = $msg_plural;
                        $t->forms = $msg_plurals;
                        $t->info = $info;
                        $fileInfo->stringsPlural[$msgid] = $t;
                    }

                    $msg_plural = '';
                    $msg_plurals = array();
                    $state = 0;
                }
            }
        }

        $fileInfo->head = $head;

        return $fileInfo;
    }

    /**
     * Generate a language file.
     *
     * @param FileInfo $fileInfo
     * @param \stdClass      $options JObject
     *
     * @return string
     */
    public function generate(FileInfo $fileInfo, $options)
    {
        $content = array();

        $head = trim($fileInfo->head);

        if($head)
        {
            $content[] = $head;
        }
        else
        {
            $content[] = '# @version SVN: $I'.'d$';
            $content[] = 'msgid ""';
            $content[] = 'msgstr ""';
        }

        $content[] = '';

        $lang = $fileInfo->langTag;

        $pluralStrings = $fileInfo->stringsPlural;

        foreach($pluralStrings as $key => $string)
        {
            $value = ''; //$key;//@TODO
            $info = '';

            if(array_key_exists($key, $checker->getTranslations()))
            {
                $value = $this->translations[$key]->string;
                $info = $this->translations[$key]->info;
            }
            else
            {
                $test = strtoupper($key);

                if(array_key_exists($test, $checker->getTranslations()))
                {
                    if($this->buildOpts->get('markKeyDiffers'))
                    {
                        $content[] = '# Key is upper cased :(';
                    }

                    $value = $this->translations[$test]->string;
                    $info = $this->translations[$key]->info;
                }
            }

            if($options->get('includeLineNumbers'))
            {
                foreach($string->files as $f => $locs)
                {
                    foreach($locs as $loc)
                    {
                        $content[] = '#: '.str_replace(JPATH_ROOT.DS, '', $f).':'.$loc;
                    }
                }
            }

            if(! $value
                && $options->get('markFuzzy')
            )
            {
                //echo '#, fuzzy'.NL;
            }

            $content[] = 'msgid "'.htmlspecialchars($key).'"';
            $content[] = 'msgid_plural "'.htmlspecialchars($string->plural).'"';

            foreach($string->pluralForms as $k => $v)
            {
                $content[] = 'msgstr['.$k.'] "'.$v.'"';
            }

            $content[] = '';
        }

        //        $translations = $checker->getTranslations();
        //        $strings = $checker->getStrings();

        //        echo '# '.count($translations).' translations'.NL;
        //        echo '# '.count($strings).' strings'.NL;

        $checkStrings = $fileInfo->strings;

        foreach($checkStrings as $key => $string
        )
            #foreach($fileInfo->strings as $key => $string)
        {
            $key = html_entity_decode($key);

            $key = addcslashes($key, '"');

            while(strpos($key, "\\\\") != false)
            {
                $key = str_replace('\\\\', '\\', $key);
            }

            while(strpos($key, "\'") != false)
            {
                $key = str_replace("\'", "'", $key);
            }

            //            $value = '';
            //            $info = '';

            #$value = $string->string;
            $value = (isset($string->translation) && $string->translation) ? $string->translation : '';

            if(! $value) //...brrrrrrr
            {
                $value = $string->string; //...right..
            }

            #$info = '';
            $info = trim($string->info);
            //            if(array_key_exists($key, $translations))
            //            {
            //                $value = $translations[$key]->string;
            //                $info = $translations[$key]->info;
            //            }
            //            else
            //            {
            //                $test = strtoupper($key);
            //
            //                if(array_key_exists($test, $translations))
            //                {
            //                    if($options->get('markKeyDiffers'))
            //                    {
            //                        $content[] = '# Key is upper cased :(';
            //                    }
            //
            //                    $value = $translations[$test]->string;
            //                    $info = $translations[$key]->info;
            //                }
            //            }

            if($options->get('includeLineNumbers'))
            {
                if(isset($string->files))
                {
                    foreach($string->files as $f => $locs)
                    {
                        foreach($locs as $loc)
                        {
                            $content[] = '#: '.str_replace(JPATH_ROOT.DS, '', $f).':'.$loc;
                        }
                    }
                }
            }

            if(! $value
                && $options->get('markFuzzy')
                && $lang != 'en-GB'
            )
            {
                $content[] = '#, fuzzy';
            }

            if($info)
                $content[] = $info;

            $content[] = 'msgid "'.htmlspecialchars($key).'"';

            if($lang == 'en-GB')
            {
                $content[] = 'msgstr "'.htmlspecialchars($key).'"';
            }
            else
            {
                $content[] = 'msgstr "'.htmlspecialchars($value).'"';
            }

            $content[] = '';
        }

        return implode(NL, $content);
    }
}