<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language\Parser;

use g11n\Support\FileInfo;

/**
 * Class Language.
 *
 * Base class for language file parsers.
 */
abstract class Language
{
	/**
	 * File extension.
	 *
	 * @var string
	 */
	protected $ext;

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
	 * Parse a po style language file.
	 *
	 * @param string $fileName Absolute path to the language file.
	 *
	 * @return FileInfo
	 */
	abstract public function parse($fileName);

	/**
	 * Generate a language file.
	 *
	 * @param FileInfo $fileInfo
	 * @param \stdClass      $options JObject
	 *
	 * @return string
	 */
	abstract public function generate(FileInfo $fileInfo, $options);
}