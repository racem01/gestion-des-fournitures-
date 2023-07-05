<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Repository\CategoriesRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\remove;

#[Route('/admin/product', name: 'admin_product_')]
class ProductadminController extends AbstractController
{
    #[Route('/s', name: 'index')]
    public function orders(Request $request,OrderRepository $orderRepository): Response
    {
        // Récupération des données de commandes
        $orders = $orderRepository->findAll();
            // Récupération du paramètre de filtre de date
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');
        
            // Récupération des commandes entre les dates spécifiées
            $orders = $orderRepository->findByDate($startDate, $endDate);
        

        // Initialisation des variables pour les statistiques
        $totalOrders = count($orders);
        $acceptedOrders = 0;
        $rejectedOrders = 0;
        $pendingOrders = 0;
        $orderDates = [];
        $orderDepartments = [];

        // Calcul des statistiques par date, par département et par état de commande
        foreach ($orders as $order) {
            // Statistiques par date
            $orderDate = $order->getCreatedAt()->format('Y-m-d');
            if (isset($orderDates[$orderDate])) {
                $orderDates[$orderDate]++;
            } else {
                $orderDates[$orderDate] = 1;
            }

            // Statistiques par département
            $orderDepartment = $order->getUser()->getDepartment();
            if (isset($orderDepartments[$orderDepartment])) {
                $orderDepartments[$orderDepartment]++;
            } else {
                $orderDepartments[$orderDepartment] = 1;
            }

            // Statistiques par état de commande
            $status = $order->isIsVerified();
            if ($status === true) {
                $acceptedOrders++;
            } elseif ($status === false) {
                $rejectedOrders++;
            } elseif ($status === null) {
                $pendingOrders++;
            }
        }

        // Création du graphique
        $chartData = [
            ['Acceptées', $acceptedOrders],
            ['Rejetées', $rejectedOrders],
            ['En attente', $pendingOrders],
        ];

        // Préparation des données pour le rendu de la vue
        $orderDatesLabels = [];
        $orderDatesData = [];
        foreach ($orderDates as $date => $count) {
            $orderDatesLabels[] = $date;
            $orderDatesData[] = $count;
        }

        $orderDepartmentsLabels = [];
        $orderDepartmentsData = [];
        foreach ($orderDepartments as $department => $count) {
            $orderDepartmentsLabels[] = $department;
            $orderDepartmentsData[] = $count;
        }

        // Rendu de la vue
        return $this->render('admin/stats.html.twig', [
            'chartData' => json_encode($chartData),
            'total_orders' => $totalOrders,
            'accepted_orders' => $acceptedOrders,
            'rejected_orders' => $rejectedOrders,
            'pending_orders' => $pendingOrders,
            'order_dates_labels' => json_encode($orderDatesLabels),
            'order_dates_data' => json_encode($orderDatesData),
            'order_departments_labels' => json_encode($orderDepartmentsLabels),
            'order_departments_data' => json_encode($orderDepartmentsData),
            'start_date' => $startDate, // Ajouter la date de début
            'end_date' => $endDate, // Ajouter la date de fin
        
            

        ]);
    }
}    