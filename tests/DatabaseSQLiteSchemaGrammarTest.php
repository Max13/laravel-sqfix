<?php

namespace Mx\Sqfix\Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Mx\Sqfix\SchemaGrammar;
use Mockery as m;

class DatabaseSQLiteSchemaGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicCreateTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->increments('id');
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create table "users" ("id" integer primary key, "email" varchar not null)', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->increments('id');
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $expected = [
            'alter table "users" add column "id" integer primary key',
            'alter table "users" add column "email" varchar not null',
        ];
        $this->assertEquals($expected, $statements);
    }

    public function testBasicCreateTableWithTrueSyntax()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->increments('id')->trueSyntax();
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create table "users" ("id" integer not null primary key autoincrement, "email" varchar not null)', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->increments('id')->trueSyntax();
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $expected = [
            'alter table "users" add column "id" integer not null primary key autoincrement',
            'alter table "users" add column "email" varchar not null',
        ];
        $this->assertEquals($expected, $statements);
    }

    public function testCreateTemporaryTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->temporary();
        $blueprint->increments('id');
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create temporary table "users" ("id" integer primary key, "email" varchar not null)', $statements[0]);
    }

    public function testCreateTemporaryTableWithTrueSyntax()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->temporary();
        $blueprint->increments('id')->trueSyntax();
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create temporary table "users" ("id" integer not null primary key autoincrement, "email" varchar not null)', $statements[0]);
    }

    public function testAddingPrimaryKey()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->string('foo')->primary();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create table "users" ("foo" varchar not null, primary key ("foo"))', $statements[0]);
    }

    public function testAddingPrimaryKeyWithTrueSyntax()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->string('foo')->primary()->trueSyntax();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create table "users" ("foo" varchar not null, primary key ("foo"))', $statements[0]);
    }

    protected function getConnection($connection = null)
    {
        return m::mock(Connection::class);
    }

    public function getGrammar()
    {
        return new SchemaGrammar;
    }
}
