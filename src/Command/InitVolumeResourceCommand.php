<?php

namespace AVAllAC\PersistentBlockStorage\Command;

use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeIsntEmpty;
use AVAllAC\PersistentBlockStorage\Model\VolumeInfo;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use GuzzleHttp\Exception\GuzzleException;

class InitVolumeResourceCommand extends Command
{
    private $urlGenerator;

    public function __construct(
        ?string $name,
        UrlGenerator $urlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('init:volume')
            ->setDescription('Init volume resource')
            ->addArgument('volume', InputArgument::REQUIRED, 'Volume ID')
            ->addArgument('device', InputArgument::REQUIRED, 'device');
    }

    /**
     * @param $id
     * @param $path
     * @throws CantOpenFileException
     * @throws IncorrectVolumeException
     * @throws VolumeIsntEmpty
     * @throws GuzzleException
     */
    protected function initVolume($id, $path)
    {
        $url = $this->urlGenerator->generate('config');
        $client = new Client();
        $request = $client->request('GET', $url);
        $data = json_decode($request->getBody()->getContents(), true);
        if (empty($data[$id])) {
            throw new IncorrectVolumeException();
        }
        $header = pack(
            "Ca16JJJ",
            ServerStorageManager::VERSION_STORAGE,
            $data[$id]['uid'],
            $id,
            $data[$id]['size'],
            0
        );
        $fRes = fopen($path, 'c+b');
        if (empty($fRes)) {
            throw new CantOpenFileException();
        }
        if (fread($fRes, VolumeInfo::HEADER_SIZE) !== hex2bin(str_repeat('00', 64))) {
            throw new VolumeIsntEmpty();
        }
        fseek($fRes, 0);
        fwrite($fRes, $header);
        fclose($fRes);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws CantOpenFileException
     * @throws GuzzleException
     * @throws IncorrectVolumeException
     * @throws VolumeIsntEmpty
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '<fg=yellow;options=bold,underscore>Init volume resource</>',
            ''
        ]);

        $this->initVolume($input->getArgument('volume'), $input->getArgument('device'));
        $output->writeln('');
        $output->writeln('<fg=blue;options=bold>Init completed</>');
        $output->writeln('');
    }
}