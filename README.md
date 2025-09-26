# 🧺 e-Laundry API

A RESTful backend system for managing laundry services, built with **Laravel 12**. This API allows customers to browse services, place orders, and track their status — while admins can manage and update order workflows.

---
<a href="https://www.buymeacoffee.com/mdshoaiburq" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>


## 🚀 Features

- 🔐 **JWT Authentication**
    - Register/Login with token-based authentication
    - Protected routes using Bearer token

- 📋 **Service Management**
    - View available services with category, price, and pricing method (`fixed` or `weight`)

- 📦 **Order Management**
    - Place orders with quantity
    - View user’s order history
    - Filter orders by status

- 👨‍💼 **Admin Controls**
    - Admins can update order status (Pending, Processing, Completed, Cancelled)
    - Order status change logs with admin reference

- 🔔 **Notifications (optional)**
    - Email or system-based alerts when order status changes

- 🧪 **Testing & CI-ready**
    - SQLite in-memory test database
    - Unit and Feature test coverage
    - Ready for GitHub Actions integration

---

## 🛠️ Installation

```bash
git clone https://github.com/your-username/laundryapi.git
cd laundryapi
composer install
cp .env.example .env
php artisan key:generate
