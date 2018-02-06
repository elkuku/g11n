<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use ElKuKu\G11n\Support\ExtensionHelper;

use PHPUnit\Framework\TestCase;

/**
 * Class FileInfoTest.
 *
 * @since  1.0
 */
class ExtensionHelperTest extends TestCase
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

	/**
	 * Test method.
	 *
	 * @expectedException \ElKuKu\G11n\G11nException
	 *
	 * @return void
	 */
	public function testUndefinedDomain()
	{
		ExtensionHelper::getDomainPath('{undefined}');
	}

	/**
	 * Test method.
	 *
	 * @expectedException \ElKuKu\G11n\G11nException
	 *
	 * @return void
	 */
	public function testSplitFailure()
	{
		ExtensionHelper::split('too.many.args');
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testIsExtension()
	{
		ExtensionHelper::addDomainPath('foo', TEST_ROOT . '/tests/testLangDir');

		$this->assertThat(
			ExtensionHelper::isExtension('testExtension', 'foo'),
			$this->equalTo(true)
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testIsNotExtension()
	{
		ExtensionHelper::addDomainPath('foo', 'bar');
		$this->assertThat(
			ExtensionHelper::isExtension('bla', 'foo'),
			$this->equalTo(false)
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testDottedExtensionPath()
	{
		$this->assertThat(
			ExtensionHelper::getExtensionPath('foo.bar'),
			$this->equalTo('foo')
		);
	}
}
