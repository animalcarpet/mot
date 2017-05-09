<?php

/**
 * Date.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaEntities\CustomDql\Functions;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * Date.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Date extends FunctionNode
{
    public $date;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'DATE('.$this->date->dispatch($sqlWalker).')';
    }
}
