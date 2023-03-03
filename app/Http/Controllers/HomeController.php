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
    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
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

    public function getChaneCoursePage()
    {
        $course = Course::where('name', 'Доллар')->first();
        return view('change_course', compact('course'));
    }

    public function saveCourse(Request $req)
    {
        // dd($req->all());
        $coure = Course::where('name', 'Доллар')->first();
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
        // Artisan::call("parse_one", [
            // 'product_id' => $req->product_id,
        // ]);
        // dd($req->all());
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

    public function test(Request $request)
    {
        $categories_ids = DB::table('wp_term_taxonomy')->where([
            ['taxonomy', '=', 'product_cat'],
            ['parent', '=', 0],
        ])->pluck('term_id')->toArray();

        $product_categories = DB::table('wp_term_relationships')
        ->where('object_id', $request->object_id)->pluck('term_taxonomy_id')->toArray();

        $categories = array_intersect($categories_ids, $product_categories);
        $category = DB::table('wp_terms')->where('term_id', end($categories))
        ->first();
        $weight = match($category->slug){
            'smartfony' => 1,
            'vse-mobilnye-ustrojstva' => 1,
            'smart-chasy' => 1,
            'apple' => 1,
            'iphone' => 1,
            'planshety' => 1.5,
            'noutbuki' => 3.5,
            'monobloki' => 15,
        };
        $raw_price = $request->raw_price;
        // $delivery = DB::table('deliveries')->where('weight', $weight)->first();
        // $delivery_price = self::getDelivery($weight, 'Onex');
            $delivery = match(true){
            $weight == 1 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            // $weight == 1 && $raw_price > 380 && $raw_price < 450 => 32323213123,
            // $weight == 1 && $raw_price < 380 =>      12312312123,
            $weight == 1.5 && $raw_price > 450 =>  3222222,
            // $weight == 1.5 && $raw_price > 380 && $raw_price < 450 => 32323213123,
            // $weight == 1.5 && $raw_price < 380 =>      12312312123,
            $weight == 3.5 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            // $weight == 3.5 && $raw_price > 380 && $raw_price < 450 => 32323213123,
            // $weight == 3.5 && $raw_price < 380 =>      12312312123,

            $weight == 15 && $raw_price > 450 => self::getDelivery($weight, 'Shopfans'),
            // $weight == 15 && $raw_price > 380 && $raw_price < 450 => 32323213123,
            // $weight == 15 &&$raw_price < 380 =>      12312312123,
            // $weight = 3, $ewewe = 322,
            // Вес равен 1кг и стоимость меньше 380
            // $weight == 1 and $raw_price > 450 =>  3222222,
            // $weight == 1 and $raw_price > 450 =>  3222222,
            // $weight == 1 and $raw_price > 450 =>  3222222,
            default => self::getDelivery($weight, 'Onex'),
        };

        $comission = match(true){
            $delivery->name == 'Onex' => 1.05,
            $delivery->name == 'Shopfans' => 1.03,
        };

        // dd($delivery);

        $delivery_price = (float)  match(true){
            $weight == 1 && $delivery->name == 'Shopfans'  => $delivery->price + 3,
            $weight == 3.5 && $delivery->name == 'Shopfans' => $delivery->price + 5,
            $weight == 15 && $delivery->name == 'Shopfans' => $delivery->price + 5,
            default => $delivery->price,
        };
        $snopfan_course = DB::table('courses')->where('name', 'Shopfans')->first();
        $dollar_course = DB::table('courses')->where('name', 'Доллар')->first();
        $price_logistic = match(true){
            $raw_price > 450 && $delivery->name = 'Shopfans' => $delivery_price * $snopfan_course->price * $comission,
            $raw_price > 380 && $raw_price < 450 && $delivery->name = 'Onex' => $delivery_price + (($raw_price - 380) * 0.15),
            default => $delivery_price * $dollar_course->price * $comission,
        };
        $price =  intval($raw_price * ((int) $dollar_course->price * 1.1271) + $price_logistic);

        return response()->json([
            'raw_price' => $raw_price * $dollar_course->price,
            'price_logistic' => $price_logistic,
            'dollar_course' => (int) $dollar_course->price,
            'price' => $price,
        ]);
    }

    public function getDelivery($weight, $name)
    {
        $weight_price =  DB::table('deliveries')->where([
            ['weight', '=', $weight],
            ['name', '=', $name],
        ])->first();
        return $weight_price;
    }
}
