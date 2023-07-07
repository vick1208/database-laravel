<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertNotNull;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from `categories`');
        // DB::table('categories')->truncate();
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
        $this->testInsertRow();
        $data = DB::table('categories')->select(["id", "name"])->get();
        assertNotNull($data);

        $data->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function insCategories(): void
    {
        DB::table("categories")->insert([
            "id" => "SMARTPHONE",
            "name" => "Smartphone",
            "created_at" => "2021-10-10 12:01:10"
        ]);
        DB::table("categories")->insert([
            "id" => "FOOD",
            "name" => "Food",
            "created_at" => "2021-10-10 12:01:10"
        ]);
        DB::table("categories")->insert([
            "id" => "LAPTOP",
            "name" => "Laptop",
            "created_at" => "2021-10-10 12:01:10"
        ]);
        DB::table("categories")->insert([
            "id" => "FASHION",
            "name" => "Fashion",
            "created_at" => "2021-10-10 12:01:10"
        ]);
    }

    public function testWhere(): void
    {
        $this->insCategories();

        $rows = DB::table("categories")->where(function (Builder $builder){
            $builder->where('id','=','SMARTPHONE');
            $builder->orWhere('id','=',"FOOD");
            // SELECT * FROM categories WHERE (id = smartphone or id = food)
        })->get();

        assertCount(2,$rows);
        $rows->each(function($item){
            Log::info(json_encode($item));
        });
    }
    public function testWhereBetween() : void {
        $this->insCategories();

        $rows = DB::table("categories")
        ->whereBetween("created_at",["2021-09-10 12:01:10","2021-10-30 12:01:10"])
        ->get();
        assertCount(4,$rows);
        $rows->each(function($item){
            Log::info(json_encode($item));
        });
    }
    public function testWhereIn(): void
    {
        $this->insCategories();

        $rows = DB::table("categories")->whereIn("id",["SMARTPHONE","FOOD"])->get();

        assertCount(2,$rows);
        $rows->each(function($item){
            Log::info(json_encode($item));
        });
    }
    public function testWhereNull() : void {
        $this->insCategories();

        $rows = DB::table("categories")
        ->whereNull("description")->get();
        assertCount(4,$rows);
        $rows->each(function($item){
            Log::info(json_encode($item));
        });
    }
    public function testWhereDate()
    {
        $this->insCategories();
        $collection = DB::table("categories")
            ->whereDate("created_at", "2021-10-10")->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
}
