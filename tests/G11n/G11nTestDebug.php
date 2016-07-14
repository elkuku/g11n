<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use g11n\g11n;

use PHPUnit_Framework_TestCase;

/**
 * Class G11nTestDebug.
 *
 * @since  1.0
 */
class G11nTestDebug extends PHPUnit_Framework_TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		g11n::setCurrent('xx-XX');
		g11n::setCacheDir(__DIR__ . '/../cache');
		g11n::cleanCache();
		g11n::addDomainPath('testDomain', __DIR__ . '/../testLangDir');
		g11n::loadLanguage('testExtension', 'testDomain');
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testDebugYTranslate()
	{
		g11n::setDebug(true);

		$this->assertThat(
			g11n3t('Hello test'),
			$this->equalTo('+-HHHH-+')
		);
	}
}
