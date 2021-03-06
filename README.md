# Dash Annotations Server

Follow these instructions if you want to set up your own annotation server for [Dash](https://kapeli.com/dash).

## Installation

* Install [Lumen](http://lumen.laravel.com/docs/installation)
* Add a MySQL database called "annotations"
* Clone this repo over your Lumen install
* Rename the `.env.example` file to `.env` and edit it
* Run `composer install`
* Install Python and [Pygments](http://pygments.org/) (used for syntax highlighting)
  * Make sure `/bin/pygmentize` exists. If it doesn't, add a link between `/bin/pygmentize` to wherever you installed Pygments
* Run `php artisan migrate` and type `Y` to confirm you want to do it in production
* Open `http://{your_server}/users/logout` in your browser and check if you get a JSON response that says you're not logged in
* Let Dash know about your server by running this command in Terminal:

```bash
# Repeat on every Mac that will connect to your server:
defaults write com.kapeli.dashdoc AnnotationsCustomServer "http(s)://{your_server}"

# To go back to the default server:
defaults delete com.kapeli.dashdoc AnnotationsCustomServer
```

* If you encounter any issues, [let me know](https://github.com/Kapeli/Dash-Annotations/issues/new)!

### Docker 

* Clone this repo
* Build the image: `docker-compose build`
* Generate your [GitHub Token](https://github.com/settings/tokens) and add it to `docker-compose.yml`
* Set your `APP_KEY` in `docker-compose.yml`
* Start the service: `docker-compose up -d`
* Add `ProxyNginx.conf` to your nginx sites and edit your `server_name`
* Open `http://dash.{your_server}/users/logout` in your browser and check if you get a JSON response that says you're not logged in
* Let Dash know about your server by running this command in Terminal:

```bash
# Repeat on every Mac that will connect to your server:
defaults write com.kapeli.dashdoc AnnotationsCustomServer "http(s)://dash.{your_server}"

# To go back to the default server:
defaults delete com.kapeli.dashdoc AnnotationsCustomServer
```

* If you encounter any issues, [let me know](https://github.com/Kapeli/Dash-Annotations/issues/new)!


### Dokku
> https://github.com/dokku-alt/dokku-alt

* Clone this repo
* Create remote for dokku: `git remote add dokku dokku@{your_server}:dash`
* Create the app: `ssh -t dokku@{your_server} create dash`
* Create the database: `ssh -t dokku@{your_server} mariadb:create dash-db`
* Link database: `ssh -t dokku@{your_server} mariadb:link dash dash-db`
* Get the database credentials: `ssh -t dokku@{your_server} mariadb:info dash dash-db`
* Create environmental variables:
	```
	ssh -t dokku@{your_server} config:set dash \
	APP_ENV=production \
	APP_FALLBACK_LOCAL=en \
	APP_KEY=SomeRandomKey! \
	APP_LOCALE=en \
	CACHE_DRIVER=file \
	DB_CONNECTION=mysql \
	DB_DATABASE=dash-db \
	DB_HOST=mariadb \
	DB_PASSWORD=YourPassword \
	DB_USERNAME=dash \
	QUEUE_DRIVER=file \
	SESSION_DRIVER=file
	```
	
* Push to dokku: `git push dokku dokku:master`
* Get your server's URL: `ssh -t dokku@{your_server}  url dash`
* Open `http://dash.{your_server}/users/logout` in your browser and check if you get a JSON response that says you're not logged in
* Let Dash know about your server by running this command in Terminal:

```bash
# Repeat on every Mac that will connect to your server:
defaults write com.kapeli.dashdoc AnnotationsCustomServer "http(s)://dash.{your_server}"

# To go back to the default server:
defaults delete com.kapeli.dashdoc AnnotationsCustomServer
```

* If you encounter any issues, [let me know](https://github.com/Kapeli/Dash-Annotations/issues/new)!
