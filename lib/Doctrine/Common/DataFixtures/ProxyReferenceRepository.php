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

/**
 * Proxy reference repository
 *
 * Allow data fixture references and identities to be persisted when cached data fixtures
 * are pre-loaded, for example, by LiipFunctionalTestBundle\Test\WebTestCase loadFixtures().
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
class ProxyReferenceRepository extends ReferenceRepository
{
    /**
     * Get real class name of a reference that could be a proxy
     *
     * @param mixed $reference Reference
     *
     * @return string
     */
    protected function getRealClass($reference)
    {
        $className = get_class($reference);

        if ( ! $reference instanceof \Doctrine\ORM\Proxy\Proxy) {
            return $className;
        }

        if (\Doctrine\Common\Version::compare('2.2.0') >= 0) {
            return \Doctrine\Common\Util\ClassUtils::getRealClass($className);
        }

        return substr($className, 0, -5);
    }

    /**
     * Serialize reference repository
     *
     * @return string
     */
    public function serialize()
    {
        $simpleReferences = array();

        foreach ($this->getReferences() as $name => $reference) {
            $className = $this->getRealClass($reference);

            $simpleReferences[$name] = array($className, $reference->getId());
        }

        $serializedData = json_encode(array(
            'references' => $simpleReferences,
            'identities' => $this->getIdentities(),
        ));

        return $serializedData;
    }

    /**
     * Unserialize reference repository
     *
     * @param string $serializedData Serialized data
     */
    public function unserialize($serializedData)
    {
        $repositoryData = json_decode($serializedData, true);
        $references     = $repositoryData['references'];

        foreach ($references as $name => $proxyReference) {
            $this->setReference(
                $name,
                $this->getManager()->getReference(
                    $proxyReference[0], // entity class name
                    $proxyReference[1]  // id
                )
            );
        }

        $identities = $repositoryData['identities'];

        foreach ($identities as $name => $identity) {
            $this->setReferenceIdentity($name, $identity);
        }
    }

    /**
     * Load data fixture reference repository
     *
     * @param string $baseCacheName Base cache name
     */
    public function load($baseCacheName)
    {
        $filename = $baseCacheName . '.ser';

        if ( ! file_exists($filename) || ($serializedData = file_get_contents($filename)) === false) {
            return;
        }

        $this->unserialize($serializedData);
    }

    /**
     * Save data fixture reference repository
     *
     * @param string $baseCacheName Base cache name
     */
    public function save($baseCacheName)
    {
        $serializedData = $this->serialize();

        file_put_contents($baseCacheName . '.ser', $serializedData);
    }
}
