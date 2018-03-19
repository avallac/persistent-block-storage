<?php

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
        ?ServerStorageManager $storageManager,
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

    protected function getHeaders($volume)
    {
        $client = new Client();
        $url = $this->urlGenerator->generate('volumeHeaders', ['volume' => $volume]);
        $request = $client->request('GET', $url);
        return json_decode($request->getBody(), true);
    }

    /**
     * @throws IncorrectVolumeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $volume = $input->getArgument('volume');
        if (!$this->storageManager->volumeAvailable($volume)) {
            $output->writeln('<error>Incorrect volume id</error>');
            exit;
        }
        $volumeResource = $this->storageManager->getVolumeResource($volume);
        $output->writeln([
            '<fg=yellow;options=bold,underscore>Volume deep checking</>',
            '',
            'Path: <comment>' . $this->storageManager->getVolumePath($volume) . '</comment>',
            ''
        ]);
        $headers = $this->getHeaders($volume);
        $elements = count($headers);
        $progressBar = new ProgressBar($output, $elements);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();
        $lastUpdate = 0;
        $now = 0;
        $counted = 0;
        foreach ($headers as $element) {
            fseek($volumeResource, $element['seek']);
            $data = fread($volumeResource, $element['size']);
            if (md5($data) !== $element['md5']) {
                var_dump($element);
                $output->writeln('<fg=red;options=bold,blink>Check failed</>');
                exit;
            }
            $counted++;
            if (($now - $lastUpdate) > 1) {
                $progressBar->setProgress($counted);
                $lastUpdate = $now;
            }
            $now = microtime(true);
        }
        $progressBar->finish();
        $expectedMD5 = $this->storageManager->getVolumeHash($volume);
        $output->writeln('<fg=blue;options=bold>Check completed successfully</>');
    }
}
