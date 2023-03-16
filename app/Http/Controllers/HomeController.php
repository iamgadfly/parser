<?php

namespace App\Http\Controllers;

use App\Jobs\ParserJob;
use App\Models\Course;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session as FacadesSession;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $course = Course::first();
        $count = DB::table('wp_postmeta')->where([
        ])->count();

        $log_file = Storage::disk('worker')->get('work.log');
        $log_file =  json_encode($log_file, true);
        $log_file = array_reverse(explode('\n', $log_file));
        foreach($log_file as $value){
            if(!str_contains($value, 'I') and empty($time)){
               $time =  $value;
            }
        }
        $time =  stristr($time, 'Ru', true);
        $time_next = Carbon::parse($time)->addHour();
        $user = auth()->user();

        return view('index', compact('course', 'count', 'time', 'time_next', 'user'));
    }

    public function logout()
    {
        FacadesSession::flush();
        Auth::logout();
        return redirect('login');
    }

    public function getChaneCoursePage($name)
    {
        $course = Course::where('name', $name)->first();
        return view('change_course', compact('course'));
    }

    public function saveCourse(Request $req, $name)
    {
        // dd($req->all());
        $coure = Course::where('name', $name)->first();
        $coure->price = $req->price ? $req->price : $coure->price;
        $coure->is_auto = $req->is_auto ? true : false;
        $coure->save();
        return redirect('');
    }

    public function updateProduct(Request $req)
    {
        // dd($req->all());
        $product_id = $req->product_id ??  throw new Exception('ID продукта не может быть пустым');
        $result = Artisan::call("parse_one --product_id=$product_id");
        if(is_array($result)){
            return response()->json(['success' => 'Ваш товар успешно обновился']);
        } else {
            return response()->json(['Error' => 'Что-то пошло не так']);
        }
    }

    public function getUpdateProductView()
    {
        return view('update_product');
    }

    public function addJob()
    {
        dispatch(new ParserJob());
        return response()->json(true, 200);
        return $this->success('true', 200);
    }

    public function getDeliveriesView()
    {
        $deliveries = DB::table('deliveries')->get();

        return view('deliveries', compact('deliveries'));
    }

    public function saveDiliveries(Request $request)
    {
        dd($request->all());
    }
}
