<?php

namespace Mx\Sqfix;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SQLiteGrammar as SqliteQueryGrammar;

class QueryGrammar extends SqliteQueryGrammar
{
    /**
     * {@inheritdoc}
     */
    public function compileTruncate(Builder $query)
    {
        return [
            'delete from '.$this->wrapTable($query->from) => [],
        ];
    }
}
