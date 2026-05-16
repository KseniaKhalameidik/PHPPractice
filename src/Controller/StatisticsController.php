<?php

namespace App\Controller;

use App\Repository\CommentReactionRepository;
use App\Repository\CommentRepository;
use App\Repository\ProfileRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'app_statistics')]
    public function index(
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        CommentReactionRepository $commentReactionRepository,
        ProfileRepository $profileRepository,
    ): Response
    {
        // /** 
        //  * @var array<Post> $allPosts 
        //  * TODO: Решить проблему N + 1
        // */
        // $allPosts = $postRepository->findAll();
        // $commentCount = 0;
        // foreach ($allPosts as $post) {
        //     $commentCount = $post->getComments()->count();
        // }

        $maxCommentsPost = $postRepository->getPostWithMaxComments();
        
        $commentCount = $commentRepository->count([]);

        $minCommentsPost = $postRepository->getPostWithMinComments();
        $postsGreaterThanAvg = $postRepository->getPostsWithCommentsGreaterThanAverage();

        $commentWithMaxContent = $commentRepository->getCommentWithMaxContent();
        $topReactedComments = $commentReactionRepository->getTopCommentsByReactionCount(5);

        $topProfiles = $profileRepository->getTopProfilesWithTotalCommentInTheirPosts(5);
        $profilesWithPostsNoComments = $profileRepository->getProfilesWithPostsAndWithoutComments();

        return $this->render('statistics/index.html.twig', [
            'commentsCount' => $commentCount,
            'maxCommentsPost' => $maxCommentsPost,
            'minCommentsPost' => $minCommentsPost,
            'postsGreaterThanAvg' => $postsGreaterThanAvg,
            'commentWithMaxContent' => $commentWithMaxContent,
            'topReactedComments' => $topReactedComments,
            'topProfiles' => $topProfiles,
            'profilesWithPostsNoComments' => $profilesWithPostsNoComments,
        ]);
    }
}
