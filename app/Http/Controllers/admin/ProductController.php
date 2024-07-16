<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function create(){
        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
       // $categories = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
      //  $data['brands'] = $brands;
        return view('admin.products.create',$data);
    }

    public function store(Request $request){

    }
}
