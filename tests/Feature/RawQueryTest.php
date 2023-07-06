<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class RawQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from categories');
    }

    public function testCrudRow()
    {
        DB::insert(
            'insert into categories(id,name,description,created_at)values(?,?,?,?)',
            ["GAD", "gadget", "gadget category", "2022-06-05 13:00:40"]
        );
        $results  = DB::select('select * from categories where id = ?', ["GAD"]);

        self::assertCount(1, $results);
        assertEquals("GAD", $results[0]->id);
        assertEquals("gadget", $results[0]->name);
        assertEquals("gadget category", $results[0]->description);
        assertEquals("2022-06-05 13:00:40", $results[0]->created_at);
    }
    public function testCrudNamed(): void
    {
        DB::insert(
            'insert into categories(id,name,description,created_at)values(:id,:name,:description,:created_at)',
            [
                "id" => "GAD",
                "name" => "gadget",
                "description" => "gadget category",
                "created_at" => "2022-06-05 13:00:40"
            ]
        );
        $results  = DB::select('select * from categories where id = :id', [
            "id"=>"GAD"
        ]);

        self::assertCount(1, $results);
        assertEquals("GAD", $results[0]->id);
        assertEquals("gadget", $results[0]->name);
        assertEquals("gadget category", $results[0]->description);
        assertEquals("2022-06-05 13:00:40", $results[0]->created_at);
    }
}
