<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\User;
use Validator;

class UserController extends Controller
{
    public function index() {

        $users = User::with('company')->get();

        return $users;
        
    }

    public function store(Request $req){

        $payload = $req->all();

        $idCompany = DB::table('companies')->insertGetId([
            'name' => $payload['company']['name']
        ]);

        $id = DB::table('users')->insertGetId([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'company_id' => $idCompany
        ]);

        return $id;

    }

    public function update( Request $req, $id ){

        $validator = Validator::make($req->all(), [
            'file' => 'required|mimes:png,jpg,jpeg|max:2048'
         ]);
   
        if($validator->fails()) {

            $message = $validator->errors()->first();

            return response($message, 422);

        }
   

        $file = $req->file('file');

        $filename = time().'_'.$file->getClientOriginalName();

        $extension = $file->getClientOriginalExtension();

        $file->move( 'files' , $filename);

        $filepath = url('files/'.$filename);

        DB::table('users')->where('id', $id)->update([
            'birthdate' => $req->birthdate,
            'img' => 'files/'.$filename
        ]);

        return [
            'birthdate' => $req->birthdate,
            'img' => 'files/'.$filename
        ];

    }
}
