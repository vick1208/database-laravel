<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from categories');
    }
    public function testTransactionSuccess(): void
    {
        DB::transaction(function () {
            DB::insert(
                'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                [
                    "id" => "GAD",
                    "name" => "gadget",
                    "description" => "gadget category",
                    "created_at" => "2022-06-05 13:00:40"
                ]
            );
            DB::insert(
                'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                [
                    "id" => "BEV",
                    "name" => "beverage",
                    "description" => "beverage category",
                    "created_at" => "2022-06-05 13:30:10"
                ]
            );
        });

        $results = DB::select('select * from categories');
        assertCount(2, $results);
    }
    public function testTransactionFailed(): void
    {
        try {
            DB::transaction(function () {
                DB::insert(
                    'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                    [
                        "id" => "GAD",
                        "name" => "gadget",
                        "description" => "gadget category",
                        "created_at" => "2022-06-05 13:00:40"
                    ]
                );
                DB::insert(
                    'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                    [
                        "id" => "GAD",
                        "name" => "beverage",
                        "description" => "beverage category",
                        "created_at" => "2022-06-05 13:30:10"
                    ]
                );
            });
        } catch (QueryException $error) {
            // var_dump($error);
        }

        $results = DB::select('select * from categories');
        assertCount(0, $results);
    }

    public function testManualTransSuccess() : void {
        try {
            DB::beginTransaction();
            DB::insert(
                'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                [
                    "id" => "GAD",
                    "name" => "gadget",
                    "description" => "gadget category",
                    "created_at" => "2022-06-05 13:00:40"
                ]
            );
            DB::insert(
                'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                [
                    "id" => "BEV",
                    "name" => "beverage",
                    "description" => "beverage category",
                    "created_at" => "2022-06-05 13:30:10"
                ]
            );
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }
        $results = DB::select('select * from categories');
        assertCount(2, $results);


    }
    public function testManualTransFailed() : void {
        try {
            DB::beginTransaction();
            DB::insert(
                'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                [
                    "id" => "GAD",
                    "name" => "gadget",
                    "description" => "gadget category",
                    "created_at" => "2022-06-05 13:00:40"
                ]
            );
            DB::insert(
                'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
                [
                    "id" => "GAD",
                    "name" => "beverage",
                    "description" => "beverage category",
                    "created_at" => "2022-06-05 13:30:10"
                ]
            );
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }
        $results = DB::select('select * from categories');
        assertCount(0, $results);


    }
}
