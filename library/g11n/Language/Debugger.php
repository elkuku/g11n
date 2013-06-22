<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language;

use g11n\g11n;

/**
 * Enter description here ...
 *
 * @package    g11n
 */
abstract class Debugger
{
    /**
     * Prints out translated and untranslated strings.
     *
     * @param boolean $untranslatedOnly Set true to print out only untranslated strings
     *
     * @return void
     */
    public static function debugPrintTranslateds($untranslatedOnly = false)
    {
        if( ! g11n::get('debug'))
        {
            echo 'Debugging is disabled<br />';

            return;
        }

        $title = ($untranslatedOnly)
        ? 'Untranslated strings' : 'All strings';//@Do_NOT_Translate

        echo '<h2>'.$title.'</h2>';

        $items = g11n::get('processedItems');

        if($untranslatedOnly)
        self::drawDesignTable();

        /*
         * Develop
         */
        echo '<table class="adminlist">';
        echo '<tr>';
        echo '<th>Status</th><th>String</th><th>Args</th><th>File (line)</th>';//@Do_NOT_Translate

        $k = 0;

        foreach($items as $string => $item)
        {
            switch($item->status)
            {
                case 'L':
                    $col = 'background-color: #ffc;';
                    break;
                case '-':
                    $col = 'background-color: #ffb2b2;';
                    break;

                default:
                    $col = 'background-color: #e5ff99;';
                    break;
            }//switch

            if($item->status != '-' && $untranslatedOnly)
            continue;

            echo '<tr class="row'.$k.'">';
            echo '<td style="'.$col.'">'.$item->status.'</td>';
            echo '<td>'.htmlentities($string).'</td>';
            echo '<td>';

            if(count($item->args) > 1)
            {
                foreach($item->args as $i => $arg)
                {
                    if( ! $i)
                    continue;//skip first element

                    echo $arg.'<br />';
                }
            }

            echo '</td>';
            echo '<td>'.self::JReplace($item->file).' ('.$item->line.')</td>';
            echo '</tr>';

            $k = 1 - $k;
        }

        echo '</table>';
    }

    /**
     * Draws untranslated strings to paste in a language file.
     *
     * @return void
     *
     * @deprecated
     */
    private static function drawDesignTable()
    {
        $items = g11n::get('processedItems');
        $file = '';
        $count = 0;

        echo '<h3>Design</h3>';//@Do_NOT_Translate

        echo '<pre style="border: 1px dashed gray; padding: 0.5em; font-size: 12px;">';

        foreach($items as $string => $item)
        {
            if($item->status != '-')
            continue;

            $f = self::JReplace($item->file);

            if($f != $file)
            {
                $file = $f;
                echo '# From file: <strong>'.$f.'</strong>';//@Do_NOT_Translate
            }

            echo "\n";
            echo htmlspecialchars($string)."\n";
            echo htmlspecialchars($string)."\n";

            $count ++;
        }

        echo '</pre>';

        echo ($count)
        ? sprintf('<h3>Found <b>%d</b> untranslated items</h3>', $count)//@Do_NOT_Translate
        : '<h3 style="color: green;">Everything\'s translated <tt>=:)</tt></h3>';
    }

    /**
     * Replaces the JPATH_ROOT by the string "J" in a path.
     *
     * @param string $path The path to replace
     *
     * @return string
     */
    public static function JReplace($path)
    {
        return str_replace(JPATH_ROOT, 'J', $path);
    }
}