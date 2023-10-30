# Bank api

Ejemplo de una api rest simple para un banco

## Endpoints 

| Metodo | Endpoint | Descripcion
|--------|----------|-----------|
| GET | /api/docs | Documentacion en swagger |
| GET | / | Alias de /api/docs |
| GET | /api/users/ | Obtener todos los usuarios |
| POST | /api/users/ | Crear un nuevo usuario |
| GET | /api/users/{id} | Obtener un usuario por su id |
| PUT | /api/users/{id} | Actualizar un usuario |
| DELETE | /api/users/{id} | Eliminar un usuario |
| POST | /api/users/login/ | Obtiene la informacion del usuario si los datos son correctos
| POST | /api/users/{id}/withdraw | Retira una cantidad de un usuario
| GET | /api/users/{id}/transactions | Obtener todas las transaciones de un usuario
| GET | /api/transactions/{id} | Obtener un transacion por su ID |
| POST | /api/transactions/ | Crear una nueva transacion

## Instalacion

### Docker

1. Clona esta repositorio 
    ```bash
    git clone https://github.com/Mateodioev/bank_api-php.git
    cd bank_api-php
    ```

2. Levanta el servicio con docker
    ```bash
    docker compose up -d
    ```
3. Ve a la documentacion
    ```
    http://localhost:8080/api/docs
    ```
    > Puedes cambiar el puerto en el archivo .env

### Manual

1. Clona esta repositorio 
    ```bash
    git clone https://github.com/Mateodioev/bank_api-php.git
    cd bank_api-php
    ```

2. Instalar la base de datos

    Crea un nuevo usuario de mysql y una db

    ```bash
    mysql -u root -p
    ```

    ```sql
    CREATE USER 'tu_usuario'@'localhost' IDENTIFIED BY 'contraseña';
    CREATE DATABASE bank_example;
    GRANT ALL PRIVILEGES ON bank_example . * TO 'tu_usuario'@'localhost';
    FLUSH PRIVILEGES;
    ```
    Crea las tablas segun las tablas del archivo `db.sql`

3. Crea los archivos de configuracion `.env` y `.htaccess`

    ```bash
    cp example.htaccess .htaccess
    cp example.env .env
    ```
    Luego edita el archivo .env segun tus datos
