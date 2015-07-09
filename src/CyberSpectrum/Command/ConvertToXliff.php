<?php

/**
 * This toolbox provides easy ways to generate .xlf (XLIFF) files from Contao language files, push them to transifex
 * and pull translations from transifex and convert them back to Contao language files.
 *
 * @package      cyberspectrum/contao-toolbox
 * @author       Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author       Yanick Witschi <yanick.witschi@terminal42.ch>
 * @copyright    CyberSpectrum
 * @license      LGPL-3.0+.
 * @filesource
 */

namespace CyberSpectrum\Command;

use CyberSpectrum\Translation\Contao;
use CyberSpectrum\Translation\Contao\ContaoFile as ContaoFile;
use CyberSpectrum\Translation\Xliff;
use CyberSpectrum\Translation\Xliff\XliffFile;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class converts Contao language files to XLIFF format.
 */
class ConvertToXliff extends ConvertBase
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('to-xliff');
        $this->setDescription('Update xliff translations from Contao base language.');

        $this->setHelp('Convert the base language from the contao folder into files in transifex folder' . PHP_EOL);
    }

    /**
     * {@inheritDoc}
     */
    protected function getLanguageBasePath()
    {
        return $this->ctolang;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDestinationBasePath()
    {
        return $this->txlang;
    }

    /**
     * {@inheritDoc}
     */
    protected function isValidSourceFile($file)
    {
        return (substr($file, -4) == '.php');
    }

    /**
     * {@inheritDoc}
     */
    protected function isValidDestinationFile($file)
    {
        return (substr($file, -4) == '.xlf');
    }

    /**
     * Convert the source file to the destination file.
     *
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @param ContaoFile      $src    The source Contao file.
     *
     * @param XLiffFile       $dst    The destination XLIFF file.
     *
     * @param ContaoFile      $base   The base Contao file.
     *
     * @return void
     */
    protected function convert(OutputInterface $output, ContaoFile $src, XLiffFile $dst, ContaoFile $base)
    {
        $baseKeys = $base->getKeys();
        foreach ($baseKeys as $key) {
            if (!($basVal = $base->getValue($key))) {
                $dst->remove($key);
                continue;
            }
            $dst->setSource($key, $basVal);
            if (($value = $src->getValue($key)) !== null) {
                $dst->setTarget($key, $value);
            }
        }

        foreach ($dst->getKeys() as $key) {
            if (!in_array($key, $baseKeys)) {
                $this->writelnVerbose(
                    $output,
                    sprintf('Language key <info>%s</info> is not present in the source. Removing it.', $key)
                );
                $dst->remove($key);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function processLanguage(OutputInterface $output, $language)
    {
        $this->writeln($output, sprintf('processing language: <info>%s</info>...', $language));

        $destinationFiles = array();
        foreach ($this->baseFiles as $file) {
            $this->writelnVerbose($output, sprintf('processing file: <info>%s</info>...', $file));

            $basFile = $this->getLanguageBasePath()
                . DIRECTORY_SEPARATOR . $this->baselanguage . DIRECTORY_SEPARATOR . $file;
            $srcFile = $this->getLanguageBasePath() . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $file;

            $domain = basename($file, '.php');

            $dstFile            = $domain . '.xlf';
            $destinationFiles[] = $dstFile;

            $src  = new ContaoFile($srcFile);
            $base = new ContaoFile($basFile);

            $dstDir = $this->getDestinationBasePath() . DIRECTORY_SEPARATOR . $language;
            if (!is_dir($dstDir)) {
                mkdir($dstDir, 0755, true);
            }

            $dest = new XliffFile($dstDir . DIRECTORY_SEPARATOR . $dstFile);
            $dest->setDataType('php');
            $dest->setSrcLang($this->baselanguage);
            $dest->setTgtLang($language);
            $dest->setOriginal($domain);
            if (file_exists($srcFile)) {
                $time = filemtime($srcFile);
            } else {
                $time = filemtime($basFile);
            }
            $dest->setDate($time);

            $this->convert($output, $src, $dest, $base);
            if (is_file($dstDir . DIRECTORY_SEPARATOR . $dstFile) || $dest->getKeys()) {
                $dest->save();
            }
        }

        $this->cleanupObsoleteFiles($output, $language, $destinationFiles);
    }
}
