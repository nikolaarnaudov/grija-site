# Dockerized WordPress for Local Development

This project contains a fully containerized WordPress setup designed for local plugin and theme development.

## 🚀 Quick Start

1.  **Start the environment:**
    ```bash
    docker-compose up -d
    ```

2.  **Access the sites:**
    - **WordPress Site:** [http://localhost:8090](http://localhost:8090)
    - **Database Manager (phpMyAdmin):** [http://localhost:8091](http://localhost:8091)

3.  **Default Credentials:**
    - **Database Host:** `db`
    - **Database Name:** `wordpress`
    - **Database User:** `wordpress`
    - **Database Password:** `wordpress_password`
    - **Root Password:** `root_password`

## 🛑 Stopping

To stop the containers:
```bash
docker-compose down
```

## 📂 Project Structure

- `docker-compose.yml`: Docker configuration.
- `wp-content/`: Maps directly to the container's `/var/www/html/wp-content`. **Edit files here!**
    - `plugins/`: Your plugins go here.
    - `themes/`: Your themes go here.

See [DEVELOPMENT.md](DEVELOPMENT.md) for detailed coding guides.
See [DEPLOYMENT.md](DEPLOYMENT.md) for how to move this to a live server.
