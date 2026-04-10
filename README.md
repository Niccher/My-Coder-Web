# My Coder

A professional-grade, containerized CodeIgniter 4 application designed for high performance and security. This boilerplate provides a robust foundation for building modern web applications with Docker orchestration and production-ready configurations.

## Prerequisites

Before getting started, ensure you have the following installed:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Composer](https://getcomposer.org/) (Optional, for local development outside Docker)

## Quick Start (The Docker Way)

Run the following command to build and start the containers:

```bash
docker-compose up -d --build
```

Once the containers are running, you can access the application and tools at the following URLs:

| Service | URL | Port |
| :--- | :--- | :--- |
| **App** | [http://localhost:8080](http://localhost:8080) | 8080 |
| **phpMyAdmin** | [http://localhost:8081](http://localhost:8081) | 8081 |

## Directory Structure

An overview of the project structure:

```text
.
├── app/            # Application source code (Controllers, Models, Views)
├── public/         # Document root (accessible to the web)
├── writable/       # Cache, logs, sessions, and debug data
├── system/         # CodeIgniter 4 core files
├── vendor/         # Composer dependencies
├── Dockerfile      # PHP-Apache container configuration
└── docker-compose.yml
```

- **public/**: This is the web root. Only this folder should be accessible from the web.
- **app/**: Contains all your application logic.
- **writable/**: Used by the framework for temporary files. This directory must be writable by the web server.

## Environment Configuration

1. Copy the provided environment template:
   ```bash
   cp env .env
   ```
2. Open `.env` and configure your settings. For Docker environments, ensure the database host is set to `db`:
   ```ini
   database.default.hostname = db
   database.default.database = my_coder
   database.default.username = user
   database.default.password = password
   ```

## Optimization & Build Details

This project uses a carefully crafted `.dockerignore` file to ensure:
- **Smaller Image Size**: Excludes `.git`, `node_modules`, and IDE configuration files.
- **Faster Builds**: Only essential source files are copied into the container.
- **Security**: Sensitive local logs and caches are not included in the production image.

## Database Migrations

To run CodeIgniter 4 migrations inside the Docker container, use the following command:

```bash
docker exec -it my_coder_app php spark migrate
```

## Security & Permissions

- **File Permissions**: The `writable/` directory is automatically configured with `775` permissions and `www-data` ownership during the Docker build process.
- **Production Hardening**: The container uses `php.ini-production`, enables `opcache`, and redirects Apache logs to `stdout/stderr` for better observability.

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/awesome-feature`).
3. Commit your changes (`git commit -m 'Add awesome feature'`).
4. Push to the branch (`git push origin feature/awesome-feature`).
5. Open a Pull Request.

Please follow the PSR-12 coding standard and include tests for new features.
