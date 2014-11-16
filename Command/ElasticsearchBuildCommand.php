<?php

namespace Aramis\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Aramis\Bundle\ElasticsearchBundle\Index\Index;

use Httpful\Request;

class ElasticsearchBuildCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('aramis:elasticsearch:build')
            ->setDescription('Elasticsearch Build Tool')
            ->addArgument('index', InputArgument::REQUIRED, 'Index')
            ->addArgument('action', InputArgument::REQUIRED, 'Action (build|create|empty|refresh|rollback|document)')
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'Use alias (concerned actions: build,create)', true)
            ->addOption('queue', null, InputOption::VALUE_REQUIRED, 'Use RabbitMQ River (concerned actions: build,empty,refresh)', false)
            ->addOption('rollback_level', null, InputOption::VALUE_REQUIRED, 'Depth of rollback (concerned actions: build), alias must be TRUE', 1)
            ->addOption('ids', null, InputOption::VALUE_REQUIRED, 'Documents ids separated by commas (concerned actions: refresh,document)', null);
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
    }

    /**
     * Execute
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = $this->getContainer()->get('aramis_elasticsearch_builder');

        switch ($input->getArgument('action')) {
            case 'build':
                $builder->buildIndex($input->getArgument('index'), $input->getOption('alias'), $input->getOption('queue'), $input->getOption('rollback_level'));
                $output->writeln("\n<info>The index is builded.</info>\n");
                break;
            case 'create':
                $builder->createIndex($input->getArgument('index'), $input->getOption('alias'));
                $output->writeln("\n<info>The index is created.</info>\n");
                break;
            case 'empty':
                $builder->requestDocuments($input->getArgument('index'), 'delete', $input->getOption('queue'));
                $output->writeln("\n<info>The index is empty.</info>\n");
                break;
            case 'refresh':
                $ids = $input->getOption('ids') ? explode(',', $input->getOption('ids')) : null;
                $builder->refreshDocuments($input->getArgument('index'), $input->getOption('queue'), $ids);
                $output->writeln("\n<info>The index is refreshed.</info>\n");
                break;
            case 'rollback':
                $builder->rollback($input->getArgument('index'), $input->getOption('rollback_level'));
                $output->writeln("\n<info>The rollback is done.</info>\n");
                break;
            case 'document':
                if ($input->getOption('ids')) {
                    $ids = $input->getOption('ids') ? explode(',', $input->getOption('ids')) : array();
                    print_r($builder->getDocumentsByIds($input->getArgument('index'), $ids));
                } else {
                    $output->writeln("\n<error>[--ids] option is required for this action.</error>\n");
                }

                break;
            default:
                $output->writeln("\n<error>Not valid action.</error>\n");
                break;
        }
    }
}
