<?php

namespace Fgir\QuickViewBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ViewCommand extends ContainerAwareCommand
{
    private $om;
    private $adapter;

    protected function configure()
    {
        $this
            ->setName('quick-view:view')
            ->setDescription('@todo')
            ->addArgument(
                'what',
                InputArgument::REQUIRED,
                'PK'
            )
            ->addArgument(
                'where',
                InputArgument::REQUIRED,
                '@todo'
            )

            ->addOption(
                'serverId',
                null,
                InputOption::VALUE_OPTIONAL,
                'Server id to use'
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->adapter = $this->getContainer()->get('fgir_quickview.adapter.odm');
        $this->input = $input;
        $this->output = $output;

        $what = $input->getArgument('what');
        $where = $input->getArgument('where');
        $className = $this->getClassName($where);

        $found = $this->adapter->findOne($className, $what);

        if (!$found) {
            $output->writeln('<error>We didn\'t found anything matching your criterias (' . $what . ' - ' . $className . ')</error>');
        }

        $this->display($found);
    }

    private function display($object)
    {
        $json = $serialized = $this->getContainer()->get('serializer')->serialize($object, 'json');
        $back = json_decode($json, true);
        $json = json_encode($back, JSON_PRETTY_PRINT);
        $this->output->writeln($json);
    }

    private function getClassName($where)
    {
        $possibilities = $this->adapter->findClassesMatching($where);
        if (count($possibilities) === 0) {
            throw new \Exception("Unable to find a matching classname");
        } elseif (count($possibilities) === 1) {
            $className = $possibilities[0];
        }

        foreach ($possibilities as $possibility) {
            if ($possibility === $where) {
                return $possibility;
            }
        }
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'We found several classes that match your query',
            $possibilities,
            0
        );
        $question->setErrorMessage('Color %s is invalid.');
        $userChoice = $helper->ask($this->input, $this->output, $question);
        return $userChoice;
    }
}
