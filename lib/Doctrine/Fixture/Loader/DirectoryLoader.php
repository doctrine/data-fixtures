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

namespace Doctrine\Fixture\Loader;

/**
 * DirectoryLoader loads a list of fixtures based on a directory.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class DirectoryLoader implements Loader
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * Constructor.
     *
     * @throws \InvalidArgumentException
     *
     * @param string $directory
     */
    public function __construct($directory)
    {
        if ( ! is_dir($directory)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $directory));
        }

        $this->directory = $directory;
    }

    /**
     * Retrieve the loader directory.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $fileList          = $this->loadFromDirectory();
        $declaredClassList = get_declared_classes();
        $fixtureList       = array();

        foreach ($declaredClassList as $class) {
            $reflectionClass = new \ReflectionClass($class);

            // Check if class was declared during this loader
            if ( ! in_array($reflectionClass->getFileName(), $fileList)) {
                continue;
            }

            // Check if class is transient
            if ($this->isTransient($reflectionClass)) {
                continue;
            }

            $fixtureList[] = new $class();
        }

        return $fixtureList;
    }

    /**
     * Loads fixture files of loader directory.
     *
     * @return array
     */
    protected function loadFromDirectory()
    {
        $iterator = new \FilesystemIterator($this->directory);

        return $this->loadFileList($iterator);
    }

    /**
     * Loads the file list using a given iterator.
     *
     * @param \Iterator $iterator
     *
     * @return array
     */
    protected function loadFileList(\Iterator $iterator)
    {
        $fileList = array();

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->getBasename('.php') === $fileInfo->getBasename()) {
                continue;
            }

            $fileRealPath = $fileInfo->getRealPath();

            require_once $fileRealPath;

            $fileList[] = $fileRealPath;
        }

        return $fileList;
    }

    /**
     * Checks if class is transient.
     *
     * @param \ReflectionClass $reflectionClass
     *
     * @return boolean
     */
    private function isTransient(\ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->isAbstract()) {
            return true;
        }

        return ( ! $reflectionClass->implementsInterface('Doctrine\Fixture\Fixture'));
    }
}
