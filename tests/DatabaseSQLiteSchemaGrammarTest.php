<?php

namespace Mx\Sqfix\Tests;

use Composer\InstalledVersions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Mx\Sqfix\Connection as SqfixConnection;
use Mx\Sqfix\SchemaGrammar;

class DatabaseSQLiteSchemaGrammarTest extends TestCase
{
    private static $laravelVersion;

    public static function setUpBeforeClass(): void
    {
        $version = explode('.', InstalledVersions::getVersion('laravel/framework'));

        foreach ($version as &$v) {
            $v = intval($v);
        }

        self::$laravelVersion = $version;
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testServiceProviderLoaded()
    {
        $this->assertInstanceOf(SqfixConnection::class, $this->getConnection());
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

        $expectedModifiers = 'not null primary key autoincrement';
        if (static::$laravelVersion[0] >= 10) {
            $expectedModifiers = 'primary key not null autoincrement';
        }
        $this->assertSame('create table "users" ("id" integer '.$expectedModifiers.', "email" varchar not null)', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->increments('id')->trueSyntax();
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $expected = [
            'alter table "users" add column "id" integer '.$expectedModifiers,
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

        $expectedModifiers = 'not null primary key autoincrement';
        if (static::$laravelVersion[0] >= 10) {
            $expectedModifiers = 'primary key not null autoincrement';
        }
        $this->assertSame('create temporary table "users" ("id" integer '.$expectedModifiers.', "email" varchar not null)', $statements[0]);
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

    protected function getConnection($connection = null, $table = null)
    {
        return DB::connection();
    }

    public function getGrammar()
    {
        return new SchemaGrammar;
    }
}
