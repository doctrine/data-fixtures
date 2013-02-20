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

namespace Doctrine\Fixture\Reference;

use Doctrine\Common\Cache\Cache;

/**
 * Doctrine Cache Reference Repository implementation.
 * Supports a variety of implementors by default, including: APC, Array
 * (runtime), Couchbase, Filesystem, Memcache, Redis, WinCache, Xcache and
 * ZendData.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class DoctrineCacheReferenceRepository implements ReferenceRepository
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $value)
    {
        $this->cache->save($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->cache->contains($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->cache->delete($key);
    }
}