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

namespace App\Domain\Log\Enum;

/**
 * Définit les actions standardisées pour le journal d'activité.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
enum ActivityAction: string 
{
    // Actions de Navigation et Systèmes
    case PAGE_VIEW = 'PAGE_VIEW';
    case AJAX_REQUEST = 'AJAX_REQUEST';
    case ROUTE_NOT_FOUND = 'ROUTE_NOT_FOUND';
    case LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    case LOGIN_FAILURE = 'LOGIN_FAILURE';
    case LOGOUT = 'LOGOUT';
    case LOGIN = 'login';
    case DOWNLOAD = 'download';
    case UPLOAD = 'upload';
    case EXPORT = 'export';
    case IMPORT = 'import';
    case OTHER = 'other';
    
    // Actions CRUD
    case ENTITY_CREATE = 'ENTITY_CREATE';
    case ENTITY_UPDATE = 'ENTITY_UPDATE';
    case ENTITY_DELETE = 'ENTITY_DELETE';
    case ENTITY_READ = 'ENTITY_READ';

    // Actions Business Spécifiques
    case PASSWORD_RESET = 'PASSWORD_RESET';
    case ORDER_PLACED = 'ORDER_PLACED';
    case PAYMENT_FAILED = 'PAYMENT_FAILED';
}