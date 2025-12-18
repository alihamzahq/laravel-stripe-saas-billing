# Laravel Stripe SaaS Billing

A complete, production-ready SaaS subscription billing system built with Laravel 12, Stripe, and React. This project demonstrates a full-stack implementation of subscription management with both an API-first architecture and an admin dashboard.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![React](https://img.shields.io/badge/React-18-61DAFB?style=flat-square&logo=react&logoColor=black)
![Stripe](https://img.shields.io/badge/Stripe-Integrated-635BFF?style=flat-square&logo=stripe&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## Screenshots

### Landing Page
![Landing Page](screenshots/landing-page.png)
*Modern landing page with pricing plans, tech stack, and API documentation downloads*

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)
*Dashboard overview with key metrics and recent activity*

### Plans Management
![Plans Management](screenshots/admin-plans.png)
*Full CRUD interface for subscription plans with Stripe synchronization*

### Subscriptions Management
![Subscriptions](screenshots/admin-subscriptions.png)
*View and manage all customer subscriptions*

### Users Management
![Users](screenshots/admin-users.png)
*User management with payment history*

## Features

### Customer-Facing API
- **Authentication** - Register, login, logout with Laravel Sanctum tokens
- **Subscription Management** - Subscribe, cancel, resume, change plans, and refunds
- **Payment Methods** - Add, remove, and set default payment methods
- **Invoices** - View invoice history and download PDFs
- **Plans** - Browse available subscription plans

### Admin Dashboard
- **Dashboard Overview** - Key metrics and recent activity
- **Plan Management** - Full CRUD with Stripe sync (products & prices)
- **User Management** - View users, payment history, and subscription status
- **Subscription Management** - View, cancel, and refund subscriptions
- **Activity Logs** - Webhook events and payment logs

### Technical Highlights
- Service layer architecture for clean, testable code
- Real-time Stripe webhook handling
- Automatic Stripe product/price synchronization
- Secure API with Laravel Sanctum
- Modern React frontend with Inertia.js

## Tech Stack

| Technology | Purpose |
|------------|---------|
| Laravel 12 | PHP Framework |
| React 18 | Frontend Library |
| Inertia.js | Modern Monolith |
| Laravel Cashier | Stripe Billing |
| Laravel Sanctum | API Authentication |
| Tailwind CSS | Styling |
| MySQL | Database |
| Stripe | Payment Processing |

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL
- Stripe Account (Test Mode)

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/laravel-stripe-saas-billing.git
   cd laravel-stripe-saas-billing
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your `.env` file**
   ```env
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   STRIPE_KEY=pk_test_xxxxx
   STRIPE_SECRET=sk_test_xxxxx
   STRIPE_WEBHOOK_SECRET=whsec_xxxxx
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets and start the server**
   ```bash
   npm run dev
   php artisan serve
   ```

7. **Set up Stripe webhook** (for local development)
   ```bash
   stripe listen --forward-to localhost:8000/api/v1/webhook/stripe
   ```

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
All protected endpoints require a Bearer token:
```
Authorization: Bearer {your-token}
```

### Endpoints

#### Authentication
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/register` | Register new user | No |
| POST | `/auth/login` | Login user | No |
| POST | `/auth/logout` | Logout user | Yes |
| GET | `/auth/me` | Get current user | Yes |

#### Plans
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/plans` | List all active plans | No |
| GET | `/plans/{id}` | Get plan details | No |

#### Subscriptions
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/subscriptions/me` | Get current subscription | Yes |
| POST | `/subscriptions` | Create subscription | Yes |
| POST | `/subscriptions/cancel` | Cancel subscription | Yes |
| POST | `/subscriptions/resume` | Resume subscription | Yes |
| PUT | `/subscriptions/change-plan` | Change plan | Yes |

#### Payment Methods
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/payment-methods` | List payment methods | Yes |
| POST | `/payment-methods` | Add payment method | Yes |
| DELETE | `/payment-methods/{id}` | Remove payment method | Yes |
| POST | `/payment-methods/set-default` | Set default method | Yes |
| POST | `/setup-intent` | Create setup intent | Yes |

#### Invoices
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/invoices` | List invoices | Yes |
| GET | `/invoices/{id}` | Get invoice details | Yes |
| GET | `/invoices/{id}/download` | Download invoice PDF | Yes |

### Postman Collection

Download the complete Postman collection with all endpoints, sample requests, and responses:

- **[Download Postman Collection](public/downloads/Laravel_Stripe_SaaS_Billing_API.postman_collection.json)**
- **[Download Environment File](public/downloads/Laravel_Stripe_SaaS_Billing_API.postman_environment.json)**

**Quick Start:**
1. Import both files into Postman
2. Select the environment from the dropdown
3. Run the Register or Login request to get your auth token
4. The token is automatically saved for subsequent requests

## Admin Panel

Access the admin dashboard at `/admin/login`

### Demo Credentials
```
Email: admin@example.com
Password: password
```

### Admin Features

**Dashboard**
- Total revenue, active subscriptions, and user counts
- Recent subscription activity
- Quick access to all management sections

**Plans Management**
- Create, edit, and delete subscription plans
- Automatic Stripe product and price synchronization
- Monthly and yearly pricing support
- Feature list management

**Users Management**
- View all registered users
- User payment history
- Subscription status overview

**Subscriptions Management**
- View all subscriptions with status
- Cancel subscriptions
- Issue refunds

**Logs**
- Webhook event history
- Payment transaction logs

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/          # API Controllers
│   │   └── Admin/           # Admin Controllers
│   ├── Requests/            # Form Requests
│   └── Resources/           # API Resources
├── Models/                  # Eloquent Models
└── Services/                # Business Logic
    ├── PlanService.php
    ├── SubscriptionService.php
    ├── PaymentMethodService.php
    └── InvoiceService.php

resources/js/
├── Components/              # Reusable Components
├── Layouts/                 # Page Layouts
└── Pages/
    ├── Admin/               # Admin Pages
    └── Welcome.jsx          # Landing Page
```

## Stripe Webhook Events

The application handles the following Stripe webhook events:

- `invoice.paid` - Logs successful payments
- `invoice.payment_failed` - Logs failed payment attempts
- `customer.subscription.updated` - Syncs subscription status changes
- `customer.subscription.deleted` - Handles subscription cancellations
- `charge.refunded` - Logs refund transactions

## Testing with Stripe

Use Stripe test card numbers:
- **Success:** `4242 4242 4242 4242`
- **Decline:** `4000 0000 0000 0002`
- **Requires Auth:** `4000 0025 0000 3155`

[View all test cards](https://stripe.com/docs/testing#cards)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

If you found this project helpful, please consider giving it a star!
