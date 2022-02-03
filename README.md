# docker-lamp

Docker example with Apache, MySql 8.0, PhpMyAdmin and Php

- You can use MariaDB 10.1 if you checkout to the tag `mariadb-10.1` - contribution made by [luca-vercelli](https://github.com/luca-vercelli)
- You can use MySql 5.7 if you checkout to the tag `mysql5.7`

I use docker-compose as an orchestrator. To run these containers, run:

```
docker-compose up -d
```

in a terminal. To shut down, run: 

```
docker-compose down
```

Open phpmyadmin at [http://localhost:8080](http://localhost:8080)
Open web browser to look at a simple php example at [http://localhost](http://localhost)

Enjoy !
