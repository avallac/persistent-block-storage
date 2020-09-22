<?php

namespace AVAllAC\PersistentBlockStorage\Command;

use AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException;
use AVAllAC\PersistentBlockStorage\Model\VolumeInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadVolumeInformationCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('read:header:volume')
            ->setDescription('Get information about volume')
            ->addArgument('device', InputArgument::REQUIRED, 'device');
    }

    /**
     * @param $path
     * @return array|false
     * @throws CantOpenFileException
     */
    protected function readVolumeHeader($path)
    {
        $fRes = fopen($path, 'rb');
        if (empty($fRes)) {
            throw new CantOpenFileException();
        }
        $data = fread($fRes, VolumeInfo::HEADER_SIZE);
        $header = unpack("Cversion/A16uid/Jid/Jsize/Jflags", $data);
        fclose($fRes);

        return $header;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws CantOpenFileException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<fg=yellow;options=bold,underscore>Volume header</>',
        ]);

        $data = $this->readVolumeHeader($input->getArgument('device'));
        foreach ($data as $key => $value) {
            $output->writeln([
                '  <fg=white;>' . $key . '</> => <fg=red;>' . $value . '</>'
            ]);
        }
    }
}