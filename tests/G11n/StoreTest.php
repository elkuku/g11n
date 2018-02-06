<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use ElKuKu\G11n\Support\Store;

use PHPUnit\Framework\TestCase;

/**
 * Class FileInfoTest.
 *
 * @since  1.0
 */
class StoreTest extends TestCase
{
	/**
	 * @var Store
	 */
	private $object = null;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->object = new Store;
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function test()
	{
		$old = $this->object->get('langPath');

		$this->object->set('langPath', '{test}');

		$this->assertThat(
			$this->object->get('langPath'),
			$this->equalTo('{test}')
		);

		$this->object->set('langPath', $old);
	}

	/**
	 * Test method.
	 *
	 * @expectedException  \UnexpectedValueException
	 *
	 * @return void
	 */
	public function testFailure()
	{
		$this->object->get('{undefined}');
	}

	/**
	 * Test method.
	 *
	 * @expectedException  \UnexpectedValueException
	 *
	 * @return void
	 */
	public function testFailure2()
	{
		$this->object->set('{undefined}', '');
	}
}
