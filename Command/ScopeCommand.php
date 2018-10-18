<?php

/*
 * This file is part of the Doss package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dos\OAuthServerBundle\Command;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Dos\OAuthServerBundle\Model\ScopeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScopeCommand extends Command
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(FactoryInterface $factory, RepositoryInterface $repository)
    {
        parent::__construct();

        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('dos:oauth:scope');
        $this->setDescription('Create new scope.');

        $this->addArgument('id', InputArgument::REQUIRED, 'Scope Identifier.');
        $this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Scope name.');
        $this->addOption('description', 'd', InputOption::VALUE_OPTIONAL, 'Scope description.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Override scope if already exist.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $id = $input->getArgument('id');
        $name = $input->getOption('name') ?? ucfirst($id);

        /** @var ScopeInterface $scope */
        $scope = $this->factory->createNew();
        $scope->setIdentifier($id);
        $scope->setName($name);
        $scope->setDescription($input->getOption('description'));

        if ($this->createOrUpdate($output, $scope, $force)) {
            $output->writeln('<info>Scope generated successfully.</info>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param ScopeInterface $newScope
     * @param bool $force
     *
     * @return int
     */
    private function createOrUpdate(OutputInterface $output, ScopeInterface $newScope, bool $force)
    {
        if (!$force) {
            try {
                $this->repository->add($newScope);
            } catch (UniqueConstraintViolationException $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                $output->writeln('<error>Scope already exist. Use the --force option to overwrite them.</error>');

                return 0;
            }

            return 1;
        }

        /** @var ScopeInterface $scope */
        if ($scope = $this->repository->findOneBy(['identifier' => $newScope->getIdentifier()])) {
            $scope->setName($newScope->getName());
            $scope->setEnabled(true);

            if ($newScope->getDescription()) {
                $scope->setDescription($newScope->getDescription());
            }

            $newScope = $scope;
        }

        $this->repository->add($newScope);

        return 1;
    }
}
