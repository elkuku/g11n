<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Support;

/**
 * FileInfo description class.
 *
 * @since  1
 */
class FileInfo
{
	/**
	 * @var string
	 */
	public $fileName = '';

	/**
	 * @var string
	 */
	public $mode = '';

	/**
	 * @var string
	 */
	public $head = '';

	/**
	 * @var string
	 */
	public $pluralForms = '';

	/**
	 * @var array
	 */
	public $strings = [];

	/**
	 * @var array
	 */
	public $stringsPlural = [];

	/**
	 * @var boolean
	 */
	public $isCore = false;

	/**
	 * @var string
	 */
	public $lines = '';

	/**
	 * @var string
	 */
	public $langTag = '';

	/**
	 * Get a property.
	 *
	 * @param   string  $property  Property name.
	 *
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	public function get(string $property)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		throw new \UnexpectedValueException('Get undefined property ' . __CLASS__ . '::' . $property);
	}
}
