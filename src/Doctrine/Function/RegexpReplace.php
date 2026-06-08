<?php

declare(strict_types=1);

namespace App\Doctrine\Function;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * "REGEXP_REPLACE" "(" StringPrimary "," StringPrimary "," StringPrimary ")".
 *
 * Maps to MySQL 8+ REGEXP_REPLACE(subject, pattern, replacement).
 */
class RegexpReplace extends FunctionNode
{
    public Node $subject;

    public Node $pattern;

    public Node $replacement;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->subject = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->pattern = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->replacement = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'REGEXP_REPLACE(%s, %s, %s)',
            $sqlWalker->walkStringPrimary($this->subject),
            $sqlWalker->walkStringPrimary($this->pattern),
            $sqlWalker->walkStringPrimary($this->replacement),
        );
    }
}
