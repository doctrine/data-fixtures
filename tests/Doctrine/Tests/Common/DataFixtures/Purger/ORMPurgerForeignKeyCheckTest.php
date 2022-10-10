<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Link;

class ORMPurgerForeignKeyCheckTest extends BaseTest
{
    public const TEST_CLASS_NAME = Link::class;
    public const TEST_TABLE_NAME = 'link';

    /** @return MappingDriver&MockObject */
    protected function getMockMetadataDriver(): MappingDriver
    {
        $metadataDriver = $this->createMock(MappingDriver::class);
        $metadataDriver->method('getAllClassNames')->willReturn([self::TEST_CLASS_NAME]);
        $metadataDriver->method('loadMetadataForClass')
            ->willReturnCallback(static function (string $className, ClassMetadata $metadata) {
                if ($className !== self::TEST_CLASS_NAME) {
                    return;
                }

                $metadata->setPrimaryTable(['name' => self::TEST_TABLE_NAME]);
                $metadata->setIdentifier(['id']);
            });
        $metadataDriver->method('isTransient')->willReturn(false);

        return $metadataDriver;
    }

    /** @return Connection&MockObject */
    protected function getMockConnectionForPlatform(AbstractPlatform $platform): Connection
    {
        $driver = $this->createMock(AbstractDriverMiddleware::class);
        $driver->method('getDatabasePlatform')->willReturn($platform);

        $connection = $this->createMock(Connection::class);
        $connection->method('getDriver')->willReturn($driver);
        $connection->method('getConfiguration')->willReturn(new Configuration());
        $connection->method('getEventManager')->willReturn(new EventManager());
        $connection->method('getDatabasePlatform')->willReturn($platform);

        return $connection;
    }

    /** @dataProvider purgeForDifferentPlatformsProvider */
    public function testPurgeForDifferentPlatforms(AbstractPlatform $platform, int $purgeMode, bool $hasForeignKeyCheckString): void
    {
        $metadataDriver = $this->getMockMetadataDriver();
        $connection     = $this->getMockConnectionForPlatform($platform);

        $config = ORMSetup::createConfiguration(true);
        $config->setMetadataDriverImpl($metadataDriver);

        $em     = EntityManager::create($connection, $config);
        $purger = new ORMPurger($em);
        $purger->setPurgeMode($purgeMode);

        if ($hasForeignKeyCheckString) {
            $connection
                ->expects($this->exactly(4))
                ->method('executeStatement')
                ->withConsecutive(
                    ['SET @DOCTRINE_OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS'],
                    ['SET FOREIGN_KEY_CHECKS = 0'],
                    [$this->stringContains(self::TEST_TABLE_NAME)],
                    ['SET FOREIGN_KEY_CHECKS = @DOCTRINE_OLD_FOREIGN_KEY_CHECKS']
                );
        } else {
            $connection
                ->expects($this->exactly(1))
                ->method('executeStatement')
                ->withConsecutive([$this->stringContains(self::TEST_TABLE_NAME)]);
        }

        $purger->purge();
    }

    /** @return list<array{AbstractPlatform, ORMPurger::PURGE_MODE_*, bool}> */
    public function purgeForDifferentPlatformsProvider()
    {
        return [
            [new MySQLPlatform(), ORMPurger::PURGE_MODE_TRUNCATE, true],
            [new MySQLPlatform(), ORMPurger::PURGE_MODE_DELETE, false],
            [new SqlitePlatform(), ORMPurger::PURGE_MODE_TRUNCATE, false],
            [new SqlitePlatform(), ORMPurger::PURGE_MODE_DELETE, false],
        ];
    }
}
