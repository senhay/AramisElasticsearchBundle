<?php

namespace Aramis\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Httpful\Request;

class DeleteRabbitMQRiverCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('aramis:elasticsearch:delete_river')
            ->setDescription('Delete RabbitMQ River')
            ->addOption('river_name', null, InputOption::VALUE_REQUIRED, 'River name', 'elasticsearch_river')
            ->addOption('es_host', null, InputOption::VALUE_REQUIRED, 'ElasticSearch host', 'localhost')
            ->addOption('es_port', null, InputOption::VALUE_REQUIRED, 'ElasticSearch port', '9200');
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
        // uri
        $uri = sprintf('%s:%d/_river/%s/', $input->getOption('es_host'), $input->getOption('es_port'), $input->getOption('river_name'));

        // Delete
        $response = Request::delete($uri)->send();

        (200 == $response->code) ? $output->writeln('<info>Done!</info>') : $output->writeln('<error>Not done!</error>');
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
    }
}
