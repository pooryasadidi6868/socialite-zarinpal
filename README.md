# ZarinPal

```bash
composer require pooryasadidi/socialite-zarinpal
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'zarinpal' => [    
  'client_id' => env('ZARINPAL_CLIENT_ID'),  
  'client_secret' => env('ZARINPAL_CLIENT_SECRET'),  
  'redirect' => env('ZARINPAL_REDIRECT_URI') 
],
```

### Add provider event listener

Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        'PooryaSadidi\\ZarinPal\\ZarinPalExtendSocialite@handle',
    ],
];
```

### Usage

this package just test in stateless usage.
You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('zarinpal')->stateless()->redirect();
return Socialite::driver('zarinpal')->stateless()->user();
```

### Returned User fields

- ``id``
- ``fullname``
- ``phone``
- ``avatar``
