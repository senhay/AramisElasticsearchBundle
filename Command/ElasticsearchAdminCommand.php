<?php

namespace Aramis\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Aramis\Bundle\ElasticsearchBundle\Index\Index;

use Httpful\Request;

class ElasticsearchAdminCommand extends ContainerAwareCommand
{
    /**
     * @var Index
     */
    private $_elasticsearch;

    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('aramis:elasticsearch:admin')
            ->setDescription('Elasticsearch Command Line Tool')
            ->addArgument('action', InputArgument::OPTIONAL, 'Action (delete|alias|search|mapping)')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Elasticsearch host', 'localhost')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Elasticsearch port', '9200')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'Elasticsearch index (concerned actions: search,mapping)', null)
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Elasticsearch type (concerned actions: search)', null)
            ->addOption('field', null, InputOption::VALUE_OPTIONAL, 'Index field (concerned actions: search)', null)
            ->addOption('value', null, InputOption::VALUE_OPTIONAL, 'Field value (concerned actions: search)', null);
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
        $this->_elasticsearch = new Index(
            array('host' => $input->getOption('host'),
            'port' => $input->getOption('port'))
        );

        $dialog = $this->getHelperSet()->get('dialog');

        switch ($input->getArgument('action')) {

            // Delete action
            case 'delete':
                $this->printStatus();
                $indexNames = $this->_elasticsearch->getIndexNames();
                $indexNames[] = '<< cancel >>';
                $name = $dialog->select(
                    $output,
                    '<question>Please select the index to delete:</question>',
                    $indexNames,
                    count($indexNames) - 1
                );
                if ($name != (count($indexNames) - 1)) {
                    if ($dialog->ask(
                        $output,
                        sprintf("\n<question>You will delete the index {%s}, are you sure ? (y/N)</question>", $indexNames[$name]),
                        ''
                    ) == 'y') {
                        $this->_elasticsearch->deleteIndex($indexNames[$name]);
                        $output->writeln("\n<info>The index is deleted.</info>");
                    }
                }
                $this->printStatus();
                break;

            // Alias action
            case 'alias':
                $this->printStatus();
                $indexNames = $this->_elasticsearch->getIndexNames();
                $indexNames[] = '<< cancel >>';
                $name = $dialog->select(
                    $output,
                    '<question>Please select the index concerned by the alias:</question>',
                    $indexNames,
                    count($indexNames) - 1
                );
                if ($name != (count($indexNames) - 1)) {
                    $choices = array('0' => 'add', '1' => 'remove', '2' => '<< cancel >>');
                    $choice = $dialog->select(
                        $output,
                        "\n<question>You wanna add or remove an alias ?</question>",
                        $choices,
                        count($choices) - 1
                    );

                    if ($choice != (count($choices) - 1)) {
                        $alias = $dialog->ask(
                            $output,
                            "\n<question>Please enter the alias name:</question>",
                            ''
                        );
                        if ('add' == $choices[$choice]) {
                            if ($alias && $dialog->ask(
                                $output,
                                sprintf(
                                    "\n<question>You will attach the alias {%s} to the index {%s}, are you sure ? (y/N)</question>",
                                    $alias,
                                    $indexNames[$name]
                                ),
                                ''
                            ) == 'y') {
                                $this->_elasticsearch->changeAlias($indexNames[$name], $alias);
                                $output->writeln("\n<info>The alias is attached.</info>");
                            }
                        } elseif ('remove' == $choices[$choice]) {
                            if ($alias && $dialog->ask(
                                $output,
                                sprintf(
                                    "\n<question>You will remove the alias {%s} from the index {%s}, are you sure ? (y/N)</question>",
                                    $alias,
                                    $indexNames[$name]
                                ),
                                ''
                            ) == 'y') {
                                $this->_elasticsearch->removeAlias($indexNames[$name], $alias);
                                $output->writeln("\n<info>The alias is removed.</info>");
                            }
                        }
                    }
                }
                $this->printStatus();
                break;

            // Search action
            case 'search':
                if (!$input->getOption('index') ||
                    !$input->getOption('type')  ||
                    !$input->getOption('field') ||
                    !$input->getOption('value')) {
                    $output->writeln("<error>\n[--index], [--type], [--field] and [--value] options are required for this action.</error>\n");
                } else {
                    $uri = sprintf(
                        '%s:%d/%s/%s/_search?q=%s:%s&pretty',
                        $input->getOption('host'),
                        $input->getOption('port'),
                        $input->getOption('index'),
                        $input->getOption('type'),
                        $input->getOption('field'),
                        $input->getOption('value')
                    );
                    $response = Request::get($uri)->send();
                    print_r($response->body->hits->hits);
                }

                break;
            default:
                if (!$input->getArgument('action')) {
                    $this->printStatus();
                }
                break;

            // Mapping action
            case 'mapping':
                if ($input->getOption('index')) {
                    $mapping = $this->_elasticsearch->getIndex($input->getOption('index'))->getMapping();
                    print_r($mapping);
                } else {
                    $output->writeln("\n<error>[--index=] option is required for this action.</error>\n");
                }
                break;
            default:
                if (!$input->getArgument('action')) {
                    $this->printStatus();
                }
                break;
        }
    }

    /**
     * Prints status
     */
    protected function printStatus()
    {
        $statuses = $this->_elasticsearch->getStatus()->getIndexStatuses();

        $mask = "| %-30.30s | %-30.30s | %-20.20s | %-20.20s | %-20.20s | %-20.20s |\n";
        $maskLineOut = "+%-157.157s+\n";
        $maskLineIn  = "|%-157.157s|\n";
        print("\n");
        printf($maskLineOut, str_repeat("-", 157));
        printf($mask, '<< Index >>', '<< Types >>', '<<  Aliases >>', '<<  Documents >>', '<<  Deleted >>', '<<  bytes >>');
        printf($maskLineIn, str_repeat("-", 157));
        foreach ($statuses as $status) {
            $index  = $status->getIndex();
            $statusData = $status->getData();
            $typeName   = key($index->getMapping());
            $arrTypeNames = array();
            foreach ($index->getMapping() as $type => $mapping) {
                $arrTypeNames[] = $type;
            }
            if (count($arrTypeNames) > 2) {
                $strTypes = key($index->getMapping()) . ' +' . (count($arrTypeNames) - 1) . ' others';
            } else {
                $strTypes   = implode(', ', $arrTypeNames);
            }
            //$strTypes   = implode(', '."\n", $arrTypeNames);
            $indexName  = $index->getName();
            $arrAliases = !is_array(@$status->getAliases()) ? array(@$status->getAliases()) : @$status->getAliases();
            $strAliases = implode(', ', $arrAliases);
            $indexData  = $statusData['indices'][$indexName];
            $numDocuments = $indexData["docs"]['num_docs'];
            $delDocuments = $indexData["docs"]['deleted_docs'];
            $sizeInBytes  = $indexData['index']['size_in_bytes'];

            printf($mask, $indexName, $strTypes, $strAliases, $numDocuments, $delDocuments, $sizeInBytes);
        }
        printf($maskLineOut, str_repeat("-", 157));
        print("\n");
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
        $connection = @fsockopen($input->getOption('host'), $input->getOption('port'));
        if (is_resource($connection)) {
            fclose($connection);
        } else {
            $output->writeln("\n<error>Elasticsearch server is not responding.</error>");
        }
    }
}
