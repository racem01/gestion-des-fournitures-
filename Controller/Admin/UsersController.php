<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Chart\Chart\BarChart;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Chart\PieChart\PieChart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\remove;

#[Route('/admin/utilisateurs', name: 'admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, UsersRepository $usersRepository): Response
    {
        $search = $request->query->get('q');
        if($search){
            $users = $usersRepository->search($search);
        } else {
            $users = $usersRepository->findBy([], ['firstname' => 'asc']);
        }
        $userss = [];
        foreach ($users as $key=> $v) {
            if (in_array("ROLE_ADMIN", $v->getRoles())) {
                $userss[$key]['user'] = $v;
                $userss[$key]['role'] = 'ROLE_ADMIN';
            }
            elseif (in_array("ROLE_PRODUCT_ADMIN", $v->getRoles())) {
                $userss[$key]['user'] = $v;
                $userss[$key]['role'] = 'ROLE_PRODUCT_ADMIN';
            }
            else {
                $userss[$key]['user'] = $v;
                $userss[$key]['role'] = 'MEMBRE';
            }
        }
        return $this->render('admin/users/index.html.twig', compact('userss', 'search'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Users $user, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if(!$user){
            throw $this->createNotFoundException('Utilisateur introuvable');
        }
            
        $userForm = $this->createForm(RegistrationFormType::class, $user);
    
        $userForm->handleRequest($request);
    
        if($userForm->isSubmitted() && $userForm->isValid()){
    
            $em->flush();
    
            $this->addFlash('success', 'la personne a été mise à jour');
            return $this->redirectToRoute('admin_users_index');
        } else {
            return $this->render('admin/users/edit.html.twig', [
                'registrationForm' => $userForm->createView(),
            ]);
        }
    }

    #[Route('/employes_delete{id}', name: 'del')]
    public function deleteEmployes(Users $user =null, ManagerRegistry $doctrine): RedirectResponse 
    {
        if($user){
            $manager = $doctrine->getManager();
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('success', 'la personne a été supprimée');
        } else {
            $this->addFlash('danger','la personne innexistante');
        }
        return $this->redirectToRoute('admin_users_index');
    }

    
    
    
}    
