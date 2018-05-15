<?php
/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Support;

/**
 * The G11n store description class.
 *
 * @since  1
 */
class Store
{
	private $strings = array();

	private $stringsPlural = array();

	private $stringsJs = array();

	private $stringsJsPlural = array();

	private $pluralForms = '';

	private $langPath = '';

	private $cachePath = '';

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

	/**
	 * Set a property.
	 *
	 * @param   string  $property  Property name
	 * @param   mixed   $value     The value to set
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
