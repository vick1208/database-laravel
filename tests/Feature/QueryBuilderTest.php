<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from `products`');
        DB::delete("delete from `categories`");
        DB::delete("delete from `counters`");
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
        $this->seed(CategorySeeder::class);
    }

    public function testWhere(): void
    {
        $this->insCategories();

        $rows = DB::table("categories")->where(function (Builder $builder) {
            $builder->where('id', '=', 'SMARTPHONE');
            $builder->orWhere('id', '=', "FOOD");
            // SELECT * FROM categories WHERE (id = smartphone or id = food)
        })->get();

        assertCount(2, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testWhereBetween(): void
    {
        $this->insCategories();

        $rows = DB::table("categories")
            ->whereBetween("created_at", ["2021-09-10 12:01:10", "2021-10-30 12:01:10"])
            ->get();
        assertCount(4, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testWhereIn(): void
    {
        $this->insCategories();

        $rows = DB::table("categories")->whereIn("id", ["SMARTPHONE", "FOOD"])->get();

        assertCount(2, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testWhereNull()
    {
        $this->insCategories();

        $rows = DB::table("categories")
            ->whereNull("description")->get();
        assertCount(4, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testWhereDate()
    {
        $this->insCategories();
        $collection = DB::table("categories")
            ->whereDate("created_at", "2021-10-10")->get();

        assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testUpdateRow()
    {
        $this->insCategories();
        DB::table("categories")->where("id", '=', 'SMARTPHONE')->update([
            "name" => "Samsung Phones"
        ]);
        $collection = DB::table("categories")->where("name", "=", 'Samsung Phones')->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpIns(): void
    {
        DB::table('categories')->updateOrInsert(["id" => "CARS"], [
            "name" => "Cars",
            "description" => "Cars and Car Accesories",
            "created_at" => "2021-10-13 14:17:30"
        ]);

        $data = DB::table('categories')->where("id", "=", "CARS")->get();
        assertCount(1, $data);
        $data->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testIncrement(): void
    {

        $this->seed(CounterSeeder::class);
        // DB::table('counters')->insert(['id' => 'test', 'counter' => 0]); 
        DB::table('counters')->where('id', '=', 'test')->increment('counter', 1);
        $data = DB::table('counters')->where("id", "=", "test")->get();
        assertCount(1, $data);
        $data->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testdeleteRow(): void
    {
        $this->insCategories();
        DB::table('categories')->where('id', '=', 'smartphone')->delete();

        $data = DB::table('categories')->where('id', '=', 'smartphone')->get();
        assertCount(0, $data);
        $data->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function insProducts(): void
    {
        $this->insCategories();
        DB::table("products")->insert([
            "id" => "1",
            "name" => "Iphone 14 Pro X",
            "category_id" => "SMARTPHONE",
            "price" => "22550000"
        ]);
        DB::table("products")->insert([
            "id" => "2",
            "name" => "Samsung Galaxy S21",
            "category_id" => "SMARTPHONE",
            "price" => "2145000"
        ]);
    }

    public function testJoinTable(): void
    {
        $this->insProducts();
        $rows = DB::table("products")
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'products.price', 'categories.name as cat_name')
            ->get();
        assertCount(2, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testOrderingTable(): void
    {
        $this->insProducts();
        $rows = DB::table("products")
            ->whereNotNull('id')
            ->orderByDesc('price')
            ->orderBy("name")
            ->get();
        assertCount(2, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testPaging(): void
    {
        $this->insCategories();
        $rows = DB::table('categories')
            ->skip(0)
            ->take(2)
            ->get();
        assertCount(2, $rows);
        $rows->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function insManyCategories(): void
    {
        for ($i = 0; $i < 100; $i++) {
            DB::table("categories")->insert([
                "id" => "CATE-$i",
                "name" => "Category $i",
                "created_at" => "2021-10-10 12:01:10"
            ]);
        }
    }

    public function testChunk(): void
    {
        $this->insManyCategories();

        DB::table('categories')->orderBy('id')
            ->chunk(10, function ($categories) {
                assertNotNull($categories);
                Log::info("Begin Chunk");
                $categories->each(function ($category) {
                    Log::info(json_encode($category));
                });
                Log::info("End Chunk");
            });
    }
    public function testLazyRes(): void
    {
        $this->insManyCategories();

        $data = DB::table('categories')->orderBy('id')
            ->lazy(10)->take(4);

        assertNotNull($data);

        $data->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
    public function testCursor(): void
    {
        $this->insManyCategories();

        $data = DB::table('categories')->orderBy('id')
            ->cursor();

        assertNotNull($data);

        $data->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testAggRow(): void
    {
        $this->insProducts();
        $res = DB::table('products')->count('id');
        assertEquals(2, $res);
        $res = DB::table('products')->min('price');
        assertEquals(2145000, $res);
        $res = DB::table('products')->max('price');
        assertEquals(22550000, $res);
        $res = DB::table('products')->sum('price');
        assertEquals(24695000, $res);
        $res = DB::table('products')->average('price');
        assertEquals(12347500, $res);
    }
    public function testQueryBuildRaw(): void
    {
        $this->insProducts();

        $rows = DB::table('products')->select(
            DB::raw('count(id) as total_product'),
            DB::raw('min(price) as min_price'),
            DB::raw('max(price) as max_price'),
        )->get();

        assertEquals(2, $rows[0]->total_product);
        assertEquals(2145000, $rows[0]->min_price);
        assertEquals(22550000, $rows[0]->max_price);
    }
    public function insProductFood()
    {
        DB::table("products")->insert([
            "id" => "3",
            "name" => "Bakso",
            "category_id" => "FOOD",
            "price" => 12000
        ]);
        DB::table("products")->insert([
            "id" => "4",
            "name" => "Mie Ayam",
            "category_id" => "FOOD",
            "price" => 10000
        ]);
    }
    public function testQueryBuildGroup(): void
    {
        $this->insProducts();
        $this->insProductFood();

        $data = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderByDesc("category_id")
            ->get();
        assertCount(2, $data);
        assertEquals("SMARTPHONE", $data[0]->category_id);
        assertEquals("FOOD", $data[1]->category_id);
        assertEquals(2, $data[0]->total_product);
        assertEquals(2, $data[1]->total_product);
    }
    public function testQueryBuildGroupHaving(): void
    {
        $this->insProducts();
        $this->insProductFood();

        $data = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->having(DB::raw('count(*)'), '>', 2)
            ->orderByDesc("category_id")
            ->get();
        assertCount(0, $data);
    }
    public function testLocking(): void
    {
        $this->insProducts();

        DB::transaction(function () {
            $data = DB::table("products")
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();

            self::assertCount(1, $data);
        });
    }

    public function testPaginate(): void
    {
        $this->insCategories();
        $paginate = DB::table('categories')->paginate(perPage: 2, page: 2);
        assertEquals(2, $paginate->currentPage());
        assertEquals(2, $paginate->perPage());
        assertEquals(2, $paginate->lastPage());
        assertEquals(4, $paginate->total());

        $data = $paginate->items();
        assertCount(2, $data);
        foreach ($data as $item) {
            Log::info(json_encode($item));
        }
    }
    public function testIterateAllPaginate(): void
    {
        $this->insCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table('categories')->paginate(perPage: 2, page: $page);
            if ($paginate->isNotEmpty()) {
                $page++;
                $data = $paginate->items();
                assertCount(2, $data);
                foreach ($data as $item) {
                    Log::info(json_encode($item));
                }
            } else {
                break;
            }
        }
    }
    public function testCursorPaginate(): void
    {
        $this->insCategories();

        $cursor = "id";
        while (true) {
            $paginate = DB::table('categories')->orderBy("id")->cursorPaginate(perPage:2,cursor:$cursor);

            foreach($paginate->items() as $item){
                assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}
