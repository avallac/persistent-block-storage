<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Command;

use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

class DeepCheckVolumeCommand extends Command
{
    private $storageManager;
    private $urlGenerator;

    public function __construct(
        ?string $name,
        ServerStorageManager $storageManager,
        UrlGenerator $urlGenerator
    ) {
        $this->storageManager = $storageManager;
        $this->urlGenerator = $urlGenerator;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('check:volume:elements')
            ->setDescription('Check md5sum for volume')
            ->addArgument('volume', InputArgument::REQUIRED, 'Volume ID');
    }

    protected function getBinHeaders($volume)
    {
        $client = new Client();
        $url = $this->urlGenerator->generate('volumeHeaders', ['volume' => $volume]);
        $request = $client->request('GET', $url);
        return $request->getBody()->getContents();
    }

    protected function markBroken($hash)
    {
        $client = new Client();
        $url = $this->urlGenerator->generate('report', ['hash' => $hash]);
        $request = $client->request('POST', $url);
        return $request->getBody()->getContents();
    }

    /**
     * @param $volume
     * @param $output
     * @throws IncorrectVolumeException
     */
    protected function checkVolume($volume, $output)
    {
        $errorNum = 0;
        $volume = (int)$volume;
        if (!$this->storageManager->volumeAvailable($volume)) {
            $output->writeln('<error>Incorrect volume id</error>');
            exit;
        }
        $volumeResource = $this->storageManager->getVolumeResource($volume);
        $headers = $this->getBinHeaders($volume);
        $elements = strlen($headers) / 32;
        $progressBar = new ProgressBar($output, $elements);
        $format = '%message% %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%';
        $progressBar->setFormat($format);
        $progressBar->setMessage($volume . ':' . $this->storageManager->getVolumePath($volume));
        $progressBar->start();
        $lastUpdate = 0;
        $now = 0;
        $counted = 0;
        for ($pos = 0; $pos < $elements; $pos++) {
            $elementBin = substr($headers, $pos * 32, 32);
            $element = unpack('a16md5/Jseek/Jsize', $elementBin);
            fseek($volumeResource, $element['seek']);
            $data = fread($volumeResource, $element['size']);
            if (md5($data, true) !== $element['md5']) {
                $errorNum++;
                $output->writeln([
                    '#' . $errorNum,
                    'Seek :' . $element['seek'] . ' ' . 'Size: ' . $element['size'],
                    'Expected MD5: <comment>' . bin2hex($element['md5']) . '</comment>',
                    'Result   MD5: <comment>' . md5($data) . '</comment>',
                    ''
                ]);
                $output->writeln('<fg=red;options=bold,blink>Check failed</>');
                $output->writeln('');
                $this->markBroken($element['md5']);
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
     * @throws IncorrectVolumeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '<fg=yellow;options=bold,underscore>Volume deep checking</>',
            ''
        ]);
        $volumes = preg_replace_callback('/(\d+)-(\d+)/', function ($m) {
            return implode(',', range($m[1], $m[2]));
        }, $input->getArgument('volume'));
        foreach (explode(',', $volumes) as $volume) {
            $this->checkVolume($volume, $output);
        }
        $output->writeln('');
        $output->writeln('<fg=blue;options=bold>Check completed successfully</>');
        $output->writeln('');
    }
}
