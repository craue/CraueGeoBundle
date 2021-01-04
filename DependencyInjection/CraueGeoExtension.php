<?php

namespace Craue\GeoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Registration of the extension via DI.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2021 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueGeoExtension extends Extension implements PrependExtensionInterface {

	/**
	 * {@inheritDoc}
	 */
	public function load(array $config, ContainerBuilder $container) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepend(ContainerBuilder $container) {
		$config = $this->processConfiguration(new Configuration(), $container->getExtensionConfig($this->getAlias()));

		if ($config['enable_postal_code_entity'] === true) {
			$container->setParameter('craue_geo.register_entity.postal_code', true);
		}

		if ($config['flavor'] !== 'none') {
			$functionClassesNamespace = sprintf('Craue\GeoBundle\Doctrine\Query\%s', ucfirst($config['flavor']));

			$container->prependExtensionConfig('doctrine', [
				'orm' => [
					'dql' => [
						'numeric_functions' => [
							$config['functions']['geo_distance'] => sprintf('%s\GeoDistance', $functionClassesNamespace),
							$config['functions']['geo_distance_by_postal_code'] => sprintf('%s\GeoDistanceByPostalCode', $functionClassesNamespace),
						],
					],
				],
			]);
		}
	}

}
