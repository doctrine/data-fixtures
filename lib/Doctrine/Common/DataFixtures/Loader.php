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

/**
 * Class responsible for loading data fixture classes.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Loader
{
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
        $fixtures = array();
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
        $this->orderedFixtures = null;
        $this->fixtures[] = $fixture;
    }

    /**
     * Returns the array of data fixtures to execute.
     *
     * @return array $fixtures
     */
    public function getFixtures()
    {
        if ($this->orderedFixtures === null) {
            $this->orderFixtures();
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
     * Orders fixtures
     * 
     * @todo maybe there is a better way to handle reordering
     * @return void
     */
    private function orderFixtures()
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
}