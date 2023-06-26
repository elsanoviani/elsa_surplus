# Waizly
Suggested recipes for lunch API

## How to Deploy
__1. Install Git__

Open your console and run this command
```console
  sudo apt-get install git
```

__2. Install Composer__
```console
  cd ~
  curl -sS https://getcomposer.org/installer -o composer-setup.php
  sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

__3. Clone this Repository__
```console
  https://github.com/elsanoviani/elsa_waizly.git && cd waizly_elsa
```

__4. Copy .env File__
```console
  cp .env.example .env
```
And set up your environment

__5. Install/Update Composer Project__
```console
  composer install
```
__6. Install Composer Laravel Passport__
```console
   composer require laravel/passport=^7
```

__7. Migration database__
```console
   php artisan:migrate
```

__8. Run Composer Laravel Passport__
```console
  php artisan passport:install

  if running php artisan migrate:fresh --seed
  running php artisan passport:install again
```

__9. Run Laravel Server__
```console
  php artisan serve
```

__10. Test on your browser__

Open your browser and type http://127.0.0.1:8000/
If nothing wrong, it will show login page
