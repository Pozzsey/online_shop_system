@extends('user.layout.main')

@section('content')
<section id="prodetails" class="section-p1">
    <div class="single-pro-image">

        <img src="{{ asset('storage/' . $product->image_url) }}" alt="{{ $product->name }}" width="400px" id="MainImg"
            alt="">

        <div class="small-img-group">
            <div class="small-img-col">
                <img src="img/products/f1.jpg" width="100%" class="small-img" alt="">
            </div>
            <div class="small-img-col">
                <img src="img/products/f2.jpg" width="100%" class="small-img" alt="">
            </div>
            <div class="small-img-col">
                <img src="img/products/f3.jpg" width="100%" class="small-img" alt="">
            </div>
            <div class="small-img-col">
                <img src="img/products/f4.jpg" width="100%" class="small-img" alt="">
            </div>
        </div>
    </div>

    <div class="single-pro-details">
        <h6>Home / T-Shirt</h6>
        <h4>{{ $product->name }}</h4>
        <h2>${{ $product->price }}</h2>
        <select>
            <option>Select Size</option>
            <option>S</option>
            <option>M</option>
            <option>L</option>
            <option>XL</option>
        </select>
        <input type="number" value="1" min="1">
        <button class="normal">Add To Cart</button>
        <h4>Product Details</h4>
        <span>{{ $product->description }}</span>
    </div>
</section>
</section>
@endsection
