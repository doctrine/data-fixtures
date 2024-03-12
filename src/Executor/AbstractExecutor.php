<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Closure;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Deprecations\Deprecation;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use TypeError;

use function get_debug_type;
use function is_callable;
use function sprintf;

/**
 * Abstract fixture executor.
 *
 * @internal since 1.8.0
 */
abstract class AbstractExecutor
{
    /**
     * Purger instance for purging database before loading data fixtures
     *
     * @var PurgerInterface|null
     */
    protected $purger;

    /**
     * Logger callback for logging messages when loading data fixtures
     *
     * @var (LoggerInterface&callable)|null
     */
    protected $logger;

    /**
     * Fixture reference repository
     *
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    public function __construct(ObjectManager $manager)
    {
        $this->referenceRepository = new ReferenceRepository($manager);
    }

    /** @return ReferenceRepository */
    public function getReferenceRepository()
    {
        return $this->referenceRepository;
    }

    /** @return void */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * Sets the Purger instance to use for this executor instance.
     *
     * @return void
     */
    public function setPurger(PurgerInterface $purger)
    {
        $this->purger = $purger;
    }

    /** @return PurgerInterface */
    public function getPurger()
    {
        return $this->purger;
    }

    /**
     * Set the logger callable to execute with the log() method.
     *
     * @param LoggerInterface|callable|null $logger
     *
     * @return void
     */
    public function setLogger($logger)
    {
        if ($logger instanceof LoggerInterface) {
            $logger = new class ($logger) extends AbstractLogger
            {
                private LoggerInterface $logger;

                public function __construct(LoggerInterface $logger)
                {
                    $this->logger = $logger;
                }

                /** @inheritDoc */
                public function log($level, $message, array $context = []): void
                {
                    $this->logger->log($level, $message, $context);
                }

                public function __invoke(string $message): void
                {
                    Deprecation::trigger(
                        'doctrine/data-fixtures',
                        'https://github.com/doctrine/data-fixtures/pull/462',
                        'Invoking the logger is deprecated, call %s methods instead',
                        LoggerInterface::class,
                    );

                    $this->logger->debug($message);
                }
            };
        } elseif (is_callable($logger)) {
            Deprecation::trigger(
                'doctrine/data-fixtures',
                'https://github.com/doctrine/data-fixtures/pull/462',
                'Passing a callable to %s() is deprecated, pass an instance of %s instead',
                __METHOD__,
                LoggerInterface::class,
            );

            $logger = new class ($logger) extends AbstractLogger
            {
                private Closure $logger;

                public function __construct(callable $logger)
                {
                    $this->logger = Closure::fromCallable($logger);
                }

                /** @inheritDoc */
                public function log($level, $message, array $context = []): void
                {
                    ($this->logger)($message);
                }

                public function __invoke(string $message): void
                {
                    Deprecation::trigger(
                        'doctrine/data-fixtures',
                        'https://github.com/doctrine/data-fixtures/pull/462',
                        'Invoking the logger is deprecated, call %s methods instead',
                        LoggerInterface::class,
                    );

                    ($this->logger)($message);
                }
            };
        } elseif ($logger !== null) {
            throw new TypeError(sprintf(
                '%s(): Parameter $logger is expected to be an instance of %s, %s given.',
                __METHOD__,
                LoggerInterface::class,
                get_debug_type($logger),
            ));
        }

        $this->logger = $logger;
    }

    /**
     * Logs a message using the logger.
     *
     * @deprecated without replacement
     *
     * @return void
     */
    public function log(string $message)
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/data-fixtures',
            'https://github.com/doctrine/data-fixtures/pull/462',
            'Method %s() is deprecated',
            __METHOD__,
        );

        $this->logger->debug($message);
    }

    /**
     * Load a fixture with the given persistence manager.
     *
     * @return void
     */
    public function load(ObjectManager $manager, FixtureInterface $fixture)
    {
        if ($this->logger) {
            $prefix = '';
            if ($fixture instanceof OrderedFixtureInterface) {
                $prefix = sprintf('[%d] ', $fixture->getOrder());
            }

            $this->log('loading ' . $prefix . get_debug_type($fixture));
        }

        // additionally pass the instance of reference repository to shared fixtures
        if ($fixture instanceof SharedFixtureInterface) {
            $fixture->setReferenceRepository($this->referenceRepository);
        }

        $fixture->load($manager);
        $manager->clear();
    }

    /**
     * Purges the database before loading.
     *
     * @return void
     *
     * @throws Exception if the purger is not defined.
     */
    public function purge()
    {
        if ($this->purger === null) {
            throw new Exception(
                PurgerInterface::class .
                 ' instance is required if you want to purge the database before loading your data fixtures.',
            );
        }

        if ($this->logger) {
            $this->log('purging database');
        }

        $this->purger->purge();
    }

    /**
     * Executes the given array of data fixtures.
     *
     * @param FixtureInterface[] $fixtures Array of fixtures to execute.
     * @param bool               $append   Whether to append the data fixtures or purge the database before loading.
     *
     * @return void
     */
    abstract public function execute(array $fixtures, bool $append = false);
}
