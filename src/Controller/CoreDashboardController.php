<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\CoreVolumesSummary;
use React\Http\Response;

class CoreDashboardController extends BaseController
{
    public const VOLUMES = 'volumes';
    public const SERVERS = 'servers';

    private $twig;
    private $coreSummary;

    /**
     * CoreStatusController constructor.
     * @param CoreVolumesSummary $coreSummary
     * @param \Twig_Environment $twig
     */
    public function __construct(CoreVolumesSummary $coreSummary, \Twig_Environment $twig)
    {
        $this->coreSummary = $coreSummary;
        $this->twig = $twig;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function volumes() : Response
    {
        return $this->htmlResponse(200, $this->twig->render('volumes.twig', [
            'volumes' => $this->coreSummary->volumes()
        ]));
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function servers() : Response
    {
        return $this->htmlResponse(200, $this->twig->render('servers.twig', [
            'servers' => $this->coreSummary->servers()
        ]));
    }
}
