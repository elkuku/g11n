<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language\Parser;

use ElKuKu\G11n\Support\FileInfo;

/**
 * Class Code.
 *
 * @since  1
 */
abstract class Code
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
	public function getExt() : string
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
		return  __CLASS__;
	}

	/**
	 * Set the language format.
	 *
	 * @param   string  $langFormatIn  The language format e.g. ini
	 *
	 * @return void
	 */
	abstract public function setLangFormat(string $langFormatIn) : void;

	/**
	 * Parse a file.
	 *
	 * @param   string  $fileName  File to parse.
	 *
	 * @return FileInfo
	 */
	abstract public function parse(string $fileName) : FileInfo;
}
