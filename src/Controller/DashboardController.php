<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\CoreVolumesSummary;
use GuzzleHttp\Client;

class DashboardController extends BaseController
{
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
     * @return \React\Http\Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function summary()
    {
        return $this->htmlResponse(200, $this->twig->render('index.twig', [
            'volumes' => $this->coreSummary->compile()
        ]));
    }
}
