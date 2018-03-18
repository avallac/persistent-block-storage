<?php

namespace AVAllAC\PersistentBlockStorage\Command;

use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckVolumeCommand extends Command
{
    private $storageManager;

    public function __construct(?string $name = null, ?ServerStorageManager $storageManager = null)
    {
        $this->storageManager = $storageManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('check:volume')
            ->setDescription('Check md5sum for volume')
            ->addArgument('volume', InputArgument::REQUIRED, 'Volume ID');
    }

    /**
     * @throws IncorrectVolumeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $M = 1024 * 1024;
        $volume = $input->getArgument('volume');
        if (!$this->storageManager->volumeAvailable($volume)) {
            $output->writeln('<error>Incorrect volume id</error>');
            exit;
        }
        $volumeResource = $this->storageManager->getVolumeResource($volume);
        $output->writeln([
            '<fg=yellow;options=bold,underscore>Volume checking</>',
            '',
            'Path: <comment>' . $this->storageManager->getVolumePath($volume) . '</comment>',
            ''
        ]);
        $volumeSize = $this->storageManager->getVolumeSize($volume);
        $progressBar = new ProgressBar($output, ceil($volumeSize / $M));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();
        fseek($volumeResource, 0);
        $hashFunc = hash_init('md5');
        $counted = 0;
        $lastUpdate = 0;
        while ($counted < $volumeSize) {
            $needRead = min($M, $volumeSize - $counted);
            $data = fread($volumeResource, $needRead);
            if (strlen($data) !== $needRead) {
                throw new \Exception('Unable to read data');
            }
            $counted += $needRead;
            $now = microtime(true);
            if (($now - $lastUpdate) > 1) {
                $progressBar->setProgress($counted / $M);
                $lastUpdate = $now;
            }
            hash_update($hashFunc, $data);
        }
        $progressBar->finish();
        $expectedMD5 = $this->storageManager->getVolumeHash($volume);
        $resultMD5 = hash_final($hashFunc);
        $output->writeln([
            '',
            '',
            'Expected MD5: <comment>' . $expectedMD5 . '</comment>',
            'Result   MD5: <comment>' . $resultMD5 . '</comment>',
            ''
        ]);
        if ($resultMD5 === $expectedMD5) {
            $output->writeln('<fg=blue;options=bold>Check completed successfully</>');
        } else {
            $output->writeln('<fg=red;options=bold,blink>Check failed</>');
        }
    }
}
