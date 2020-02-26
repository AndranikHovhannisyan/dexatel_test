<?php

namespace App\Command;

use App\Entity\ServerLog;
use App\Form\ServerLogType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;

class DexatelDownloadDataCommand extends Command
{
    protected static $defaultName = 'dexatel:download-data';

    const SUCCESS_STATUS = 1;

    protected $authToken;
    protected $formFactory;
    protected $em;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $em, $authToken)
    {
        $this->authToken   = $authToken;
        $this->formFactory = $formFactory;
        $this->em          = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command is used get data from API and save it in the database')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //Generate random start date
        $startDate = new \DateTime('01-01-2019');
        $randTimestamp = mt_rand($startDate->getTimestamp(), time());
        $startDate->setTimestamp($randTimestamp);

        //Generate random end date based on start date
        $endDate = clone $startDate;
        $endDate->modify(mt_rand(1, 29) . ' day');

        $client = HttpClient::create([]);
        $response = $client->request("POST", "http://dev.plan.am/api/", [
            'headers' => ['Auth-Token' => $this->authToken],
            'body'  => [
                'method'     => 'getUsersList',
                'date_start' => $startDate->format('Y-m-d H:i:s'),
                'date_end'   => $endDate->format('Y-m-d H:i:s')
            ],
        ]);

        if ($response->getStatusCode() != 200){
            $io->error('Something went wrong with http://dev.plan.am/api/ API, status code: ' . $response->getStatusCode());
            return 1;
        }

        try {
            $serverLogsArray = $response->getContent();
            $serverLogsArray = json_decode($serverLogsArray, true);
        }
        catch (\Exception $e){
            $io->error('Seems http://dev.plan.am/api/ returns invalid json');
            return 1;
        }

        if ($serverLogsArray['success'] != self::SUCCESS_STATUS || !isset($serverLogsArray['data'])){
            $io->error(json_encode($serverLogsArray));
            return 1;
        }

        $progressBar = new ProgressBar($output, count($serverLogsArray));
        $progressBar->start();

        foreach ($serverLogsArray['data'] as $rawServerLog){

            $form = $this->formFactory->create(ServerLogType::class, new ServerLog());
            $form->submit($rawServerLog);

            if (!$form->isValid()){
                $io->error("Something went wrong with this data " . json_encode($rawServerLog));
            }

            $serverLog = $form->getData();
            $this->em->persist($serverLog);

            $progressBar->advance();
        }

        $this->em->flush();
        $progressBar->finish();
        $io->success('Server log entries created');

        return 0;
    }
}
