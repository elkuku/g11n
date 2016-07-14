<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Support;

/**
 * FileInfo description class.
 *
 * @since  1
 */
class FileInfo
{
	public $fileName = '';

	public $mode = '';

	public $head = '';

	public $pluralForms = '';

	public $strings = array();

	public $stringsPlural = array();

	public $isCore = false;

	public $lines = '';

	public $langTag = '';

	/**
	 * Get a property.
	 *
	 * @param   string  $property  Property name.
	 *
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	public function get($property)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		throw new \UnexpectedValueException('Get undefined property ' . __CLASS__ . '::' . $property);
	}
}
