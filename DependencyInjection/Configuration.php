<?php

namespace Craue\GeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Semantic bundle configuration.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();

		$treeBuilder->root('craue_geo')
			->children()
				->arrayNode('functions')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('geo_distance')->defaultValue('GEO_DISTANCE')->end()
						->scalarNode('geo_distance_by_postal_code')->defaultValue('GEO_DISTANCE_BY_POSTAL_CODE')->end()
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}

}
