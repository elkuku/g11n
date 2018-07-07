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

		echo self::getCSS();

		echo '<h2 class="g11n-debug-table">' . $title . '</h2>';

		$items = G11n::get('processedItems');

		echo '<table class="g11n-debug-table">';
		echo '<tr>';
		echo '<th>Status</th><th>String</th><th>Args</th><th>File (line)</th>';

		foreach ($items as $item)
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

			echo '<tr>';
			echo '<td style="' . $col . '">' . $item->status . '</td>';
			echo '<td>' . htmlentities($item->string) . '</td>';
			echo '<td>';

			switch (\count($item->args))
			{
				case 1:
					// Simple translation
					break;

				case 2:
					// Translation with parameters
					dump($item->args[1]);
					break;

				case 3:
					// Plural translation
					dump($item->args[2]);
					break;

				case 4:
					// Plural translation with parameters
					dump([$item->args[2], $item->args[3]]);
					break;

				default:
					// Other ??
					dump($item->args);
			}

			echo '</td>';
			echo '<td>' . $item->file . ' (' . $item->line . ')</td>';
			echo '</tr>';
		}

		echo '</table>';
	}

	/**
	 * Print loaded language files.
	 *
	 * @return void
	 */
	public static function debugPrintEvents(): void
	{
		$events = G11n::getEvents();

		if (!$events)
		{
			echo '<h3>No events recorded.</h3>';
		}

		echo '<h2 class="g11n-debug-table">Events</h2>';

		echo '<table class="g11n-debug-table">';
		echo '<tr>';

		foreach ($events[0] as $key => $event)
		{
			echo "<th>$key</th>";
		}

		echo '</tr>';

		foreach ($events as $event)
		{
			echo '<tr>';

			foreach ($event as $e)
			{
				echo "<td>$e</td>";
			}

			echo '</tr>';
		}

		echo '</table>';

	}

	/**
	 * @return string
	 */
	private static function getCSS(): string
	{
		$css = [];

		$css[] = '<style>';
		$css[] = '.g11n-debug-table{background-color: #333; color: lime; font-family: monospace; width: 100%}';
		$css[] = '';
		$css[] = '</style>';

		return implode('', $css);
	}

	/**
	 * Draws untranslated strings to paste in a language file.
	 *
	 * @return void
	 *
	 * @deprecated
	 */
	public static function drawDesignTable() : void
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
			: '<h3 style="color: green;">Everything\'s translated <code>=:)</code></h3>';
	}
}
