<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\VolumeInfo;
use AVAllAC\PersistentBlockStorage\Model\ServerInfo;
use AVAllAC\PersistentBlockStorage\Exception\VolumeNoInformationException;

class CoreStorageManager
{
    /** @var VolumeInfo[] */
    private $volumes;
    /** @var ServerInfo[] */
    private $servers;

    /**
     * CoreStorageManager constructor.
     * @param array $servers
     * @param array $volumes
     */
    public function __construct(array $servers, array $volumes)
    {
        $this->servers = [];
        $this->volumes = [];
        foreach ($servers as $serverId => $serverData) {
            $server = new ServerInfo($serverId, $serverData['deliveryUrl'], $serverData['adminUrl']);
            $this->servers[] = $server;
            $vols = preg_replace_callback(
                '/(\d+)-(\d+)/',
                function ($m) {
                    return implode(',', range($m[1], $m[2]));
                },
                $serverData['volumes']
            );

            foreach (explode(',', $vols) as $item) {
                if (empty($volumes[$item])) {
                    throw new VolumeNoInformationException('no details about block #'. $item);
                }
                $info = $volumes[$item];
                $this->volumes[$item] = new VolumeInfo((int)$item, $server, $info['uid'], $info['size']);
            }
        }
    }

    /**
     * @param int $volume
     * @return string
     */
    public function getServerAdminUrl(int $volume) : string
    {
        return $this->volumes[$volume]->getServer()->getAdminUrl();
    }

    /**
     * @param int $volume
     * @return int
     */
    public function getServerId(int $volume) : int
    {
        return $this->volumes[$volume]->getServer()->getId();
    }

    /**
     * @param int $volume
     * @return string
     */
    public function getServerDeliveryUrl(int $volume) : string
    {
        return $this->volumes[$volume]->getServer()->getDeliveryUrl();
    }

    /**
     * @return ServerInfo[]
     */
    public function getServers() : array
    {
        return $this->servers;
    }

    /**
     * @return VolumeInfo[]
     */
    public function getVolumes() : array
    {
        return $this->volumes;
    }

    public function getVolumeInfo($id) : ?VolumeInfo
    {
        return $this->volumes[$id] ?? null;
    }
}
