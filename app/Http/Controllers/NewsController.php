<?php

namespace App\Http\Controllers;

use App\News;
use App\Category;
use App\District;
use App\Status;
use App\Gallery;
Use App\Setting;
Use App\Attributes;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class NewsController extends Controller
{

    public function index()
    {
        $newslist = News::with('category')->latest()->get();
        return view('backend.news.index', compact('newslist'));
    }


    public function create()
    {
        $categories = Category::latest()->select('id','name')->where('status',1)->get();
        $types = config('properties.news_types');
        $districts = District::latest()->select('id','name')->where('status',1)->get();
        $statuses = Status::latest()->select('id','name')->get();
        $galleries = Gallery::latest()->select('id','path', 'size')->where('news_id',Setting::getRandomId())->get();
        $attributes = Attributes::latest()->select('id','name', 'value')->where('news_id',Setting::getRandomId())->get();
        return view('backend.news.create', compact('categories', 'types', 'districts', 'statuses', 'galleries', 'attributes'));
    }

    public function storeApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'         => 'required|unique:news|max:191',
            'slug'         => 'required|unique:news|max:191',
        ]);
        if ($validator->fails()) {
            return "false";
        } 
        if (strlen($request->image) > 0) {
            $imageName = 'news-'. time().uniqid(). '.jp2';
            file_put_contents(public_path('images/').$imageName, file_get_contents($request->image));
        }

        $news_id = News::create([
            'title'         => $request->title,
            'slug'          => str_slug($request->slug),
            'address'       => $request->address,
            'coords'        => "",
            'category_id'   => (!Category::where('name', $request->category)->exists() ?        
                 Category::create([
                    'name'   => $request->category,
                    'slug'   => str_slug($request->category),
                    'image'  => $imageName,
                    'status' => true,
                    'group_categories_id' => 1,
                ]) : Category::where('name', $request->category)->first())->id,
            'type_id'       => "Musashi",
            'district_id'   => (!District::where('name', $request->district)->exists() ?  
                 District::create([
                    'name'   => $request->district,
                    'romanji_name'  => $request->district,
                    'image'         => $imageName,
                    'status' => true,
                ]) : District::where('name', $request->district)->first())->id,
            'statuses_id'     => (!Status::where('name', $request->statuses)->exists() ?           
                 Status::create([
                    'name'   => $request->statuses,
                    'color'   => "#000000",
                    'status' => true
                ]) : Status::where('name', $request->statuses)->first())->id,
            'details'       => $request->details,
            'image'         => $imageName,
            'bus_station_distance'         => $request->bus_station_distance,
            'free_first_months'         => $request->free_first_months,
            'is_foreign_nationality_consultation'         => $request->is_foreign_nationality_consultation,
            'is_newly_built_properties'         => $request->is_newly_built_properties,
            'receiving_time'         => $request->receiving_time,
            'price'         => $request->price,
            'closest_bus_station'         => $request->closest_bus_station,
            'key_money'         => $request->key_money, 
            'deposit'         => $request->deposit, 
            'floor_plan'         => $request->floor_plan,  
            'year_built'         => $request->year_built, 
            'management_costs'         => $request->management_costs,
            'acreage'       => $request->acreage,
            'floor_amount'  => $request->floor_amount,
            'room_amount'   => $request->room_amount,
            'bathroom_amount'=> $request->bathroom_amount,
            'bed_amount'    => $request->bed_amount,
            'host_name'     => $request->host_name,
            'phone_number'  => $request->phone_number,
            'note'          => $request->note,
            'status'        => true,
            'tags'          => $request->tags,
            'user_id'       => $request->user_id,
            'created_at'    => $request->created_at,
            'updated_at'    => $request->updated_at,
        ])->id;

        foreach (explode(",", $request->imgs) as $img) {
            $data = file_get_contents($img);
            $imageName = 'news-img-'.time().uniqid().'.jp2';
            file_put_contents(public_path('images/').$imageName, $data);       
            $id = Gallery::create([
                'news_id'       => $news_id,
                'path'          => $imageName,
                'size'          => strlen($data)
            ])->id;
        }
        return "true";
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|unique:news|max:191',
            'slug'         => 'required|unique:news|max:191',
            'details'       => 'required',
            'address'       => 'required',
            'coords'       => 'required',
            'image'         => 'required|is_img',
            'bus_station_distance'         => 'required',
            'free_first_months'         => 'required',
            'receiving_time'         => 'required',
            'type_id'       => 'required',
            'category_id'   => 'required', 
            'district_id'   => 'required', 
            'statuses_id'     => 'required', 
            'price'         => 'required|integer', 
            'closest_bus_station'         => 'required', 
            'key_money'         => 'required|integer', 
            'deposit'         => 'required|integer', 
            'floor_plan'         => 'required', 
            'year_built'         => 'required|integer', 
            'management_costs' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'acreage'       => 'required|integer', 
            'floor_amount'  => 'required|integer', 
            'room_amount'   => 'required|integer',
            'bathroom_amount'=> 'required|integer',
            'bed_amount'    => 'required|integer',
            'host_name'     => 'required',
            'phone_number'  => 'required'
        ]);
        if(!Gallery::where('news_id',Setting::getRandomId())->exists())
            return redirect()->back()->withErrors(['gallery' => 'you must select at least 1 image']);
        
        if(isset($request->status)){
            $status = true;
        }else{
            $status = false;
        }
        if(isset($request->is_foreign_nationality_consultation)){
            $is_foreign_nationality_consultation = true;
        }else{
            $is_foreign_nationality_consultation = false;
        }
        if(isset($request->is_newly_built_properties)){
            $is_newly_built_properties = true;
        }else{
            $is_newly_built_properties = false;
        }
        if (strlen($request->image) > 0) {

            $image_array_1 = explode(";", $request->image);

            $image_array_2 = explode(",", $image_array_1[1]);

            $data = base64_decode($image_array_2[1]);

            $imageName = 'news-'. time().uniqid(). '.jp2';

            file_put_contents(public_path('images/').$imageName, $data);
        }

        $news_id = News::create([
            'title'         => $request->title,
            'slug'          => str_slug($request->slug),
            'address'       => $request->address,
            'coords'        => $request->coords,
            'category_id'   => $request->category_id,
            'type_id'       => $request->type_id,
            'district_id'   => $request->district_id,
            'statuses_id'     => $request->statuses_id,
            'details'       => $request->details,
            'image'         => $imageName,
            'bus_station_distance'         => $request->bus_station_distance,
            'free_first_months'         => $request->free_first_months,
            'is_foreign_nationality_consultation'         => $is_foreign_nationality_consultation,
            'is_newly_built_properties'         => $is_newly_built_properties,
            'receiving_time'         => $request->receiving_time,
            'price'         => $request->price,
            'closest_bus_station'         => $request->closest_bus_station,
            'key_money'         => $request->key_money, 
            'deposit'         => $request->deposit, 
            'floor_plan'         => $request->floor_plan,  
            'year_built'         => $request->year_built, 
            'management_costs'         => $request->management_costs,
            'acreage'       => $request->acreage,
            'floor_amount'  => $request->floor_amount,
            'room_amount'   => $request->room_amount,
            'bathroom_amount'=> $request->bathroom_amount,
            'bed_amount'    => $request->bed_amount,
            'host_name'     => $request->host_name,
            'phone_number'  => $request->phone_number,
            'note'          => $request->note,
            'status'        => $status,
            'tags'          => $request->tags,
            'user_id'       => Auth::id()
        ])->id;
        Gallery::where('news_id',Setting::getRandomId())->update(['news_id' => $news_id]);
        Attributes::where('news_id',Setting::getRandomId())->update(['news_id' => $news_id]);
        return redirect()->route('admin.news.index')->with(['message' => 'Tạo thành công!']);
    }


    public function show(News $news)
    {
        $news = News::with('category')->with('district')->with('statuses')->with('gallerylist')->with('attributelist')->findOrFail($news->id);
        return view('backend.news.detail', compact('news'));
    }


    public function edit(News $news)
    {
        $news       = News::findOrFail($news->id);
        $categories = Category::latest()->select('id','name')->where('status',1)->get();
        $types = config('properties.news_types');
        $districts = District::latest()->select('id','name')->where('status',1)->get();
        $statuses = Status::latest()->select('id','name')->get();
        $galleries = Gallery::latest()->select('id','path', 'size')->where('news_id',$news->id)->get();
        $attributes = Attributes::latest()->select('id','name', 'value')->where('news_id',Setting::getRandomId())->get();
        return view('backend.news.edit', compact('categories', 'types', 'news', 'districts', 'statuses', 'galleries', 'attributes'));
    }


    public function update(Request $request, News $news)
    {
        $request->validate([
            'title'         => 'required|max:191',
            'slug'         => 'required|max:191',
            'address'       => 'required',
            'coords'       => 'required',
            'bus_station_distance'         => 'required',
            'free_first_months'         => 'required',
            'receiving_time'         => 'required',
            'details'       => 'required',
            'type_id'       => 'required',
            'category_id'   => 'required', 
            'district_id'   => 'required', 
            'statuses_id'     => 'required',  
            'price'         => 'required|integer',
            'closest_bus_station'         => 'required', 
            'key_money'         => 'required|integer', 
            'deposit'         => 'required|integer', 
            'floor_plan'         => 'required', 
            'year_built'         => 'required|integer', 
            'management_costs' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'acreage'       => 'required|integer', 
            'floor_amount'  => 'required|integer', 
            'room_amount'   => 'required|integer',
            'bathroom_amount'=> 'required|integer',
            'bed_amount'    => 'required|integer',
            'host_name'     => 'required|max:191',
            'phone_number'  => 'required|max:191',
        ]);
        if(!Gallery::where('news_id', $news->id)->exists())
            return redirect()->back()->withErrors(['gallery' => 'you must select at least 1 image']);


        if(isset($request->status)){
            $status = true;
        }else{
            $status = false;
        }

        if(isset($request->is_foreign_nationality_consultation)){
            $is_foreign_nationality_consultation = true;
        }else{
            $is_foreign_nationality_consultation = false;
        }
        if(isset($request->is_newly_built_properties)){
            $is_newly_built_properties = true;
        }else{
            $is_newly_built_properties = false;
        }

        $news = News::findOrFail($news->id);
        if (strlen($request->image) > 0) {
            // save new images
            $image_array_1 = explode(";", $request->image);

            $image_array_2 = explode(",", $image_array_1[1]);

            $data = base64_decode($image_array_2[1]);

            $imageName = 'news-'. time().uniqid(). '.jp2';

            file_put_contents(public_path('images/').$imageName, $data);

            // delete old images
            if(file_exists(public_path('images/') . $news->image)){
                unlink(public_path('images/') . $news->image);
            }
        }else{
            $imageName = $news->image;
        }

        $news->update([
            'title'         => $request->title,
            'slug'          => str_slug($request->slug),
            'address'       => $request->address,
            'coords'        => $request->coords,
            'category_id'   => $request->category_id,
            'type_id'       => $request->type_id,
            'district_id'   => $request->district_id,
            'statuses_id'     => $request->statuses_id,
            'details'       => $request->details,
            'image'         => $imageName,
            'bus_station_distance'         => $request->bus_station_distance,
            'free_first_months'         => $request->free_first_months,
            'is_foreign_nationality_consultation'         => $is_foreign_nationality_consultation,
            'is_newly_built_properties'         => $is_newly_built_properties,
            'receiving_time'         => $request->receiving_time,
            'price'         => $request->price,
            'management_costs'         => $request->management_costs,
            'closest_bus_station'         => $request->closest_bus_station,
            'key_money'         => $request->key_money, 
            'deposit'         => $request->deposit, 
            'floor_plan'         => $request->floor_plan,  
            'year_built'         => $request->year_built, 
            'acreage'       => $request->acreage,
            'floor_amount'  => $request->floor_amount,
            'room_amount'   => $request->room_amount,
            'bathroom_amount'=> $request->bathroom_amount,
            'bed_amount'    => $request->bed_amount,
            'host_name'     => $request->host_name,
            'phone_number'  => $request->phone_number,
            'note'          => $request->note,
            'status'        => $status,
            'tags'          => $request->tags,
            'user_id'       => Auth::id()
        ]);
        return redirect()->route('admin.news.index')->with(['message' => 'Chỉnh sửa thành công!']);
    }


    public function destroy(News $news)
    {
        News::deleteNews($news);
        return back()->with(['message' => 'Xóa thành công!']);
    }
}
