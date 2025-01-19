# Inrave Fitness API

The **Inrave Fitness API** is a backend application built on Laravel 11 for managing the premium fitness studio **Inrave**. This API supports both user-facing features and an admin panel for efficient management of services, training requests, news posts, and more. It also includes a **Swagger API documentation** for developers and is equipped with an admin dashboard powered by **Filament**.

---

## Features

### Public Endpoints
- **Services**  
  - `GET /services` - Fetch a list of all services.
  - `GET /services/{service}` - Get details of a specific service.

- **Trainers**  
  - `GET /trainers` - Fetch a list of all trainers.
  - `GET /trainers/{trainer}` - Get details of a specific trainer.

- **Products**  
  - `GET /products` - Fetch a list of all products.
  - `GET /products/{product}` - Get details of a specific product.

- **News**  
  - `GET /news` - Fetch a list of all news articles.
  - `GET /news/{news}` - Get details of a specific news article.

### Authenticated Endpoints
- **Training Requests**  
  - `GET /training-requests` - Fetch all training requests.
  - `POST /training-requests` - Create a new training request.
  - `PUT /training-requests/{id}` - Update an existing training request.
  - `DELETE /training-requests/{id}` - Delete a training request.

- **Authentication**  
  - `POST /login` - Log in to the system (returns an API token).  
  - `POST /logout` - Log out from the system (requires authentication).  

### Admin Panel (Filament)
The admin panel provides an intuitive interface for managing:
- **Services** - Add, update, or remove training services.
- **Training Requests** - View and respond to requests submitted via the feedback form.
- **News** - Create and manage news posts.
- **General Site Settings** - Configure various options for the fitness studio.

---

## Tech Stack
- **Framework:** Laravel 11
- **Admin Dashboard:** Filament
- **API Documentation:** Swagger
- **Authentication:** Laravel Sanctum

---

## Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd inrave_fitness_api
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Set up the environment**:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Configure database and other settings in the `.env` file.

4. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

6. **Seed the database** (optional):
   ```bash
   php artisan db:seed
   ```

7. **Serve the application**:
   ```bash
   php artisan serve
   ```

---

## Usage

### Accessing Swagger Documentation
The Swagger API documentation is available at:
```
<your-app-url>/api/documentation
```

### Admin Panel
The admin panel can be accessed at:
```
<your-app-url>/admin
```
Log in with the credentials configured in your database.

---

## API Routes

### Public Routes
| Method | Endpoint              | Description                        |
|--------|-----------------------|------------------------------------|
| GET    | `/services`           | Fetch all services.               |
| GET    | `/services/{service}` | Fetch details of a specific service. |
| GET    | `/trainers`           | Fetch all trainers.               |
| GET    | `/trainers/{trainer}` | Fetch details of a specific trainer. |
| GET    | `/products`           | Fetch all products.               |
| GET    | `/products/{product}` | Fetch details of a specific product. |
| GET    | `/news`               | Fetch all news articles.          |
| GET    | `/news/{news}`        | Fetch details of a specific article. |

### Authenticated Routes
| Method | Endpoint                  | Description                        |
|--------|---------------------------|------------------------------------|
| GET    | `/training-requests`      | Fetch all training requests.      |
| POST   | `/training-requests`      | Create a new training request.    |
| PUT    | `/training-requests/{id}` | Update an existing training request. |
| DELETE | `/training-requests/{id}` | Delete a training request.        |
| POST   | `/login`                  | Log in to the system.             |
| POST   | `/logout`                 | Log out from the system.          |

---

## Contributing
If you want to contribute:
1. Fork the repository.
2. Create a feature branch: `git checkout -b feature-name`.
3. Commit your changes: `git commit -m "Add new feature"`.
4. Push to the branch: `git push origin feature-name`.
5. Create a pull request.

---

## License
This project is licensed under the [MIT License](LICENSE).

---

## Contact
For questions or support, please contact **Me** at [kudzeri@gmail.com](mailto:kudzeri@gmail.com).
