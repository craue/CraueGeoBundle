<?php

namespace Craue\GeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Semantic bundle configuration.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() : TreeBuilder {
		$treeBuilder = new TreeBuilder('craue_geo');

		$treeBuilder->getRootNode()
			->children()
				->enumNode('flavor')->values(['none', 'mysql', 'postgresql'])->defaultValue('mysql')->end()
				->booleanNode('enable_postal_code_entity')->defaultValue(true)->end()
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
