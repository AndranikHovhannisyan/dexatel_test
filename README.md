# Dexatel Test

1. Clone the project
 
2. Run `composer update`

3. Create .env.local file and add `AUTH_TOKEN=real-auth-token`

4. Create database by running `bin/console d:d:c`

5. Create corresponding tables by running `bin/console d:m:m`

6. After all this you can run `symfony server:start` to run php server. To install symfony see [here](https://symfony.com/download)
7. To download data from API run `bin/console dexatel:download-data`
