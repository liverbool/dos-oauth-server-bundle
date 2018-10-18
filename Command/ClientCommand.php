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

use Dos\OAuthServerBundle\Model\ClientInterface;
use Dos\OAuthServerBundle\Model\ScopeInterface;
use Dos\OAuthServerBundle\Repository\Doctrine\ORM\UserRepository;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClientCommand extends Command
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var FactoryInterface
     */
    private $scopeFactory;

    /**
     * @var RepositoryInterface
     */
    private $scopeRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var array
     */
    private $availableGrantTypes = [];

    public function __construct(
        FactoryInterface $factory, RepositoryInterface $repository,
        FactoryInterface $scopeFactory, RepositoryInterface $scopeRepository,
        UserRepository $userRepository,
        array $availableGrantTypes
    )
    {
        parent::__construct();

        $this->factory = $factory;
        $this->repository = $repository;
        $this->scopeFactory = $scopeFactory;
        $this->scopeRepository = $scopeRepository;
        $this->userRepository = $userRepository;
        $this->availableGrantTypes = $availableGrantTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('dos:oauth:client');
        $this->setDescription('Create new client.');

        $this->addArgument('name', null, InputArgument::REQUIRED, 'Client name.');

        $this->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Client Id.');
        $this->addOption('secret', null, InputOption::VALUE_OPTIONAL, 'Client secret.');
        $this->addOption('scopes', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Allowed scopes for this client. Full scope used if not provide.', []);
        $this->addOption('grants', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Allowed grant types for this client. All grant used if not provide.', []);
        $this->addOption('redirects', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Allowed redirect uris for this client.');
        $this->addOption('user', null, InputOption::VALUE_REQUIRED, 'The user who is owned of this client (username or email or id).');
        $this->addOption('force_scope', 'f', InputOption::VALUE_NONE, 'Force create scope if not exist.');
    }

    /**
     * @param InputInterface $input
     */
    private function checkAllRequiredOptionsAreNotEmpty(InputInterface $input)
    {
        $errors = [];
        $option1 = $this->getDefinition()->getOption('redirects');

        /** @var InputOption $option */
        foreach ([$option1] as $option) {
            $name = $option->getName();
            $value = $input->getOption($name);

            if ($value === null || $value === '' || ($option->isArray() && empty($value))) {
                $errors[] = sprintf('The required option --%s is not set or is empty', $name);
            }
        }

        if (count($errors)) {
            throw new \InvalidArgumentException(implode("\n\n", $errors));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkAllRequiredOptionsAreNotEmpty($input);

        /** @var ClientInterface $client */
        $client = $this->factory->createNew();
        $client->setName($input->getArgument('name'));
        $client->setIdentifier($input->getOption('id') ?? bin2hex(random_bytes(40)));
        $client->setSecret($input->getOption('secret') ?? bin2hex(random_bytes(40)));

        $redirects = $input->getOption('redirects');

        foreach ($redirects as $redirect) {
            if (!filter_var($redirect, FILTER_VALIDATE_URL)) {
                $output->writeln('<error>Not valid uri: ' . $redirect . '</error>');

                return 0;
            }
        }

        $client->setRedirectUris($redirects);

        $user = null;

        if ($userIdentify = $input->getOption('user')) {
            if (!$user = $this->userRepository->findUserByEmailOrUsernameOrId($userIdentify)) {
                $output->writeln('<error>Not found user `' . $userIdentify . '`.</error>');

                return 0;
            }
        }

        $client->setUser($user);

        $scopes = [];
        $forceCreateScope = $input->getOption('force_scope');
        $inputScopes = $input->getOption('scopes');

        foreach ($inputScopes as $scope) {
            $scopeObject = $this->findScope($scope);

            if (!$forceCreateScope && !$scopeObject) {
                $output->writeln('<error>Scope `' . $scope . '` not exist. Use the `-f` option to create them.</error>');

                return 0;
            }

            if (!$scopeObject) {
                $scopeObject = $this->createScope($scope);
            }

            $scopes[] = $scopeObject;
        }

        $this->addClientScopes($client, $scopes);

        $inputGrantTypes = $input->getOption('grants');

        if (empty($inputGrantTypes)) {
            foreach ($this->availableGrantTypes as $id => $definition) {
                if ($definition['enabled']) {
                    $inputGrantTypes[] = $id;
                }
            }
        }

        $client->setGrantTypes($inputGrantTypes);

        $this->repository->add($client);

        $output->writeln('<info>Client generated successfully.</info>');
        $output->writeln('<info> - Id: ' . $client->getIdentifier() . '</info>');
        $output->writeln('<info> - Secret: ' . $client->getSecret() . '</info>');
        $output->writeln('<info> - Name: ' . $client->getName() . '</info>');
        $output->writeln('<info> - User: ' . (string)$client->getUser() . '</info>');
        $output->writeln('<info> - Scopes: ' . $this->getScopes($client) . '</info>');
        $output->writeln('<info> - Grants: ' . $this->getGrantTypes($client) . '</info>');
        $output->writeln('<info> - Uris: ' . $this->getRedirectUris($client) . '</info>');

        return 1;
    }

    private function getRedirectUris(ClientInterface $client)
    {
        return "\r\n\t- " . implode("\r\n\t- ", $client->getRedirectUris());
    }

    private function getGrantTypes(ClientInterface $client)
    {
        return "\r\n\t- " . implode("\r\n\t- ", $client->getGrantTypes());
    }

    private function getScopes(ClientInterface $client)
    {
        return "\r\n\t- " . implode("\r\n\t- ", $client->getSupportsScopeIds());
    }

    /**
     * @param ClientInterface $client
     * @param array $scopes
     */
    private function addClientScopes(ClientInterface $client, array $scopes): void
    {
        $scopes = empty($scopes) ? $this->scopeRepository->findBy(['enabled' => true]) : $scopes;

        foreach ($scopes as $scope) {
            $client->addScope($scope);
        }
    }

    /**
     * @param string $id
     *
     * @return ScopeInterface
     */
    private function createScope($id): ScopeInterface
    {
        /** @var ScopeInterface $scope */
        $scope = $this->scopeFactory->createNew();
        $scope->setEnabled(true);
        $scope->setIdentifier($id);
        $scope->setName(ucfirst($id));

        return $scope;
    }

    /**
     * @param string $scope
     *
     * @return ScopeInterface|null|object
     */
    private function findScope(string $scope): ?ScopeInterface
    {
        return $this->scopeRepository->findOneBy(['identifier' => $scope]);
    }
}
