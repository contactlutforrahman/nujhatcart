<?php

/**
 * Created by PhpStorm.
 * User: Lutfor Rahman
 * Email: contact.lutforrahman@gmail.com
 * Web: www.lutforrahman.com
 * GitHub: https://github.com/contactlutforrahman
 * Packagist: https://packagist.org/users/lutforrahman/
 * Date: 7/16/2016
 * Time: 12:16 PM
 */

namespace Lutforrahman\Nujhatcart;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

class Cart
{

    /**
     * Session class instance
     *
     * @var Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * Event class instance
     *
     * @var Illuminate\Events\Dispatcher
     */
    protected $event;

    /**
     * Current cart instance
     *
     * @var string
     */
    protected $instance;

    /**
     * The Eloquent model a cart is associated with
     *
     * @var string
     */
    protected $associatedModel;

    /**
     * An optional namespace for the associated model
     *
     * @var string
     */
    protected $associatedModelNamespace;

    /**
     * Constructor
     *
     * @param Illuminate\Session\SessionManager $session Session class instance
     * @param \Illuminate\Contracts\Events\Dispatcher $event Event class instance
     */
    public function __construct($session, Dispatcher $event)
    {
        $this->session = $session;
        $this->event = $event;

        $this->instance = 'main';
    }

    /**
     * Set the current cart instance
     *
     * @param  string $instance Cart instance name
     * @return Lutforrahman\Nujhatcart\Cart
     */
    public function instance($instance = null)
    {
        if (empty($instance)) throw new Exceptions\NujhatcartInstanceException;

        $this->instance = $instance;

        // Return self so the method is chainable
        return $this;
    }

    /**
     * Set the associated model
     *
     * @param  string $modelName The name of the model
     * @param  string $modelNamespace The namespace of the model
     * @return void
     */
    public function associate($modelName, $modelNamespace = null)
    {
        $this->associatedModel = $modelName;
        $this->associatedModelNamespace = $modelNamespace;

        if (!class_exists($modelNamespace . '\\' . $modelName)) throw new Exceptions\NujhatcartUnknownModelException;

        // Return self so the method is chainable
        return $this;
    }

    /**
     * Add an item to the cart
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
    public function insert($id, $sku = null, $name = null, $slug = null, $image = null, $description = null, $quantity = null, $price = null, $discount = null, $tax = null, array $options = [])
    {
        // If the first parameter is an array we need to call the insert() function again
        if (is_array($id)) {
            // And if it's not only an array, but a multidimensional array, we need to
            // recursively call the insert function
            if ($this->is_multi($id)) {
                // Fire the cart.batch event
                $this->event->fire('cart.batch', $id);

                foreach ($id as $item) {
                    $options = array_get($item, 'options', []);
                    $this->insertItem($item['id'], $item['sku'], $item['name'], $item['slug'], $item['image'], $item['description'], $item['quantity'], $item['price'], $this->discountResolve($item), $item['tax'], $options);
                }

                // Fire the cart.batched event
                $this->event->fire('cart.batched', $id);

                return null;
            }

            $options = array_get($id, 'options', []);

            // Fire the cart.insert event
            $this->event->fire('cart.insert', array_merge($id, ['options' => $options]));

            $result = $this->insertItem($id['id'], $id['sku'], $id['name'], $id['slug'], $id['image'], $id['description'], $id['quantity'], $id['price'], $this->discountResolve($id), $id['tax'], $options);

            // Fire the cart.inserted event
            $this->event->fire('cart.inserted', array_merge($id, ['options' => $options]));

            return $result;
        }

        // Fire the cart.insert event
        $this->event->fire('cart.insert', compact('id', 'sku', 'name', 'slug', 'image', 'description', 'quantity', 'price', 'tax', 'options'));

        $result = $this->insertItem($id, $name, $slug, $image, $description, $quantity, $price, $discount, $tax, $options);

        // Fire the cart.inserted event
        $this->event->fire('cart.inserted', compact('id', 'sku', 'name', 'slug', 'image', 'description', 'quantity', 'price', 'tax', 'options'));

        return $result;
    }

    /**
     * @param $data
     * @return null
     */
    public function discountResolve($data)
    {
        if (isset($data['discount']))
            return $data['discount'];
        else
            return null;
    }

    /**
     * Update the quantity of one item of the cart
     *
     * @param  string $itemId The itemid of the item you want to update
     * @param  integer|array $attribute New quantity of the item|Array of attributes to update
     * @return boolean
     */
    public function update($itemId, $attribute)
    {
        if (!$this->hasItemId($itemId)) throw new Exceptions\NujhatcartInvalidItemIDException;

        if (is_array($attribute)) {
            // Fire the cart.update event
            $this->event->fire('cart.update', $itemId);

            $result = $this->updateAttribute($itemId, $attribute);

            // Fire the cart.updated event
            $this->event->fire('cart.updated', $itemId);

            return $result;
        }

        // Fire the cart.update event
        $this->event->fire('cart.update', $itemId);

        $result = $this->updateQuantity($itemId, $attribute);

        // Fire the cart.updated event
        $this->event->fire('cart.updated', $itemId);

        return $result;
    }

    /**
     * Remove an item from the cart
     *
     * @param string $itemId The itemid of an item
     * @return boolean
     */
    public function remove($itemId)
    {
        if (!$this->hasRowId($itemId)) throw new Exceptions\NujhatcartInvalidItemIDException;

        $cart = $this->getContent();

        // Fire the cart.remove event
        $this->event->fire('cart.remove', $itemId);

        $cart->forget($itemId);

        // Fire the cart.removed event
        $this->event->fire('cart.removed', $itemId);

        return $this->updateCart($cart);
    }

    /**
     * Get an item of the cart by its ID
     *
     * @param string $itemId The ID of the item to fetch
     * @return Lutforrahman\Nujhatcart\CartCollection
     */
    public function get($itemId)
    {
        $cart = $this->getContent();

        return ($cart->has($itemId)) ? $cart->get($itemId) : NULL;
    }

    /**
     * Get the cart content
     *
     * @return Lutforrahman\Nujhatcart\CartItemCollection
     */
    public function contents()
    {
        $cart = $this->getContent();

        return (empty($cart)) ? NULL : $cart;
    }

    /**
     * Empty the cart
     *
     * @return boolean
     */
    public function destroy()
    {
        // Fire the cart.destroy event
        $this->event->fire('cart.destroy');

        $result = $this->updateCart(NULL);

        // Fire the cart.destroyed event
        $this->event->fire('cart.destroyed');

        return $result;
    }


    /**
     * Get the number of items in the cart
     *
     * @param  boolean $totalItems Get all the items (when false, will return the number of items)
     * @return int
     */
    public function cartQuantity($totalItems = true)
    {
        $cart = $this->getContent();

        if (!$totalItems) {
            return $cart->count();
        }

        $count = 0;

        foreach ($cart AS $item) {
            $count += $item->quantity;
        }

        return $count;
    }

    /**
     * insert item to the cart
     *
     * @param string $id Unique ID of the item
     * @param string $sku Unique SKU of the item
     * @param string $name Name of the item
     * @param string $slug Slug of the item
     * @param string $image Image of the item
     * @param string $description Description of the item
     * @param int $quantity Item quantity to insert to the cart
     * @param float $price Price of one item
     * @param float $discount Discount amount of one item
     * @param float $tax Tax amount of one item
     * @param array $options Array of additional options, such as 'size' or 'color'
     */
    protected function insertItem($id, $sku, $name, $slug, $image, $description, $quantity, $price, $discount, $tax, array $options = [])
    {
        if (empty($id) || empty($name) || empty($quantity) || !isset($price)) {
            throw new Exceptions\NujhatcartInvalidItemException;
        }

        if (!is_numeric($quantity)) {
            throw new Exceptions\NujhatcartInvalidQuantityException;
        }

        if (!is_numeric($price)) {
            throw new Exceptions\NujhatcartInvalidPriceException;
        }

        if (!is_numeric($discount)) {
            throw new Exceptions\NujhatcartInvalidDiscountException;
        }

        if (!is_numeric($tax)) {
            throw new Exceptions\NujhatcartInvalidTaxException;
        }

        $cart = $this->getContent();
        $itemId = $this->generateItemId($id, $options);

        if ($cart->has($itemId)) {
            $item = $cart->get($itemId);
            $cart = $this->updateItem($itemId, ['quantity' => $item->quantity + $quantity]);
        } else {
            $cart = $this->createItem($itemId, $id, $sku, $name, $slug, $image, $description, $quantity, $price, $discount, $tax, $options);
        }

        $this->updateCart($cart);
        $this->setTotal();
        $this->setSubTotal();
        $this->setDiscount();
		$this->setTax();
        return null;
    }

    /**
     * Generate a unique id for the new item
     *
     * @param  string $id Unique ID of the item
     * @param  array $options Array of additional options, such as 'size' or 'color'
     * @return boolean
     */
    protected function generateItemId($id, $options)
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    /**
     * Check if a itemid exists in the current cart instance
     *
     * @param  string $itemId Unique ID of an item
     * @return boolean
     */
    protected function hasItemId($itemId)
    {
        return $this->getContent()->has($itemId);
    }

    /**
     * Update the cart
     *
     * @param  Lutforrahman\Nujhatcart\CartCollection $cart The new cart content
     * @return void
     */
    protected function updateCart($cart)
    {
        $this->session->put($this->getInstance(), $cart);
        $this->session->save();
        return null;
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return Lutforrahman\Nujhatcart\CartCollection
     */
    protected function getContent()
    {
        $content = ($this->session->has($this->getInstance())) ? $this->session->get($this->getInstance()) : new CartCollection;

        return $content;
    }

    /**
     * Get the current cart instance
     *
     * @return string
     */
    protected function getInstance()
    {
        return 'cart.' . $this->instance;
    }

    /**
     * Updates a specific item.
     *
     * @param  string $itemId The ID of the item to update
     * @param  integer $quantity The quantity to insert to the item
     * @return Lutforrahman\Nujhatcart\CartCollection
     */
    protected function updateItem($itemId, $attributes)
    {
        $cart = $this->getContent();

        $item = $cart->get($itemId);

        foreach ($attributes as $key => $value) {
            if ($key == 'options') {
                $options = $item->options->merge($value);
                $item->put($key, $options);
            } else {
                $item->put($key, $value);
            }
        }

        if (!is_null(array_keys($attributes, ['quantity', 'price']))) {
            $item->put('total', $item->quantity * $item->price);
            $item->put('total_discount', $item->quantity * $item->discount);
            $item->put('total_tax', $item->quantity * $item->tax);
            $item->put('subtotal', ($item->quantity * $item->price) + ($item->quantity * $item->tax) - ($item->quantity * $item->discount));
        }

        $cart->put($itemId, $item);

        $this->setTotal();
        $this->setSubTotal();
        $this->setDiscount();
		$this->setTax();

        return $cart;
    }

    /**
     * Create a new item Object
     *
     * @param  string $itemId The ID of the new item
     * @param  string $id Unique ID of the item
     * @param  string $sku Unique SKU of the item
     * @param  string $name Name of the item
     * @param  string $slug Slug of the item
     * @param  string $image Image of the item
     * @param  string $description Description of the item
     * @param  int $quantity Item quantity to insert to the cart
     * @param  float $price Price of one item
     * @param  float $discount Discount of one item
     * @param  float $tax Tax of one item
     * @param  array $options Array of additional options, such as 'size' or 'color'
     * @return Lutforrahman\Nujhatcart\CartCollection
     */
    protected function createItem($itemId, $id, $sku, $name, $slug, $image, $description, $quantity, $price, $discount, $tax, $options)
    {
        $cart = $this->getContent();

        $newItem = new CartItemCollection([
            'itemid' => $itemId,
            'id' => $id,
            'sku' => $sku,
            'name' => $name,
            'slug' => $slug,
            'image' => $image,
            'description' => $description,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'tax' => $tax,
            'options' => new CartItemOptionsCollection($options),
            'total' => $quantity * $price  - ($quantity * $discount),
            'total_discount' => $quantity * $discount,
            'total_tax' => $quantity * $tax,
            'subtotal' => ($quantity * $price) + ($quantity * $tax) - ($quantity * $discount),
        ], $this->associatedModel, $this->associatedModelNamespace);

        $cart->put($itemId, $newItem);

        return $cart;
    }

    /**
     * Update the quantity of an item
     *
     * @param  string $itemId The ID of the item
     * @param  int $quantity The quantity to insert
     * @return Lutforrahman\Nujhatcart\CartCollection
     */
    protected function updateQuantity($itemId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->remove($itemId);
        }

        return $this->updateItem($itemId, ['quantity' => $quantity]);
    }

    /**
     * Update an attribute of the item
     *
     * @param string $itemId The ID of the item
     * @param array $attributes An array of attributes to update
     * @return Lutforrahman\Nujhatcart\CartCollection
     */
    protected function updateAttribute($itemId, $attributes)
    {
        return $this->updateItem($itemId, $attributes);
    }

    /**
     * Check if the array is a multidimensional array
     *
     * @param  array $array The array to check
     * @return boolean
     */
    protected function is_multi(array $array)
    {
        return is_array(head($array));
    }

    /**
     * @param $amount
     * @return bool
     */
    protected function setCustomDiscount($amount)
    {
        $cart = $this->getContent();

        if (!$cart->isEmpty() && is_numeric($amount)) {
            $cart->custom_discount = floatval($amount);
            $this->setSubTotal();
            $this->updateCart($cart);
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function customDiscount()
    {
        return $this->getContent()->custom_discount;
    }

    /**
     * @return bool
     */
    public function setDiscount()
    {
        $cart = $this->getContent();

        if ($cart->isEmpty()) {
            return false;
        }

        $discount = 0;
        foreach ($cart AS $item) {
            $discount += $item->total_discount;
        }

        $cart->discount = floatval($discount);
        $this->updateCart($cart);

        return true;
    }

    /**
     * @return mixed
     */
    public function discount()
    {
        return $this->getContent()->discount;
    }

    /**
     * @return mixed
     */
    protected function setTotal()
    {
        $cart = $this->getContent();

        if ($cart->isEmpty()) {
            return false;
        }

        $total = 0;
        foreach ($cart AS $item) {
            $total += $item->total;
        }

        $cart->total = floatval($total);
        $this->updateCart($cart);

        return true;
    }

    /**
     * @return mixed
     */
    public function total()
    {
        return $this->getContent()->total;
    }
	
	/**
     * @return mixed
     */
    protected function setTax()
    {
        $cart = $this->getContent();

        if ($cart->isEmpty()) {
            return false;
        }

        $total_tax = 0;
        foreach ($cart AS $item) {
            $total_tax += $item->total_tax;
        }

        $cart->total_tax = floatval($total_tax);
        $this->updateCart($cart);

        return true;
    }

    /**
     * @return mixed
     */
    public function totalTax()
    {
        return $this->getContent()->total_tax;
    }

    /**
     * @return mixed
     */
    protected function setSubTotal()
    {
        $cart = $this->getContent();

        if ($cart->isEmpty()) {
            return false;
        }

        $subtotal = 0;
        foreach ($cart AS $item) {
            $subtotal += $item->subtotal;
        }

        $cart->subtotal = floatval($subtotal - $this->customDiscount());
        $this->updateCart($cart);

        return true;
    }

    /**
     * @return mixed
     */
    public function subtotal()
    {
        return $this->getContent()->subtotal;
    }

}