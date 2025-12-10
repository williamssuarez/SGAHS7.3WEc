<?php

namespace App\Exception;

class BusinessRuleException extends \RuntimeException
{
    // This class doesn't need a body; it just helps us differentiate
    // a business error from a low-level framework error (like FileException).
}
