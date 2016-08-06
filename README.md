# Nujhatcart The Laravel Shoppingcart

A simple shoppingcart implementation for Laravel >=5.

## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:


### Laravel 5

```php
composer require lutforrahman/nujhatcart

```

OR


```php

"require": {
	"laravel/framework": "5.0.*",
	"lutforrahman/nujhatcart": "1.0.0"
}

```

Next, run the Composer update command from the Terminal:

    composer update
	
	
Now all you have to do is add the service provider of the package and alias the package. To do this open your `app/config/app.php` file.

Add a new line to the `service providers` array:

	Lutforrahman\Nujhatcart\ShoppingcartServiceProvider::class

After that add a new line to the `aliases` array:

	'Cart' => Lutforrahman\Nujhatcart\Facades\Cart::class,

Now you're ready to start using the shoppingcart in your application.

## Documentation

Look at one of the following topics to learn more about iLaCart


### Add to Cart

The shoppingcart gives you the following methods to use:


**Cart::insert()**

```php

	/**
     * Add a row to the cart
     * @param string|array $id Unique ID of the item|Item formated as array|Array of items
     * @param string $sku Unique SKU of the item
     * @param string $name Name of the item
     * @param string $slug Slug of the item
     * @param string $image Image of the item
     * @param string $description Description of the item
     * @param int $quantity Item quantity to add to the cart
     * @param float $price Price of one item
     * @param float $discount Discount amount of one item
     * @param float $tax Tax amount of one item
     * @param array $options Array of additional options, such as 'size' or 'color'
     */
 

// Basic form

Cart::insert('101', '090-BRC', 'Product name', 'product-name', 'uploads/product-thumbnail.jpg', 1, 9.99, 0.00, 0.00, array('size' => 'large', 'color' => 'white'));

// Array form

$product = Product::find($id);
$item = [
	'id' => $product->id,
	'sku' => $product->sku,
	'name' => $product->name,
	'slug' => $product->slug,
	'image' => $product->thumbnail,
	'description' => $product->description,
	'quantity' => $quantity > 0 ? $quantity : 1,
	'price' => $product->price,
	'discount' => $product->discount_amount,
	'tax' => 0,
	'options' => array('size' => 'XL', 'color' => 'Red')
];
Cart::insert($item);
		
		
// Batch method

$product = Product::find($id);
$item = [
	'id' => $product->id,
	'sku' => $product->sku,
	'name' => $product->name,
	'slug' => $product->slug,
	'image' => $product->thumbnail,
	'description' => $product->description,
	'quantity' => $quantity > 0 ? $quantity : 1,
	'price' => $product->price,
	'discount' => $product->discount_amount,
	'tax' => 0,
	'options' => array('size' => 'XL', 'color' => 'Red')
];

$product2 = Product::find($id2);
$item2 = [
	'id' => $product2->id,
	'sku' => $product2->sku,
	'name' => $product2->name,
	'slug' => $product2->slug,
	'image' => $product2->thumbnail,
	'description' => $product2->description,
	'quantity' => $product2 > 0 ? $quantity : 1,
	'price' => $product2->price,
	'discount' => $product2->discount_amount,
	'tax' => 0,
	'options' => array('size' => 'XL', 'color' => 'Red')
];

Cart::insert(array($item, $item2));
		
	
```


### Update Cart


**Cart::update()**

```php

/**
 * Update the quantity of one row of the cart
 *
 * @param  string        $rowId       The rowid of the item you want to update
 * @param  integer|Array $attribute   New quantity of the item|Array of attributes to update
 * @return boolean
 */
 
 $rowId = 'feeb69e1a11765b136a0de76c2baaa40';

Cart::update($rowId, 4);

OR

Cart::update($rowId, array('name' => 'Product name'));

OR

$product = Product::find($id);

$item = [
	'id' => $product->id,
	'sku' => $product->sku,
	'name' => $product->name,
	'slug' => $product->slug,
	'image' => $product->thumbnail,
	'description' => $product->description,
	'quantity' => $quantity > 0 ? $quantity : 1,
	'price' => $product->price,
	'discount' => $product->discount_amount,
	'tax' => 0,
	'options' => array('size' => 'XL', 'color' => 'Red')
];
Cart::update($rowId, $item);
	
```

// In Controller

```php

	public function updateCart($rowId){
		
		$item = [
			'quantity' => 3,
			'discount' => 9,
			'tax' => 9.5,
			'options' => array('size' => 'XL', 'color' => 'Red')
		];
		Cart::update($rowId, $item);
		
		return Cart::contents();
		
	}

```

### Remove an Item from Cart


**Cart::remove()**

```php

/**
 * Remove a row from the cart
 *
 * @param  string  $rowId The rowid of the item
 * @return boolean
 */

 $rowId = 'feeb69e1a11765b136a0de76c2baaa40';

Cart::remove($rowId);

```


// In Controller

```php

	public function removeCart($rowId){
		Cart::remove($rowId);
		return Cart::contents();
	}

```


### Get a single Item from Cart


**Cart::get()**

```php

/**
 * Get a row of the cart by its ID
 *
 * @param  string $rowId The ID of the row to fetch
 * @return CartRowCollection
 */

$rowId = 'feeb69e1a11765b136a0de76c2baaa40';

Cart::get($rowId);

```


### Get all Items from Cart


**Cart::contents()**

```php

/**
 * Get the cart content
 *
 * @return CartCollection
 */

Cart::contents();

```


### Empty Cart [ remove all items from cart]


**Cart::destroy()**

```php

/**
 * Empty the cart
 *
 * @return boolean
 */

Cart::destroy();

```


### Get total amount of added Items in Cart


**Cart::total()**

```php

/**
 * Total amount of cart
 *
 * @return float
 */

Cart::total();

```


### [Subtotal] Get total amount of an added Item in Cart [single item with quantity > 1]


**Cart::subtotal()**

```php

/**
 * Sub total amount of cart
 *
 * @return float
 */

Cart::subtotal();

```


### Get total discount amount of items added in Cart


**Cart::discount()**

```php

/**
 * Discount of cart
 *
 * @return float
 */

Cart::discount();

```


**Cart::setCustomDiscount(5.00)**

```php

/**
 * @param $amount
 * @return bool
 */

Cart::setCustomDiscount(5.00);

```


**Cart::customDiscount()**

```php

/**
 * Custom discount of cart
 *
 * @return float
 */

 
Cart::customDiscount();

```


### Get total quantity of a single item added in Cart

**Cart::cartQuantity()**

```php

/**
 * Get the number of items in the cart
 *
 * @param  boolean $totalItems Get all the items (when false, will return the number of rows)
 * @return int
 */

 Cart::cartQuantity();      // Total items
 Cart::cartQuantity(false); // Total rows
 
```

### Show Cart contents

```php

foreach(Cart::contents() as $item)
{
	echo "<img src=".$item->image." width='40'/> " . ' Name : ' . $item->name . ' Price : ' . $item->price . ' Size : ' . $item->options->size;
}

```


### Exceptions
The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Cart package or to handle the error based on the type of exceptions. The Cart packages can throw the following exceptions:

| Exception                                 | Reason                                                                                       |
| ----------------------------------------- | -------------------------------------------------------------------------------------------- |
| *NujhatcartInstanceException*       		| When no instance is passed to the instance() method                                          |
| *NujhatcartInvalidItemException*		    | When a new product misses one of it's arguments (`id`, `name`, `quantity`, `price`, `tax`)   |
| *NujhatcartInvalidDiscountException*   	| When a non-numeric discount is passed                                                        |
| *NujhatcartInvalidPriceException*	        | When a non-numeric price is passed                                                           |
| *NujhatcartInvalidQuantityException*      | When a non-numeric quantity is passed                                                        |
| *NujhatcartInvalidRowIDException*   		| When the `$rowId` that got passed doesn't exists in the current cart                         |
| *NujhatcartInvalidTaxException*   		| When a non-numeric tax is passed                                                             |
| *NujhatcartUnknownModelException*	        | When an unknown model is associated to a cart row                                            |



## Example

// Controller

```php

	/**
     * Display the specified resource.
     *
     * @param int $id
     * @param int $quantity
     * @param string $size
     * @param string $color
     * @return \Illuminate\Http\Response
     */
    public function storeCart($id, $quantity, $size, $color)
    {
        $product = Product::find($id);
        $item = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail,
            'description' => $product->description,
            'quantity' => $quantity > 0 ? $quantity : 1,
            'price' => $product->price,
            'discount' => $product->discount_amount,
            'tax' => 0,
			'options' => ['size' => $size, 'color' => $color]
        ];
        Cart::insert($item);
        $items = Cart::contents();
        $quantity = Cart::cartQuantity();
        $total = Cart::total();
		return view('home', ['items' => $items, 'quantity' => $quantity, 'total' => $total]);
    }
```

// View

```php

<table>
   	<thead>
       	<tr>
           	<th>Product</th>
           	<th>Quantity</th>
           	<th>Item Price</th>
           	<th>Discount</th>
           	<th>Subtotal</th>
       	</tr>
   	</thead>

   	<tbody>

   	@foreach(Cart::contents() as $item)

       	<tr>
           	<td>
               	<p><strong>{{ $item->name }} </strong></p>
               	<p>{{ $item->options->has('size') ? $item->options->size : '' }} </p>
               	<p>{{ $item->options->has('color') ? $item->options->color : '' }} </p>
           	</td>
           	<td><input type="text" value="{{ $item->quantity }}"></td>
           	<td>${{ $item->price }} </td>
           	<td>${{ $item->discount }} </td>
           	<td>${{ $item->subtotal }}</td>
       </tr>

   	@endforeach

   	</tbody>
</table>

```

## Follow me
   
For further information and help : www.lutforrahman.com

Follow me on twitter : https://twitter.com/social_lutfor

Like my facebook page : https://www.facebook.com/dev.lutfor.rahman
