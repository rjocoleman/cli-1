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
use Ymir\Cli\Console\ConsoleOutput;

class DeleteDatabaseServerCommand extends AbstractDatabaseCommand
{
    /**
     * The name of the command.
     *
     * @var string
     */
    public const NAME = 'database:server:delete';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Delete an existing database server')
            ->addArgument('database', InputArgument::OPTIONAL, 'The ID or name of the database server to delete');
    }

    /**
     * {@inheritdoc}
     */
    protected function perform(InputInterface $input, ConsoleOutput $output)
    {
        $databaseId = $this->determineDatabaseServer('Which database server would you like to delete', $input, $output);

        if (!$output->confirm('Are you sure you want to delete this database server?', false)) {
            return;
        }

        $this->apiClient->deleteDatabaseServer($databaseId);

        $output->infoWithDelayWarning('Database deleted');
    }
}
