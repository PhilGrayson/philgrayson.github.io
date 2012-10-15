<?php

namespace Library\Doctrine\Extensions\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class DateFormat extends FunctionNode
{

    public $dateExpression = null;
    public $formatChar = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->dateExpression = $parser->ArithmeticExpression();

        $parser->match(Lexer::T_COMMA);

        $this->formatChar = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'DATE_FORMAT(' .
        $sqlWalker->walkArithmeticExpression($this->dateExpression) .
        ','.
        $sqlWalker->walkStringPrimary($this->formatChar) .
        ')';
    }

}
