<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;
use ReflectionClass;

final class RequestValidator
{
    public function validate(string $class, Request $request, RequestType $requestType = RequestType::BODY): array
    {
        $reflection = new ReflectionClass($class);
        $requestParams = match ($requestType) {
            RequestType::BODY => json_decode($request->getContent(), true),
            RequestType::GET => $request->query->all(),
            RequestType::POST => $request->request->all()
        };
        
        $requestParams = $requestParams ?? [];

        $errors = [];
        foreach ($reflection->getProperties() as $property) {
            if (!array_key_exists($property->name, $requestParams)) {
                array_push($errors, "{$property->name} is required.");
            }
        }

        return $errors;
    }
}