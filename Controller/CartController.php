<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderQuantity;
use App\Entity\Products;
use App\Repository\OrderQuantityRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route("/", name: "index"), IsGranted('ROLE_USER')]
    public function index(SessionInterface $session,OrderRepository $rep, ProductsRepository $productsRepository, Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $currentYear = (new \DateTime())->format('Y');
        $orders = $rep->findBy(['user' => $user ]);
        $listYear = [];
        $listeProduct = [];
        foreach($orders as $item) {
            $createdAtYear = $item->getCreatedAt()->format('Y');
            if($createdAtYear == $currentYear){
                $listYear [] = $item ;
            }
        }
        foreach($listYear as $list){
            foreach ($list->getProducts()[0]->getProduct() as $value) {
                $listeProduct [] = $value->getName();
            }
        }
        $occurrences = array_count_values($listeProduct);
        // dd($listYear[5]->getProducts()[0]->getProduct()[0]);
        // dd($orders);
        $order = new Order();
        $panier = $session->get("panier", []);
        $dataPanier = [];
        $countz=[];
        foreach ($panier as $id => $quantite) {
            $product = $productsRepository->find($id);
            if(in_array($product->getName(),$listeProduct)){
                $countz[] = [$product->getName() => $occurrences[$product->getName()]];
            }else{
                $countz[] = [$product->getName() => 0];
            }
            $dataPanier[] = [
                "produit" => $product,
                "quantite" => $quantite
            ];
            $orderQuantity = new OrderQuantity();
            $orderQuantity->setQuantite($quantite);
            $orderQuantity->addProduct($product);
            $orderQuantity->addOrder($order);
            $entityManager->persist($orderQuantity);
            $order->addProduct($orderQuantity);
        }
        // dd($countz);
        
        $user = $this->getUser();
        $order->setUser($user);
        if ($request->getMethod() == 'POST') {
            // dd($order);
            foreach ($order->getProducts() as $pp) {
                // dd($pp);
                foreach ($pp->getProduct() as $hamma) {
                    $numbertotal = 0;
                    foreach ($countz as $va) {
                        foreach ($va as $key => $val) {
                            // dd($val);
                            if($hamma->getName() == $key ){
                                $productz = $productsRepository->findBy(['name' =>$key]);
                                // dd($hamma->getOrderQuantities()[0]);
                                $numbertotal = $pp->getQuantite() + $val ;
                                if($numbertotal<= $productz[0]->getMaxq() ){
                                $productz[0]->setMaxq($productz[0]->getMaxq() - $pp->getQuantite());
                                    $entityManager->persist($order);
                                    $entityManager->flush();
                                    $session->set("panier", []);
                                    $this->addFlash(
                                        'success',
                                        'Votre demande a été soumise à l\'administration des produits.'
                                    );
                                    return $this->redirectToRoute('main');
                                }else{
                                    $this->addFlash(
                                        'danger',
                                        "Vous avez dépassé la quantité maximale de ce produit pour cette année : ".$productz[0]->getName()."."
                                    );
                                    return $this->redirectToRoute('cart_index');
                                }
                            }
                        }
                        }
                    }
                }
            }


        return $this->render('cart/index.html.twig', [
            'dataPanier' => $dataPanier
        ]);
    }

    
    #[Route("/add/{id}", name :"add")]  
    public function add(Products $product, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();
        // dd($panier);

        if(!empty($panier[$id])){
            if($panier[$id]<$product->getStock()){

                $panier[$id]++;
            }
        }else{
            $panier[$id] = 1;
        }
        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("cart_index");
    }

    
    #[Route("/remove/{id}", name : "remove")]
    
    public function remove(Products $product, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if(!empty($panier[$id])){
            if($panier[$id] > 1){
                $panier[$id]--;
            }else{
                unset($panier[$id]);
            }
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("cart_index");
    }

    
    #[Route("/delete/{id}", name :"delete")]
    
    public function delete(Products $product, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("cart_index");
    }

    /**
     * @Route("/delete", name="delete_all")
     */
    public function deleteAll(SessionInterface $session)
    {
        $session->remove("panier");

        return $this->redirectToRoute("cart_index");
    }

}