<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        a{
            display: block;
            text-decoration: none;
            color: black;
        }
    </style>
</head>
<body>
    


<a href="{{ url('') . '' }}">{{ url('') . '' }}</a>
<a href="{{ url('') . '/categories' }}">{{ url('') . '/categories' }}</a>
<a href="{{ url('') . '/brands' }}">{{ url('') . '/brands' }}</a>
<a href="{{ url('') . '/campaigns' }}">{{ url('') . '/campaigns' }}</a>
<a href="{{ url('') . '/sellers' }}">{{ url('') . '/sellers' }}</a>
<a href="{{ url('') . '/blogs' }}">{{ url('') . '/blogs' }}</a>

    @foreach ($pages as $page)
    
            <a href="{{ url('') . '/page/' . $page->link }}">{{ url('') . '/page/' . $page->link }}</a>
            {{ $page->created_at->tz('UTC')->toAtomString() }}
    
    @endforeach   
    @foreach ($products as $product)
    
            <a href="{{ url('') . '/product/' . $product->slug }}">{{ url('') . '/product/' . $product->slug }}</a>
            {{ $product->created_at->tz('UTC')->toAtomString() }}
            
            
    
    @endforeach
    @foreach ($blogs as $blog)
    
            <a href="{{ url('') . '/blog/' . $blog->slug }}">{{ url('') . '/blog/' . $blog->slug }}</a>
            {{ $blog->created_at->tz('UTC')->toAtomString() }}
            
            
    
    @endforeach
    @foreach ($categories as $category)
    
            <a href="{{ url('') . '/category/' . $category->slug }}">{{ url('') . '/category/' . $category->slug }}</a>
            {{ $category->created_at->tz('UTC')->toAtomString() }}
            
            
    
    @endforeach
    @foreach ($shops as $shop)
    
            <a href="{{ url('') . '/shop/' . $shop->slug }}">{{ url('') . '/shop/' . $shop->slug }}</a>
            {{ $shop->created_at->tz('UTC')->toAtomString() }}
            
            
    
    @endforeach
    @foreach ($brands as $brand)
    
            <a href="{{ url('') . '/brand/' . $brand->slug }}">{{ url('') . '/brand/' . $brand->slug }}</a>
            {{ $brand->created_at->tz('UTC')->toAtomString() }}
            
            
    
    @endforeach



</body>
</html>