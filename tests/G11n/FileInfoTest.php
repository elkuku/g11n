<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use ElKuKu\G11n\Support\FileInfo;

use PHPUnit_Framework_TestCase;

/**
 * Class FileInfoTest.
 *
 * @since  1.0
 */
class FileInfoTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var FileInfo
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
		$this->object = new FileInfo;
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function test()
	{
		$this->object->fileName = '{test}';

		$this->assertThat(
			$this->object->get('fileName'),
			$this->equalTo('{test}')
		);
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
}
