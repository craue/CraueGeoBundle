<?php

namespace Craue\GeoBundle\Doctrine\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Usage: GEO_DISTANCE_BY_POSTAL_CODE(countryOrigin, postalCodeOrigin, countryDestination, postalCodeDestination)
 * Returns: distance in km
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2016 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class GeoDistanceByPostalCode extends FunctionNode {

	protected $countryOrigin;
	protected $postalCodeOrigin;
	protected $countryDestination;
	protected $postalCodeDestination;

	public function parse(Parser $parser) {
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->countryOrigin = $parser->StringPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->postalCodeOrigin = $parser->StringPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->countryDestination = $parser->StringPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->postalCodeDestination = $parser->StringPrimary();
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}

	public function getSql(SqlWalker $sqlWalker) {
		return sprintf(
			'%s * ASIN(SQRT(POWER(SIN(((SELECT `lat` FROM `craue_geo_postalcode` WHERE `country` = %s AND `postal_code` = %s) - (SELECT `lat` FROM `craue_geo_postalcode` WHERE `country` = %s AND `postal_code` = %s)) * PI()/360), 2) + COS((SELECT `lat` FROM `craue_geo_postalcode` WHERE `country` = %s AND `postal_code` = %s) * PI()/180) * COS((SELECT `lat` FROM `craue_geo_postalcode` WHERE `country` = %s AND `postal_code` = %s) * PI()/180) * POWER(SIN(((SELECT `lng` FROM `craue_geo_postalcode` WHERE `country` = %s AND `postal_code` = %s) - (SELECT `lng` FROM `craue_geo_postalcode` WHERE `country` = %s AND `postal_code` = %s)) *  PI()/360), 2)))',
			GeoDistance::EARTH_DIAMETER,
			$sqlWalker->walkStringPrimary($this->countryOrigin),
			$sqlWalker->walkStringPrimary($this->postalCodeOrigin),
			$sqlWalker->walkStringPrimary($this->countryDestination),
			$sqlWalker->walkStringPrimary($this->postalCodeDestination),
			$sqlWalker->walkStringPrimary($this->countryOrigin),
			$sqlWalker->walkStringPrimary($this->postalCodeOrigin),
			$sqlWalker->walkStringPrimary($this->countryDestination),
			$sqlWalker->walkStringPrimary($this->postalCodeDestination),
			$sqlWalker->walkStringPrimary($this->countryOrigin),
			$sqlWalker->walkStringPrimary($this->postalCodeOrigin),
			$sqlWalker->walkStringPrimary($this->countryDestination),
			$sqlWalker->walkStringPrimary($this->postalCodeDestination)
		);
	}

}
