<?php

namespace Aramis\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRabbitMQRiverCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('aramis_elasticsearch:create_river')
            ->setDescription('Create RabbitMQ River')
            ->addOption('es_host', null, InputOption::VALUE_REQUIRED, 'ElasticSearch host', 'localhost')
            ->addOption('es_port', null, InputOption::VALUE_REQUIRED, 'ElasticSearch port', '9200')
            ->addOption('amqp_host', null, InputOption::VALUE_REQUIRED, 'RabbitMQ host', 'localhost')
            ->addOption('amqp_port', null, InputOption::VALUE_REQUIRED, 'RabbitMQ port', '5672')
            ->addOption('amqp_user', null, InputOption::VALUE_REQUIRED, 'RabbitMQ user', 'guest')
            ->addOption('amqp_pass', null, InputOption::VALUE_REQUIRED, 'RabbitMQ password', 'guest')
            ->addOption('amqp_vhost', null, InputOption::VALUE_REQUIRED, 'RabbitMQ vhost', '/')
            ->addOption('amqp_queue', null, InputOption::VALUE_REQUIRED, 'RabbitMQ queue', 'elasticsearch')
            ->addOption('amqp_exchange', null, InputOption::VALUE_REQUIRED, 'RabbitMQ exchange', 'elasticsearch')
            ->addOption('amqp_routing', null, InputOption::VALUE_REQUIRED, 'RabbitMQ routing_key', 'elasticsearch');
    }

    /**
     * Initialize
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->checkPorts($input, $output);
    }

    /**
     * Execute
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uri = sprintf('%s:%d/_river/my_river/_meta', $input->getOption('es_host'), $input->getOption('es_port'));

        $river = array('type' => 'rabbitmq');

        $rabbitmq = array(
            'host' => $input->getOption('amqp_host'),
            'port' => $input->getOption('amqp_port'),
            'user' => $input->getOption('amqp_user'),
            'pass' => $input->getOption('amqp_pass'),
            'vhost' => $input->getOption('amqp_vhost'),
            'queue' => $input->getOption('amqp_queue'),
            'exchange' => $input->getOption('amqp_exchange'),
            'routing_key' => $input->getOption('amqp_routing'),
            'exchange_declare' => true,
            'exchange_type' => "direct",
            'exchange_durable' => true,
            'queue_bind' => true,
            'queue_durable' => true,
            'queue_auto_delete' => false,
            'heartbeat' => '30m',
            'qos_prefetch_size' => 0,
            'qos_prefetch_count' => 10,
            'nack_errors' => true
            );

        $index = array(
            'bulk_size' => 100,
            'bulk_timeout' => '10ms',
            'ordered' => false,
            'replication' => 'default'
            );



        //die($input->getOption('host'));
        //try {
            // Un jour, il faudra trés certainement faire évoluer le bundle pour qu'il demande aussi le catalog
            /*$locale = $input->getArgument('locale');
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
        }*/
    }

    /**
     * Checks ports
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function checkPorts(InputInterface $input, OutputInterface $output)
    {
        // Check ElasticSearch server
        $connection = @fsockopen($input->getOption('es_host'), $input->getOption('es_port'));
        if (is_resource($connection)) {
            fclose($connection);
        } else {
            $output->writeln('<error>ElasticSearch server is not responding.</error>');
        }

        // Check RabbitMQ server
        $connection = @fsockopen($input->getOption('amqp_host'), $input->getOption('amqp_port'));
        if (is_resource($connection)) {
            fclose($connection);
        } else {
            $output->writeln('<error>RabbitMQ server is not responding.</error>');
        }
    }
}
