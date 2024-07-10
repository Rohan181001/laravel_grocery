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
    public function index(Request $request){
          $categories = Category::latest();

          if(!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
          }

          $categories = $categories->paginate(10);
          return view('admin.category.list',compact('categories'));
    }

    public function create(){
        return view('admin.category.create');
    }

    public function store(Request $request){
       $validator = validator::make(request()->all(),[
        'name'=> 'required',
        'slug'=> 'required|unique:categories',
       ]);

       if($validator->passes()){

        $category = new Category();
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->status = $request->status;
        $category->save();

        //Save Image Here

        if(!empty($request->image_id)){
          $tempImage = TempImage::find($request->image_id);
          $extArray = explode('.',$tempImage->name);
          $ext = last($extArray);

          $newImageName = $category->id.'.'.$ext;
          $spath = public_path().'/temp/'.$tempImage->name;
          $dpath = public_path().'/uploads/category/'.$newImageName;
          File::copy($spath, $dpath);

          // Generate Image Thumbnail
          $dpath = public_path().'/uploads/category/thumb/'.$newImageName;
          $img = Image::make($spath);
          $img->resize(450, 600);
          $img->save($dpath);

          $category->image = $newImageName;
          
          $category->save();
        }

        $request->session()->flash('success','Category added successfully');

        return response()->json([
          'status'=> true,
          'message'=> 'Category added successfully'
       ]);

       } else{
         return response()->json([
            'status'=> false,
            'errors'=>$validator->errors()
         ]);
       }
    }

    public function edit(){

    }

    public function update(){

    }

    public function destroy(){

    }

    

}
