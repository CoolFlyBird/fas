FROM unual/lamp
MAINTAINER unual  "chengqiwenmail@gmail.com"

COPY . /app
WORKDIR /app
CMD ["php", "./app.php"]