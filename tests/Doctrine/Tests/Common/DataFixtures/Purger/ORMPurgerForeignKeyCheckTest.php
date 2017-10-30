<?php

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use ReflectionClass;

/**
 * @author Robert Freigang <robertfreigang@gmx.de>
 *
 * @covers ORMPurger::getTruncateTableSQL
 */
class ORMPurgerForeignKeyCheckTest extends BaseTest
{
    const FOREIGN_KEY_CHECK_STRING_START = 'SET FOREIGN_KEY_CHECKS = 0;';
    const FOREIGN_KEY_CHECK_STRING_END = ';SET FOREIGN_KEY_CHECKS = 1;';
    const TEST_TABLE_NAME = 'test_table_name';

    /**
     * @param Driver $driver
     * @param bool   $hasForeignKeyCheckString
     *
     * @dataProvider purgeForDifferentDriversProvider
     */
    public function testPurgeForDifferentDrivers(Driver $driver, $hasForeignKeyCheckString)
    {
        $truncateTableSQL = $this->getTruncateTableSQLForDriver($driver);

        if ($hasForeignKeyCheckString) {
            $this->assertStringStartsWith(self::FOREIGN_KEY_CHECK_STRING_START, $truncateTableSQL);
            $this->assertStringEndsWith(self::FOREIGN_KEY_CHECK_STRING_END, $truncateTableSQL);
        } else {
            $this->assertNotContains(self::FOREIGN_KEY_CHECK_STRING_START, $truncateTableSQL);
            $this->assertNotContains(self::FOREIGN_KEY_CHECK_STRING_END, $truncateTableSQL);
        }

        $this->assertContains(self::TEST_TABLE_NAME, $truncateTableSQL);
    }

    /**
     * @return array
     */
    public function purgeForDifferentDriversProvider()
    {
        return [
            [$this->createMock(AbstractMySQLDriver::class), true],
            [$this->createMock(AbstractSQLiteDriver::class), false],
        ];
    }

    /**
     * @param Driver $driver
     *
     * @return string
     */
    private function getTruncateTableSQLForDriver(Driver $driver)
    {
        $em = $this->getMockAnnotationReaderEntityManager();

        $platform = $em->getConnection()->getDatabasePlatform();

        $connection = $this->createMock(Connection::class);
        $connection->method('getDriver')->willReturn($driver);

        $purger = new ORMPurger($em);
        $purgerClass = new ReflectionClass(ORMPurger::class);
        $getTruncateTableSQLMethod = $purgerClass->getMethod('getTruncateTableSQL');
        $getTruncateTableSQLMethod->setAccessible(true);

        $truncateTableSQL = $getTruncateTableSQLMethod->invokeArgs(
            $purger,
            [$platform, $connection, self::TEST_TABLE_NAME]
        );

        return $truncateTableSQL;
    }
}
