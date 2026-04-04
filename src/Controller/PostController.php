<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class PostController extends AbstractController
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        /** @var User */
        $user = $this->getUser();
        $profile = $user->getProfile();
        $allPosts = $this->postRepository->getPostsByProfile($profile);
        return $this->render('post/index.html.twig', 
        [ 'posts' => $allPosts, ]
        );
    }

    #[Route('/post/create', name: 'app_post_new', methods:['GET', 'POST'])]
    public function createPost(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
            {
                /** @var User */
                $user = $this->getUser();
                $profile = $user->getProfile();
                $post->setProfile($profile);

                $this->postRepository->savePost($post);
                return $this->redirectToRoute('app_post');
            }

        return $this->render(
            'post/new.html.twig',
            ['form' => $form]
        );
    }
}
