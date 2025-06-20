<?php

namespace App\Security\Attribute;

enum EntityAction: string
{
    case VIEW   = 'VIEW';
    case EDIT   = 'EDIT';
    case CREATE = 'CREATE';
    case DELETE = 'DELETE';
}
