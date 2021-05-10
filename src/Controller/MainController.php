<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\Visit;
use App\Entity\Item;
use App\Entity\Craft;
use App\Entity\UpVote;
use App\Entity\Report;
use App\Entity\Level;
use App\Form\CraftType;
use App\Repository\VisitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;


class MainController extends AbstractController
{
    /**
     * @Route("/main", name="main")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $visitRepository = $this->getDoctrine()->getRepository(Visit::class);
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);

        $ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
        $visit = new Visit();
        $visit->setIp($ip);

        $em->persist($visit);
        $em->flush();

        return $this->render('main/index.html.twig', [
            'userCount' => $userRepository->countUser(),
            'visitCount' => $visitRepository->countVisit(),
            'craftCount' => $craftRepository->countCraft(),
            'topUsers' => $userRepository->topUsers()
        ]);
    }

    /**
     * @Route("/card/list", name="card.list")
     */
    public function cardList(): Response
    {
        return $this->render('main/cardList.html.twig');
    }

    /**
     * @Route("/card/{id}", name="card.id")
     * 
     * @param $id
     */
    public function cardById($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $cardRepository = $this->getDoctrine()->getRepository(Card::class);
        

        return $this->render('main/card.html.twig', [
            'card' => $cardRepository->findOneById($id),
        ]);
    }

    /**
     * @Route("/ajax/card", name="card.ajax")
     * 
     * @param Request $request
     */
    public function cardAjax(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cardRepository = $this->getDoctrine()->getRepository(Card::class);
        $params = $request->request->all();
        return new JsonResponse($cardRepository->getListCards($params));
    }

    /**
     * @Route("/item/list", name="item.list")
     */
    public function itemList(): Response
    {
        return $this->render('main/itemList.html.twig');
    }

    /**
     * @Route("/item/{id}", name="item.id")
     * 
     * @param $id
     */
    public function itemById($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $itemRepository = $this->getDoctrine()->getRepository(Item::class);

        return $this->render('main/item.html.twig', [
            'item' => $itemRepository->findOneById($id),
        ]);
    }

    /**
     * @Route("/ajax/item", name="item.ajax")
     * 
     * @param Request $request
     */
    public function itemAjax(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $itemRepository = $this->getDoctrine()->getRepository(Item::class);
        $params = $request->request->all();
        return new JsonResponse($itemRepository->getListItems($params));
    }

    /**
     * @Route("/craft/list", name="craft.list")
     */
    public function craftList(): Response
    {
        return $this->render('craft/craftList.html.twig');
    }

    /**
     * @Route("/craft/id/{id}", name="craft.id")
     * 
     * @param $id
     */
    public function craftById($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);

        return $this->render('craft/craft.html.twig', [
            'craft' => $craftRepository->findOneById($id),
        ]);
    }

    /**
     * @Route("/ajax/craft", name="craft.ajax")
     * 
     * @param Request $request
     */
    public function craftAjax(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        $params = $request->request->all();
        return new JsonResponse($craftRepository->getListCrafts($params));
    }

    /**
     * @Route("/craft/new", name="craft.new")
     */
    public function newCraft(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $currentUser = $this->getUser();
        
        if (null === $currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if ($currentUser->getBan()) {
            return $this->redirectToRoute('ban');
        }

        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        $craft = new Craft();
        $craft->setCreator($currentUser);

        $form = $this->createForm(CraftType::class, $craft);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $craftPerDay = $currentUser->getCraftPerDay();
            if ($craftPerDay >= 25) {
                return $this->render('craft/new.html.twig', [
                    'form' => $form->createView(),
                    'error' => 'Vous avez atteint la limite de craft par jour'
                ]);
            }
            // $form->getData() holds the submitted values
            // but, the original `$craft` variable has also been updated
            $craft = $form->getData();
            $craftedItem = $craft->getItem();

            if (5 !== count($craft->getCards()) || null === $craftedItem) {
                return $this->render('craft/new.html.twig', [
                    'form' => $form->createView(),
                    'error' => 'Nombre de carte incorrect'
                ]);
            }

            $existingCraftsList = $craftedItem->getCrafts();

            $alreadyExist = false;

            foreach($existingCraftsList as $testedCraft) {
                $id = $testedCraft->getId();
                $arrayCollectionCraft =  $craftRepository->findOneById($id);
                $testedCards = $arrayCollectionCraft->getCards()->getValues();
                $cards = $craft->getCards()->getValues();
                
                if ($testedCards === $cards) {
                    $alreadyExist = true;
                }
            }
            

            if ($alreadyExist) {
                return $this->render('craft/new.html.twig', [
                    'form' => $form->createView(),
                    'error' => 'Craft déjà existant'
                ]);
            }

            $currentUser->setCraftPerDay($craftPerDay + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($currentUser);
            $em->persist($craft);
            $em->flush();

            $request = HttpClient::create();
            $response = $request->request(
                'GET',
                'http://localhost:8080/newCraft?craftId=' . $craft->getId() . '&itemName=' . urlencode($craft->getItem()->getName())
            );

            $listSubs = $craftedItem->getSubscriptions();
            if (null !== $listSubs || 0 !== count($listSubs)) {
                $discordIdList = [];
                foreach ($listSubs as $sub) {
                    $discordIdList[] = $sub->getDiscordId();
                }
                $request2 = HttpClient::create();

                $response = $request2->request(
                    'GET',
                    'http://localhost:8080/notifications', [
                        'query' => [
                            'discordIdList' => $discordIdList,
                            'itemName' => $craftedItem->getName(),
                            'craftId' => $craft->getId(),
                            'key' => $this->getParameter('apikey')
                        ]
                    ]
                );
            }

            return $this->render('craft/craft.html.twig', [
                'craft' => $craft,
            ]);
        }

        return $this->render('craft/new.html.twig', [
            'form' => $form->createView(),
            'error' => null
        ]);
    }

    /**
     * @Route("/craft/add/vote/{id}", name="craft.add.vote")
     * 
     * @param $id
     */
    public function craftAddVote($id)
    {
        $em = $this->getDoctrine()->getManager();
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        $craft = $craftRepository->findOneById($id);
        $currentUser = $this->getUser();
        if (null === $currentUser) {
            return $this->redirectToRoute('craft.list');
        }

        if ($currentUser->getBan()) {
            return $this->redirectToRoute('ban');
        }

        $votesPerDay = $currentUser->getVotesPerDay();

        if ($votesPerDay >= 40) {
            return $this->render('craft/craft.html.twig', [
                'craft' => $craft,
            ]);
        }

        if (null === $craft) {
            return $this->redirectToRoute('craft.list');
        }

        foreach($craft->getUpVotes() as $vote) {
            if ($currentUser === $vote->getUser()) {
                return $this->redirectToRoute('craft.list');
            }
        }

        $newVote = new UpVote();
        $newVote->setUser($currentUser);
        $newVote->setCraft($craft);

        $currentUser->setVotesPerDay($votesPerDay + 1);
        $em->persist($currentUser);
        $em->persist($newVote);
        $em->flush();

        return $this->render('craft/craft.html.twig', [
            'craft' => $craft,
        ]);
    }

    /**
     * @Route("/craft/add/report/{id}", name="craft.add.report")
     * 
     * @param $id
     */
    public function craftAddReport($id)
    {
        $em = $this->getDoctrine()->getManager();
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        $craft = $craftRepository->findOneById($id);
        $currentUser = $this->getUser();
        if (null === $currentUser) {
            return $this->redirectToRoute('craft.list');
        }
        if ($currentUser->getBan()) {
            return $this->redirectToRoute('ban');
        }
        if (null === $craft) {
            return $this->redirectToRoute('craft.list');
        }

        $votesPerDay = $currentUser->getVotesPerDay();

        if ($votesPerDay >= 40) {
            return $this->render('craft/craft.html.twig', [
                'craft' => $craft,
            ]);
        }

        foreach($craft->getReports() as $report) {
            if ($currentUser === $report->getUser()) {
                return $this->redirectToRoute('craft.list');
            }
        }

        $newReport = new Report();
        $newReport->setUser($currentUser);
        $newReport->setCraft($craft);

        $currentUser->setVotesPerDay($votesPerDay + 1);
        $em->persist($currentUser);
        $em->persist($newReport);
        $em->flush();

        return $this->render('craft/craft.html.twig', [
            'craft' => $craft,
        ]);
    }

    /**
     * @Route("/craft/validate/{id}", name="craft.validate")
     * 
     * @param $id
     */
    public function validateCraft($id)
    {
        $em = $this->getDoctrine()->getManager();
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        $craft = $craftRepository->findOneById($id);
        $currentUser = $this->getUser();
        if (null === $currentUser) {
            return $this->redirectToRoute('craft.list');
        }
        if (!in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return $this->redirectToRoute('craft.list');
        }
        if ($currentUser->getBan()) {
            return $this->redirectToRoute('ban');
        }
        if (null === $craft) {
            return $this->redirectToRoute('craft.list');
        }

        if ($craft->getIsVerified()) {
            return $this->render('craft/craft.html.twig', [
                'craft' => $craft,
            ]); 
        }

        $craft->setIsVerified(true);

        $em->persist($craft);
        $em->flush();

        return $this->render('craft/craft.html.twig', [
            'craft' => $craft,
        ]);
    }

    /**
     * @Route("/craft/false/{id}", name="craft.false")
     * 
     * @param $id
     */
    public function falseCraft($id)
    {
        $em = $this->getDoctrine()->getManager();
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        $craft = $craftRepository->findOneById($id);
        $currentUser = $this->getUser();
        if (null === $currentUser) {
            return $this->redirectToRoute('craft.list');
        }
        if (!in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return $this->redirectToRoute('craft.list');
        }
        if ($currentUser->getBan()) {
            return $this->redirectToRoute('ban');
        }
        if (null === $craft) {
            return $this->redirectToRoute('craft.list');
        }

        if ($craft->getIsFalse()) {
            return $this->render('craft/craft.html.twig', [
                'craft' => $craft,
            ]); 
        }

        $craft->setIsFalse(true);

        $em->persist($craft);
        $em->flush();

        return $this->render('craft/craft.html.twig', [
            'craft' => $craft,
        ]);
    }

    /**
     * @Route("/ban", name="ban")
     * 
     * @param Request $request
     */
    public function ban(Request $request)
    {
        return $this->render('main/ban.html.twig');
    }

    /**
     * @Route("/craft/test", name="craft.test")
     * 
     */
    public function testCraft()
    {
        $em = $this->getDoctrine()->getManager();
        $cardRepository = $this->getDoctrine()->getRepository(Card::class);

        $currentUser = $this->getUser();

        return $this->render('craft/test.html.twig', [
            'cards' => $cardRepository->findAll()
        ]);
    }

    /**
     * @Route("/ajax/testCraft", name="ajax.craft.test")
     * 
     * @param Request $request
     */
    public function ajaxTestCraft(Request $request)
    {
        $response = [
            'craftExist' => false,
        ];

        $params = $request->request->all();
        $cardsListId = $params['cardsListId'];
        $em = $this->getDoctrine()->getManager();
        $cardRepository = $this->getDoctrine()->getRepository(Card::class);

        foreach ($cardsListId as $cardId) {
            $card = $cardRepository->findOneById($cardId);
            $crafts = $card->getCrafts();
            if (!$crafts->isEmpty()) {
                foreach ($crafts as $craft) {
                    $cardsArray = [];
                    foreach($craft->getCards() as $craftCard) {
                        $cardsArray[] = $craftCard->getId();
                    }
                    //Don't check type because cardsListId from postRequest is a string array
                    if ($cardsArray == $cardsListId) {
                        $response['craftExist'] = true;
                        $response['craftId'] = $craft->getId();
                        break 2;
                    }
                }
            }
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/user/list", name="user.list")
     * 
     */
    public function userList()
    {
        return $this->render('user/userList.html.twig');
    }

    /**
     * @Route("/ajax/user", name="user.ajax")
     * 
     * @param Request $request
     */
    public function userAjax(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $params = $request->request->all();
        return new JsonResponse($userRepository->getListUsers($params));
    }

    /**
     * @Route("/level/list", name="level.list")
     * 
     */
    public function levelList()
    {
        return $this->render('level/levelList.html.twig');
    }

    /**
     * @Route("/level/id/{id}", name="level.id")
     * 
     * @param $id
     */
    public function levelById($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $levelRepository = $this->getDoctrine()->getRepository(Level::class);

        return $this->render('level/level.html.twig', [
            'level' => $levelRepository->findOneByDofusLevel($id),
        ]);
    }

    /**
     * @Route("/ajax/level", name="level.ajax")
     * 
     * @param Request $request
     */
    public function levelAjax(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $levelRepository = $this->getDoctrine()->getRepository(Level::class);
        $params = $request->request->all();
        return new JsonResponse($levelRepository->getListLevels($params));
    }
}