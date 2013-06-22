<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language\Parser;

/**
 * Class Code.
 *
 * @package g11n\Language\Parser
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
		return (string)__CLASS__;
	}

	/**
	 * Set the language format.
	 *
	 * @param string $langFormatIn The language format e.g. ini
	 *
	 * @return void
	 */
	abstract public function setLangFormat($langFormatIn);

	/**
	 * Parse a file.
	 *
	 * @param string $fileName File to parse
	 *
	 * @return object g11nFileInfo
	 */
	abstract public function parse($fileName);
}
