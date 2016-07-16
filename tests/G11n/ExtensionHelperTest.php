<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use ElKuKu\G11n\Tests\Support\Helper;
use g11n\Support\ExtensionHelper;
use PHPUnit_Framework_TestCase;

/**
 * Class FileInfoTest.
 *
 * @since  1.0
 */
class ExtensionHelperTest extends PHPUnit_Framework_TestCase
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
		//(new Helper());
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testSetDirName()
	{
		ExtensionHelper::setDirName('{test}');

		$this->assertThat(
			ExtensionHelper::$langDirName,
			$this->equalTo('{test}')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testSetCacheDir()
	{
		ExtensionHelper::setCacheDir(TEST_ROOT . '/tests/cache');

		$this->assertThat(
			ExtensionHelper::getCacheDir(),
			$this->equalTo(TEST_ROOT . '/tests/cache/g11n')
		);
	}

}
