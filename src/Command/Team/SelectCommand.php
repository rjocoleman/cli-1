<?php

declare(strict_types=1);

/*
 * This file is part of Placeholder command-line tool.
 *
 * (c) Carl Alexander <contact@carlalexander.ca>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Placeholder\Cli\Command\Team;

use Placeholder\Cli\Command\AbstractCommand;
use Placeholder\Cli\Console\OutputStyle;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

class SelectCommand extends AbstractCommand
{
    /**
     * The name of the command.
     *
     * @var string
     */
    public const NAME = 'team:select';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Select to a new currently active team');
    }

    /**
     * {@inheritdoc}
     */
    protected function perform(InputInterface $input, OutputStyle $output)
    {
        $teams = $this->apiClient->getTeams();

        if (empty($teams)) {
            throw new RuntimeException('You\'re not on any team');
        }

        $teamId = $output->choiceCollection('Which team would you like to switch to?', $teams->sortBy->name);

        $this->setActiveTeamId($teamId);

        $output->writeln(sprintf('"%s" is now your active team', $teams->firstWhere('id', $teamId)['name']));
    }
}
