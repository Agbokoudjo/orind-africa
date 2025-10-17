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

use Sonata\AdminBundle\Controller\CRUDController;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
// #[IsGranted('ROLE_ADMIN', message: 'User tried to access a page without having ROLE_ADMIN', statusCode: 423)]
// #[IsGranted('ROLE_MEMBER', message: 'User tried to access a page without having ROLE_MEMBER', statusCode: 423)]
abstract class WlindablaAdminCRUDController extends CRUDController {}
