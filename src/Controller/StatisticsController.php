<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'app_statistics')]
    public function index(PostRepository $postRepository, CommentRepository $commentRepository): Response
    {
        /** @var array<Post> $allPosts */
        $allPosts = $postRepository->findAll();
        $commentCount = 0;
        foreach ($allPosts as $post) {
            $commentCount = $post->getComments()->count();
        }

        $maxPost = $postRepository->getPostWithMaxComments();
        
        // $commentCount = count($commentRepository->findAll());

        return $this->render('statistics/index.html.twig', [
            'commentsCount' => $commentCount,
        ]);
    }
}
