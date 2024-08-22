# Information:

* Author: Marcin Stanik

# Used technologies

* PHP 8.3
* Python
* Symfony 7.1 - traditional way - MVC, DDD/CQRS will be prepared soon 
* MySQL
* RabbitMQ
* Rest API
* Docker (dunglas/symfony-docker, https://github.com/dunglas/symfony-docker)
* Cron job
* Sending emails by AWS SES (https://docs.aws.amazon.com/ses/latest/APIReference/API_SendEmail.html)
* Sending email by SMTP
* Sending SMS by Twilio (https://www.twilio.com/docs/messaging/api)

# Description:

#### 1
Application is responsible for sending notifications by different channels:
* Different messaging service providers (email: AWS SES, SMTP | sms: Twilio)
* Different technologies for communication (e.g. SMS, email, push notification, Facebook Messenger, etc)

#### 2
* If some of the service notification is down then notification is send by the second one (but only from the same group e.g. email)
* If all servers down then notification is postponed to send later

#### 3
Everything is configurable (config/custom/notifications.yaml):
* define several providers for the same type of notification channel. e.g. two providers for SMS
* enable/disable different communication channels
* maximum number of attempts to send a single message
* delay in minutes for re-sending the notification

#### 4
Logs:
* tracking in DB what messages were sent, when, and to whom.

### 1. All API requests ( REST )
- (POST) https://notification.localhost/api/notification
- (POST) https://notification.localhost/api/notification/email
- (POST) https://notification.localhost/api/notification/sms

save notifications in the database, function **NotificationService->storage()**

tables: 
- notification
- notification_recipient

### 2. every 1 minute CRON execute scrip:
* file: src/Command/NotificationProcessing.php
* cron: * * * * * /usr/local/bin/php /app/bin/console app:notification:processing
* Dockerfile line: 59

### 3. Scrip check if there are any notifications to send

* function **NotificationService->processing()**

### 4. If there are notifications to send, they are sent to RabbitMQ (AMQP)

* file: src/Service/NotificationService.php
* line: 127

### 5. RabbitMQ delegates sending notifications to consumers

### 6. RabbitMQ consumer

python bin/notificationConsumer.py

* is possible create **n** numbers of consumers
* file: frankenphp/docker-entrypoint.sh
* line: 74

### 7. Python script run PHP script: 

Python
* file: bin/notificationConsumer.py
* line: 17

### 8. PHP script is responsible for sending notifications

PHP:
* src/Command/NotificationSending.php
* sending function: **NotificationService->send()**

## Summary

There are 3 steps of sending notifications:
* storage
* processing
* send

####################


### Installation

* git clone https://github.com/MarcinStanik/Notification.git notification
* cd notification
* docker compose down --remove-orphans
* docker compose build --no-cache
* SERVER_NAME="notification.localhost" docker compose up -d --wait
* edit configuration: config/custom/notifications.yaml
* URL: https://notification.localhost/

--

* docker compose stop
* SERVER_NAME="notification.localhost" docker compose up -d --wait

### 2. URL:

https://notification.localhost/

### 3. configs:

- file: config/custom

### 4. API

#### 4.1. https://notification.localhost/api/notification

```
curl --location 'https://notification.localhost/api/notification' \
--header 'Content-Type: application/json' \
--data-raw '{
"recipients": [
{
"id": 1,
"name": "Marcin Stanik",
"email": "marcin.stanik@gmail.com",
"mobile": "+48609778584"
},
{
"id": 2,
"name": "Marcin Stanik 2",
"email": "staniol007@gmail.com",
"mobile": "+48609778584"
}
],
"subject": "Egzample subject 123",
"text_body": "Example text body",
"html_body": "<p>Example html body,<br>second line</p>",
"maxAmountOfNotificationsPerHour": 50,
"chanels": ["SMS", "EMAIL"]
}'
```

#### 4.2. https://notification.localhost/api/notification/email

```
curl --location 'https://notification.localhost/api/notification/email' \
--header 'Content-Type: application/json' \
--data-raw '{
"recipients": ["staniol007@gmail.com"],
"subject": "Egzample subject 123",
"text_body": "Example text body",
"html_body": "<p>Example html body,<br>second line</p>",
"maxAmountOfNotificationsPerHour": 50
}'
```

#### 4.3. https://notification.localhost/api/notification/sms

```
curl --location 'https://notification.localhost/api/notification/sms' \
--header 'Content-Type: application/json' \
--data '{
"recipients": ["+48609778510", "+48609778511", "+48609778512", "+48609778513", "+48609778514", "+48609778515", "+48609778516", "+48609778517", "+48609778518", "+48609778519"],
"text_body": "Example sms body",
"maxAmountOfNotificationsPerHour": 3
}'
```

### 5. MYSQL

* host: localhost:3312
* l: app
* p: !ChangeMe!

## 6. RabbitMQ

* config: config/custom/rabbitmq.yaml
* admin: http://localhost:25672/
* u: user
* p: password

## 7. Tests

* docker compose exec php bin/phpunit

## Screens
![image 01](assets/01.png)

--

![image 01](assets/02.png)

--

![image 01](assets/03.png)

--

![image 01](assets/04.png)

--

![image 01](assets/05.png)

--

![image 01](assets/06.png)
