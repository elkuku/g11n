<?php
/**
 * User: elkuku
 * Date: 21.06.13
 * Time: 15:57
 */

namespace g11n\Support;

/**
 * FileInfo description class.
 *
 * @package g11n
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
	 * @param string $property Property name
	 *
	 * @return mixed
	 */
	public function get($property)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		JFactory::getApplication()->enqueueMessage('Undefined property ' . __CLASS__ . '::' . $property, 'error');
	}
}


