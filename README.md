# localhost

url: 
https://server02.transfer-go.localhost/

RabbitMQ url:
http://localhost:15673/
l: user
p: password

path: 
/Users/marcins/works/programming/MagaMS/learning/TransferGo02

notification processing worker:
docker compose exec php bin/console app:notification:processing

PHP: notification sending RabbitMQ consumer - not work
docker compose exec php bin/console app:notification:sending:rabbit-mq-consumer

Python : notification sending RabbitMQ consumer
docker compose exec php python3 bin/notificationConsumer.py


DOCKER
Docer network:
docker network create --driver bridge transfer_go_network
docker network list

SERVER_NAME="server02.transfer-go.localhost" docker compose up -d --wait

stop docker container:
docker compose down --remove-orphans

after Dockerfile changes:
docker compose down --remove-orphans
docker compose build --no-cache
SERVER_NAME="server02.transfer-go.localhost" docker compose up -d --wait

after compose.yaml changes
docker compose stop
SERVER_NAME="server02.transfer-go.localhost" docker compose up -d --wait

add DB:
docker compose exec php composer req symfony/orm-pack
docker compose exec php composer req --dev symfony/maker-bundle

docker compose exec php bin/console app:make:entity Notification

docker compose exec php bin/console make:migration
docker compose exec php bin/console doctrine:migrations:migrate

add RabbitMQ
docker compose exec php composer req php-amqplib/php-amqplib

add Validation
docker compose exec php composer require symfony/validator

add PHPMailer
docker compose exec php composer require phpmailer/phpmailer

add Twilio
docker compose exec php composer require twilio/sdk

add HttpCLient
docker compose exec php composer require symfony/http-client

add testing
docker compose exec php composer req --dev symfony/test-pack
docker compose exec php bin/phpunit

Docer network:
docker network create --driver bridge transfer_go_network
docker network list
docker network inspect transfer_go_network
docker network connect transfer_go_network transfergo02-php-1

