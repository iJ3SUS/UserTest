<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\User;

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

        DB::table('users')->where('id', $id)->update( $req->all() );

        return $id;

    }
}
