<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\CategoryLink;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ExtApiController;

class ApiProductController extends ExtApiController
{
     // Mendapatkan semua produk
    public function index(){
        $products = Product::query()
                ->select('id', 'name', 'price')
                ->get()
                ->toArray();
        for($i = 0; $i < count($products); $i++){
            $product_id = $products[$i]["id"];

            $products[$i]["categories"] = Category::whereIn("id", CategoryLink::query()
                                                                                ->where("product_id", "=", $product_id)
                                                                                ->pluck("category_id"))
                                                    ->pluck("name");
            $products[$i]["images"] = ProductImage::query()
                                                        ->where("product_id", "=", $product_id)
                                                        ->first("image")["image"];

            $products[$i]["stars"] = Review::query()
            ->where("product_id", "=", $product_id)
            ->avg("stars");
        }
        return $this->send_success("Data produk:", $products);
    }

    // Mendapatkan semua produk secara detail
   public function index_detailed(){
       $products = Product::query()
               ->get()
               ->toArray();
        for($i = 0; $i < count($products); $i++){
            $product_id = $products[$i]["id"];

            $products[$i]["categories"] = Category::whereIn("id", CategoryLink::query()
                                                    ->where("product_id", "=", $product_id)
                                                    ->pluck("category_id"))
                                                    ->pluck("name");
            $products[$i]["images"] = ProductImage::query()
                                                    ->where("product_id", "=", $product_id)
                                                    ->pluck("image");
            $products[$i]["review"] = Review::query()
                                                    ->where("product_id", "=", $product_id)
                                                    ->select("id", "username", "stars", "description", "created_at")
                                                    ->get()
                                                    ->toArray();
        }
       return $this->send_success("Data produk detail:", $products);
   }

     // Menampilkan suatu produk berdasarkan id
     // (id)
    public function show($id = 0){
        $product = Product::find($id);
        $product_id = $product["id"];

        $product["categories"] = Category::whereIn("id", CategoryLink::query()
                                                                        ->where("product_id", "=", $product_id)
                                                                        ->pluck("category_id"))
                                                ->pluck("name");
        $product["images"] = ProductImage::query()
                                            ->where("product_id", "=", $product_id)
                                            ->pluck("image");
        $product["review"] = Review::query()
                                                ->where("product_id", "=", $product_id)
                                                ->select("id", "username", "stars", "description", "created_at")
                                                ->get()
                                                ->toArray();
        if ($product == null){
            return $this->send_bad_request("Tidak ada produk dengan id ini");
        }
        return $this->send_success("Menampilkan produk ber-id $id:", $product);
    }

    // Mencari produk berdasarkan kueri pencarian
    // (q (kueri pencarian))
    public function search(Request $request){
 
        $name = $request->q;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $category = $request->category;

        // mengambil data dari table produk sesuai pencarian data
        $query = $users = Product::query()
        ->select('id', 'name', 'price');

        if (isset($name)){
            $query->where('name','like', "%".$name."%");
        }

        if (isset($min_price)){
            $query->where('price','<=',$min_price);
        }
        if (isset($request->max_price)){
            $query->where('price','>=',$max_price);
        }
        if (isset($request->category)){
            $query->whereIn('id', CategoryLink::where("category_id", "=", Category::where("name", "like", "%".$category."%")
                                                                         ->first("id")["id"])
                                              ->get()
                                              ->pluck("product_id")
                                              ->toArray());
        }
        
        $query2 = $query->get()->toArray();

        // mengirim data pegawai ke view index
        return $this->send_success("Hasil pencarian:", $query2);
    }

    // Menampilkan produk yang difavoritkan
    // (user_id)
    public function show_favorites(Request $request){
        $favorites = Favorite::where("user_id", "=", auth()->id())
        ->get()
        ->toArray();
        for ($i = 0; $i < count($favorites); $i++){
            $favorites[$i]["product"] = Product::query()
            ->select('id', 'name', 'price')
            ->where('id', "=", $favorites[$i]["product_id"])
            ->first();
        }
        return $this->send_success("Produk yang difavoritkan:", $favorites);
    }

    // Memfavoritkan suatu produk
    // (user_id, product_id)
    public function favorite(Request $request, $product_id){
        if (empty(Favorite::where("product_id", "=", $product_id)
                        ->where("user_id", "=", auth()->id())
                        ->first()))
        {
            Favorite::create([
                "user_id" => auth()->id(),
                "product_id" => $product_id
            ]);
            return $this->send_success("Produk $product_id berhasil difavoritkan");
        }
        else{
            return $this->send_bad_request("Produk ini sudah difavoritkan!");
        }
    }

    // Memfavoritkan suatu produk
    // (user_id, product_id)
    public function unfavorite(Request $request, $product_id){
        if (!empty(Favorite::where("product_id", "=", $product_id)
                        ->where("user_id", "=", auth()->id())
                        ->first()))
        {
            Favorite::query()
            ->where("product_id", "=", $product_id)
            ->where("user_id", "=", auth()->id())
            ->first("id")
            ->delete();
            return $this->send_success("Berhasil menghapus pengfavoritan produk $product_id");
        }
        else{
            return $this->send_bad_request("Produk ini belum difavoritkan!");
        }
    }
}
