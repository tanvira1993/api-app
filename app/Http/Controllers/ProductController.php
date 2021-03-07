<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use DB;

class ProductController extends Controller
{
	public function store(Request $request)
	{		 
		$rules = [			
			'image' => 'required |max:51200|mimes:jpg,jpeg,png,gif',
			'title' => 'required',
			'description' => 'required',
			'price' => 'required'
		];

		$messages = [
			'image.required' => 'Attachment is required',
			'title.required' => 'Title is required',
			'description.required' => 'Description is required',
			'price.required' => 'Price is required'
		];

		$validation = Validator::make($request->all(), $rules, $messages);

		if ($validation->fails()) {
			$errorMsgString = implode("<br/>",$validation->messages()->all());			
			return response()->json([
				'success' => false,
				'message' => $errorMsgString
			], 400);
		}

		$files = $request->file('image');
		$nameonly = preg_replace('/\..+$/', '', $files->getClientOriginalName());

		$path = public_path().'/uploads/';
		if (!is_dir($path)) {
			\File::makeDirectory($path, $mode = 0777, true, true);
		}
		$fileName = null;
		if(!empty($request->file('image'))){
			$fileName = str_replace(" ", "_", $nameonly).'_'.time().'.'.$request->file('image')->extension();
			$request->file('image')->move(public_path() . '/uploads/', $fileName);
		}		

		try{		

			$product = new Product;
			$product->title = $request->title;
			$product->description = $request->description;
			$product->price = $request->price;
			$product->image = !empty($fileName) ? $fileName : null;

			$product->save();

			if($product){
				DB::commit();
				return response()->json([
					'success' => true,
					'message' => 'Product added successfully!'
				],200);
			}else{
			//If block image already existing it is deleted previous block image
				$filePath = public_path() . '/uploads/'. $product->image;

				if (file_exists($filePath)) {
					@unlink($filePath);
				}
				DB::rollBack();
				return response()->json([
					'success' => false,
					'message' => 'Sorry, Product could not be added!!'
				], 400);
			}
		}

		catch (\Exception $e) {
			$filePath = public_path() . '/uploads/'. $fileName;

			if (file_exists($filePath)) {
				@unlink($filePath);
			}
			DB::rollBack();
			return response()->json([
				'success' => false,
				'message' => 'Server Error!! Try Later.'
			], 500);
		}
	}

	public function getProductList(){
		$products = Product::select('products.*')->paginate(3);
		return response()->json([
			'success' => true,
			'products' => $products == null? []:$products,
			'base_url' => url('/').'/uploads/'
		],200);
	}

	public function delete($id){
		$product = Product::where('id', $id)->first();
		if(empty($product)){
			return response()->json([
				'success' => false,
				'message' => 'Invalid Product ID.'
			], 400);
		}
		try{
			$filePath = public_path() . '/uploads/'. $product->image;		

			if (file_exists($filePath)) {
				@unlink($filePath);
			}
			$product->delete();		

			return response()->json([
				'success' => true,
				'message' => 'Product deleted successfully!!'
			],200);
		}

		catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'message' => 'Server Error!! Try Later.'
			], 500);
		}

	}

	public function details($id){
		$product = Product::select('products.*')->where('id', $id)->first();
		if(empty($product)){
			return response()->json([
				'success' => false,
				'message' => 'Invalid Product ID.'
			], 400);
		}
		return response()->json([
			'success' => true,
			'products' => $product,
			'base_url' => url('/').'/uploads/'
		],200);
	}

	public function updateProduct(Request $request, $id){
		$files = $request->file('image');
		$target = Product::find($id);
		if(empty($target)){
			return response()->json([
				'success' => false,
				'message' => 'Invalid Product ID.'
			], 400);
		}
		
		$rules = [
			'title' => 'required',
			'description' => 'required',
			'price' => 'required'
		];

		if(!empty($request->file('image'))){
			$rules['image'] = 'required |max:52000|mimes:jpg,jpeg,png,gif';
		}

		$messages = [
			'image.required' => 'Attachment is required',
			'title.required' => 'Title is required',
			'description.required' => 'Description is required',
			'price.required' => 'Price is required'

		];

		$validation = Validator::make($request->all(), $rules, $messages);

		if ($validation->fails()) {
			$errorMsgString = implode("<br/>",$validation->messages()->all());
			return response()->json([
				'success' => false,
				'message' => $errorMsgString
			], 400);
		}

		$path = public_path().'/uploads/';		
		if (!is_dir($path)) {
			\File::makeDirectory($path, $mode = 0777, true, true);
		}

    	//Finding the product by Id		

		$fileName = null;
		if(!empty($request->file('image'))){
			$nameonly = preg_replace('/\..+$/', '', $files->getClientOriginalName());
			$fileName = str_replace(" ", "_", $nameonly).'_'.time().'.'.$request->file('image')->extension();
			$request->file('image')->move(public_path() . '/uploads/', $fileName);

    		//If block image already existing it is deleted previous block image
			$filePath = public_path() . '/uploads/'. $target->image;

			if (file_exists($filePath)) {
				@unlink($filePath);
			}
		}

		$update = Product::find($id);
		$update->title = $request->title;
		$update->description = $request->description; 
		$update->price = $request->price;

		if(!empty($request->file('image'))){
			$update->image = $fileName;
		}
		$update->save();

		if($update){
			DB::commit();
			return response()->json([
				'success' => true,
				'message' => "Product updated successfully!!"
			], 200);
		}else{
			//If block image already existing it is deleted previous block image
			$filePath = public_path() . '/uploads/'. $fileName;
			if (file_exists($filePath)) {
				@unlink($filePath);
			}
			DB::rollBack();
			return response()->json([
				'success' => false,
				'message' => "Product could not be updated!!"
			], 400);
			
		}
	}
}
