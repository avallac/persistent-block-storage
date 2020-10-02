<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Command;

use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeReadException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Exception\GuzzleException;

class MigrationFromV1Command extends Command
{
    private $storageManager;

    protected $headerStorage;

    public function __construct(
        ?string $name,
        ServerStorageManager $serverStorageManager,
        HeaderStorage $headerStorage
    ) {
        $this->storageManager = $serverStorageManager;
        $this->headerStorage = $headerStorage;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('migration:v1')
            ->setDescription('Check md5sum for volume')
            ->addArgument('inputDevice', InputArgument::REQUIRED, 'input device')
            ->addArgument('inputBlockId', InputArgument::REQUIRED, 'input block id')
            ->addArgument('inputCoreUrl', InputArgument::REQUIRED, 'input core url with headers');
    }

    /**
     * @param $url
     * @param $volume
     * @return string
     * @throws GuzzleException
     */
    protected function getBinHeaders($url, $volume)
    {
        $client = new Client(['timeout' => 120]);
        $request = $client->request('GET', $url . '/volume/export/' . $volume);
        return $request->getBody()->getContents();
    }

    public function migrate($device, $volume, $coreUrl, $output)
    {
        $md5InDB = $this->headerStorage->exportAll();
        $headers = $this->getBinHeaders($coreUrl, $volume);
        $elements = strlen($headers) / 32;
        $fRes = fopen($device, 'c+b');
        if (empty($fRes)) {
            throw new CantOpenFileException();
        }

        $progressBar = new ProgressBar($output, $elements);
        $format = '%message% %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%';
        $progressBar->setFormat($format);
        $progressBar->setMessage('Migration');
        $progressBar->start();
        $lastUpdate = 0;
        $counted = 0;
        $now = 0;
        for ($pos = 0; $pos < $elements; $pos++) {
            $elementBin = substr($headers, $pos * 32, 32);
            $element = unpack('a16md5/Jseek/Jsize', $elementBin);
            if (empty($md5InDB[bin2hex($element['md5'])])) {
                fseek($fRes, $element['seek']);
                $data = fread($fRes, $element['size']);
                if (md5($data) !== bin2hex($element['md5'])) {
                    throw new VolumeReadException();
                }
                $position = $this->headerStorage->insert(bin2hex($element['md5']), $element['size'] + ServerStorageManager::FILE_HEAD_SIZE);
                $this->storageManager->write($position, $data, md5($data));
            }
            $counted++;
            if (($now - $lastUpdate) > 1) {
                $progressBar->setProgress($counted);
                $lastUpdate = $now;
            }
            $now = microtime(true);
        }
        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws GuzzleException
     * @throws IncorrectVolumeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '<fg=yellow;options=bold,underscore>Volume deep checking</>',
            ''
        ]);

        $this->migrate(
            $input->getArgument('inputDevice'),
            $input->getArgument('inputBlockId'),
            $input->getArgument('inputCoreUrl'),
            $output
        );
        $output->writeln('');
        $output->writeln('<fg=blue;options=bold>Check completed successfully</>');
        $output->writeln('');
    }
}
