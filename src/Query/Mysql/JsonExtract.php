<?php

namespace App\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\{Parser, SqlWalker, TokenType};

/**
 * JsonExtract ::= "JSON_EXTRACT" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class JsonExtract extends FunctionNode
{
    public $field = null;
    public $key = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->field = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->key = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'JSON_UNQUOTE(JSON_EXTRACT(%s, %s))',
            $this->field->dispatch($sqlWalker),
            $this->key->dispatch($sqlWalker),
        );
    }
}
