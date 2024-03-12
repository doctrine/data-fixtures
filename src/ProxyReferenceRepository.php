<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function serialize;
use function unserialize;

/**
 * Proxy reference repository
 *
 * Allow data fixture references and identities to be persisted when cached data fixtures
 * are pre-loaded, for example, by LiipFunctionalTestBundle\Test\WebTestCase loadFixtures().
 */
class ProxyReferenceRepository extends ReferenceRepository
{
    /**
     * Serialize reference repository
     */
    public function serialize(): string
    {
        return serialize([
            'identitiesByClass' => $this->getIdentitiesByClass(),
        ]);
    }

    /**
     * Unserialize reference repository
     *
     * @param string $serializedData Serialized data
     */
    public function unserialize(string $serializedData): void
    {
        $repositoryData = unserialize($serializedData);

        foreach ($repositoryData['identitiesByClass'] as $className => $identities) {
            foreach ($identities as $name => $identity) {
                $this->setReference(
                    $name,
                    $this->getManager()->getReference(
                        $className,
                        $identity,
                    ),
                );

                $this->setReferenceIdentity($name, $identity, $className);
            }
        }
    }

    /**
     * Load data fixture reference repository
     *
     * @param string $baseCacheName Base cache name
     */
    public function load(string $baseCacheName): bool
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
    public function save(string $baseCacheName): void
    {
        $serializedData = $this->serialize();

        file_put_contents($baseCacheName . '.ser', $serializedData);
    }
}
