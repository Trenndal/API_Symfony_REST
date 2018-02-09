<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\UpdateAt;
use AppBundle\Exception\ResourceValidationException;
use AppBundle\Representation\Products;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;


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

		$requestTime=new \DateTime(Request::createFromGlobals()->headers->get('Last-Modified'));
		$dbTime=$this->getDoctrine()->getManager()->getRepository('AppBundle:UpdateAt')->findOneByTable("product")->getUpdatedAt();
		if($requestTime==$dbTime){
			return new Response("",Response::HTTP_NOT_MODIFIED);
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
		$requestTime=new \DateTime(Request::createFromGlobals()->headers->get('Last-Modified'));
		$dbTime=$product->getUpdatedAt();
		if($requestTime==$dbTime){
			return new Response("",Response::HTTP_NOT_MODIFIED);
		}
        return $product;
    }

}
