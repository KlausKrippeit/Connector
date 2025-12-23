<?php
namespace Connector\Command;

use Connector\Client\RssClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'connector:fetch-api',
    description: 'Fetch RSS/XML APIs and output items'
)]
class FetchApiCommand extends Command
{
    public function __construct(
        private RssClient $rssClient
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('api', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->rssClient->fetch() as $item) {
            $output->writeln(sprintf(
                '- %s (%s)',
                $item->title,
                $item->publishedAt->format('Y-m-d H:i')
            ));
        }

        return Command::SUCCESS;
    }
}
