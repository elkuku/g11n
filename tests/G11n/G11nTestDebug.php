<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use ElKuKu\G11n\G11n;
use ElKuKu\G11n\Support\ExtensionHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class G11nTestDebug.
 *
 * @since  1.0
 */
class G11nTestDebug extends TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 * @throws \ElKuKu\G11n\G11nException
	 */
	protected function setUp()
	{
		ExtensionHelper::setCacheDir(TEST_ROOT . '/tests/cache');
		ExtensionHelper::cleanCache();
		ExtensionHelper::addDomainPath('testDomain', TEST_ROOT . '/tests/testLangDir');

		G11n::setCurrent('xx-XX');
		G11n::loadLanguage('testExtension', 'testDomain');
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		ExtensionHelper::cleanCache();
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testDebugYTranslate()
	{
		G11n::setDebug(true);

		$this->assertThat(
			g11n3t('Hello test'),
			$this->equalTo('+-HHHH-+')
		);
	}
}
