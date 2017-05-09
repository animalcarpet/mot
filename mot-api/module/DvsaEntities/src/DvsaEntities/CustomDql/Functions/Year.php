<?php

namespace DvsaEntities\CustomDql\Functions;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * "YEAR" "(" SimpleArithmeticExpression ")".
 *
 * @author Rafael Kassner <kassner@gmail.com>
 */
class Year extends FunctionNode
{
    public $date;
    /**
     * @override
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'YEAR('.$sqlWalker->walkArithmeticPrimary($this->date).')';
    }
    /**
     * @override
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->date = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
