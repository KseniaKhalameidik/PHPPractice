<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
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

    #[Route('/post/{id}/show', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, ['action' => $this->generateUrl('app_comment_new', ['post_id' => $post->getId()])]);

         /** @var User */
        $user = $this->getUser();
        $profile = $user->getProfile();

        return $this->render(
            'post/show.html.twig', 
            ['post' => $post,
            'form' => $commentForm ]
        );
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        /** @var User */
        $user = $this->getUser();
        $profile = $user->getProfile();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->postRepository->savePost($post);
            return $this->redirectToRoute('app_post');
        }
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post): Response
    {
        /** @var User */
        $user = $this->getUser();
        $profile = $user->getProfile();

        $this->postRepository->deletePost($post);

        return $this->redirectToRoute('app_post');
    }
}
