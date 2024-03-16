<?php

namespace Mx\Sqfix\Tests;

use Illuminate\Support\Facades\DB;

class DatabaseSQLiteQueryGrammarTest extends TestCase
{
    public function testTruncateTable()
    {
        $query = DB::connection()->query()->from('test');
        $statements = $query->getGrammar()->compileTruncate($query);

        $this->assertCount(1, $statements);
        $this->assertSame(['delete from "test"' => []], $statements);
    }
}
