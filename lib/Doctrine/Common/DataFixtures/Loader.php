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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\DataFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\Exception\CircularReferenceException;

/**
 * Class responsible for loading data fixture classes.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Loader
{
    const ORDER_BY_NUMBER = 0;
    const ORDER_BY_PARENT_CLASS = 1;
    
    
    /**
     * Array of fixture object instances to execute.
     *
     * @var array
     */
    private $fixtures = array();

    /**
     * Array of ordered fixture object instances.
     *
     * @var array
     */
    private $orderedFixtures;

    /**
     * Ordering method used to load fixtures
     *
     * @var string
     */
    private $orderingType;

    /**
     * The file extension of fixture files.
     *
     * @var string
     */
    private $fileExtension = '.php';

    /**
     * Find fixtures classes in a given directory and load them.
     *
     * @param string $dir Directory to find fixture classes in.
     * @return array $fixtures Array of loaded fixture object instances
     */
    public function loadFromDirectory($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $dir));
        }

        $fixtures = array();
        $includedFiles = array();

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (($fileName = $file->getBasename($this->fileExtension)) == $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }
        $declared = get_declared_classes();
        
        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();
            
            if (in_array($sourceFile, $includedFiles) && ! $this->isTransient($className)) {
                $fixture = new $className;
                $fixtures[] = $fixture;
                $this->addFixture($fixture); 
            }
        }
        return $fixtures;
    }

    /**
     * Add a fixture object instance to the loader.
     *
     * @param object $fixture
     */
    public function addFixture(FixtureInterface $fixture)
    {
        $fixtureClass = get_class($fixture);

        if (!isset($this->fixtures[$fixtureClass])) {
            if ($fixture instanceof OrderedFixtureInterface) {
                $this->orderingType = self::ORDER_BY_NUMBER;
            }
            else if ($fixture instanceof OrderedByParentFixtureInterface) {
                $this->orderingType = self::ORDER_BY_PARENT_CLASS;
            }

            $this->fixtures[$fixtureClass] = $fixture;
        }
    }

    /**
     * Returns the array of data fixtures to execute.
     *
     * @return array $fixtures
     */
    public function getFixtures()
    {
        switch ($this->orderingType) {
            case self::ORDER_BY_NUMBER:
                $this->orderFixturesByNumber();

                break;
            case self::ORDER_BY_PARENT_CLASS:
                $this->orderFixturesByParentClass();

                break;
            default:
                $this->orderedFixtures = $this->fixtures;

                break;
        }

        return $this->orderedFixtures;
    }

    /**
     * Check if a given fixture is transient and should not be considered a data fixtures
     * class.
     *
     * @return boolean
     */
    public function isTransient($className)
    {
        $interfaces = class_implements($className);
        return in_array('Doctrine\Common\DataFixtures\FixtureInterface', $interfaces) ? false : true;
    }

    /**
     * Orders fixtures by number
     * 
     * @todo maybe there is a better way to handle reordering
     * @return void
     */
    private function orderFixturesByNumber()
    {
        $this->orderedFixtures = $this->fixtures;
        usort($this->orderedFixtures, function($a, $b) {
            if ($a instanceof OrderedFixtureInterface && $b instanceof OrderedFixtureInterface) {
                if ($a->getOrder() === $b->getOrder()) {
                    return 0;
                }
                return $a->getOrder() < $b->getOrder() ? -1 : 1;
            } elseif ($a instanceof OrderedFixtureInterface) {
                return $a->getOrder() === 0 ? 0 : 1;
            } elseif ($b instanceof OrderedFixtureInterface) {
                return $b->getOrder() === 0 ? 0 : -1;
            }
            return 0;
        });
    }
    
    
    /**
     * Orders fixtures by parent
     * 
     * @return void
     */
    private function orderFixturesByParentClass()
    {
        $sequenceForClasses = array();

        // First we determine which classes has dependencies and which don't
        foreach ($this->fixtures as $fixture) {
            $fixtureClass = get_class($fixture);
            
            if ($fixture instanceof OrderedByParentFixtureInterface) {
                $parentClasses  = $fixture->getParentDataFixtureClasses();

                if (!is_array($parentClasses) || empty($parentClasses)) {
                    throw new \InvalidArgumentException(sprintf('Method "%s" in class "%s" must return an array of parent classes for the fixture, and it must be NOT empty.', 'getParentDataFixtureClasses', $fixtureClass));
                }

                if (in_array($fixtureClass, $parentClasses)) {
                    throw new \InvalidArgumentException(sprintf('Class "%s" can\'t have itself as parent', $fixtureClass));
                }
                
                // We mark this class as unsequenced
                $sequenceForClasses[ $fixtureClass ] = -1;
            } else {
                // This class has no dependencies, so we assign 0
                $sequenceForClasses[ $fixtureClass ] = 0;
            }
        }

        $sequence = 1;
        $lastCount = -1;

        while (($count = count($unsequencedClasses = $this->getUnsequencedClasses($sequenceForClasses, array_keys($sequenceForClasses)))) > 0 && $count !== $lastCount) {
            foreach ($unsequencedClasses as $key => $class) {
                $fixture = $this->fixtures[$class];
                $parents = $fixture->getParentDataFixtureClasses();
                $unsequencedParents = $this->getUnsequencedClasses($sequenceForClasses, $parents);

                if (count($unsequencedParents) === 0) {
                    $sequenceForClasses[$class] = $sequence++;
                }                
            }
            
            $lastCount = $count;
        }

        if ($count > 0) {
            throw new CircularReferenceException(sprintf('Classes "%s" has produced a Circular Reference Exception.', implode(',', $unsequencedClasses)));
        } else {
            // We order the classes by sequence
            asort($sequenceForClasses);

            $this->orderedFixtures = array();

            foreach ($sequenceForClasses as $class => $sequence) {
                $this->orderedFixtures[] = $this->fixtures[$class];
            }
        }
    }

    private function getUnsequencedClasses($sequences, $classes = null)
    {
        $unsequencedClasses = array();

        if (is_null($classes)) {
            $classes = $sequences;
        }

        foreach ($classes as $class) {
            if ($sequences[$class] === -1) {
                $unsequencedClasses[] = $class;
            }
        }

        return $unsequencedClasses;
    }           
}
