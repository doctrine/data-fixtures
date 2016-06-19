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

namespace Doctrine\Common\DataFixtures\Sorter;

/**
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @internal this class is to be used only by data-fixtures internals: do not
 *           rely on it in your own libraries/applications. This class is
 *           designed to work with {@see \Doctrine\Common\DataFixtures\Sorter\TopologicalSorter}
 *           only.
 */
class Vertex
{
    const NOT_VISITED = 0;
    const IN_PROGRESS = 1;
    const VISITED     = 2;

    /**
     * @var int one of either {@see self::NOT_VISITED}, {@see self::IN_PROGRESS} or {@see self::VISITED}.
     */
    public $state = self::NOT_VISITED;

    /**
     * @var mixed Actual node value
     */
    public $value;

    /**
     * @var string[] Map of node dependencies defined as hashes.
     */
    public $dependencyList = [];

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
