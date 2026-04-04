<?php

namespace App\Controller;

use App\Repository\LuckyNumberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HelloController extends AbstractController
{
    private LuckyNumberRepository $luckyNumberRepository;

    public function __construct(LuckyNumberRepository $luckyNumberRepository)
    {
        $this->luckyNumberRepository = $luckyNumberRepository;
    }

    #[Route(path: '/hello')]
    public function hello(Request $request): Response
    {
        $requestIp = $request->getClientIp();
        $httpMethod = $request->getMethod();
        return new Response("Hello, $requestIp. Current method: $httpMethod");
    }

    #[Route('/hello/{name}')]
    public function greet(string $name, Request $request): Response
    {
        $baseUri = $request->getUri();
        return new Response("Hello $name. BaseURI: $baseUri");
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/hello/lucky/number', name: 'app_generate_lucky_number')]
    public function generateLuckyNumber(): Response
    {
        $luckyNumber = random_int(0, 100);
        $this->luckyNumberRepository->saveLuckyNumber($luckyNumber);
        return $this->render(
            'HelloController/index.html.twig', 
            [ 'luckyNumber' => $luckyNumber]
        );
    }

    #[Route('/hello/lucky/number/odd/{maxValue}')]
    public function getOddLuckyNumber(int $maxValue = 100): Response
    {
        $luckyNumberArray = $this->luckyNumberRepository->findOddLuckyNumber($maxValue);
        return $this->render(
            'HelloController/list.html.twig', 
            [ 'luckyNumbers' => $luckyNumberArray]
        );
    }

    #[Route('/hello/lucky/number/even')]
    public function getEvenLuckyNumber(): Response
    {
        $luckyNumberArray = $this->luckyNumberRepository->findEvenLuckyNumber();
        return $this->render(
            'HelloController/list.html.twig', 
            [ 'luckyNumbers' => $luckyNumberArray]
        );
    }
}