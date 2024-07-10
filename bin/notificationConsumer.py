#!/usr/bin/python3

import json
import pika
import time
import subprocess
from datetime import datetime

def callback(ch, method, properties, body):
    message = body.decode()

    data = json.loads(message)
    print(f" [x] {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} - received:")
    print(json.dumps(data, indent=4))

    phpCommand = ['php', '/app/bin/console', 'app:notification:sending', message]
    result = subprocess.run(phpCommand, capture_output=True, text=True)
    print("Output from PHP script:")
    print(result.stdout)

    ch.basic_ack(delivery_tag=method.delivery_tag)

def main():
    url = 'rabbitmq'
    port = 5672
    user = 'user'
    password = 'password'
    queueName = 'notification'

    credentials = pika.PlainCredentials(user, password)

    parameters = pika.ConnectionParameters(
        host=url,
        port=port,
        credentials=credentials
    )

    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()

    channel.queue_declare(queue=queueName, durable=True)

    print(' [*] Waiting for messages. To exit press CTRL+C')

    channel.basic_qos(prefetch_count=1)
    channel.basic_consume(queue=queueName, on_message_callback=callback)

    try:
        channel.start_consuming()
    except KeyboardInterrupt:
        print('Downloading of messages was interrupted')
    finally:
        connection.close()

if __name__ == "__main__":
    main()
