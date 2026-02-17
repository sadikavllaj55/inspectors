# Inspectors API

This is a Symfony-based API for managing jobs and inspectors.  


---

## Requirements

- PHP 8.3+
- Composer
- MySQL
- Symfony CLI (optional, recommended)
- Git

---

## Installation

1. **Clone the repository**

```bash
git clone https://github.com/sadikavllaj55/inspectors.git
cd inspectors

composer install

set up env

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

symfony serve
https://localhost:8000
https://localhost:8000/api/doc



