<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

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
class ORMPurger implements PurgerInterface
{
    const PURGE_MODE_DELETE = 1;
    const PURGE_MODE_TRUNCATE = 2;

    /** EntityManagerInterface instance used for persistence. */
    private $em;

    /**
     * If the purge should be done through DELETE or TRUNCATE statements
     *
     * @var int
     */
    private $purgeMode = self::PURGE_MODE_DELETE;

    /**
     * Construct new purger instance.
     *
     * @param EntityManagerInterface $em EntityManagerInterface instance used for persistence.
     */
    public function __construct(EntityManagerInterface $em = null)
    {
        $this->em = $em;
    }

    /**
     * Set the purge mode
     *
     * @param $mode
     * @return void
     */
    public function setPurgeMode($mode)
    {
        $this->purgeMode = $mode;
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

    /**
     * Set the EntityManagerInterface instance this purger instance should use.
     *
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
      $this->em = $em;
    }

    /**
     * Retrieve the EntityManagerInterface instance this purger instance is using.
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getObjectManager()
    {
        return $this->em;
    }

    /** @inheritDoc */
    public function purge()
    {
        $classes = array();

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
        foreach($orderedTables as $tbl) {
            if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                $connection->executeUpdate('DELETE FROM ' . $tbl);
            } else {
                $connection->executeUpdate($platform->getTruncateTableSQL($tbl, true));
            }
        }
    }

    /**
     * @param EntityManagerInterface $em
     * @param ClassMetadata[]        $classes
     *
     * @return ClassMetadata[]
     */
    private function getCommitOrder(EntityManagerInterface $em, array $classes)
    {
        $sorter = new TopologicalSorter();

        foreach ($classes as $class) {
            $sorter->addNode($class->name, $class);

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

        return $sorter->sort();
    }

    /**
     * @param array $classes
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return array
     */
    private function getAssociationTables(array $classes, AbstractPlatform $platform)
    {
        $associationTables = array();

        foreach ($classes as $class) {
            foreach ($class->associationMappings as $assoc) {
                if ($assoc['isOwningSide'] && $assoc['type'] == ClassMetadata::MANY_TO_MANY) {
                    $associationTables[] = $this->getJoinTableName($assoc, $class, $platform);
                }
            }
        }

        return $associationTables;
    }

    /**
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return string
     */
    private function getTableName($class, $platform)
    {
        if (isset($class->table['schema']) && !method_exists($class, 'getSchemaName')) {
            return $class->table['schema'].'.'.$this->em->getConfiguration()->getQuoteStrategy()->getTableName($class, $platform);
        }

        return $this->em->getConfiguration()->getQuoteStrategy()->getTableName($class, $platform);
    }

    /**
     *
     * @param array            $association
     * @param \Doctrine\ORM\Mapping\ClassMetadata    $class
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return string
     */
    private function getJoinTableName($assoc, $class, $platform)
    {
        if (isset($assoc['joinTable']['schema']) && !method_exists($class, 'getSchemaName')) {
            return $assoc['joinTable']['schema'].'.'.$this->em->getConfiguration()->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
        }

        return $this->em->getConfiguration()->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
    }
}
