<?php

/**
 * This file is part of cyberspectrum/contao-toolbox.
 *
 * (c) 2013-2017 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/contao-toolbox.
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2017 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/contao-toolbox/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace CyberSpectrum\ContaoToolBox\Console\Command\Convert;

use CyberSpectrum\ContaoToolBox\Console\Command\CommandBase;
use CyberSpectrum\ContaoToolBox\Converter\AbstractConverter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class provides base methods for converting commands.
 */
abstract class ConvertBase extends CommandBase
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();
        $this->addOption('cleanup', null, InputOption::VALUE_NONE, 'if set, remove obsolete files.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $converter = $this->createConverter(new ConsoleLogger($output));
        if ('all' !== ($languages = $input->getArgument('languages'))) {
            $converter->setOnlyLanguages(explode(',', $languages));
        }
        if ($skipFiles = $this->project->getSkipFiles()) {
            $converter->setIgnoredResources($skipFiles);
        }
        if ($input->getOption('cleanup')) {
            $converter->setCleanupObsolete();
        }

        $converter->convert();
    }

    /**
     * Create the converter instance.
     *
     * @param LoggerInterface $logger The logger instance.
     *
     * @return AbstractConverter
     */
    abstract protected function createConverter(LoggerInterface $logger);
}
