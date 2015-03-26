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
use Doctrine\ORM\Internal\CommitOrderCalculator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Platforms\AbstractPlatform;

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
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($metadatas as $metadata) {
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
                ($class->isInheritanceTypeSingleTable() && $class->name != $class->rootEntityName) ||
                (isset($class->isEmbeddedClass) && $class->isEmbeddedClass) ||
                $class->isMappedSuperclass
            ) {
                continue;
            }

            $orderedTables[] = $class->getQuotedTableName($platform);
        }

        foreach($orderedTables as $tbl) {
            if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                $this->em->getConnection()->executeUpdate("DELETE FROM " . $tbl);
            } else {
                $this->em->getConnection()->executeUpdate($platform->getTruncateTableSQL($tbl, true));
            }
        }
    }

    private function getCommitOrder(EntityManagerInterface $em, array $classes)
    {
        $calc = new CommitOrderCalculator;

        foreach ($classes as $class) {
            $calc->addClass($class);

            // $class before its parents
            foreach ($class->parentClasses as $parentClass) {
                $parentClass = $em->getClassMetadata($parentClass);

                if ( ! $calc->hasClass($parentClass->name)) {
                    $calc->addClass($parentClass);
                }

                $calc->addDependency($class, $parentClass);
            }

            foreach ($class->associationMappings as $assoc) {
                if ($assoc['isOwningSide']) {
                    $targetClass = $em->getClassMetadata($assoc['targetEntity']);

                    if ( ! $calc->hasClass($targetClass->name)) {
                        $calc->addClass($targetClass);
                    }

                    // add dependency ($targetClass before $class)
                    $calc->addDependency($targetClass, $class);

                    // parents of $targetClass before $class, too
                    foreach ($targetClass->parentClasses as $parentClass) {
                        $parentClass = $em->getClassMetadata($parentClass);

                        if ( ! $calc->hasClass($parentClass->name)) {
                            $calc->addClass($parentClass);
                        }

                        $calc->addDependency($parentClass, $class);
                    }
                }
            }
        }

        return $calc->getCommitOrder();
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
                    if (isset($assoc['joinTable']['schema'])) {
                        $associationTables[] = $assoc['joinTable']['schema'] . '.' . $class->getQuotedJoinTableName($assoc, $platform);
                    } else {
                        $associationTables[] = $class->getQuotedJoinTableName($assoc, $platform);
                    }
                }
            }
        }

        return $associationTables;
    }
}
