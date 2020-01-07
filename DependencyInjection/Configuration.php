<?php

namespace Craue\GeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Semantic bundle configuration.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2020 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder('craue_geo');

		if (!method_exists($treeBuilder, 'getRootNode')) {
			// TODO remove as soon as Symfony >= 4.2 is required
			$rootNode = $treeBuilder->root('craue_geo');
		} else {
			$rootNode = $treeBuilder->getRootNode();
		}

		$rootNode
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
