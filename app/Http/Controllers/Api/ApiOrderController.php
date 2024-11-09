<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\FinalOrder;
use App\Models\CategoryLink;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\ExtApiController;
use App\Http\Controllers\Api\ApiProductController;

class ApiOrderController extends ExtApiController
{
    // Menampilkan order user
    public function index_user(){
        $orders = Order::query()
        ->where("done", "=", "0")
        ->where("user_id", "=", auth()->id())
        ->get()
        ->toArray();
        return $this->send_success("Order saat ini:", $orders);
    }

    // WIP
    public function select($id = 0){
        $order = Order::find($id);
        return $this->send_success("Order id-$id: ", $order);
    }

    public function add(Request $request){
        $form_fields = $request->validate([
            'product_id' => 'required',
            'quantity' => 'required',
        ]);
        $order = Order::create([
            "user_id" => auth()->id(),
            "product_id" => $form_fields["product_id"],
            "quantity" => $form_fields["quantity"],
            "price" => Product::query()
                                ->where("id", "=", $form_fields["product_id"])
                                ->first("price")["price"]
                       * $form_fields["quantity"],
            "done" => 0
        ]);
        return $this->send_success("Berhasil membuat order: ", $order);
    
    }

    public function multi_add(Request $request){
        $uid = auth()->id();
        foreach($request->items as $item){
            Order::create([
                "id" => $item["id"],
                "user_id" => $uid,
                "product_id" => $item["product_id"],
                "quantity" => $item["quantity"],
                "price" => Product::query()
                                    ->where("id", "=", $item["product_id"])
                                    ->first("price")["price"]
                           * $item["quantity"],
                "done" => 0
            ]);
        }
        
        return $this->send_success("Your orders have been processed.");
    }

    public function edit(Request $request, $id = null){
        $query = Order::query()
        ->where("id", "=", $id);
        if (isset($request->product_id)){
            $query->update(["product_id" => $request->product_id]);
        }
        if (isset($request->quantity)){
            $query->update(["quantity" => $request->quantity,
                            "price" => Product::query()
                                                ->where("id", "=", $request->price)
                                                ->first("price")["price"] * $request->quantity]);
        }
        return $this->send_success("Edit complete.", $query);
    
    }

    public function multi_edit(Request $request){
        $uid = auth()->id();
        foreach($request->items as $item){
            Order::query()
            ->where("id", "=", $item["id"])
            ->update([
                "user_id" => $uid,
                "product_id" => $item["product_id"],
                "quantity" => $item["quantity"],
                "price" => Product::query()
                                    ->where("id", "=", $item["product_id"])
                                    ->first("price")["price"] * $item["quantity"]
            ]);
        }
        
        return $this->send_success("Multiple edit complete.");
    }

    public function multi_edit_quantity(Request $request){
        $counter = 0;
        foreach($request->id as $id){
            Order::query()
            ->where("id", "=", $id)
            ->update([
                "quantity" => $request->quantities[$counter],
                "price" => $request->quantities[$counter] * Product::query()
                    ->where("id", "=", $request->product_id[$counter])
                    ->first("price")["price"]
            ]);
            $counter++;
        }
        
        return $this->send_success("Multiple edit complete.");
    }

    // Batalkan sebuah order
    // (product_id)
    public function delete(Request $request){
        Order::query()
        ->where("id", "=", $request->id)
        ->take(1)
        ->delete();
        return $this->send_success("Order $request->id have been canceled");
        //return $this->send_success("Your order has been canceled");
    }

    // Batalkan beberapa order
    // (user_id, [product_id])
    public function multi_delete(Request $request){
        Order::query()
        ->where("user_id", "=", auth()->id())
        ->whereIn("id", "=", $request->id)
        ->delete();

        return $this->send_success("Order $request->id has been canceled");
    }

    // Batalkan order pengguna yang belum selesai
    // (user_id)
    public function delete_user(Request $request){
        Order::query()
        ->where("user_id", "=", auth()->id())
        ->where("done", "=", "0")
        ->delete();

        return $this->send_success("Your orders have been canceled");
    }

    // Beli order user
    // (user_id)
    public function buy(Request $request){
        $order = Order::query()
        ->where("user_id", "=", auth()->id())
        ->where("done", "=", "0");
        $order->update(["done" => "1"]);

        return $this->send_success("Berhasil membeli order");
    }

    // Langsung beli produk
    // (user_id, product_id)
    public function direct_buy(Request $request, $product_id){
        $form_fields = $request->validate([
            'quantity' => 'required'
        ]);
        Order::create([
            "user_id" => auth()->id(),
            "product_id" => $product_id,
            "quantity" => $form_fields["quantity"],
            "price" => Product::where("id", "=", $product_id)
                                ->first("price")["price"]
                       * $form_fields["quantity"],
            "done" => 1
        ]);

        return $this->send_success("Berhasil membeli langsung produk $product_id");
    }

    // Tampilkan sejarah order pengguna beserta detailnya
    // (user_id)
    public function order_history(Request $request){
        $user_order = Order::query()
        ->where("user_id", "=", auth()->id())
        ->where("done", "=", "1");
        $total_products = $user_order->count();
        $total_price = $user_order->pluck("price")
                                  ->sum();
        
        $product_ids = Order::query()
        ->where("user_id", "=", auth()->id())
        ->where("done", "=", "1")
        ->pluck("product_id");
        $products = Product::query()
        ->select('id', 'name', 'price')
        ->whereIn('id', $product_ids)
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
        }
        return $this->send_success("Sejarah order produk:", [
            "total_products" => $total_products,
            "total_price" => $total_price,
            "products" => ["order" => Order::query()
            ->where("user_id", "=", auth()->id())
            ->where("done", "=", "1")
            ->get()
            ->toArray(), "data" => $products]
        ]);
        
    }
}
