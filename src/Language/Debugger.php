<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language;

use ElKuKu\G11n\G11n;

/**
 * G11n Language debugger class.
 *
 * @since  1.0
 */
abstract class Debugger
{
	/**
	 * Prints out translated and untranslated strings.
	 *
	 * @param   boolean  $untranslatedOnly  Set true to print out only untranslated strings
	 *
	 * @return void
	 */
	public static function debugPrintTranslateds(bool $untranslatedOnly = false) : void
	{
		if (!G11n::get('debug'))
		{
			echo 'Debugging is disabled<br />';

			return;
		}

		$title = $untranslatedOnly ? 'Untranslated strings' : 'All strings';

		echo '<h2>' . $title . '</h2>';

		$items = G11n::get('processedItems');

		if ($untranslatedOnly)
		{
			self::drawDesignTable();
		}

		/*
		 * Develop
		 */
		echo '<table class="adminlist">';
		echo '<tr>';
		echo '<th>Status</th><th>String</th><th>Args</th><th>File (line)</th>';

		$k = 0;

		foreach ($items as $string => $item)
		{
			switch ($item->status)
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
			}

			if ($item->status !== '-' && $untranslatedOnly)
			{
				continue;
			}

			echo '<tr class="row' . $k . '">';
			echo '<td style="' . $col . '">' . $item->status . '</td>';
			echo '<td>' . htmlentities($string) . '</td>';
			echo '<td>';

			if (\count($item->args) > 1)
			{
				foreach ($item->args as $i => $arg)
				{
					// Skip first element
					if (!$i)
					{
						continue;
					}

					echo $arg . '<br />';
				}
			}

			echo '</td>';
			echo '<td>' . $item->file . ' (' . $item->line . ')</td>';
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
	private static function drawDesignTable() : void
	{
		$items = G11n::get('processedItems');
		$file  = '';
		$count = 0;

		echo '<h3>Design</h3>';

		echo '<pre style="border: 1px dashed gray; padding: 0.5em; font-size: 12px;">';

		foreach ($items as $string => $item)
		{
			if ($item->status !== '-')
			{
				continue;
			}

			if ($item->file !== $file)
			{
				$file = $item->file;
				echo '# From file: <strong>' . $item->file . '</strong>';
			}

			echo "\n";
			echo htmlspecialchars($string) . "\n";
			echo htmlspecialchars($string) . "\n";

			$count++;
		}

		echo '</pre>';

		echo $count
			? sprintf('<h3>Found <b>%d</b> untranslated items</h3>', $count)
			: '<h3 style="color: green;">Everything\'s translated <tt>=:)</tt></h3>';
	}
}
