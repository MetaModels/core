<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Command;

use MetaModels\InformationProvider\MetaModelInformationCollector;
use MetaModels\Schema\SchemaGenerator;
use MetaModels\Schema\SchemaInformation;
use MetaModels\Schema\SchemaManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This validates the schema of the current installation.
 */
class SchemaValidatorCommand extends Command
{
    /**
     * The information collector.
     */
    private MetaModelInformationCollector $collector;

    /**
     * The schema generator.
     */
    private SchemaGenerator $generator;

    /**
     * The schema manager.
     */
    private SchemaManager $manager;

    public function __construct(
        MetaModelInformationCollector $collector,
        SchemaGenerator $generator,
        SchemaManager $manager
    ) {
        $this->collector = $collector;
        $this->generator = $generator;
        $this->manager   = $manager;
        parent::__construct('metamodels:schema-update');
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Perform the update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generator->generate($information = new SchemaInformation(), $this->collector->getCollection());

        if ($input->getOption('force')) {
            $this->manager->preprocess($information);
            $this->manager->process($information);
            $this->manager->postprocess($information);

            return 0;
        }

        foreach ($this->manager->validate($information) as $item) {
            $output->writeln($item);
        }

        return 0;
    }
}
