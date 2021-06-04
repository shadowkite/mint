# Mint toolbox
## Running local
Required: PHP 7.4, Composer, Docker
- Clone repository and do ``cd <project dir>``
- Install dependencies with ``composer install``
- Start mainnet-js docker machine with ``bin/start_wallet.sh``
- Start local PHP server with ``cd public/``, ``php -S localhost:8080``
- Go to ``http://localhost:8080``