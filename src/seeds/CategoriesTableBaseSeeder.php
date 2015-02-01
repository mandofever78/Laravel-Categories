<?php namespace Mandofever78\LaravelCategories;

class CategoriesTableBaseSeeder extends \Seeder {

    public function run()
    {
        \DB::table('mandofever78_categories')->delete();
        $types = \Config::get('laravel-categories::types');
        $roots = array_keys($types);
        foreach ($roots as $root)
        {
	        Category::create(array(
	            'name' => $root,
	        ));
        }
    }

}
