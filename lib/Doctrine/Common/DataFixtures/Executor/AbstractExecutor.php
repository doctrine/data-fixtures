<?php

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;

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
     * @param object $manager
     */
    public function __construct($manager)
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
     * Sets the Purger instance to use for this exector instance.
     *
     * @param Purger $purger
     */
    public function setPurger(Purger $purger)
    {
        $this->purger = $purger;
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
     * @param object $manager
     * @param FixtureInterface $fixture
     */
    public function load($manager, FixtureInterface $fixture)
    {
        if ($this->logger) {
            $this->log('loading ' . get_class($fixture));
        }
        // additionaly pass the instance of reference repository to shared fixtures
        if ($fixture instanceof SharedFixtureInterface) {
            $fixture->setReferenceRepository($this->referenceRepository);
        }
        $fixture->load($manager);
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
