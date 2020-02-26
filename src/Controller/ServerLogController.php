<?php

namespace App\Controller;

use App\Entity\ServerLog;
use App\Form\ServerLogSearchType;
use App\Repository\ServerLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServerLogController extends AbstractController
{
    /**
     * @Route("/", name="server_log")
     */
    public function index(Request $request, ServerLogRepository $serverLogRepository)
    {
        $searchForm = $this->createForm(ServerLogSearchType::class, new ServerLog());
        $searchForm->submit($request->query->all());
        $serverLogs = $serverLogRepository->findAllBySearch($searchForm->getData());

        return $this->render('serverLog/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'serverLogs' => $serverLogs,
        ]);
    }
}
