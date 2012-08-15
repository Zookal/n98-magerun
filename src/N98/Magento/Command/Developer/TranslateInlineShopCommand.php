<?php

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Cache\ClearCommand as ClearCacheCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

class TranslateInlineShopCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:translate:shop')
            ->addArgument('store', InputArgument::REQUIRED, 'Store code or ID')
            ->setDescription('Toggle inline translation tool for shop')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @TODO move shop init code into own base class */
        $this->detectMagento($output);
        if ($this->initMagento()) {
            try {
                $store = \Mage::app()->getStore($input->getArgument('store'));
            } catch (\Mage_Core_Exception $e) {
                $output->writeln(array(
                    '<error>Invalid store</error>',
                    '<info>Try one of this:</info>'
                ));
                foreach (\Mage::app()->getStores() as $store) {
                    $output->writeln('- <comment>' . $store->getCode() . '</comment>');
                }
                return;
            }
        }

        $enabled = \Mage::getStoreConfigFlag('dev/translate_inline/active', $store->getId());
        \Mage::app()->getConfig()->saveConfig('dev/translate_inline/active', $enabled ? 0 : 1, 'stores', $store->getId());

        $output->writeln('<info>Inline Translation ' . (!$enabled ? 'enabled' : 'disabled') . '</info>');

        $this->getApplication()->get('cache:clear')->run($input, new NullOutput());
    }
}