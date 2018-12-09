# What is KhatovarWeb?

[![Build Status](https://travis-ci.org/damien-carcel/khatovar-web.svg?branch=master)](https://travis-ci.org/damien-carcel/khatovar-web)

This repository contain the source code of the CMS handling the web site of the medieval association : [“La compagnie franche du Khatovar”](http://www.compagniefranchedukhatovar.fr/)

## Requirements

- PHP 7.1+
- PHP Modules:
    - apcu
    - curl
    - gd
    - intl
    - mysql
- MySQL or MariaDB

## Installation

The following part assume the use of Docker and Docker Compose. However, the same commands (without the Docker part) can be used on a local environment.

### Download and install from GitHub

Just clone the repository, the copy the file `docker-compose.yml.dist` by removing the `.dist` extension.

First build the custom docker images:
```bash
$ docker-compose pull
$ docker-compose build --pull
```

Up the containers by running 

```bash
$ CURRENT_IDS="$(id -u):$(id -g)" docker-compose up -d
```

and install dependencies with

```bash
$ CURRENT_IDS="$(id -u):$(id -g)" docker-compose exec fpm composer install --prefer-dist --optimize-autoloader
```

Composer will ask you for your application configuration (database name, user and password).

You can now populate this database with a basic set of [doctrine fixtures](https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) provided by the bundle (or create your own, of course):

```bash
$ CURRENT_IDS="$(id -u):$(id -g)" docker-compose exec fpm bin/console doctrine:schema:update --force
$ CURRENT_IDS="$(id -u):$(id -g)" docker-compose exec fpm bin/console doctrine:fixtures:load --fixtures=features/Context/DataFixtures/ORM/LoadUserData.php
```

### Deploy the assets

Run the following command:

```bash
$ docker-compose exec fpm bin/console assets:install www --symlink --relative
$ CURRENT_IDS="$(id -u):$(id -g)" docker-compose run node yarn install
$ CURRENT_IDS="$(id -u):$(id -g)" docker-compose run node yarn run assets
```

You should now be able to access the application and login with `localhost:8080/login`.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

The Symfony framework is licensed under The MIT License. For full copyright and license information, please see the [MIT License](http://www.opensource.org/licenses/mit-license.php).
