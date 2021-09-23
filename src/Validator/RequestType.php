<?php

namespace App\Validator;

enum RequestType
{
    case BODY;
    case GET;
    case POST;
}