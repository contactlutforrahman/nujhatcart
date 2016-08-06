<?php
/**
 * Created by PhpStorm.
 * User: Lutfor Rahman
 * Email: contact.lutforrahman@gmail.com
 * Web: www.lutforrahman.com
 * GitHub: https://github.com/contactlutforrahman
 * Packagist: https://packagist.org/users/lutforrahman/
 * Date: 8/6/2016
 * Time: 12:20 AM
 */

namespace Lutforrahman\Nujhatcart;

use Illuminate\Support\ServiceProvider;
class NujhatcartServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['cart'] = $this->app->share(function($app)
        {
            $session = $app['session'];
            $events = $app['events'];
            return new Cart($session, $events);
        });
    }
}