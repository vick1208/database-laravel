<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // DB::delete('delete from categories');
        DB::table('categories')->truncate();
    }

    public function testInsertRow(): void
    {
        DB::table("categories")->insert([
            "id" => "GAD",
            "name" => "gadget"
        ]);
        DB::table("categories")->insert([
            "id" => "CFD",
            "name" => "food"
        ]);

        $results = DB::select("select count(id) as total from categories");
        self::assertEquals(2, $results[0]->total);
    }

    public function testSelectRow(): void
    {
        
    }
}
