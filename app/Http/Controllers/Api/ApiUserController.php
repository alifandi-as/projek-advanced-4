<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ExtApiController;

class ApiUserController extends ExtApiController
{
     // Mendapatkan semua user
    public function index(){
        $users = User::query()
                ->get()
                ->toArray();
        return $this->send_success("Data para pengguna:", $users);
    }

     // Menampilkan sebuah user berdasarkan id
     // (id)
    public function show(){
        $users = User::find(auth()->id());
        if ($users == null){
            return $this->send_bad_request("Tidak ada user dengan id ini");
        }
        return $this->send_success("Data pengguna Anda adalah:", $users);
    }

    // Menampilkan user yang diautentikasi
    // (user auth)
   public function profile(){
        $user = auth()->user();
        $user["image_path"] = $user["name"] . strval($user["id"]);
        return $user;
   }

    // Mencari user berdasarkan kueri pencarian
    // (q (kueri pencarian))
    public function search(Request $request){
 
        $search = $request->name;

        // mengambil data dari table pegawai sesuai pencarian data
        $users = DB::table('users')
        ->select('id', 'name', 'email', 'created_at', 'updated_at')
        ->where('name','like',"%".$search."%")
        ->get()
        ->toArray();

        // mengirim data pegawai ke view index
        return $this->send_success("Hasil pencarian:", $users);
    }
    
    // Memasukkan dan memberi akses user
    // (email, password)
    public function login(Request $request){//set validation
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:strict'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        //if auth success
        return $this->send_success("Berhasil login", ["user_data" => auth()->guard('api')->user(), "token" => $token]);
    }
    
    // Mendaftar user dan menambah user ke database
    // (name, email, image, password, password confirmation)
    public function register(Request $request){

        
        //set validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:8|max:2555',
            'email' => ['required', 'email', 'min:8', 'max:255', 'unique:users,email'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,tif,tiff,webp,avif', 'dimensions:max_width=1024,max_height=`1024'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed']
        ]);

        //if validation fails
        if ($validator->fails()) {
            return $this->send_fail($validator->errors());
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password)
        ]);
            
        // Create image
        $img = $request->file('image');
        $destination = "uploads/profile_pic";

        //return response JSON user is created
        if($user) {
            $img_path = $request->name . strval(auth()->id());

            if (!$request->hasFile('image') || $img->move($destination, $img_path)){
                $user['image_path'] = $img_path;
                return $this->send_success_created("Registrasi berhasil", $user);
            }
            else{
                return $this->send_fail("Gagal mengunggah gambar");
            }
        }

        //return JSON process insert failed 
        return $this->send_fail("Registrasi gagal");
    }

    // Mengubah profil user
    // (name, email)
    public function edit_profile(Request $request){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,tif,tiff,webp,avif', 'dimensions:max_width=1024,max_height=`1024']
            ]);
            if ($validator->fails()){
                return $this->send_fail("Gagal mengedit profil", $validator->errors());
            }
            $uid = strval(auth()->id());

            // Update form fields
            $form_fields = $validator->validated();
            User::query()
                ->where("id", "=", $uid)
                ->update($form_fields);

            // Tambah/ganti gambar
            if ($request->hasFile('image'))
            {
                if (file_exists(url("/uploads/profile_pic/".auth()->user()->name . $uid))){
                    if (!$request->file('image')->move("uploads/profile_pic", $request->name . $uid)){
                        return $this->send_fail("Gagal mengganti gambar");
                    }
                }
                else {
                    if (!$request->file('image')->move("uploads/profile_pic", $request->name . $uid)){
                        return $this->send_fail("Gagal menambah gambar");
                    }
                }
            }
            else{
                if (file_exists(url("/uploads/profile_pic/".auth()->user()->name . $uid))){
                    rename(public_path("/uploads/profile_pic/".auth()->user()->name . $uid), public_path('/uploads/profile_pic/'.auth()->user()->name . $uid));
                }
            }
            return $this->send_success("Profil berhasil diubah");
    }

    // Mengubah password user
    // (name, email, old password, new password, new password confirmation)
    public function edit_password(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email:strict'],
            'old_password' => ['required', 'string', 'min:8', 'max:255'],
            'new_password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        if ($validator->fails()){
            return $this->send_fail("Edit password gagal", $validator->errors());
        }
        $form_fields = $validator->validated();
        $prior_form_fields = ['email' => $form_fields["email"],
                            'password' => $form_fields["old_password"]];
        // dd($prior_form_fields['password']);
        // Hash password
        

        if($token = auth()->guard('api')->attempt($prior_form_fields)){
            
            $form_fields['new_password'] = bcrypt($form_fields['new_password']);
            User::query()
                ->where("id", "=", auth()->id())
                ->update(["password" => $form_fields["new_password"]]);

            $auth_form_fields = ['email' => $form_fields["email"],
                                 'password' => $form_fields["new_password"]];
            return $this->send_success("Edit berhasil", auth()->guard('api')->attempt($auth_form_fields));
        }
        return $this->send_fail("Edit gagal karena password lama tidak cocok");
    }

    // Mengeluarkan user
    public function logout(){
       //remove token
       $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

       if($removeToken) {
           //return response JSON
            return $this->send_success("Berhasil logout");
       }
    }

    // Menghapus user
    // (id)
    public function delete(Request $request){
        Order::query()
        ->where("user_id", "=", auth()->id())
        ->delete();
        User::query()
        ->where("id", "=", auth()->id())
        ->take(1)
        ->delete();

        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        return $this->send_success("Berhasil menghapus user");
    }
}
