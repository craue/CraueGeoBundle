<?php

namespace Craue\GeoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Registration of the extension via DI.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
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

		$container->prependExtensionConfig('doctrine', array(
			'orm' => array(
				'dql' => array(
					'numeric_functions' => array(
						$config['functions']['geo_distance'] => 'Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance',
						$config['functions']['geo_distance_by_postal_code'] => 'Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistanceByPostalCode',
					),
				),
			),
		));
	}

}
