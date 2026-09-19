<?php

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType; // <-- Modern Doctrine uses this for constants

class CastFunction extends FunctionNode
{
    public $expression;
    public $type;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // CAST
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // (

        // Matches the column (e.g., u.roles)
        $this->expression = $parser->StringPrimary();

        $parser->match(TokenType::T_AS); // AS

        // Capture the target type (e.g., 'text') BEFORE matching it to avoid skipping it
        $token = $parser->getLexer()->lookahead;
        // Supports both Doctrine Lexer 2 (arrays) and 3 (objects)
        $this->type = is_array($token) ? $token['value'] : $token->value;

        $parser->match(TokenType::T_IDENTIFIER); // text
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // )
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'CAST(%s AS %s)',
            $this->expression->dispatch($sqlWalker),
            $this->type
        );
    }
}
