<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use ElKuKu\G11n\Support\ExtensionHelper;
use PHPUnit_Framework_TestCase;

/**
 * Class FileInfoTest.
 *
 * @since  1.0
 */
class ExtensionHelperTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testSetDirName()
	{
		$old = ExtensionHelper::$langDirName;

		ExtensionHelper::setDirName('{test}');

		$this->assertThat(
			ExtensionHelper::$langDirName,
			$this->equalTo('{test}')
		);

		ExtensionHelper::setDirName($old);
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
