<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;
use Doctrine\ORM\Mapping\MappingException;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2020 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DisablePostalCodeEntityTest extends IntegrationTestCase {

	/**
	 * Ensure that the GeoPostalCode class is not registered as a Doctrine entity.
	 *
	 * @dataProvider dataDisablePostalCodeEntity
	 */
	public function testDisablePostalCodeEntity($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => 'disablePostalCodeEntity_' . $platform, 'config' => $config]);

		$this->expectException(MappingException::class);
		/*
		 * Doctrine ORM 2.3.0 would throw an exception with message
		 *   Class "Craue\GeoBundle\Entity\GeoPostalCode" sub class of "" is not a valid entity or mapped super class.
		 * here, so just verify the relevant parts of the message. This has been fixed in ORM 2.3.1.
		 */
		$this->expectExceptionMessageMatches(sprintf('/^%s.*%s$/s', preg_quote('Class "Craue\GeoBundle\Entity\GeoPostalCode"'), preg_quote('is not a valid entity or mapped super class.')));

		$this->getRepo();
	}

	public function dataDisablePostalCodeEntity() {
		return self::duplicateTestDataForEachPlatform([
			[],
		], 'config_disablePostalCodeEntity.yml');
	}

}
