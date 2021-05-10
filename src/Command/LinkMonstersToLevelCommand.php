<?php
namespace App\Command;

use App\Entity\Card;
use App\Entity\Monster;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class LinkMonstersToLevelCommand extends Command
{
    protected static $defaultName = 'app:links-cards';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Links all cards to mobs')
            ->setName('app:links-cards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            $cardRepository = $this->em->getRepository(Card::class);
            $monsterRepository = $this->em->getRepository(Monster::class);
        
            $json = json_decode(file_get_contents('resources/levels.json'), true);
            $goodCardsList = [];
            foreach ($json as $level) {
                foreach ($level['cards'] as $card) {
                    $currentCard = $cardRepository->findOneById($card['cardIconId']);
                    $exist = false;
                    foreach ($goodCardsList as $goodCard) {
                        if ($goodCard->getId() === $currentCard->getId()) {
                            $exist = true;
                            break;
                        }
                    }
                    if (!$exist) {
                        $goodCardsList[] = $currentCard;
                        foreach ($card['cardDrop'] as $dropId) {
                            $monster = $monsterRepository->findOneByAnkamaId($dropId);
                            if (null !== $monster) {
                                $currentCard->addMonster($monster);
                            }
                        }
                        $this->em->persist($currentCard);
                    }
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
