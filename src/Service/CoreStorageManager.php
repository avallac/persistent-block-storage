<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

class CoreStorageManager
{
    private $volumes;
    private $servers;

    /**
     * CoreStorageManager constructor.
     * @param array $servers
     */
    public function __construct(array $servers)
    {
        $this->servers = $servers;
        $this->volumes = [];
        foreach ($servers as $sid => $server) {
            $volumes = preg_replace_callback('/(\d+)-(\d+)/', function ($m) {
                return implode(',', range($m[1], $m[2]));
            }, $server['volumes']);
            foreach (explode(',', $volumes) as $item) {
                $this->volumes[$item] = [
                    'server' => [
                        'id' => $sid,
                        'deliveryUrl' => $server['deliveryUrl'],
                        'adminUrl' => $server['adminUrl']
                    ]
                ];
            }
        }
    }

    /**
     * @param int $volume
     * @return string
     */
    public function getServerAdminUrl(int $volume) : string
    {
        return $this->volumes[$volume]['server']['adminUrl'];
    }

    /**
     * @param int $volume
     * @return string
     */
    public function getServerDeliveryUrl(int $volume) : string
    {
        return $this->volumes[$volume]['server']['deliveryUrl'];
    }

    /**
     * @return array
     */
    public function getServers() : array
    {
        return $this->servers;
    }

    /**
     * @return array
     */
    public function getVolumes() : array
    {
        return $this->volumes;
    }
}
