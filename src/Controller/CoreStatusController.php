<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\CoreSummary;

class CoreStatusController extends BaseController
{
    private $twig;
    private $coreSummary;

    public function __construct(CoreSummary $coreSummary, \Twig_Environment $twig)
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
