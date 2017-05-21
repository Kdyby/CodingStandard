<?php

namespace RootNamespace\Nemam\Boom;

interface Exception extends \RootNamespace\Exception
{

}

class InvalidArgumentException extends \RuntimeException implements Exception
{

}
