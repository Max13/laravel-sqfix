<?php

namespace Mx\Sqfix;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as SQLiteSchemaGrammar;
use Illuminate\Support\Fluent;

class SchemaGrammar extends SQLiteSchemaGrammar
{
    /**
     * Adds TrueAutoIncrement modifier to explicitely
     * use this keyword in SQLite
     */
    public function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }

        $this->modifiers[] = 'TrueSyntax';
    }


    /**
     * Fixes by default, INTEGER PRIMARY KEY AUTOINCREMENT
     * to as alias of SQLite rowid
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @return void
     */
    protected function fixIncrement(Blueprint $blueprint)
    {
        collect($blueprint->getAddedColumns())->filter(function ($column) {
            return $column->autoIncrement === true && $column->trueSyntax !== true;
        })->each(function ($column) {
            unset($column->unsigned);
            $column->nullable();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        $this->fixIncrement($blueprint);

        return parent::compileCreate($blueprint, $command);
    }

    /**
     * {@inheritdoc}
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $this->fixIncrement($blueprint);

        return parent::compileAdd($blueprint, $command);
    }

    /**
     * {@inheritdoc}
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        if (is_null($column->virtualAs) && is_null($column->storedAs)) {
            return $column->nullable ? '' : ' not null';
        }

        if ($column->nullable === false) {
            return ' not null';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' primary key';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function modifyTrueSyntax(Blueprint $blueprint, Fluent $column)
    {
        if (
               in_array($column->type, $this->serials)
            && $column->autoIncrement
            && $column->trueSyntax
        ) {
            return ' autoincrement';
        }
    }
}
