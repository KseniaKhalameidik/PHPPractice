<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\CommentReaction;
use App\Entity\User;
use App\Entity\Comment;
use App\Repository\CommentReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CommentReactionController extends AbstractController
{
    #[Route('/comment/{id}/reaction/{value}', name: 'app_comment_reaction', methods: ['POST'])]
    public function react(Comment $comment, int $value, Request $request, CommentReactionRepository $commentReactionRepository, EntityManagerInterface $entityManager): Response 
    {
        if (!in_array($value, [1, -1], true)) {
            return new Response('Invalid reaction value', Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $token = $request->request->getString('_token');
        if (!$this->isCsrfTokenValid('comment_reaction'.$comment->getId(), $token)) {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }

        $existing = $commentReactionRepository->findOneBy([
            'comment' => $comment,
            'author' => $user,
        ]);

        if ($existing === null) {
            $reaction = new CommentReaction();
            $reaction->setComment($comment);
            $reaction->setAuthor($user);
            $reaction->setValue($value);
            $comment->addReaction($reaction);
            $entityManager->persist($reaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($existing->getValue() === $value) {
            $comment->removeReaction($existing);
            $entityManager->remove($existing);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()], Response::HTTP_SEE_OTHER);
        }

        $existing->setValue($value);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()], Response::HTTP_SEE_OTHER);
    }
}
