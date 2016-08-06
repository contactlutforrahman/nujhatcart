<?php
/**
 * Created by PhpStorm.
 * User: Lutfor Rahman
 * Email: contact.lutforrahman@gmail.com
 * Web: www.lutforrahman.com
 * GitHub: https://github.com/contactlutforrahman
 * Packagist: https://packagist.org/users/lutforrahman/
 * Date: 7/28/2016
 * Time: 8:40 AM
 */

namespace Lutforrahman\Nujhatcart;

use Illuminate\Support\Collection;

class CartItemCollection extends Collection
{
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
     * Constructor for the CartRowCollection
     *
     * @param array    $items
     * @param string   $associatedModel
     * @param string   $associatedModelNamespace
     */
    public function __construct($items, $associatedModel, $associatedModelNamespace)
    {
        parent::__construct($items);

        $this->associatedModel = $associatedModel;
        $this->associatedModelNamespace = $associatedModelNamespace;
    }

    public function __get($arg)
    {
        if($this->has($arg))
        {
            return $this->get($arg);
        }

        if($arg == strtolower($this->associatedModel))
        {
            $modelInstance = $this->associatedModelNamespace ? $this->associatedModelNamespace . '\\' .$this->associatedModel : $this->associatedModel;
            $model = new $modelInstance;

            return $model->find($this->id);
        }

        return null;
    }
}