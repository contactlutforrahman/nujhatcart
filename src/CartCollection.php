<?php
/**
 * Created by PhpStorm.
 * User: Lutfor Rahman
 * Email: contact.lutforrahman@gmail.com
 * Web: www.lutforrahman.com
 * GitHub: https://github.com/contactlutforrahman
 * Packagist: https://packagist.org/users/lutforrahman/
 * Date: 7/16/2016
 * Time: 1:30 PM
 */

namespace Lutforrahman\Nujhatcart;


use Illuminate\Support\Collection;

class CartCollection extends Collection
{
    public $discount = 0.00;

    public $custom_discount = 0.00;

    public $tax = 0.00;

    public $total_tax = 0.00;

    public $total = 0.00;

    public $subtotal = 0.00;
}