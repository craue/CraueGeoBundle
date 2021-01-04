<?php

namespace Craue\GeoBundle\Doctrine\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Usage: GEO_DISTANCE(latOrigin, lngOrigin, latDestination, lngDestination)
 * Returns: distance in km
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2021 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class GeoDistance extends FunctionNode {

	const EARTH_DIAMETER = 12742; // 2 * Earth's radius (6371 km)

	protected $latOrigin;
	protected $lngOrigin;
	protected $latDestination;
	protected $lngDestination;

	public function parse(Parser $parser) {
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->latOrigin = $parser->ArithmeticExpression();
		$parser->match(Lexer::T_COMMA);
		$this->lngOrigin = $parser->ArithmeticExpression();
		$parser->match(Lexer::T_COMMA);
		$this->latDestination = $parser->ArithmeticExpression();
		$parser->match(Lexer::T_COMMA);
		$this->lngDestination = $parser->ArithmeticExpression();
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}

	public function getSql(SqlWalker $sqlWalker) {
		/*
		 * Giving each argument only once and using %1$s, %2$s, ... doesn't work. Would result in:
		 * SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
		 */
		// formula adapted from http://www.scribd.com/doc/2569355/Geo-Distance-Search-with-MySQL
		// originally returns distance in miles: 3956 * 2 * ASIN(SQRT(POWER(SIN((orig.lat - dest.lat) * PI()/180 / 2), 2) + COS(orig.lat * PI()/180) * COS(dest.lat * PI()/180) * POWER(SIN((orig.lon - dest.lon) * PI()/180 / 2), 2)))
		return sprintf(
			$this->getSqlWithPlaceholders(),
			self::EARTH_DIAMETER,
			$sqlWalker->walkArithmeticPrimary($this->latOrigin),
			$sqlWalker->walkArithmeticPrimary($this->latDestination),
			$sqlWalker->walkArithmeticPrimary($this->latOrigin),
			$sqlWalker->walkArithmeticPrimary($this->latDestination),
			$sqlWalker->walkArithmeticPrimary($this->lngOrigin),
			$sqlWalker->walkArithmeticPrimary($this->lngDestination)
		);
	}

	protected function getSqlWithPlaceholders() {
		return '%s * ASIN(SQRT(POWER(SIN((%s - %s) * PI()/360), 2) + COS(%s * PI()/180) * COS(%s * PI()/180) * POWER(SIN((%s - %s) * PI()/360), 2)))';
	}

}
