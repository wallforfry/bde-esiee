<?php

namespace Application\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Application\ShopBundle\Entity\Product;
use Application\ShopBundle\Entity\Category;
use Application\ShopBundle\Form\ProductType;
use JMS\SecurityExtraBundle\Annotation\Secure;

class ShopController extends Controller
{
    public function indexAction(Category $category = null)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('ApplicationShopBundle:Product');

        if ($category === null)
    	   $product_list = $repository->findAll();
        else {
            $product_list = $category->getProducts();
        }

        return $this->render('ApplicationShopBundle:Shop:index.html.twig', array(
            'product_list' => $product_list,
        ));
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function viewAction(Product $product)
    {
        $form = $this->createForm(new ProductType, $product, array(
            'show_legend' => true,
            'label'       => 'Renseignez votre email',
        ));

        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $interestedPerson = $product->getInterestedPerson();
                $interestedPerson[] = $form->get('email')->getData();
                $product->setInterestedPerson($interestedPerson);
                $product->incrementCounter();

                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Votre mail a bien été enregistré ! Vous serez contacté si le produit est mis en vente.'
                );

                return $this->redirect($this->generateUrl('application_shop_breguet_shop_show', array('id' => $product->getId())));
            }
        }

        return $this->render('ApplicationShopBundle:Shop:view.html.twig', array(
        	'product'	=> $product,
            'form'      => $form->createView(),
        ));
    }

    public function listCategoryAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('ApplicationShopBundle:Category');

    	$category_list = $repository->findAll();

        return $this->render('ApplicationShopBundle:Shop:category.html.twig', array(
        	'category_list'	=> $category_list,
        ));
    }
}
