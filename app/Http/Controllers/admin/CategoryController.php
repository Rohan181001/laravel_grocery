<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\File;
use Image;

class CategoryController extends Controller
{
  public function index(Request $request)
  {
    $categories = Category::latest();

    if (!empty($request->get('keyword'))) {
      $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
    }

    $categories = $categories->paginate(10);
    return view('admin.category.list', compact('categories'));
  }

  public function create()
  {
    return view('admin.category.create');
  }

  public function store(Request $request)
  {
    $validator = validator::make(request()->all(), [
      'name' => 'required',
      'slug' => 'required|unique:categories',
    ]);

    if ($validator->passes()) {

      $category = new Category();
      $category->name = $request->name;
      $category->slug = $request->slug;
      $category->status = $request->status;
      $category->save();

      //Save Image Here

      if (!empty($request->image_id)) {
        $tempImage = TempImage::find($request->image_id);
        $extArray = explode('.', $tempImage->name);
        $ext = last($extArray);

        $newImageName = $category->id . '.' . $ext;
        $spath = public_path() . '/temp/' . $tempImage->name;
        $dpath = public_path() . '/uploads/category/' . $newImageName;
        File::copy($spath, $dpath);

        // Generate Image Thumbnail
        $dpath = public_path() . '/uploads/category/thumb/' . $newImageName;
        $img = Image::make($spath);
        //$img->resize(450, 600);
        $img->fit(450, 600, function ($constraint) {
          $constraint->upsize();
        });
        $img->save($dpath);

        $category->image = $newImageName;

        $category->save();
      }

      $request->session()->flash('success', 'Category added successfully');

      return response()->json([
        'status' => true,
        'message' => 'Category added successfully'
      ]);

    } else {
      return response()->json([
        'status' => false,
        'errors' => $validator->errors()
      ]);
    }
  }

  public function edit($categoryId, Request $request)
  {
    $category = Category::find($categoryId);
    if(empty($category)){
      return redirect()->route('categories.index');
    }
    return view('admin.category.edit',compact('category'));
  }

  public function update($categoryId, Request $request) 
  {
    $category = Category::find($categoryId);

    if(empty($category)){
      $request->session()->flash('error','Category not found');

      return response()->json([
        'status' => false,
        'notFound' =>true,
        'message' => 'Category not found'
      ]);
    }



    $validator = validator::make(request()->all(), [
      'name' => 'required',
      'slug' => 'required|unique:categories,slug,'.$category->id.',id',
    ]);

    if ($validator->passes()) {

      
      $category->name = $request->name;
      $category->slug = $request->slug;
      $category->status = $request->status;
      $category->save();

      $oldImage = $category->image;

      //Save Image Here

      if (!empty($request->image_id)) {
        $tempImage = TempImage::find($request->image_id);
        $extArray = explode('.', $tempImage->name);
        $ext = last($extArray);

        $newImageName = $category->id.'-'.time().'.'.$ext;
        $spath = public_path() . '/temp/' . $tempImage->name;
        $dpath = public_path() . '/uploads/category/' . $newImageName;
        File::copy($spath, $dpath);

        // Generate Image Thumbnail
        $dpath = public_path() . '/uploads/category/thumb/' . $newImageName;
        $img = Image::make($spath);
        //$img->resize(450, 600);
        $img->fit(450, 600, function ($constraint) {
          $constraint->upsize();
        });
        $img->save($dpath);

        $category->image = $newImageName;
        $category->save();

        //Delete Old Images Here
        File::Delete(public_path().'/uploads/category/thumb/'.$oldImage);
        File::Delete(public_path().'/uploads/category/'.$oldImage);

      }

      $request->session()->flash('success', 'Category updated successfully');

      return response()->json([
        'status' => true,
        'message' => 'Category updated successfully'
      ]);

    } else {
      return response()->json([
        'status' => false,
        'errors' => $validator->errors()
      ]);
    }
  }

  public function destroy($categoryId, Request $request)
  {
    $category = Category::find($categoryId);

    if(empty($category)){
      $request->session()->flash('error','Category not found');
      return response()->json([
        'status' => true,
        'message' => 'Category not found' 
      ]);
      //return redirect()->route('categories.index');
    }
    
    File::Delete(public_path().'/uploads/category/thumb/'.$category->image);
    File::Delete(public_path().'/uploads/category/'.$category->image);

    $category->delete();

    $request->session()->flash('success','Category deleted successfully');

    return response()->json([
      'status' => true,
      'message' => 'Category deleted successfully' 
    ]);

  }



}
