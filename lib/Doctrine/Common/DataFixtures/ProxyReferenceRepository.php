<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Version;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function get_class;
use function json_decode;
use function json_encode;
use function substr;

/**
 * Proxy reference repository
 *
 * Allow data fixture references and identities to be persisted when cached data fixtures
 * are pre-loaded, for example, by LiipFunctionalTestBundle\Test\WebTestCase loadFixtures().
 */
class ProxyReferenceRepository extends ReferenceRepository
{
    /**
     * Get real class name of a reference that could be a proxy
     *
     * @param string $className Class name of reference object
     *
     * @return string
     */
    protected function getRealClass($className)
    {
        if (Version::compare('2.2.0') <= 0) {
            return ClassUtils::getRealClass($className);
        }

        if (substr($className, -5) === 'Proxy') {
            return substr($className, 0, -5);
        }

        return $className;
    }

    /**
     * Serialize reference repository
     *
     * @return string
     */
    public function serialize()
    {
        $unitOfWork       = $this->getManager()->getUnitOfWork();
        $simpleReferences = [];

        foreach ($this->getReferences() as $name => $reference) {
            $className = $this->getRealClass(get_class($reference));

            $simpleReferences[$name] = [$className, $this->getIdentifier($reference, $unitOfWork)];
        }

        return json_encode([
            'references' => $simpleReferences,
            'identities' => $this->getIdentities(),
        ]);
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
                    $proxyReference[1]  // identifiers
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
     *
     * @return bool
     */
    public function load($baseCacheName)
    {
        $filename = $baseCacheName . '.ser';

        if (! file_exists($filename)) {
            return false;
        }

        $serializedData = file_get_contents($filename);

        if ($serializedData === false) {
            return false;
        }

        $this->unserialize($serializedData);

        return true;
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
