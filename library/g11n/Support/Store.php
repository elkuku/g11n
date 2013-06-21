<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Support;

/**
 * The g11n store description class.
 *
 * @package g11n
 */
class Store
{
	private $strings = array();

	private $stringsPlural = array();

	private $stringsJs = array();

	private $stringsJsPlural = array();

	private $pluralForms = '';

	/**
	 * Get a property.
	 *
	 * @param string $property Property name
	 *
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	public function get($property)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		throw new \UnexpectedValueException('Get undefined property ' . __CLASS__ . '::' . $property);
	}

	/**
	 * Set a property.
	 *
	 * @param string $property Property name
	 * @param mixed  $value    The value to set
	 *
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function set($property, $value)
	{
		if (!isset($this->$property))
		{
			throw new \UnexpectedValueException('Set undefined property ' . __CLASS__ . '::' . $property);
		}

		$this->$property = $value;
	}
}
