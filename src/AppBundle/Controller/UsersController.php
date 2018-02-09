<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UpdateAt;
use AppBundle\Exception\ResourceValidationException;
use AppBundle\Representation\Users;
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
class UsersController extends FOSRestController
{
    /**
     * @Rest\Get("/api/users", name="app_user_list")
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
     *     description="Max number of users per page."
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
        $client=$this->get('security.token_storage')->getToken()->getUser();

		$requestTime=Request::createFromGlobals()->headers->get('Last-Modified');
		$dbTime=$this->getDoctrine()->getManager()->getRepository('AppBundle:UpdateAt')->findOneByTable("user")->getUpdatedAt();
		if($requestTime==$dbTime){
			return new Response($requestTime,Response::HTTP_OK);
		}

        $pager = $this->getDoctrine()->getRepository('AppBundle:User')->search(
            (string)$client,
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return new Users($pager);
    }

    /**
     * @Rest\Get(
     *     path = "/api/users/{id}",
     *     name = "app_user_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     * 
     * @Cache(lastModified="user.getUpdatedAt()")
     */
    public function showAction(User $user)
    {
		if($this->get('security.token_storage')->getToken()->getUser()!=$user->getClient()){
			throw $this->createAccessDeniedException('You cannot access this user !');
		}
		
        return $user;
    }

    /**
     * @Rest\Post("/api/users")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("user", converter="fos_rest.request_body")
     */
    public function createAction(User $user, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();
		$user->setClient($this->get('security.token_storage')->getToken()->getUser());

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/api/users/{id}",
     *     name = "app_user_update",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newUser", converter="fos_rest.request_body")
     */
    public function updateAction(User $user, User $newuser, ConstraintViolationList $violations)
    {
		if($this->get('security.token_storage')->getToken()->getUser()!=$user->getClient()){
			throw $this->createAccessDeniedException('You cannot update this user !');
		}

        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em=$this->getDoctrine()->getManager();
        $user->setName($newuser->getName());
        $user->setFirstName($newuser->getFirstName());
        $user->setEmail($newuser->getEmail());
        $user->setPassword($newuser->getPassword());
        $user->setUpdateAt(new \DateTime());
        $user->setClient($this->get('security.token_storage')->getToken()->getUser());

        $em->flush();

        return $user;
    }

    /**
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/api/users/{id}",
     *     name = "app_user_delete",
     *     requirements = {"id"="\d+"}
     * )
     */
    public function deleteAction(User $user)
    {
		if($this->get('security.token_storage')->getToken()->getUser()!=$user->getClient()){
			throw $this->createAccessDeniedException('You cannot delete this user !');
		}
        $em=$this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return;
    }
}
