<?php
namespace App\Command;

use App\Entity\Card;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class CorrectCardCommand extends Command
{
    protected static $defaultName = 'app:modify-cards';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Modify all cards with good names and levels')
            ->setName('app:modify-cards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            $cardRepository = $this->em->getRepository(Card::class);
        
            $json = json_decode(file_get_contents('resources/levels.json'), true);
            $goodCardsList = [];
            foreach ($json as $level) {
                foreach ($level['cards'] as $card) {
                    $cardExist = false;
                    $currentCard = $cardRepository->findOneById($card['cardIconId']);
                    $currentCard->setName(ucwords($card['cardName']));
                    $currentCard->setLvl($card['cardLevel']);
                    $this->em->persist($currentCard);
                }
            }
            
            $this->em->flush();

            $io->success('Insertion réussit');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
