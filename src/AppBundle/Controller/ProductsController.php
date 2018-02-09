<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Exception\ResourceValidationException;
use AppBundle\Representation\Products;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;


/**
 * @Cache(expires="tomorrow", public=true)
 */
class ProductsController extends FOSRestController
{
    /**
     * @Rest\Get("/api/products", name="app_product_list")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of products per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="The pagination offset"
     * )
     * @Rest\View(StatusCode = 200)
     */
    public function listAction(ParamFetcherInterface $paramFetcher)
    {

		$requestTime=Request::createFromGlobals()->headers->get('Last-Modified');
		$dbTime=$this->getDoctrine()->getManager()->getRepository('AppBundle:UpdateAt')->findOneByTable("product")->getUpdatedAt();
		if($requestTime==$dbTime){
			return new Response($requestTime,Response::HTTP_OK);
		}
        $pager = $this->getDoctrine()->getRepository('AppBundle:Product')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return new Products($pager);
    }

    /**
     * @Rest\Get(
     *     path = "/api/products/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     * 
     * @Cache(lastModified="product.getUpdatedAt()")
     */
    public function showAction(Product $product)
    {
        return $product;
    }

    /**
     * @Rest\Post("/api/products")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("product", converter="fos_rest.request_body")
     */
    public function createAction(Product $product, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($product);
        $em->flush();

        return $product;
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/api/products/{id}",
     *     name = "app_product_update",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newProduct", converter="fos_rest.request_body")
     */
    public function updateAction(Product $product, Product $newProduct, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em=$this->getDoctrine()->getManager();
        $product->setTitle($newProduct->getTitle());
        $product->setContent($newProduct->getContent());
        $product->setContent($newProduct->getContent());
        $product->setAuthor($newProduct->getAuthor());

        $em->flush();

        return $product;
    }

    /**
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/api/products/{id}",
     *     name = "app_product_delete",
     *     requirements = {"id"="\d+"}
     * )
     */
    public function deleteAction(Product $product)
    {
        $em=$this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        return;
    }
}
