<?php

namespace Craue\GeoBundle\Doctrine\Query\Postgresql;

use Craue\GeoBundle\Doctrine\Query\Mysql\GeoDistance as BaseGeoDistance;

/**
 * {@inheritDoc}
 */
class GeoDistance extends BaseGeoDistance {

	protected function getSqlWithPlaceholders(): string {
		return '%s * ASIN(SQRT(POWER(SIN((CAST(%s AS numeric) - CAST(%s AS numeric)) * PI()/360), 2) + COS(%s * PI()/180) * COS(%s * PI()/180) * POWER(SIN((CAST(%s AS numeric) - CAST(%s AS numeric)) * PI()/360), 2)))';
	}

}
