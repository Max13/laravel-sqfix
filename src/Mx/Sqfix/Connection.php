<?php

namespace Mx\Sqfix;

use Illuminate\Database\SQLiteConnection;

class Connection extends SQLiteConnection
{
    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Schema\Grammars\SQLiteGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }
}
