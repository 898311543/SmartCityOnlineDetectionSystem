From eboraas/apache-php

WORKDIR /var/www/html

ADD . /var/www/html

RUN ["chmod","-R","+777","/var/www/html"]

EXPOSE 80

ENV NAME World

