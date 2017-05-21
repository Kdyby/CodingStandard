<?php

namespace RootNamespace;

interface Exception
{

}

class InvalidArgumentException extends \RuntimeException implements Exception
{

}
