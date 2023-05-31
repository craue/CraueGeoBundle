<?php

namespace Craue\GeoBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueGeoBundle extends Bundle {

	/**
	 * {@inheritDoc}
     *
     * @return void
	 */
	public function build(ContainerBuilder $container) {
		parent::build($container);
		$this->addRegisterMappingsPass($container);
	}

	/**
	 * @param ContainerBuilder $container
	 */
	private function addRegisterMappingsPass(ContainerBuilder $container) {
		$mappings = [
			realpath(__DIR__ . '/Resources/config/doctrine-mapping') => 'Craue\GeoBundle\Entity',
		];

		$container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, [], 'craue_geo.register_entity.postal_code'));
	}

}
