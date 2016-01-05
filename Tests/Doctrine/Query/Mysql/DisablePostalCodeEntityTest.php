<?php

namespace Craue\GeoBundle\Tests\Doctrine\Query\Mysql;

use Craue\GeoBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2016 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DisablePostalCodeEntityTest extends IntegrationTestCase {

	/**
	 * Ensure that the GeoPostalCode class is not registered as a Doctrine entity.
	 *
	 * @expectedException Doctrine\ORM\Mapping\MappingException
	 * @expectedExceptionMessage Class "Craue\GeoBundle\Entity\GeoPostalCode"
	 * @expectedExceptionMessage is not a valid entity or mapped super class.
	 *
	 * Note: Doctrine ORM 2.3.0 would throw an exception with message
	 *   Class "Craue\GeoBundle\Entity\GeoPostalCode" sub class of "" is not a valid entity or mapped super class.
	 * here, so just verify the relevant parts of the message. This has been fixed in ORM 2.3.1.
	 */
	public function testDisablePostalCodeEntity() {
		$this->initClient(array('environment' => 'disablePostalCodeEntity', 'config' => 'config_disablePostalCodeEntity.yml'));
		$this->getRepo();
	}

}
