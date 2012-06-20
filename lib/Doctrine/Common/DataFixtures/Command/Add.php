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

namespace Doctrine\Common\DataFixtures\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console,
    Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

class Add extends Console\Command\Command
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
                'Viewing the fixtures instead of importing them'
            ),
        ));
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        /**
         * @todo find a better way to get the path?
         */
        $em = $this->getHelper('em')->getEntityManager(); /** @var $em \Doctrine\ORM\EntityManager */

        if (!($input->getOption('directory'))) {
            $output->write($this->getSynopsis() . PHP_EOL);
            throw new \InvalidArgumentException('You need to input the directory');
        } else {
            $dir = realpath($em->getConfiguration()->getProxyDir() . '/' . $input->getOption('directory') . '/');
            if (! is_dir($dir)) {
                throw new \InvalidArgumentException(sprintf('The inputted "%s" is not a directory', $dir));
            } else {
                $loader = new Loader();
                $loader->loadFromDirectory($dir);
                $fixtures = $loader->getFixtures();

                if ($input->getOption('append') === true) {
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

