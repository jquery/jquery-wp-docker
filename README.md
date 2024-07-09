# jquery-wp-docker

This repo has the necessary setup for running [jquery-wp-content](https://github.com/jquery/jquery-wp-content) in [WordPress](https://wordpress.com/) locally using [Docker](https://www.docker.com/).

## Getting started

1. Clone this repo and its submodules

    ```sh
    git clone --recursive git@github.com:jquery/jquery-wp-docker.git
    ```

1. Copy the wp-config-sample.php file to wp-config.php

    ```sh
    cp wp-config-sample.php wp-config.php
    ```

1. Edit the wp-config.php file and set unique keys and salts using https://api.wordpress.org/secret-key/1.1/salt/. Do NOT change the `DB_` defines.

    ```php
    define('AUTH_KEY',         'put your unique phrase here');
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
    // etc.
    ```

1. Copy .env.example to .env and edit the file to define database credentials

    ```sh
    cp .env.example .env
    ```

1. Optionally, add local SSL certs to the `ssl` directory.

    * If you don't have any, you can generate them using [mkcert](https://github.com/FiloSottile/mkcert).
      Run the following:

      ```sh
      mkcert -install
      ```

    * Then, in the `ssl` directory, run:

      ```sh
      mkcert \
      local.jquery.com \
      local.api.jquery.com \
      local.blog.jquery.com \
      local.learn.jquery.com \
      local.releases.jquery.com \
      local.jqueryui.com \
      local.api.jqueryui.com \
      local.blog.jqueryui.com \
      local.jquerymobile.com \
      local.api.jquerymobile.com \
      local.blog.jquerymobile.com \
      local.jquery.org \
      local.brand.jquery.org \
      local.contribute.jquery.org \
      local.meetings.jquery.org
      ```

      Wildcards don't work for multi-level subdomains. Add each site to the list of domains.

    * Rename the created certs to `cert.pem` and `cert-key.pem`.

1. Run `docker compose up --build` to start the containers.

1. Construct the database.

    #### Outside contributors

    You do not need to be on the jQuery Infrastructure Team to test jQuery websites. Each site can be deployed after installing wordpress locally, but the database for that site needs to be created first. The database name for each site is listed below:

    | Site | Database Name |
    |------|---------------|
    | jquery.com | wordpress_jquery_com |
    | api.jquery.com | wordpress_api_jquery_com |
    | blog.jquery.com | wordpress_blog_jquery_com |
    | learn.jquery.com | wordpress_learn_jquery_com |
    | releases.jquery.com | wordpress_releases_jquery_com |
    | jqueryui.com | wordpress_jqueryui_com |
    | api.jqueryui.com | wordpress_api_jqueryui_com |
    | blog.jqueryui.com | wordpress_blog_jqueryui_com |
    | jquerymobile.com | wordpress_jquerymobile_com |
    | api.jquerymobile.com | wordpress_api_jquerymobile_com |
    | blog.jquerymobile.com | wordpress_blog_jquerymobile_com |
    | jquery.org | wordpress_jquery_org |
    | brand.jquery.org | wordpress_brand_jquery_org |
    | contribute.jquery.org | wordpress_contribute_jquery_org |
    | meetings.jquery.org | wordpress_meetings_jquery_org |

    Select the corresponding database name from the table above for the site you wish to test and run the following command to create the database:

    ```sh
    echo 'CREATE DATABASE IF NOT EXISTS wordpress_api_jquery_com;' | docker exec -i jquerydb mysql -u root -proot
    ```

    Then, finish installing WordPress by visiting the appropriate install URL for that site, such as http://local.api.jquery.com/wp-admin/install.php. Make sure the address begins with `local.`.

    Fill in the form with the following information:

    - Site Title: Any (e.g., "jQuery")
    - Username: Any
    - Password: Any
    - Your Email: Any email address
    - Search Engine Visibility: Uncheck

    Click Install WordPress.

    You should now be able to run `grunt deploy` from the corresponding jQuery site repo. Make sure the repo has a `config.json` with the following:

    ```json
    {
      "url": "http://local.api.jquery.com",
      "username": "dev",
      "password": "dev"
    }
    ```

    Replace the `url` with the site you are testing. The `dev` user is automatically created by this repo's wp-config.php.

    After a successful deployment, visit http://local.api.jquery.com to see the site, or https://local.api.jquery.com if you created certs.

    ---

    #### Infrastructure team members only

    ```sh
    # You need SSH admin access to this production server
    ssh wp-05.ops.jquery.net

    sudo -u tarsnap mysqldump --databases `sudo -u tarsnap mysql -B -N -e "SHOW DATABASES LIKE 'wordpress_%'"` > wordpress.sql
    ```

    Then, on your local machine, run:

    ```sh
    # Copy the SQL dump from your home directory on the server (as created by the previous command)
    # NOTE: There must be no space between -p and the password!
    scp -C wp-05.ops.jquery.net:~/wordpress.sql .
    docker exec -i jquerydb mysql -u root -proot < wordpress.sql
    ```

    Optionally, import the blog database as well. This uses a slightly different set of commands because our blogs have a shorter naming convention for their databases than the doc sites. This stems from a time that the blogs were in fact native to the jquery.com site and database, and remain internally named as such.

    ```sh
    ssh wpblogs-01.ops.jquery.net

    # Export wordpress_jquery, and import as wordpress_blog_jquery_com.
    # Use --no-create-db to omit DB name during export, so we can set expected name during import.
    sudo -u tarsnap mysqldump -p wordpress_jquery --no-create-db > wordpress_blog_jquery_com.sql;
    sudo -u tarsnap mysqldump -p wordpress_jqueryui --no-create-db > wordpress_blog_jqueryui_com.sql;
    sudo -u tarsnap mysqldump -p wordpress_jquerymobile --no-create-db > wordpress_blog_jquerymobile_com.sql;
    ```

    And then locally:

    ```sh
    scp -C wpblogs-01.ops.jquery.net:wordpress_blog_{jquery_com,jqueryui_com,jquerymobile_com}.sql .

    echo 'CREATE DATABASE IF NOT EXISTS wordpress_blog_jquery_com; CREATE DATABASE IF NOT EXISTS wordpress_blog_jqueryui_com; CREATE DATABASE IF NOT EXISTS wordpress_blog_jquerymobile_com;' | docker exec -i jquerydb mysql -u root -proot

    docker exec -i jquerydb mysql -u root -proot --database wordpress_blog_jquery_com < wordpress_blog_jquery_com.sql;
    docker exec -i jquerydb mysql -u root -proot --database wordpress_blog_jqueryui_com < wordpress_blog_jqueryui_com.sql;
    docker exec -i jquerydb mysql -u root -proot --database wordpress_blog_jquerymobile_com < wordpress_blog_jquerymobile_com.sql;
    ```

    Then visit http://local.api.jquery.com, or https://local.api.jquery.com if you created certs.

## Updating

To update your setup after pulling down changes, run:

```
docker compose down
docker compose up --build -d
```

## Troubleshooting

### MySQL

To open a REPL to the database, run the `mysql` CLI in the jquerydb container. Make sure to include the `-i` and `-t` opens to connect your own shell to the shell in the container.

```
docker exec -it jquerydb mysql -u root -proot
```

### Ports

jquery-wp-docker is set up to use ports `80` and `443` by default so no extra work is needed to support SSL. However, if either port is in use on your host, you can create a `.env` file in this directory and set the following environment variable with a port number of your own choosing:

```
JQUERY_WP_HTTP_PORT=4000
```

Then, visit the port directly when visiting sites, e.g. http://local.api.jquery.com:4000.

#### A note about port 443

443 is only spun up by Apache if certs are available in the /ssl folder. However, the `docker-compose.yml` does still expose port `443` to the docker images's 443, even if nothing is listening on that port. This shouldn't be an issue in most cases, but the port can be changed in the `.env.` file to avoid any conflicts.

```
JQUERY_WP_HTTPS_PORT=0
```

### DNS

You do not need to configure your `/etc/hosts` file to define `local.jquery.com`, because we have defined these domains in the production DNS for jquery.com as alias for localhost. However, if you plan to work offline, you can add the following rules:

```
127.0.0.1 local.jquery.com
127.0.0.1 local.api.jquery.com
127.0.0.1 local.blog.jquery.com
127.0.0.1 local.learn.jquery.com
127.0.0.1 local.releases.jquery.com

127.0.0.1 local.jqueryui.com
127.0.0.1 local.api.jqueryui.com
127.0.0.1 local.blog.jqueryui.com

127.0.0.1 local.jquerymobile.com
127.0.0.1 local.api.jquerymobile.com
127.0.0.1 local.blog.jquerymobile.com

127.0.0.1 local.jquery.org
127.0.0.1 local.brand.jquery.org
127.0.0.1 local.contribute.jquery.org
127.0.0.1 local.meetings.jquery.org
```
