<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator,Redirect,Response;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;


class PracticalController extends Controller
{
    public $successStatus = 201;
    public $errorStatus = 401;

    protected $maxAttempts = 3; // default is 5
    protected $decayMinutes = 2; // default is 1
    
    public function register(Request $req)
    {
        
      $validator = Validator::make($req->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'cpassword' => 'required|same:password',
      ],[
          'email.unique' => 'Email Already Taken',
          'name.required' => 'Name is Required',
          'email.required' => 'Email is Required',
          'password.min' => 'Password Minium of 6 Characters',
      ]
    );
        
      if ($validator->fails()) {
        return response()->json(['message'=>$validator->errors()],  $this->errorStatus);            
     }
      
    $user = new User([
        'name' => $req->name,
        'email' => $req->email,
        'password' => bcrypt($req->password),
    ]);

    $user->save();

    return response()->json(['message'=>'User successfully registered'], $this->successStatus);            
            
    }
  
    public function login(Request $req)
    {   
        $validator = Validator::make($req->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6'
            ],[
                'email.unique' => 'Email Already Taken',
                'name.required' => 'Name is Required',
                'email.email' => 'Invaild Email Format',
                'password.min' => 'Password Minium of 6 Characters',
            ]
        );
  
              $creds = $req->only(['email','password']);
  
              if(!Auth::attempt($creds)){
              
                return response()->json(['message' => 'Invalid credentials'], $this->errorStatus);
              };
              
              $user = $req->user();

              $tokenRes = $user->createToken('Access Token');
              $token = $tokenRes->accessToken;
              return response()->json(['accessToken' => $token], $this->successStatus);
        
    }

    public function getToken($token)
    {
        return response()->json(['accessToken' => $token], $this->successStatus);
    }

    public function orders(Request $req)
    {

        $validator = Validator::make($req->all(),[
            'product' => 'required',
            'quantity' => 'required'
            ],[
                'product.required' => 'Product is Required',
                'quantity.required' => 'Quantity is Required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['message'=>$validator->errors()],  $this->errorStatus);            
         }

        $product = $req->product;
        $quantity = $req->quantity;

         $products = Product::where('product_id', $product)->get('quantity');

         if(!$products){
           return response()->json(['message' => 'Product does not Exist.'], $this->errorStatus);
         }else{
            if($products[0]->quantity >= $quantity){
                $oldCount = $products[0]->quantity;
                $total = $oldCount - $quantity;

                //update the product table
                $prod = Product::where('product_id', $product)->get();

                $prod->toQuery()->update([
                    'quantity' => $total,
                ]);
                if($prod){
                    $order = new Order([
                        'product_id' => $product,
                        'product_name' => $prod[0]->product_name,
                        'upload_by' => Auth::user()->name,
                        'quantity' => $quantity,
                    ]);

                    $order->save();
                    return response()->json(['message' => 'You have successfully ordered this product.'], $this->successStatus);

                }

            }else{
                return response()->json(['message' => 'Failed to order this product due to unavailability of the stock.'], $this->errorStatus);
            }
         };


         

        // $orders = new Order([
        //     'product_name' => $product,
        //     'product_id' => rand(0,999999),
        //     'upload_by' => Auth::user()->name,
        //     'quantity' => $quantity
        // ]);
    
        // $orders->save();

        // return response()->json(['message' => 'You have successfully ordered this product.','data'=> Order::all()], $this->successStatus);

    }
}
