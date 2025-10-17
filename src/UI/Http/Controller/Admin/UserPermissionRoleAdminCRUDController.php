<?php

declare(strict_types=1);
/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

namespace App\UI\Http\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use App\Domain\User\BaseUserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;
use App\UI\Http\Controller\Admin\WlindablaAdminCRUDController;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserPermissionRoleAdminCRUDController extends WlindablaAdminCRUDController {

    public function __construct(private readonly EntityManagerInterface $em)
    {
        
    }
    public function preCreate(Request $request,object $newObject):?Response{

        if(!$this->isXmlHttpRequest($request)){
            return null ;
        }

        if(!$request->isMethod('get')){ 
            throw new MethodNotAllowedHttpException(['GET'],\sprintf('the http method %s not allowed',$request->getmethod()));
         }
         
        $userType=$request->query->get('userType');

        /**
         * @var EntityRepository|null
         */
        $repo=match($userType){
            'ADMIN'=>$this->em->getRepository(AdminUser::class),
            'MEMBER'=>$this->em->getRepository(MemberUser::class),
            'SIMPLE'=> null, // puisque l'entity n'est pas encore implementer
            default =>null
        };

        if(!$repo){
            return new JsonResponse([],Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var array<int,BaseUserInterface>
         */
        $users=$repo->findAll();

        return new JsonResponse(array_map(fn ($user)=>[
            'id'=>$user->getId(),
            'username'=>$user->getUsername()
            ],$users)
        );
    }
}
