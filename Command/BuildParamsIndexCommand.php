<?php

namespace Aramis\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DriverManager;
use Doctrine\Shards\DBAL\SQLAzure\SQLAzureShardManager;

use Aramis\Bundle\ElasticsearchBundle\Query\OfferQuery;
use Aramis\Bundle\ElasticsearchBundle\Query\GeneralQuery;

class BuildParamsIndexCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('index:params:build')
            ->setDescription('Build params elasticsearch index')
            ->addArgument('locale', InputArgument::REQUIRED, 'The locale (fr|de|es)');
            // Un jour, il faudra trés certainement faire évoluer le bundle pour qu'il demande aussi le catalog
    }

    /**
     * Initialize
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->generalQuery = new GeneralQuery($this->getContainer()->get('doctrine.dbal.default_connection'));
    }

    /**
     * Execute
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Un jour, il faudra trés certainement faire évoluer le bundle pour qu'il demande aussi le catalog
            $locale = $input->getArgument('locale');
            $generalServiceName = 'general_' . $locale . '_index';

            $output->writeln('<comment>Getting General data ...</comment>');
            $arrGeneralInfos = $this->generalQuery->getGeneralInfos($locale);

            $dialog = $this->getHelperSet()->get('dialog');
            if ($dialog->ask($output, count($arrGeneralInfos) . ' params found; Are you sure? (y/N)', '') == 'y') {
                $generalDataIndex = $this->getContainer()->get($generalServiceName);
                $generalDataIndex->create();
                $generalDataType = $generalDataIndex->getIndexType();

                $timestamp = time();
                $output->writeln('<comment>Adding general data to index ...</comment>');
                $generalDataType->addDocuments($arrGeneralInfos);
                $generalDataType->getIndex()->refresh();

                $output->writeln('<comment>Indexing done (' . (time() - $timestamp) . 's).</comment>');
            }

        } catch (\Exception $e) {
            $output->writeln('<error>Can not get data !</error>');
        }
    }
}
