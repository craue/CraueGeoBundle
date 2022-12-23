<?php

namespace Craue\GeoBundle\Tests;

use Craue\GeoBundle\Entity\GeoPostalCode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	public const PLATFORM_MYSQL = 'mysql';
	public const PLATFORM_POSTGRESQL = 'postgresql';

	public static function getValidPlatformsWithRequiredExtensions(): array
    {
		return [
			self::PLATFORM_MYSQL => 'pdo_mysql',
			self::PLATFORM_POSTGRESQL => 'pdo_pgsql',
		];
	}

	/**
	 * @var bool[]
	 */
	private static array $databaseInitialized = [];

	/**
	 * @param string $testName The name of the test, set by PHPUnit when called directly as a {@code dataProvider}.
	 * @param string $baseConfig The base config filename.
	 * @return array<int, array<int, mixed>>
	 */
	public static function getPlatformConfigs(string $testName, string $baseConfig = 'config.yml'): array
    {
		$testData = [];

		foreach (self::getValidPlatformsWithRequiredExtensions() as $platform => $extension) {
			$testData[] = [$platform, [$baseConfig, sprintf('config_flavor_%s.yml', $platform)], $extension];
		}

		return $testData;
	}

	public static function duplicateTestDataForEachPlatform(array $allTestData, string $baseConfig = 'config.yml'): array
    {
		$testData = [];

		foreach ($allTestData as $oneTestData) {
			foreach (self::getPlatformConfigs('', $baseConfig) as $envConf) {
				$testData[] = array_merge($envConf, $oneTestData);
			}
		}

		return $testData;
	}

	/**
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistance(float $lat,
                                             float $lng,
                                             ?float $maxRadiusInKm = null,
                                             bool  $addRadiusOptimization = false): array
    {
		$distanceFunction = 'GEO_DISTANCE(:lat, :lng, poi.lat, poi.lng)';

		$qb = $this->getRepo()->createQueryBuilder('poi')
			->select(sprintf('poi, %s AS distance', $distanceFunction))
			->setParameter('lat', $lat)
			->setParameter('lng', $lng)
			->groupBy('poi')
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
			if ($addRadiusOptimization) {
				$this->addRadiusOptimization($qb, $lat, $lng, $maxRadiusInKm);
			}

			$qb
				->having(sprintf('%s <= :radius', $this->platformSupportsAliasInHavingClause() ? 'distance' : $distanceFunction))
				->setParameter('radius', $maxRadiusInKm)
			;
		}

		return $qb->getQuery()->getResult();
	}

	/**
	 * @return GeoPostalCode[]
	 */
	protected function getPoisPerGeoDistanceByPostalCode(string $country,
                                                         string $postalCode,
                                                         ?float $maxRadiusInKm = null,
                                                         bool   $addRadiusOptimization = false): array
    {
		$distanceFunction = 'GEO_DISTANCE_BY_POSTAL_CODE(:country, :postalCode, poi.country, poi.postalCode)';

		$qb = $this->getRepo()->createQueryBuilder('poi')
			->select(sprintf('poi, %s AS distance', $distanceFunction))
			->setParameter('country', $country)
			->setParameter('postalCode', $postalCode)
			->groupBy('poi')
			->orderBy('distance')
		;

		if ($maxRadiusInKm !== null) {
			if ($addRadiusOptimization) {
				$qbOrigin = $this->getRepo()->createQueryBuilder('poi')
					->andWhere('poi.country = :country')
					->andWhere('poi.postalCode = :postalCode')
					->setParameter('country', $country)
					->setParameter('postalCode', $postalCode)
				;
				$origin = $qbOrigin->getQuery()->getOneOrNullResult();
				if ($origin !== null) {
					$this->addRadiusOptimization($qb, $origin->getLat(), $origin->getLng(), $maxRadiusInKm);
				}
			}

			$qb
				->having(sprintf('%s <= :radius', $this->platformSupportsAliasInHavingClause() ? 'distance' : $distanceFunction))
				->setParameter('radius', $maxRadiusInKm)
			;
		}

		return $qb->getQuery()->getResult();
	}

	/**
	 * Adds the radius optimization mentioned in {@see http://www.scribd.com/doc/2569355/Geo-Distance-Search-with-MySQL} (pages 11-13) to
	 * the given {@code QueryBuilder} instance.
	 */
	private function addRadiusOptimization(QueryBuilder $qb, float $latOrigin, float $lngOrigin, float $maxRadiusInKm): void
    {
		$latDistance = 111.2; // distance between two latitudes is about 111.2 km
		$latDiff = $maxRadiusInKm / $latDistance;
		$lngDiff = $maxRadiusInKm / abs(cos(deg2rad($latOrigin)) * $latDistance);

		$qb
			->andWhere('poi.lat BETWEEN :lat1 AND :lat2')
			->andWhere('poi.lng BETWEEN :lng1 AND :lng2')
			->setParameter('lat1', $latOrigin - $latDiff)
			->setParameter('lat2', $latOrigin + $latDiff)
			->setParameter('lng1', $lngOrigin - $lngDiff)
			->setParameter('lng2', $lngOrigin + $lngDiff)
		;
	}

	/**
	 * @return boolean Whether the database platform supports using aliases in the HAVING clause.
	 */
	private function platformSupportsAliasInHavingClause(): bool
    {
		return $this->getEntityManager()->getConnection()->getDatabasePlatform()->getName() !== self::PLATFORM_POSTGRESQL;
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function createKernel(array $options = []) : KernelInterface {
		$environment = $options['environment'] ?? 'test';
		$configFile = $options['config'] ?? 'config.yml';

		return new AppKernel($environment, $configFile);
	}

	/**
	 * Initializes a client and prepares the database.
	 * @param string|null $requiredExtension Required PHP extension.
	 * @param array $options Options for creating the client.
	 * @param bool $cleanDatabase If the database should be cleaned in case it already exists.
	 * @return AbstractBrowser
	 */
	protected function initClient(?string $requiredExtension, array $options = [], bool $cleanDatabase = true): AbstractBrowser
    {
		if ($requiredExtension !== null && !extension_loaded($requiredExtension)) {
			$this->markTestSkipped(sprintf('Extension "%s" is not loaded.', $requiredExtension));
		}

		$client = static::createClient($options);
		$environment = static::$kernel->getEnvironment();

		// Avoid completely rebuilding the database for each test. Create it only once per environment. After that, cleaning it is enough.
		if (!array_key_exists($environment, self::$databaseInitialized) || !self::$databaseInitialized[$environment]) {
			$this->rebuildDatabase();
			self::$databaseInitialized[$environment] = true;
		} elseif ($cleanDatabase) {
			$this->removeAllGeoPostalCodes();
		}

		return $client;
	}

	protected function rebuildDatabase(): void
    {
		$em = $this->getEntityManager();
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool = new SchemaTool($em);

		$schemaTool->dropSchema($metadata);
		$schemaTool->createSchema($metadata);
	}

	/**
	 * Persists a {@code GeoPostalCode}.
	 */
	protected function persistGeoPostalCode(string $country, string $postalCode, float $lat, float $lng): GeoPostalCode
    {
		$entity = new GeoPostalCode();
		$entity->setCountry($country);
		$entity->setPostalCode($postalCode);
		$entity->setLat($lat);
		$entity->setLng($lng);

		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush();

		return $entity;
	}

	/**
	 * Persists a number of {@code GeoPostalCode}s using non-random dummy data.
	 */
	protected function persistDummyGeoPostalCodes(int $number): void
    {
		$em = $this->getEntityManager();

		for ($i = 0; $i < $number; ++$i) {
			$entity = new GeoPostalCode();
			$entity->setCountry('DE');
			$entity->setPostalCode((string) $i);
			$entity->setLat((float) ('52.' . $i));
			$entity->setLng((float) ('13.'.$i));
			$em->persist($entity);

			if ((($i + 1) % 10000) === 0) {
				$em->flush();
				$em->clear();
			}
		}

		$em->flush();
	}

	/**
	 * Removes all {@code GeoPostalCode}s.
	 */
	protected function removeAllGeoPostalCodes(): void
    {
		$em = $this->getEntityManager();

		foreach ($this->getRepo()->findAll() as $entity) {
			$em->remove($entity);
		}

		$em->flush();
	}

	protected function getEntityManager(): EntityManager
    {
		return $this->getService('doctrine')->getManager();
	}

	protected function getRepo(): EntityRepository
    {
		return $this->getEntityManager()->getRepository(GeoPostalCode::class);
	}

	/**
	 * @param string $id The service identifier.
	 * @return object The associated service.
	 */
	protected function getService(string $id): object
    {
		return static::$kernel->getContainer()->get($id);
	}

}
