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

namespace Ymir\Cli\Command\Project;

use Carbon\Carbon;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection;
use Ymir\Cli\Console\ConsoleOutput;

class RollbackProjectCommand extends AbstractProjectDeploymentCommand
{
    /**
     * The alias of the command.
     *
     * @var string
     */
    public const ALIAS = 'rollback';

    /**
     * The name of the command.
     *
     * @var string
     */
    public const NAME = 'project:rollback';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Rollback project environment to a previous deployment')
            ->setAliases([self::ALIAS])
            ->addArgument('environment', InputArgument::OPTIONAL, 'The name of the environment to rollback', 'staging')
            ->addOption('select', null, InputOption::VALUE_NONE, 'Select the deployment to rollback to');
    }

    /**
     * {@inheritdoc}
     */
    protected function createDeployment(InputInterface $input, ConsoleOutput $output): Collection
    {
        $environment = $this->getStringArgument($input, 'environment');
        $projectId = $this->projectConfiguration->getProjectId();

        $deployments = $this->apiClient->getDeployments($projectId, $environment);

        if ($deployments->isEmpty()) {
            throw new RuntimeException(sprintf('The "%s" environment has never been deployed to', $environment));
        }

        $deployments = $deployments->where('status', 'finished')->values();

        if ($deployments->isEmpty()) {
            throw new RuntimeException(sprintf('The "%s" environment has no successful deployments to rollback to', $environment));
        }

        $deploymentId = !$this->getBooleanOption($input, 'select') ? $deployments[0]['id'] : $output->choice('Please select a deployment to rollback to', $deployments->mapWithKeys(function (array $deployment) {
            return [$deployment['id'] => $this->getDeploymentChoiceDisplayName($deployment)];
        })->all());

        $rollback = $this->apiClient->createRollback($this->projectConfiguration->getProjectId(), $this->getStringArgument($input, 'environment'), (int) $deploymentId);

        if (!$rollback->has('id')) {
            throw new RuntimeException('There was an error creating the rollback');
        }

        return $rollback;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuccessMessage(string $environment): string
    {
        return sprintf('Project "<comment>%s</comment>" environment rolled back successfully', $environment);
    }

    /**
     * Get the display name for the deployment for the choice question.
     */
    private function getDeploymentChoiceDisplayName(array $deployment): string
    {
        return sprintf('%s - %s (%s)', $deployment['uuid'], Arr::get($deployment, 'initiator.name'), Carbon::parse($deployment['created_at'])->diffForHumans());
    }
}
