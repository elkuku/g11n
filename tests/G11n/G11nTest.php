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
 * Class G11nTest.
 *
 * @since  1.0
 */
class G11nTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since  1.0
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

	public function testHello()
	{
		$this->assertThat(
			g11n3t('Hello test'),
			$this->equalTo('HHHH')
		);
	}

	public function testNotFound()
	{
		$this->assertThat(
			g11n3t('not found'),
			$this->equalTo('not found')
		);
	}

	public function testGetsomething()
	{
		$this->assertThat(
			g11n::get('lang'),
			$this->equalTo('xx-XX')
		);
	}

	/**
	 * @expectedException \UnexpectedValueException
	 */
	public function testGetsomethingInvalid()
	{
		g11n::get('somethingInvalid');
	}

	public function testParams()
	{
		$this->assertThat(
			g11n3t('Hello %test% case', ['%test%' => 'TEST']),
			$this->equalTo('Hällo TEST Foo')
		);
	}

	public function testParamsNotFound()
	{
		$this->assertThat(
			g11n3t('Hello %test% Case', ['%test%' => 'TEST']),
			$this->equalTo('Hello TEST Case')
		);
	}

	public function testGetDefault()
	{
		$this->assertThat(
			g11n::getDefault(),
			$this->equalTo('en-GB')
		);
	}

	public function testSetDefault()
	{
		g11n::setDefault('yy-YY');

		$this->assertThat(
			g11n::getDefault(),
			$this->equalTo('yy-YY')
		);
	}

	public function testGetCurrent()
	{
		$this->assertThat(
			g11n::getCurrent(),
			$this->equalTo('xx-XX')
		);
	}

	public function testPlural0()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 0),
			$this->equalTo('Ho')
		);
	}

	public function testPlural1()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 1),
			$this->equalTo('Hey')
		);
	}

	public function testPlural2()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 2),
			$this->equalTo('Ho')
		);
	}

	public function testPlural3()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 3),
			$this->equalTo('Ho')
		);
	}
}
