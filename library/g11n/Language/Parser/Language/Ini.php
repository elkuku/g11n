<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language\Parser\Language;

use ElKuKu\G11n\Language\Parser\Language;
use ElKuKu\G11n\Support\FileInfo;


/**
 * Parser for ini language files.
 *
 * @since  1
 */
class Ini extends Language
{
	/**
	 * @var string
	 */
	protected $ext = 'ini';

	/**
	 * Parse an ini style language file with *few restrictions*.
	 *
	 * Example:
	 * key = value
	 *
	 * @param   string  $fileName  Absolute path to the file.
	 *
	 * @return FileInfo
	 */
	public function parse($fileName)
	{
		$fileInfo = new FileInfo;

		if (!$fileName)
		{
			return $fileInfo;
		}

		if (! file_exists($fileName))
		{
			return $fileInfo;
		}

		$lines = file($fileName);

		if (! $lines)
		{
			return $fileInfo;
		}

		$fileInfo->fileName = $fileName;

		$info = '';
		$parsing = false;

		foreach ($lines as $line)
		{
			$line = trim($line);

			if (! $line)
			{
				// First empty line stops head parsing
				$parsing = true;

				continue;
			}

			if (strpos($line, ';') === 0
				|| strpos($line, '#') === 0)
			{
				if ($parsing)
				{
					$info .= $line . "\n";
				}
				else
				{
					$fileInfo->head .= $line . "\n";
				}

				continue;
			}

			$pos = strpos($line, '=');

			if (! $pos)
			{
				$info .= $line . "\n";

				continue;
			}

			$key = trim(substr($line, 0, $pos));
			$value = trim(substr($line, $pos + 1), ' "');

			$e = new \stdClass;
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
	 * @param   FileInfo   $fileInfo  The FileInfo object.
	 * @param   \stdClass  $options   JObject
	 *
	 * @return string
	 */
	public function generate(FileInfo $fileInfo, $options)
	{
		$out = [];

		if ($fileInfo->head)
		{
			$out[] = trim($fileInfo->head);
			$out[] = '';
		}

		$translations = $fileInfo->strings;

		foreach ($translations as $key => $string)
		{
			if ($string->info)
			{
				$out[] = '';
				$out[] = trim($string->info);
			}

			$out[] = htmlspecialchars($key) . '=' . htmlspecialchars($string->string);
		}

		$out[] = '';

		return implode("\n", $out);
	}
}
