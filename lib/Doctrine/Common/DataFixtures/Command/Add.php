<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lsv
 * Date: 4/3/12
 * Time: 4:38 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Doctrine\Common\DataFixtures\Command;

use Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console,
	Doctrine\Common\DataFixtures\Loader,
	Doctrine\Common\DataFixtures\Executor\ORMExecutor,
	Doctrine\Common\DataFixtures\Purger\ORMPurger;

class Add
	extends Console\Command\Command
{

	protected function configure()
	{
		$this
		->setName('fixtures:add')
		->setDescription('Adding fixtures to your database')
		->setDefinition(array(
			new InputOption(
				'directory', 'd', InputOption::VALUE_REQUIRED,
				'Directory with your fixtures - Its relative to your Entities\Proxies path'
			),
			new InputOption(
				'append', 'a', InputOption::VALUE_NONE,
				'If you want to append your fixtures instead'
			),
			new InputOption(
				'dump-fixtures', null, InputOption::VALUE_NONE,
				'Vieweing the fixtures instead of importing them'
			),
		));
	}

	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		/**
		 * @todo find a better way to get the path?
		 */
		$em = $this->getHelper('em')->getEntityManager(); /** @var $em \Doctrine\ORM\EntityManager */
		$dir = realpath($em->getConfiguration()->getProxyDir()) . '/';

		if (!($input->getOption('directory'))) {
			$output->write('You need to input the directory' . PHP_EOL);
		} else {
			$dir = realpath($dir . $input->getOption('directory') . '/');
			if (! is_dir($dir)) {
				$output->write('The inputted "' . $dir . '" is not a directory.' . PHP_EOL);
			} else {
				$loader = new Loader();
				$loader->loadFromDirectory($dir);
				$fixtures = $loader->getFixtures();

				if ($input->getOption('dump-fixtures') === true) {
					$output->writeln($fixtures . PHP_EOL);
				} elseif ($input->getOption('append') === true) {
					$purger = new ORMPurger();
					$executor = new ORMExecutor($em, $purger);
					$executor->execute($loader->getFixtures(), TRUE);
				} else {
					$purger = new ORMPurger();
					$executor = new ORMExecutor($em, $purger);
					$executor->execute($loader->getFixtures());
				}

			}
		}
	}

}

