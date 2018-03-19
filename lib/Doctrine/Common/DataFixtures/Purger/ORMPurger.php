<?php

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Sorter\TopologicalSorter;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
final class ORMPurger implements PurgerInterface
{
    const PURGE_MODE_DELETE = 1;
    const PURGE_MODE_TRUNCATE = 2;

    /**
     * @var EntityManagerInterface
     */
    private $em;

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
    private $excluded;

    /**
     * @param EntityManagerInterface $em EntityManagerInterface instance used for persistence.
     * @param string[] $excluded array of table/view names to be excleded from purge
     */
    public function __construct(EntityManagerInterface $em = null, array $excluded = [])
    {
        $this->em = $em;
        $this->excluded = $excluded;
    }

    public function setPurgeMode(int $mode)
    {
        $this->purgeMode = $mode;
    }

    public function getPurgeMode(): int
    {
        return $this->purgeMode;
    }

    public function setEntityManager(EntityManagerInterface $em)
    {
      $this->em = $em;
    }

    public function getObjectManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /** @inheritDoc */
    public function purge()
    {
        $classes = [];

        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $metadata) {
            if (! $metadata->isMappedSuperclass && ! (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                $classes[] = $metadata;
            }
        }

        $commitOrder = $this->getCommitOrder($this->em, $classes);

        // Get platform parameters
        $platform = $this->em->getConnection()->getDatabasePlatform();

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

        $connection = $this->em->getConnection();
        $filterExpr = $connection->getConfiguration()->getFilterSchemaAssetsExpression();
        $emptyFilterExpression = empty($filterExpr);
        foreach($orderedTables as $tbl) {
            if(($emptyFilterExpression||preg_match($filterExpr, $tbl)) && array_search($tbl, $this->excluded) === false){
                if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                    $connection->executeUpdate("DELETE FROM " . $tbl);
                } else {
                    $connection->executeUpdate($platform->getTruncateTableSQL($tbl, true));
                }
            }
        }
    }

    /**
     * @param EntityManagerInterface $em
     * @param ClassMetadata[]        $classes
     *
     * @return ClassMetadata[]
     */
    private function getCommitOrder(EntityManagerInterface $em, array $classes): array
    {
        $sorter = new TopologicalSorter();

        foreach ($classes as $class) {
            if ( ! $sorter->hasNode($class->name)) {
                $sorter->addNode($class->name, $class);
            }

            // $class before its parents
            foreach ($class->parentClasses as $parentClass) {
                $parentClass     = $em->getClassMetadata($parentClass);
                $parentClassName = $parentClass->getName();

                if ( ! $sorter->hasNode($parentClassName)) {
                    $sorter->addNode($parentClassName, $parentClass);
                }

                $sorter->addDependency($class->name, $parentClassName);
            }

            foreach ($class->associationMappings as $assoc) {
                if ($assoc['isOwningSide']) {
                    /* @var $targetClass ClassMetadata */
                    $targetClass     = $em->getClassMetadata($assoc['targetEntity']);
                    $targetClassName = $targetClass->getName();

                    if ( ! $sorter->hasNode($targetClassName)) {
                        $sorter->addNode($targetClassName, $targetClass);
                    }

                    // add dependency ($targetClass before $class)
                    $sorter->addDependency($targetClassName, $class->name);

                    // parents of $targetClass before $class, too
                    foreach ($targetClass->parentClasses as $parentClass) {
                        $parentClass     = $em->getClassMetadata($parentClass);
                        $parentClassName = $parentClass->getName();

                        if ( ! $sorter->hasNode($parentClassName)) {
                            $sorter->addNode($parentClassName, $parentClass);
                        }

                        $sorter->addDependency($parentClassName, $class->name);
                    }
                }
            }
        }

        return array_reverse($sorter->sort());
    }

    private function getAssociationTables(array $classes, AbstractPlatform $platform): array
    {
        $associationTables = [];

        foreach ($classes as $class) {
            foreach ($class->associationMappings as $assoc) {
                if ($assoc['isOwningSide'] && $assoc['type'] == ClassMetadata::MANY_TO_MANY) {
                    $associationTables[] = $this->getJoinTableName($assoc, $class, $platform);
                }
            }
        }

        return $associationTables;
    }

    private function getTableName(ClassMetadata $class, AbstractPlatform $platform): string
    {
        if (isset($class->table['schema']) && !method_exists($class, 'getSchemaName')) {
            return $class->table['schema'].'.'.$this->em->getConfiguration()->getQuoteStrategy()->getTableName($class, $platform);
        }

        return $this->em->getConfiguration()->getQuoteStrategy()->getTableName($class, $platform);
    }

    private function getJoinTableName(array $assoc, ClassMetadata $class, AbstractPlatform $platform): string
    {
        if (isset($assoc['joinTable']['schema']) && !method_exists($class, 'getSchemaName')) {
            return $assoc['joinTable']['schema'].'.'.$this->em->getConfiguration()->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
        }

        return $this->em->getConfiguration()->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
    }
}
