<?php

namespace App\Controller;

use DateTime;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrdersController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function index(Request $request, OrderRepository $ss): Response
    {
        $orders = [];
        $o = $ss->findAll();
        foreach ($o as $key => $value) {
            $user = $value->getUser()->getLastname(). '  ' .$value->getUser()->getFirstname();
            $products = [];
            for ($i = 0; $i < count($value->getProducts()); $i++) {
                $quantity = $value->getProducts()[$i]->getQuantite();
                $product = null;
                if (!is_null($value->getProducts()[$i]->getProduct()[0])) {
                    $product = $value->getProducts()[$i]->getProduct()[0]->getName();
                }
                if (!is_null($product)) { // Vérifier si le produit n'est pas null
                    $products[] = [
                        "produit" => $product,
                        "quantite" => $quantity
                    ];
                }
            }
            
            if (!empty($products)) {
                // Vérifier si la commande contient des produits
                $orders[] = [
                    "id" => $value->getId(),
                    "verif" => $value->isIsverified(),
                    "user" => $user,
                    "product" => $products,
                    "created_at" => $value->getCreatedAt() // Assuming `getCreatedAt()` method exists in your Order entity
                ];
            }
            
        }
        
        $f = [];
        foreach ($orders as $hamma) {
            if ($hamma['verif'] === null) {
                $f[] = $hamma;
            }
        }
        $orders = $f;
        
        return $this->render('orders/index.html.twig', [
            'orders' => $orders
        ]);
    }        
    #[Route("/orders/update-status", name:"app_update_order_status", methods:"POST")]
    public function updateOrderStatus(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $orderId = $request->request->get('orderId');
        $status = $request->request->get('status');
        $deliveryDate = $request->request->get('deliveryDate');
    
        $order = $orderRepository->find($orderId);
    
        if (!$order) {
            return new JsonResponse(['status' => 'error', 'message' => 'Order not found.'], 404);
        }
    
        if ($status == 'yes') {
            $order->setIsVerified(true);
            $order->setDateliv(new DateTime($deliveryDate));
            // Réduire la quantité en stock pour chaque produit de la commande
            foreach ($order->getProducts() as $orderProduct) {
                $product = $orderProduct->getProduct()[0] ?? null;
                if ($product && !is_null($product->getName())) {
                    $quantity = $orderProduct->getQuantite();
    
                    if ($product->getStock() < $quantity) {
                        return new JsonResponse(['status' => 'error', 'message' => 'Order quantity exceeds available stock for product ' . $product->getName()], 400);
                    }
    
                    $product->setStock($product->getStock() - $quantity);
                }
            }
        } else {
            $order->setIsVerified(false);
        }
    
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'success', 'message' => 'Order status updated successfully.']);
    }
    
#[Route('/orders/trash', name: 'app_trash_orders')]
public function trash(OrderRepository $orderRepository): Response
{
    $orders = [];
    $o = $orderRepository->findAll();
    foreach ($o as $key => $value) {
        if ($value->isIsverified() === false) {
            $user = $value->getUser()->getLastname();
            $products = [];
            foreach ($value->getProducts() as $orderProduct) {
                $product = $orderProduct->getProduct()[0] ?? null; // Vérifier si le produit existe
                if (!is_null($product)) { // Vérifier si le produit n'est pas null
                    $quantity = $orderProduct->getQuantite();
                    $products[] = [
                        "produit" => $product->getName(),
                        "quantite" => $quantity
                    ];
                }
            }
            // Vérifier si la liste des produits est vide
            if (empty($products)) {
                continue; // Passer à l'itération suivante
            }
            $orders[] = [
                "id" => $value->getId(),
                "verif" => $value->isIsverified(),
                "user" => $user,
                "product" => $products,
                "created_at" => $value->getCreatedAt(),
                "dateliv" => $value->getDateliv()
            ];
        }
    }
    
    return $this->render('orders/trash.html.twig', [
        'orders' => $orders
    ]);
}


#[Route('/orders/accept', name: 'app_accept_orders')]
public function accept(OrderRepository $orderRepository): Response
{
    $orders = [];
    $o = $orderRepository->findAll();
    foreach ($o as $key => $value) {
        if ($value->isIsverified() === true) {
            $user = $value->getUser()->getLastname();
            $products = [];
            for ($i = 0; $i < count($value->getProducts()); $i++) {
                $quantity = $value->getProducts()[$i]->getQuantite();
                $product = $value->getProducts()[$i]->getProduct()[0] ?? null; // Vérifier si le produit existe
                if (!is_null($product)) { // Vérifier si le produit n'est pas null
                    $products[] = [
                        "produit" => $product->getName(),
                        "quantite" => $quantity
                    ];
                }
            }
            // Vérifier si la liste des produits est vide
            if (empty($products)) {
                continue; // Passer à l'itération suivante
            }
            $orders[] = [
                "id" => $value->getId(),
                "verif" => $value->isIsverified(),
                "user" => $user,
                "product" => $products,
                "created_at" => $value->getCreatedAt(),
                "dateliv" => $value->getDateliv()
            ];
        }
    }
    
    return $this->render('orders/accept.html.twig', [
        'orders' => $orders
    ]);
}

#[Route('/suppressionn/{id}', name: 'deletee')]
    public function deleteEmployes(Order $order, ManagerRegistry $doctrine): RedirectResponse 
    {
        if($order){
            $manager = $doctrine->getManager();
            $manager->remove($order);
            $manager->flush();
            $this->addFlash('success', 'la demande est supprimée');
        } else {
            $this->addFlash('danger','la demande innexistante');
        }
        return $this->redirectToRoute('app_trash_orders');
    }
    #[Route('/suppressionnn/{id}', name: 'deleteee')]
    public function deletEmployes(Order $order, ManagerRegistry $doctrine): RedirectResponse 
    {
        if($order){
            $manager = $doctrine->getManager();
            $manager->remove($order);
            $manager->flush();
            $this->addFlash('success', 'la demande est supprimée');
        } else {
            $this->addFlash('danger','la demande innexistante');
        }
        return $this->redirectToRoute('app_accept_orders');
    }

    #[Route('/orders/restore/{id}', name: 'app_restore_order')]
public function restore(Order $order, ManagerRegistry $doctrine): RedirectResponse
{
    if ($order) {
        $order->setIsVerified(null);
        
        $manager = $doctrine->getManager();
        $manager->flush();
        
        $this->addFlash('success', 'La demande a été restaurée.');
    } else {
        $this->addFlash('danger', 'La demande est inexistante.');
    }
    
    return $this->redirectToRoute('app_orders');
}



}
