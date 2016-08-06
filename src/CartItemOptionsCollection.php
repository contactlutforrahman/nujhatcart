<?php
/**
 * Created by PhpStorm.
 * User: Lutfor Rahman
 * Email: contact.lutforrahman@gmail.com
 * Web: www.lutforrahman.com
 * GitHub: https://github.com/contactlutforrahman
 * Packagist: https://packagist.org/users/lutforrahman/
 * Date: 7/28/2016
 * Time: 8:42 AM
 */

namespace Lutforrahman\Nujhatcart;

use Illuminate\Support\Collection;

class CartItemOptionsCollection extends Collection
{
    public function __construct($items)
    {
        parent::__construct($items);
    }

    public function __get($arg)
    {
        if($this->has($arg))
        {
            return $this->get($arg);
        }

        return NULL;
    }
}