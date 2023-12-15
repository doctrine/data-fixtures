<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Sorter\TopologicalSorter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToManyOwningSideMapping;

use function array_map;
use function array_reverse;
use function assert;
use function count;
use function in_array;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 *
 * @final since 1.8.0
 */
class ORMPurger implements PurgerInterface, ORMPurgerInterface
{
    public const PURGE_MODE_DELETE   = 1;
    public const PURGE_MODE_TRUNCATE = 2;

    private ?EntityManagerInterface $em;

    /**
     * If the purge should be done through DELETE or TRUNCATE statements
     *
     * @var int
     */
    private $purgeMode = self::PURGE_MODE_DELETE;

    /**
     * Table/view names to be excluded from purge
     *
     * @var string[]
     */
    private array $excluded;

    /** @var list<string>|null */
    private ?array $cachedSqlStatements = null;

    /**
     * Construct new purger instance.
     *
     * @param EntityManagerInterface|null $em       EntityManagerInterface instance used for persistence.
     * @param string[]                    $excluded array of table/view names to be excluded from purge
     */
    public function __construct(?EntityManagerInterface $em = null, array $excluded = [])
    {
        $this->em       = $em;
        $this->excluded = $excluded;
    }

    /**
     * Set the purge mode
     *
     * @return void
     */
    public function setPurgeMode(int $mode)
    {
        $this->purgeMode           = $mode;
        $this->cachedSqlStatements = null;
    }

    /**
     * Get the purge mode
     *
     * @return int
     */
    public function getPurgeMode()
    {
        return $this->purgeMode;
    }

    /** @inheritDoc */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em                  = $em;
        $this->cachedSqlStatements = null;
    }

    /**
     * Retrieve the EntityManagerInterface instance this purger instance is using.
     *
     * @return EntityManagerInterface
     */
    public function getObjectManager()
    {
        return $this->em;
    }

    /** @inheritDoc */
    public function purge()
    {
        $connection = $this->em->getConnection();
        array_map([$connection, 'executeStatement'], $this->getPurgeStatements());
    }

    /** @return list<string> */
    private function getPurgeStatements(): array
    {
        if ($this->cachedSqlStatements !== null) {
            return $this->cachedSqlStatements;
        }

        $connection = $this->em->getConnection();
        $classes    = [];

        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $metadata) {
            if ($metadata->isMappedSuperclass || (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                continue;
            }

            $classes[] = $metadata;
        }

        $commitOrder = $this->getCommitOrder($this->em, $classes);

        // Get platform parameters
        $platform = $connection->getDatabasePlatform();

        // Drop association tables first
        $orderedTables = $this->getAssociationTables($commitOrder, $platform);

        // Drop tables in reverse commit order
        for ($i = count($commitOrder) - 1; $i >= 0; --$i) {
            $class = $commitOrder[$i];

            if (
                (isset($class->isEmbeddedClass) && $class->isEmbeddedClass) ||
                $class->isMappedSuperclass ||
                ($class->isInheritanceTypeSingleTable() && $class->name !== $class->rootEntityName)
            ) {
                continue;
            }

            $orderedTables[] = $this->getTableName($class, $platform);
        }

        $connectionConfiguration = $connection->getConfiguration();

        $schemaAssetsFilter = $connectionConfiguration->getSchemaAssetsFilter()
            ?? static fn (): bool => true;

        $this->cachedSqlStatements = [];
        foreach ($orderedTables as $tbl) {
            // If the table is excluded, skip it as well
            if (in_array($tbl, $this->excluded)) {
                continue;
            }

            // Support schema asset filters as presented in
            if (! $schemaAssetsFilter($tbl)) {
                continue;
            }

            if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                $this->cachedSqlStatements[] = $this->getDeleteFromTableSQL($tbl, $platform);
            } else {
                $this->cachedSqlStatements[] = $platform->getTruncateTableSQL($tbl, true);
            }
        }

        return $this->cachedSqlStatements;
    }

    /**
     * @param ClassMetadata[] $classes
     *
     * @return ClassMetadata[]
     */
    private function getCommitOrder(EntityManagerInterface $em, array $classes): array
    {
        $sorter = new TopologicalSorter();

        foreach ($classes as $class) {
            if (! $sorter->hasNode($class->name)) {
                $sorter->addNode($class->name, $class);
            }

            // $class before its parents
            foreach ($class->parentClasses as $parentClass) {
                $parentClass     = $em->getClassMetadata($parentClass);
                $parentClassName = $parentClass->getName();

                if (! $sorter->hasNode($parentClassName)) {
                    $sorter->addNode($parentClassName, $parentClass);
                }

                $sorter->addDependency($class->name, $parentClassName);
            }

            foreach ($class->associationMappings as $assoc) {
                if (! $assoc['isOwningSide']) {
                    continue;
                }

                $targetClass = $em->getClassMetadata($assoc['targetEntity']);
                assert($targetClass instanceof ClassMetadata);
                $targetClassName = $targetClass->getName();

                if (! $sorter->hasNode($targetClassName)) {
                    $sorter->addNode($targetClassName, $targetClass);
                }

                // add dependency ($targetClass before $class)
                $sorter->addDependency($targetClassName, $class->name);

                // parents of $targetClass before $class, too
                foreach ($targetClass->parentClasses as $parentClass) {
                    $parentClass     = $em->getClassMetadata($parentClass);
                    $parentClassName = $parentClass->getName();

                    if (! $sorter->hasNode($parentClassName)) {
                        $sorter->addNode($parentClassName, $parentClass);
                    }

                    $sorter->addDependency($parentClassName, $class->name);
                }
            }
        }

        return array_reverse($sorter->sort());
    }

    /**
     * @param ClassMetadata[] $classes
     *
     * @return string[]
     */
    private function getAssociationTables(array $classes, AbstractPlatform $platform): array
    {
        $associationTables = [];

        foreach ($classes as $class) {
            foreach ($class->associationMappings as $assoc) {
                if (! $assoc['isOwningSide'] || $assoc['type'] !== ClassMetadata::MANY_TO_MANY) {
                    continue;
                }

                $associationTables[] = $this->getJoinTableName($assoc, $class, $platform);
            }
        }

        return $associationTables;
    }

    private function getTableName(ClassMetadata $class, AbstractPlatform $platform): string
    {
        return $this->em->getConfiguration()->getQuoteStrategy()->getTableName($class, $platform);
    }

    /** @param ManyToManyOwningSideMapping|mixed[] $assoc */
    private function getJoinTableName(
        $assoc,
        ClassMetadata $class,
        AbstractPlatform $platform
    ): string {
        return $this->em->getConfiguration()->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
    }

    private function getDeleteFromTableSQL(string $tableName, AbstractPlatform $platform): string
    {
        $tableIdentifier = new Identifier($tableName);

        return 'DELETE FROM ' . $tableIdentifier->getQuotedName($platform);
    }
}
