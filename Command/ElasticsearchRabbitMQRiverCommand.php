<?php

namespace Aramis\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Httpful\Request;

class ElasticsearchRabbitMQRiverCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('aramis:elasticsearch:rabbitmq_river')
            ->setDescription('Elasticsearch RabbitMQ River Manager')
            ->addArgument('action', InputArgument::REQUIRED, 'Action (create|delete)')
            ->addOption('river_name', null, InputOption::VALUE_REQUIRED, 'River name', 'elasticsearch_river')
            ->addOption('es_host', null, InputOption::VALUE_REQUIRED, 'Elasticsearch host', 'localhost')
            ->addOption('es_port', null, InputOption::VALUE_REQUIRED, 'Elasticsearch port', '9200')
            ->addOption('amqp_host', null, InputOption::VALUE_REQUIRED, 'RabbitMQ host (concerned actions: create)', 'localhost')
            ->addOption('amqp_port', null, InputOption::VALUE_REQUIRED, 'RabbitMQ port (concerned actions: create)', '5672')
            ->addOption('amqp_user', null, InputOption::VALUE_REQUIRED, 'RabbitMQ user (concerned actions: create)', 'guest')
            ->addOption('amqp_pass', null, InputOption::VALUE_REQUIRED, 'RabbitMQ password (concerned actions: create)', 'guest')
            ->addOption('amqp_vhost', null, InputOption::VALUE_REQUIRED, 'RabbitMQ vhost (concerned actions: create)', '/')
            ->addOption('amqp_queue', null, InputOption::VALUE_REQUIRED, 'RabbitMQ queue (concerned actions: create)', 'elasticsearch')
            ->addOption('amqp_exchange', null, InputOption::VALUE_REQUIRED, 'RabbitMQ exchange (concerned actions: create)', 'elasticsearch')
            ->addOption('amqp_routing', null, InputOption::VALUE_REQUIRED, 'RabbitMQ routing_key (concerned actions: create)', 'elasticsearch');
    }

    /**
     * Initialize
     *
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
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uri = sprintf('%s:%d/_river/%s/_meta', $input->getOption('es_host'), $input->getOption('es_port'), $input->getOption('river_name'));

        if ('create' == $input->getArgument('action')) {
            // Build river
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
                'exchange_type' => 'direct',
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
            $river = array('type' => 'rabbitmq');
            $river['rabbitmq'] = $rabbitmq;
            $river['index'] = $index;

            // PUT Data
            $serializer = $this->getContainer()->get('jms_serializer');
            $response = Request::put($uri)
                ->sendsJson()
                ->body($serializer->serialize($river, 'json'))
                ->send();

            (201 == $response->code) ? $output->writeln('<info>Done!</info>') : $output->writeln('<error>Not done!</error>');
        } elseif ('delete' == $input->getArgument('action')) {
            // Delete
            $response = Request::delete($uri)->send();

            (200 == $response->code) ? $output->writeln('<info>Done!</info>') : $output->writeln('<error>Not done!</error>');
        } else {
            $output->writeln('<error>Not valid argument!, action must be "create" or "delete".</error>');
        }

    }

    /**
     * Checks ports
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function checkPorts(InputInterface $input, OutputInterface $output)
    {
        // Check Elasticsearch server
        $connection = @fsockopen($input->getOption('es_host'), $input->getOption('es_port'));
        if (is_resource($connection)) {
            fclose($connection);
        } else {
            throw new InvalidException("\nElasticsearch server is not responding.");
        }

        if ('delete' != $input->getArgument('action')) {
            // Check RabbitMQ server
            $connection = @fsockopen($input->getOption('amqp_host'), $input->getOption('amqp_port'));
            if (is_resource($connection)) {
                fclose($connection);
            } else {
                throw new InvalidException("\nRabbitMQ server is not responding.");
            }
        }
    }
}
