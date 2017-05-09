<?php

namespace DvsaEntities\CustomDql\Functions;

/*
 * Example Usage:
 * $query = $this->getEntityManager()->createQuery('SELECT e FROM Entity e WHERE REGEXP(e.field, :regexp) = 1');
 */

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * Class Regexp.
 */
class Regexp extends FunctionNode
{
    private $regexp = null;
    private $value = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->value = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->regexp = $parser->StringExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return '('.$this->value->dispatch($sqlWalker).' REGEXP '.$this->regexp->dispatch($sqlWalker).')';
    }
}
