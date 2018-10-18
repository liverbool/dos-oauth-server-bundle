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

use phpseclib\Crypt\RSA;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KeysCommand extends Command
{
    /**
     * @var string
     */
    private $defaultOutputPath;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $defaultOutputPath)
    {
        parent::__construct();

        $this->defaultOutputPath = $defaultOutputPath;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('dos:oauth:keys');
        $this->setDescription('Generate RSA key.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite keys they already exist.');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output path to store key files. Default is root project.',
            $this->defaultOutputPath
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $keys = (new RSA())->createKey(4096);
        $defaultOutputPath = $input->getOption('output');

        list($publicKey, $privateKey) = [
            rtrim($defaultOutputPath ?? $this->defaultOutputPath, '/') . '/oauth-public.key',
            rtrim($defaultOutputPath ?? $this->defaultOutputPath, '/') . '/oauth-private.key',
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && !$input->getOption('force')) {
            $output->writeln(
                '<error>Encryption keys already exist. Use the --force option to overwrite them.</error>'
            );

            return 0;
        }

        file_put_contents($publicKey, $keys['publickey']);
        file_put_contents($privateKey, $keys['privatekey']);

        $output->writeln('<info>Encryption keys generated successfully.</info>');
        $output->writeln("<info> -> $publicKey</info>");
        $output->writeln("<info> -> $privateKey</info>");

        return 1;
    }
}
