<?php declare(strict_types=1);

namespace App\Command\Backup;

use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\ActionHandler\Collection\CreationHandler;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Errors;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCollectionCommand extends Command
{
    public const NAME = 'backup:create-collection';

    /**
     * @var CreationHandler
     */
    private CreationHandler $handler;

    /**
     * @var SecurityContextFactory
     */
    private SecurityContextFactory $authFactory;

    public function __construct(CreationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Creates a backup collection, where the versions could be uploaded')
            ->addOption('max-backups-count', 'b', InputOption::VALUE_REQUIRED)
            ->addOption('max-one-version-size', 'o', InputOption::VALUE_REQUIRED)
            ->addOption('max-collection-size', 'c', InputOption::VALUE_REQUIRED)
            ->addOption(
                'strategy', 's', InputOption::VALUE_OPTIONAL,
                'Backup rotation strategy (available: ' . implode(', ', BackupStrategy::STRATEGIES) . ')', BackupStrategy::STRATEGY_AUTO
            )
            ->addOption('description', 'd', InputOption::VALUE_REQUIRED)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL)
            ->addOption('filename', 'f', InputOption::VALUE_REQUIRED)
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL,
                'Assign custom id, do not generate random', '')
            ->addOption('ignore-error-if-exists', null, InputOption::VALUE_NONE,
                'Return success, when collection of given id already exists (does not compare collection details)')
            ->setHelp('Specify all limits and parameters to create a collection');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $form = new CreationForm();
        $form->maxBackupsCount   = (int) $input->getOption('max-backups-count');
        $form->maxCollectionSize = $input->getOption('max-collection-size');
        $form->maxOneVersionSize = $input->getOption('max-one-version-size');
        $form->id                = $input->getOption('id');

        $form->strategy    = $input->getOption('strategy');
        $form->description = $input->getOption('description');
        $form->password    = $input->getOption('password');
        $form->filename    = $input->getOption('filename');

        $response = $this->handler->handle($form, $this->authFactory->createShellContext());

        try {
            $this->handler->flush();
        } catch (BackupLogicException $exception) {
            if ($exception->getCode() === Errors::ERR_COLLECTION_ID_EXISTS && !$input->getOption('ignore-error-if-exists')) {
                throw $exception;
            }

            // case: when collection already exists, and we want to skip this error, then we return normal exit code
            //       It's very useful in scripts in Docker, where we want to have a "present state" enforced
            $output->writeln($form->id);
            return 0;
        }

        if ($response->isSuccess()) {
            $output->writeln($response->getCollection()->getId());

            return 0;
        }

        $output->writeln(json_encode($response->jsonSerialize(), JSON_PRETTY_PRINT));
        return 1;
    }
}
