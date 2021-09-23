<?php

namespace App\Controller;

use App\Validator\RequestValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected RequestValidator $requestValidator;

    public function __construct()
    {
        $this->requestValidator = new RequestValidator();
    }
}