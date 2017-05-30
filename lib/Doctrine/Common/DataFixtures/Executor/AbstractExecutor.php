<?php

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;

/**
 * Abstract fixture executor.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class AbstractExecutor
{
    /** Purger instance for purging database before loading data fixtures */
    protected $purger;

    /** Logger callback for logging messages when loading data fixtures */
    protected $logger;

    /**
     * Fixture reference repository
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * Loads an instance of reference repository
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->referenceRepository = new ReferenceRepository($manager);
    }

    /**
     * Get reference repository
     *
     * @return ReferenceRepository
     */
    public function getReferenceRepository()
    {
        return $this->referenceRepository;
    }

    /**
     * Set the reference repository
     *
     * @param ReferenceRepository $referenceRepository Reference repository
     */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * Sets the Purger instance to use for this executor instance.
     *
     * @param PurgerInterface $purger
     */
    public function setPurger(PurgerInterface $purger)
    {
        $this->purger = $purger;
    }

    /**
     * Get purger
     *
     * @return Purger
     */
    public function getPurger()
    {
        return $this->purger;
    }

    /**
     * Set the logger callable to execute with the log() method.
     *
     * @param $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs a message using the logger.
     *
     * @param string $message
     */
    public function log($message)
    {
        $logger = $this->logger;
        $logger($message);
    }

    /**
     * Load a fixture with the given persistence manager.
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     * @param FixtureInterface $fixture
     */
    public function load(ObjectManager $manager, FixtureInterface $fixture)
    {
        if ($this->logger) {
            $prefix = '';
            if ($fixture instanceof OrderedFixtureInterface) {
                $prefix = sprintf('[%d] ',$fixture->getOrder());
            }
            $this->log('loading ' . $prefix . get_class($fixture));
        }
        // additionally pass the instance of reference repository to shared fixtures
        if ($fixture instanceof SharedFixtureInterface) {
            $fixture->setReferenceRepository($this->referenceRepository);
        }
        $fixture->load($manager, $this->logger ?: null);
        $manager->clear();
    }

    /**
     * Purges the database before loading.
     */
    public function purge()
    {
        if ($this->purger === null) {
            throw new \Exception('Doctrine\Common\DataFixtures\Purger\PurgerInterface instance is required if you want to purge the database before loading your data fixtures.');
        }
        if ($this->logger) {
            $this->log('purging database');
        }
        $this->purger->purge();
    }

    /**
     * Executes the given array of data fixtures.
     *
     * @param array $fixtures Array of fixtures to execute.
     * @param boolean $append Whether to append the data fixtures or purge the database before loading.
     */
    abstract public function execute(array $fixtures, $append = false);
}
