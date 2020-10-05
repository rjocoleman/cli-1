<?php

declare(strict_types=1);

/*
 * This file is part of Ymir command-line tool.
 *
 * (c) Carl Alexander <support@ymirapp.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ymir\Cli\Command\Database;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Ymir\Cli\Console\OutputStyle;

class CreateDatabaseCommand extends AbstractDatabaseCommand
{
    /**
     * The name of the command.
     *
     * @var string
     */
    public const NAME = 'database:create';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Create a new database on a database server')
            ->addArgument('database', InputArgument::OPTIONAL, 'The ID or name of the database server where the database will be created')
            ->addArgument('name', InputArgument::OPTIONAL, 'The username of the new database user');
    }

    /**
     * {@inheritdoc}
     */
    protected function perform(InputInterface $input, OutputStyle $output)
    {
        $database = $this->determineDatabaseServer('On which database server would you like to create the new database?', $input, $output);
        $name = $this->getStringArgument($input, 'name');

        if (empty($name) && $input->isInteractive()) {
            $name = $output->ask('What is the name of the database');
        }

        $this->apiClient->createDatabase((int) $database['id'], $name);

        $output->info('Database created');
    }
}
