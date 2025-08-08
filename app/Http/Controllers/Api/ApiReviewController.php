<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ExtApiController;

class ApiReviewController extends ExtApiController
{
     // Mendapatkan semua review
    public function index(){
        $reviews = Review::query()
                ->toArray();
        return $this->send_success("Data review:", $reviews);
    }

    // Mendapatkan semua review sesuatu produk
   public function show_product(int $product_id){
       $reviews = Review::query()
               ->where('product_id', '=', $product_id)
               ->toArray();
       return $this->send_success("Data review:", $reviews);
   }

     // Menampilkan review sesuatu produk berdasarkan id
     // (id)
    public function show(int $review_id){
        $product = Product::findOrFail($review_id);
        if ($product == null){
            return $this->send_bad_request("Tidak ada produk dengan id ini");
        }
        return $this->send_success("Menampilkan review ber-id $review_id:", $product);
    }

    // Membuat review suatu produk berdasarkan request dan id
    // (request, id)
    public function create(Request $request, int $product_id){
        
        if (empty(Review::where("product_id", "=", $product_id)
                        ->where("user_id", "=", auth()->id())
                        ->first()))
        {
            
            $added_review = Review::create([
                "product_id" => $product_id,
                "user_id" => auth()->id(),
                "username" => auth()->user()->name,
                "stars" => $request->stars,
                "description" => $request->description
            ]);
            return $this->send_success("Berhasil menambah review untuk produk ber-id $product_id:", $added_review);
        }
        else{
            return $this->send_bad_request("Tidak dapat membuat lebih dari satu review per produk");
        }
    }

    // Menghapus review suatu produk berdasarkan id
    // (id)
    public function remove(int $product_id){
        
        if (!empty(Review::where("product_id", "=", $product_id)
                        ->where("user_id", "=", auth()->id())
                        ->first()))
        {
            Review::query()
            ->where("product_id", "=", $product_id)
            ->where("user_id", "=", auth()->id())
            ->first("id")
            ->delete();

            return $this->send_success("Berhasil menghapus review untuk produk ber-id $product_id");
        }
        else{
            return $this->send_bad_request("Tidak ada review milik Anda untuk produk ini!");
        }
        
    }
}
