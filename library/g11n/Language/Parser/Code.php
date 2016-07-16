<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language\Parser;

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
		return (string) __CLASS__;
	}

	/**
	 * Set the language format.
	 *
	 * @param   string  $langFormatIn  The language format e.g. ini
	 *
	 * @return void
	 */
	abstract public function setLangFormat($langFormatIn);

	/**
	 * Parse a file.
	 *
	 * @param   string  $fileName  File to parse.
	 *
	 * @return object g11nFileInfo
	 */
	abstract public function parse($fileName);
}
