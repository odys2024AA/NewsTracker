<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
final class UsercrudController extends AbstractController
{
    
    #[Route('/{id}/edit', name: 'app_usercrud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        
        $this->denyAccessUnlessGranted('USER_EDIT', $user);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
    
            if ($plainPassword) {
                // Hash the new password and set it on the user object
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_watchlist', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usercrud/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_usercrud_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('USER_DELETE', $user);
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $submittedToken)) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Usr deleted successfully');

            // Logout after deletion
            if($this->getUser() === $user){
                $request->getSession()->invalidate();
                $this->container->get('security.token_storage')->setToken(null);
                return $this->redirectToRoute('app_login');
            }
        } else {
            $this->addFlash('error', 'Invalid security token');
        }

        return $this->redirectToRoute('app_usercrud_index');
    }
}
