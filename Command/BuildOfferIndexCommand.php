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

class BuildOfferIndexCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('index:build')
            ->setDescription('Build elasticsearch index')
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
        $this->offerQuery = new OfferQuery($this->getContainer()->get('doctrine.dbal.default_connection'), $this->getContainer()->get('tools'));
        //$this->generalQuery = new GeneralQuery($this->getContainer()->get('doctrine.dbal.default_connection'));
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
            $serviceName = 'offer_' . $locale . '_index';
            //$generalServiceName = 'general_' . $locale . '_index';

            $output->writeln('<comment>Getting data ...</comment>');
            $arrOffers = $this->offerQuery->getOffers($locale);

            //$output->writeln('<comment>Getting General data ...</comment>');
            //$arrGeneralInfos = $this->generalQuery->getGeneralInfos($locale);

            $dialog = $this->getHelperSet()->get('dialog');
            if ($dialog->ask($output, count($arrOffers) . ' offers found; Are you sure? (y/N)', '') == 'y') {
                $dataIndex = $this->getContainer()->get($serviceName);
                $dataIndex->create();
                $dataType = $dataIndex->getIndexType();

                $timestamp = time();
                $output->writeln('<comment>Adding data to index ...</comment>');

                try {
                    $dataType->addDocuments($arrOffers);
                    $dataType->getIndex()->refresh();
                } catch (\Elastica\Exception\BulkResponseException $e) {
                    $output->writeln(var_dump($e->getFailures()));
                    throw $e;
                }

                $output->writeln('<comment>Indexing done (' . (time() - $timestamp) . 's).</comment>');
            }

        } catch (\Exception $e) {
            $output->writeln('<error>Can not get data !</error>');
        }
    }
}
