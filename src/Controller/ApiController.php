<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Item;
use App\Entity\ApiKey;
use App\Entity\Card;
use App\Entity\Craft;
use App\Entity\User;
use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/getCraftItem", name="api.get.craft.item")
     * 
     * @param Request $request
     */
    public function getCraftItem(Request $request): JsonResponse
    {
        $apiRepository = $this->getDoctrine()->getRepository(ApiKey::class);
        $isKeyCorrect = $apiRepository->findOneByPublicKey($request->query->get('apiKey'));
        if (null === $isKeyCorrect) {
            return new JsonResponse('Clef API incorrecte');
        }

        $itemRepository = $this->getDoctrine()->getRepository(Item::class);
        $item = $itemRepository->findOneByName($request->query->get('itemName'));
        if (null === $item) {
            return new JsonResponse('Aucun item trouvé à ce nom');
        }
        $listCrafts = $item->getCrafts();
        if (0 === count($listCrafts)) {
            return new JsonResponse('Aucun craft lié à cet item');
        }

        $response = [];
        foreach ($listCrafts as $craft) {
            $response[] = $craft->getId();
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/api/followItem", name="api.follow.item")
     * 
     * @param Request $request
     */
    public function followItem(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => ''
        ];
        $apiRepository = $this->getDoctrine()->getRepository(ApiKey::class);
        $isKeyCorrect = $apiRepository->findOneByPublicKey($request->query->get('apiKey'));
        if (null === $isKeyCorrect) {
            $response['message'] = 'Clef API incorrecte';
            return new JsonResponse($response);
        }

        $itemRepository = $this->getDoctrine()->getRepository(Item::class);
        $item = $itemRepository->findOneByName($request->query->get('itemName'));
        if (null === $item) {
            $response['message'] = 'Aucun item trouvé à ce nom';
            return new JsonResponse($response);
        }

        $discordId = $request->query->get('discordId');
        if (null === $discordId) {
            $response['message'] = 'Aucun id Discord';
            return new JsonResponse($response);
        }

        $subscriptionRepository = $this->getDoctrine()->getRepository(Subscription::class);
        $subscriptionForUser = $subscriptionRepository->findByDiscordId($discordId);

        if (null !== $subscriptionForUser && count($subscriptionForUser) >= 10) {
            $response['message'] = 'Vous avez atteint la limite de 10 abonnements';
            return new JsonResponse($response);
        }

        if (null !== $subscriptionForUser) {
            foreach ($subscriptionForUser as $singleSub) {
                if ($singleSub->getItem()->getId() === $item->getId()) {
                    $response['message'] = 'Vous suivez actuellement déjà cet item';
                    return new JsonResponse($response);
                }
            }
        }

        $newSubscription = new Subscription();
        $newSubscription->setDiscordId($discordId);
        $newSubscription->setItem($item);

        $em = $this->getDoctrine()->getManager();
        $em->persist($newSubscription);
        $em->flush();

        $response['success'] = true;
        $response['message'] = 'Abonnement à l\'item enregistré !';
        return new JsonResponse($response);
    }

    /**
     * @Route("/api/stopFollowItem", name="api.stopfollow.item")
     * 
     * @param Request $request
     */
    public function stopFollowItem(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => ''
        ];
        $apiRepository = $this->getDoctrine()->getRepository(ApiKey::class);
        $isKeyCorrect = $apiRepository->findOneByPublicKey($request->query->get('apiKey'));
        if (null === $isKeyCorrect) {
            $response['message'] = 'Clef API incorrecte';
            return new JsonResponse($response);
        }

        $itemRepository = $this->getDoctrine()->getRepository(Item::class);
        $item = $itemRepository->findOneByName($request->query->get('itemName'));
        if (null === $item) {
            $response['message'] = 'Aucun item trouvé à ce nom';
            return new JsonResponse($response);
        }

        $discordId = $request->query->get('discordId');
        if (null === $discordId) {
            $response['message'] = 'Aucun id Discord';
            return new JsonResponse($response);
        }

        $subscriptionRepository = $this->getDoctrine()->getRepository(Subscription::class);
        $subscriptionForUser = $subscriptionRepository->findByDiscordId($discordId);

        if (null === $subscriptionForUser) {
            $response['message'] = 'Vous n\'avez aucun abonnement';
            return new JsonResponse($response);
        }

        $foundSubscription = false;
        foreach ($subscriptionForUser as $sub) {
            if ($sub->getItem()->getId() === $item->getId()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($sub);
                $em->flush();

                $response['success'] = true;
                $response['message'] = 'Suppression de l\'abonnement enregistrée !';
                return new JsonResponse($response);
            }
        }

        $response['message'] = 'Aucun abonnement trouvé pour cet item';
        return new JsonResponse($response);
    }

    /**
     * @Route("/api/followList", name="api.follow.list")
     * 
     * @param Request $request
     */
    public function followList(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => ''
        ];
        $apiRepository = $this->getDoctrine()->getRepository(ApiKey::class);
        $isKeyCorrect = $apiRepository->findOneByPublicKey($request->query->get('apiKey'));
        if (null === $isKeyCorrect) {
            $response['message'] = 'Clef API incorrecte';
            return new JsonResponse($response);
        }

        $discordId = $request->query->get('discordId');
        if (null === $discordId) {
            $response['message'] = 'Aucun id Discord';
            return new JsonResponse($response);
        }

        $subscriptionRepository = $this->getDoctrine()->getRepository(Subscription::class);
        $subscriptionForUser = $subscriptionRepository->findByDiscordId($discordId);

        if (null === $subscriptionForUser || 0 === count($subscriptionForUser)) {
            $response['message'] = 'Vous n\'avez aucun abonnement';
            return new JsonResponse($response);
        }

        foreach ($subscriptionForUser as $sub) {
            $response['subscriptions'][] = $sub->getItem()->getName();
        }

        $nbrSubs = count($subscriptionForUser);
        $response['message'] = "Vous avez $nbrSubs abonnements";
        $response['success'] = true;
        return new JsonResponse($response);
    }

    /**
     * @Route("/api/resetFollow", name="api.follow.reset")
     * 
     * @param Request $request
     */
    public function resetFollow(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => ''
        ];
        $apiRepository = $this->getDoctrine()->getRepository(ApiKey::class);
        $isKeyCorrect = $apiRepository->findOneByPublicKey($request->query->get('apiKey'));
        if (null === $isKeyCorrect) {
            $response['message'] = 'Clef API incorrecte';
            return new JsonResponse($response);
        }

        $discordId = $request->query->get('discordId');
        if (null === $discordId) {
            $response['message'] = 'Aucun id Discord';
            return new JsonResponse($response);
        }

        $subscriptionRepository = $this->getDoctrine()->getRepository(Subscription::class);
        $subscriptionForUser = $subscriptionRepository->findByDiscordId($discordId);

        if (null === $subscriptionForUser) {
            $response['message'] = 'Vous n\'avez aucun abonnement';
            return new JsonResponse($response);
        }

        $nbrSubs = count($subscriptionForUser);
        $em = $this->getDoctrine()->getManager();
                
        foreach ($subscriptionForUser as $sub) {
            $em->remove($sub);
        }

        $em->flush();

        $response['message'] = "Vous avez supprimé $nbrSubs abonnements";
        $response['success'] = true;
        return new JsonResponse($response);
    }

    /**
     * @Route("/api/newCraft", name="api.new.craft")
     * 
     * @param Request $request
     */
    public function newCraft(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => ''
        ];
        $apiRepository = $this->getDoctrine()->getRepository(ApiKey::class);
        $isKeyCorrect = $apiRepository->findOneByPublicKey($request->query->get('apiKey'));
        if (null === $isKeyCorrect) {
            $response['message'] = 'Clef API incorrecte';
            return new JsonResponse($response);
        }

        $itemRepository = $this->getDoctrine()->getRepository(Item::class);
        $item = $itemRepository->findOneByName($request->query->get('itemName'));
        if (null === $item) {
            $response['message'] = 'Aucun item trouvé à ce nom';
            return new JsonResponse($response);
        }

        $cards = $request->query->get('cards');
        if (null === $cards) {
            $response['message'] = 'Aucunes cartes trouvés';
            return new JsonResponse($response);
        }

        $cardRepository = $this->getDoctrine()->getRepository(Card::class);
        $craftRepository = $this->getDoctrine()->getRepository(Craft::class);
        
        $cardsArray = [];
        $cards = json_decode($cards);

        foreach ($cards as $cardName) {
            $findCard = $cardRepository->findOneByName($cardName);
            if  (null === $findCard) {
                $response['message'] = 'Problème de lecture';
                return new JsonResponse($response);
            }
            $cardsArray[] = $findCard;
        }

        $craft = new Craft();
        foreach ($cardsArray as $card) {
            $craft->addCard($card);
        }

        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $craft->setItem($item);
        $craft->setCreator($userRepository->findOneByPseudo('Nira'));
        $craft->setIsVerified(true);

        $existingCraftsList = $item->getCrafts();

        $alreadyExist = false;

        foreach($existingCraftsList as $testedCraft) {
            $testedCards = $testedCraft->getCards();
            $testedArray = [];
            foreach ($testedCards as $card) {
                $testedArray[] = $card->getId();
            }

            $currentCards = $craft->getCards();
            $currentArray = [];
            foreach ($testedCards as $card) {
                $currentArray[] = $card->getId();
            }
            $diff = array_diff($currentArray, $testedArray);
            if (null === $diff || empty($diff)) {
                $alreadyExist = true;
                $craft = $testedCraft;
                $craft->setIsVerified(true);
            }
        }

        
        $em = $this->getDoctrine()->getManager();
        $em->persist($craft);

        $em->flush();

        if (!$alreadyExist) {
            $request = HttpClient::create();
            $response = $request->request(
                'GET',
                'http://localhost:8080/newCraft?craftId=' . $craft->getId() . '&itemName=' . urlencode($craft->getItem()->getName())
            );

            $listSubs = $item->getSubscriptions();
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
                            'itemName' => $item->getName(),
                            'craftId' => $craft->getId(),
                            'key' => $this->getParameter('apikey')
                        ]
                    ]
                );
            }
        }

        if ($alreadyExist) {
            $response['success'] = true;
            $response['message'] = 'Craft déjà existant mais vérifié !';
            return new JsonResponse($response);
        } else {
            $response['message'] = "Craft bien enregistré !";
            $response['success'] = true;
            return new JsonResponse($response);
        }
    }
}
