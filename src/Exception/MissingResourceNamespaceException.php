<?php

namespace Laminas\Loader\Exception;

require_once __DIR__ . '/ExceptionInterface.php';

class MissingResourceNamespaceException extends \Exception implements ExceptionInterface
{
}
