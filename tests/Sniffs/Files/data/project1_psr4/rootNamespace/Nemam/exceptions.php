<?php

namespace RootNamespace\Nemam;

interface Exception extends \RootNamespace\Exception
{

}

class InvalidArgumentException extends \RuntimeException implements Exception
{

}
