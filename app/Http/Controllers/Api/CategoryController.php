<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryLevelFour;
use App\Models\SubCategory;
use App\Models\SubSubCategory;

class CategoryController extends Controller
{
	public function index(){
		$categories = Category::where('status', 'Active')->get();
		return jsonResponse(
			true, 
			'Category feteched successflly', 
			['categories' => $categories]
		);
	}

	public function subCategory(Request $request){
		$subCategories = SubCategory::where('status', 'Active')
							->where('category_id', $request->category_id)
							->get();
		return jsonResponse(
			true, 
			'Category feteched successflly', 
			['subCategories' => $subCategories]
		);
	}

	public function subSubCategory(Request $request){
		$subCategories = SubSubCategory::where('status', 'Active')
							->where('sub_category_id', $request->sub_category_id)
							->get();
		return jsonResponse(
			true, 
			'Category feteched successflly', 
			['subCategories' => $subCategories]
		);
	}

	public function categoryLevelFour(Request $request){
		$subCategories = CategoryLevelFour::where('status', 'Active')
							->where('sub_sub_category_id', $request->sub_sub_category_id)
							->get();
		return jsonResponse(
			true, 
			'Category feteched successflly', 
			['subCategories' => $subCategories]
		);
	}

	public function getHierarchicalCategories()
	{
		$categories = Category::where('status', 'Active')
			->with([
				'subCategories' => function($query) {
					$query->where('status', 'Active')
						->with([
							'subSubCategories' => function($subQuery) {
								$subQuery->where('status', 'Active')
									->with([
										'categoryLevelFours' => function($levelFourQuery) {
											$levelFourQuery->where('status', 'Active');
										}
									]);
							}
						]);
				}
			])
			->get();

		return jsonResponse(
			true,
			'Hierarchical categories fetched successfully',
			['categories' => $categories]
		);
	}
}