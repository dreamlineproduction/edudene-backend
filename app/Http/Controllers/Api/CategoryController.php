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
		$categoryIds = @explode(',', $request->category_id);


		$subCategories = SubCategory::where('status', 'Active')
							->whereIn('category_id', $categoryIds)
							->get();
		return jsonResponse(
			true, 
			'Category Level Two feteched successflly', 
			['subCategories' => $subCategories]
		);
	}

	public function subSubCategory(Request $request){

		$subCategoryIds = @explode(',', $request->sub_category_id);

		$subCategories = SubSubCategory::where('status', 'Active')
							->whereIn('sub_category_id', $subCategoryIds)
							->get();
		return jsonResponse(
			true, 
			'Category Level Three feteched successflly', 
			['subCategories' => $subCategories]
		);
	}

	public function categoryLevelFour(Request $request){
		$subSubCategoryIds = @explode(',', $request->sub_sub_category_id);

		$subCategories = CategoryLevelFour::where('status', 'Active')
							->whereIn('sub_sub_category_id', $subSubCategoryIds)
							->get();
		return jsonResponse(
			true, 
			'Category Level Four feteched successflly', 
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