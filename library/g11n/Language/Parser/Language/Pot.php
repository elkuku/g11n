<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language\Parser\Language;

use ElKuKu\G11n\Support\FileInfo;

/**
 * Enter description here ...
 *
 * @since  1
 */
class Pot extends Parser\Language
{
	/**
	 * @var string
	 */
	protected $ext = 'pot';

	/**
	 * Parse a language file.
	 *
	 * @param   string  $fileName  Absolute path to the file.
	 *
	 * @return FileInfo
	 */
	public function parse($fileName)
	{
		$fileInfo = new FileInfo;

		$fileInfo->fileName = $fileName;

		if ( ! file_exists($fileName))
		{
			return $fileInfo;
		}

		$lines = file($fileName);

		if ( ! $lines)
		{
			return $fileInfo;
		}

		$msgid = '';
		$msgstr = '';
		$msg_plural = '';
		$msg_plurals = array();

		$head = '';

		$info = '';

		$state = -1;

		foreach ($lines as $line)
		{
			$line = trim($line);

			$match = array();

			switch ($state)
			{
				case - 1 :
					// Start parsing
					if ( ! $line)
					{
						// First empty line stops header
						$state = 0;
					}
					else
					{
						$head .= $line . "\n";
					}
					break;
				case 0:
					// Waiting for msgid
					if (preg_match('/^msgid "(.*)"$/', $line, $match))
					{
						$msgid = stripcslashes($match[1]);
						$state = 1;
					}
					else
					{
						$info .= $line . "\n";
					}
					break;
				case 1:
					// Reading msgid, waiting for msgstr
					if (preg_match('/^msgstr "(.*)"$/', $line, $match))
					{
						$msgstr = stripcslashes($match[1]);
						$state = 2;
					}
					elseif (preg_match('/^msgid_plural "(.*)"$/', $line, $match))
					{
						$msg_plural = stripcslashes($match[1]);
						$state = 1;
					}
					elseif (preg_match('/^msgstr\[(\d+)\] "(.*)"$/', $line, $match))
					{
						$msg_plurals[stripcslashes($match[1])] = stripcslashes($match[2]);
						$state = 1;
					}
					elseif (preg_match('/^"(.*)"$/', $line, $match))
					{
						$msgid = stripcslashes($match[1]);
					}
					break;
				case 2:
					// Reading msgstr, waiting for blank
					if (preg_match('/^"(.*)"$/', $line, $match))
					{
						$msgstr = stripcslashes($match[1]);
					}
					elseif (empty($line))
					{
						// We have a complete entry
						$e = new \stdClass;
						$e->info = $info;
						$e->string = $msgstr;
						$fileInfo->strings[$msgid] = $e;

						$state = 0;
						$info = '';
					}
					break;
			}

			// Comment or blank line?
			if (empty($line)
				|| preg_match('/^#/', $line))
			{
				if ($msg_plural)
				{
					$t = new \stdClass;
					$t->plural = $msg_plural;
					$t->forms = $msg_plurals;
					$t->info = $info;
					$fileInfo->stringsPlural[$msgid] = $t;

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
	 * @param   FileInfo   $checker  FileInfo object.
	 * @param   \stdClass  $options  Options.
	 *
	 * @return string
	 */
	public function generate(FileInfo $checker, $options)
	{
		$dateTimeZone = new \DateTimeZone(date_default_timezone_get());
		$dateTime = new \DateTime('now', $dateTimeZone);

		$timeOffset = $dateTimeZone->getOffset($dateTime) / 3600;

		$contents = array();
		$strings = $checker->strings;

		$stringsPlural = $checker->stringsPlural;

		$contents[] = '' . "
# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR Free Software Foundation, Inc.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid \"\"
msgstr \"\"
\"Project-Id-Version: PACKAGE VERSION\\n\"
\"Report-Msgid-Bugs-To: wp-polyglots@lists.automattic.com\\n\"
\"POT-Creation-Date: " . date('Y-m-d H:i ') . $timeOffset . "00\\n\"
\"PO-Revision-Date: 2010-MO-DA HO:MI+ZONE\\n\"
\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"
\"Language-Team: LANGUAGE <LL@li.org>\\n\"
\"Content-Type: text/plain; charset=CHARSET\\n\"
\"Content-Transfer-Encoding: 8bit\\n\"
\"X-Generator: G11n\\n\"
\"MIME-Version: 1.0\\n\"
\"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n\"
";

		foreach ($strings as $key => $string)
		{
			if ($string->info)
			{
				$contents[] = trim($string->info);
			}

			if ($options->get('includeLineNumbers'))
			{
				foreach ($string->files as $f => $locs)
				{
					foreach ($locs as $loc)
					{
						$contents[] = "#: $f:$loc";
					}
				}
			}

			$key = html_entity_decode($key);

			$key = addcslashes($key, '"');

			while (strpos($key, "\\\\") != false)
			{
				$key = str_replace('\\\\', '\\', $key);
			}

			while (strpos($key, "\'") != false)
			{
				$key = str_replace("\'", "'", $key);
			}

			$contents[] = 'msgid "' . htmlentities($key) . '"';
			$contents[] = 'msgstr ""';
			$contents[] = '';
		}

		foreach ($stringsPlural as $key => $string)
		{
			if ($options->get('includeLineNumbers'))
			{
				foreach ($string->files as $f => $locs)
				{
					foreach ($locs as $loc)
					{
						$contents[] = "#: $f:$loc";
					}
				}
			}

			$key = html_entity_decode($key);
			$value = html_entity_decode($string->pluralForms[1]);

			$contents[] = 'msgid "' . htmlspecialchars($key) . '"';
			$contents[] = 'msgid_plural "' . htmlspecialchars($value) . '"';
			$contents[] = 'msgstr[0] ""';
			$contents[] = 'msgstr[1] ""';
			$contents[] = '';
		}

		return implode("\n", $contents);
	}
}
