# Laravel Stripe Billing System – Full Implementation Plan

## Project Overview
**Tech Stack:** Laravel 12, Inertia, React, Stripe, Sanctum, Cashier

**Architecture:**
- **Admin (CMS):** Inertia/React - Internal panel for configuration & monitoring (no payment actions)
- **User (API):** Laravel/Sanctum - Customer-facing, all payment actions via API

---

## Roles & Responsibilities

### Admin (CMS - Inertia/React)
| Responsibility | Description |
|----------------|-------------|
| Plan Management | CRUD plans, sync with Stripe, enable/disable |
| User Management | View all users |
| Subscription Management | View all subscriptions, cancel, refund |
| Logs | View webhook logs, payment logs |
| Auth | Login only (seeded admin, no registration) |

### User (API - Sanctum)
| Responsibility | Description |
|----------------|-------------|
| Auth | Register, login, logout via API |
| Subscription | Subscribe, change plan, cancel, resume |
| Payment Methods | Add card, list methods, setup intent |
| Invoices | List invoices from Stripe |
| Billing | View current subscription status |

---

## Directory Structure

```
app/
├── Contracts/
│   └── PaymentMethodInterface.php
├── Factories/
│   └── PaymentMethodFactory.php
├── Handlers/
│   ├── CardPaymentHandler.php
│   └── BankTransferPaymentHandler.php
├── Services/
│   ├── SubscriptionService.php
│   └── PlanService.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── PlanController.php
│   │   │   ├── SubscriptionController.php
│   │   │   ├── UserController.php
│   │   │   └── LogController.php
│   │   └── Api/V1/
│   │       ├── AuthController.php
│   │       ├── PlanController.php
│   │       ├── SubscriptionController.php
│   │       ├── PaymentMethodController.php
│   │       ├── InvoiceController.php
│   │       └── WebhookController.php
│   ├── Middleware/
│   │   └── AdminMiddleware.php
│   ├── Requests/
│   │   ├── Admin/
│   │   │   ├── StorePlanRequest.php
│   │   │   └── UpdatePlanRequest.php
│   │   └── Api/
│   │       ├── RegisterRequest.php
│   │       ├── LoginRequest.php
│   │       └── SubscribeRequest.php
│   └── Resources/
│       ├── PlanResource.php
│       ├── SubscriptionResource.php
│       ├── InvoiceResource.php
│       └── UserResource.php
└── Models/
    ├── User.php
    ├── Plan.php
    ├── WebhookLog.php
    └── PaymentLog.php
```

---

## Database Structure

### Migration 1: Add columns to users table
**File:** `database/migrations/xxxx_add_stripe_columns_to_users_table.php`
```
- stripe_id (string, nullable, index)
- pm_type (string, nullable)
- pm_last_four (string, 4, nullable)
- trial_ends_at (timestamp, nullable)
- is_admin (boolean, default: false)
```

### Migration 2: Create plans table
**File:** `database/migrations/xxxx_create_plans_table.php`
```
- id
- name (string)
- slug (string, unique)
- description (text, nullable)
- features (json, nullable)
- monthly_price (integer, cents)
- yearly_price (integer, cents)
- stripe_product_id (string, nullable)
- stripe_price_id_monthly (string, nullable)
- stripe_price_id_yearly (string, nullable)
- is_active (boolean, default: true)
- sort_order (integer, default: 0)
- timestamps
```

### Migration 3: Add columns to Cashier subscriptions table
**File:** `database/migrations/xxxx_add_columns_to_subscriptions_table.php`
```
- plan_id (foreign key to plans)
- payment_method_type (string: 'card' or 'bank_transfer')
```

### Migration 4: Create webhook_logs table
**File:** `database/migrations/xxxx_create_webhook_logs_table.php`
```
- id
- event_type (string, e.g., 'invoice.paid')
- stripe_event_id (string, unique)
- payload (json)
- status (enum: received, processed, failed)
- error_message (text, nullable)
- processed_at (timestamp, nullable)
- timestamps
```

### Migration 5: Create payment_logs table
**File:** `database/migrations/xxxx_create_payment_logs_table.php`
```
- id
- user_id (foreign key)
- subscription_id (nullable, foreign key)
- action (string: subscribe, cancel, resume, change_plan, refund, payment_failed)
- payment_method (string: card, bank_transfer)
- amount (integer, nullable, cents)
- status (enum: success, failed, pending)
- stripe_payment_intent_id (string, nullable)
- metadata (json, nullable)
- timestamps
```

### Cashier Migrations (publish)
```
- subscriptions table
- subscription_items table
```

---

## API Endpoints (Versioned: /api/v1/)

### Auth APIs
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register new user |
| POST | `/api/v1/auth/login` | Login, returns token |
| POST | `/api/v1/auth/logout` | Logout, revoke token |
| GET | `/api/v1/auth/me` | Get authenticated user |

### Plans APIs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/plans` | List active plans |
| GET | `/api/v1/plans/{id}` | Get single plan |

### Subscription APIs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/subscriptions/me` | Get current subscription |
| POST | `/api/v1/subscriptions` | Create subscription |
| POST | `/api/v1/subscriptions/cancel` | Cancel subscription |
| POST | `/api/v1/subscriptions/resume` | Resume subscription |
| PUT | `/api/v1/subscriptions/change-plan` | Change to different plan |

**POST /api/v1/subscriptions payload:**
```json
{
  "plan_id": 1,
  "payment_method": "card",
  "payment_method_id": "pm_xxx",
  "billing_period": "monthly"
}
```

### Payment Methods APIs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/payment-methods` | List user's payment methods |
| POST | `/api/v1/payment-methods` | Add card payment method |
| DELETE | `/api/v1/payment-methods/{id}` | Remove payment method |
| POST | `/api/v1/setup-intent` | Create Stripe SetupIntent |

### Invoices APIs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/invoices` | List user's invoices (from Stripe) |
| GET | `/api/v1/invoices/{id}` | Get invoice details |

### Webhook
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/webhook/stripe` | Handle Stripe webhooks |

**Webhook events handled:**
- `invoice.paid` → Mark subscription active (bank transfer)
- `invoice.payment_failed` → Update status, log failure
- `customer.subscription.updated` → Sync status
- `customer.subscription.deleted` → Mark cancelled
- `charge.refunded` → Log refund

---

## Admin CMS Routes (Web - Inertia)

### Auth
| Route | Description |
|-------|-------------|
| `/login` | Admin login (Breeze) |
| `/logout` | Admin logout |

### Dashboard
| Route | Page | Description |
|-------|------|-------------|
| `/admin/dashboard` | `Admin/Dashboard.jsx` | Stats overview |

### Plans Management
| Route | Page | Description |
|-------|------|-------------|
| `/admin/plans` | `Admin/Plans/Index.jsx` | List all plans |
| `/admin/plans/create` | `Admin/Plans/Create.jsx` | Create plan form |
| `/admin/plans/{id}/edit` | `Admin/Plans/Edit.jsx` | Edit plan form |

### Subscriptions Management
| Route | Page | Description |
|-------|------|-------------|
| `/admin/subscriptions` | `Admin/Subscriptions/Index.jsx` | List all subscriptions |
| `/admin/subscriptions/{id}` | `Admin/Subscriptions/Show.jsx` | View details, cancel, refund |

### Users Management
| Route | Page | Description |
|-------|------|-------------|
| `/admin/users` | `Admin/Users/Index.jsx` | List all users |
| `/admin/users/{id}` | `Admin/Users/Show.jsx` | User details + subscription |

### Logs
| Route | Page | Description |
|-------|------|-------------|
| `/admin/logs/webhooks` | `Admin/Logs/Webhooks.jsx` | Webhook logs, filterable |
| `/admin/logs/payments` | `Admin/Logs/Payments.jsx` | Payment logs, filterable |

---

## Admin Capabilities

| Feature | Actions |
|---------|---------|
| Plans | Create, Read, Update, Delete (archive), Sync with Stripe |
| Subscriptions | View all, Cancel any, Refund payments |
| Users | View all, View subscription history |
| Logs | View webhook logs, View payment logs, Filter by status/date |
| Dashboard | Total users, Active subscriptions, Revenue stats |

---

## Backend Implementation

### Contracts
**File:** `app/Contracts/PaymentMethodInterface.php`
```php
interface PaymentMethodInterface
{
    public function createSubscription(User $user, string $priceId, array $data);
    public function getType(): string;
}
```

### Handlers

**File:** `app/Handlers/CardPaymentHandler.php`
- Implements `PaymentMethodInterface`
- Uses `$user->newSubscription('default', $priceId)->create($paymentMethodId)`

**File:** `app/Handlers/BankTransferPaymentHandler.php`
- Implements `PaymentMethodInterface`
- Uses `$user->newSubscription('default', $priceId)->createAndSendInvoice([], ['days_until_due' => 7])`

### Factory
**File:** `app/Factories/PaymentMethodFactory.php`
```php
public static function make(string $method): PaymentMethodInterface
{
    return match($method) {
        'card' => new CardPaymentHandler(),
        'bank_transfer' => new BankTransferPaymentHandler(),
        default => throw new \InvalidArgumentException("Invalid payment method: {$method}")
    };
}
```

### Services

**File:** `app/Services/SubscriptionService.php`
```php
- create(User $user, Plan $plan, string $paymentMethod, array $data)
- cancel(User $user)
- resume(User $user)
- changePlan(User $user, Plan $newPlan)
- getSubscription(User $user)
- refund(Subscription $subscription, ?int $amount = null)  // Full or partial refund
```

**File:** `app/Services/PlanService.php`
```php
- create(array $data)        // Creates plan + Stripe product/prices
- update(Plan $plan, array $data)
- delete(Plan $plan)         // Archives in Stripe
- toggleActive(Plan $plan)   // Enable/disable
```

---

## Routes Summary

### API Routes (`routes/api.php`)
```php
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Subscriptions
        Route::get('subscriptions/me', [SubscriptionController::class, 'me']);
        Route::post('subscriptions', [SubscriptionController::class, 'store']);
        Route::post('subscriptions/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('subscriptions/resume', [SubscriptionController::class, 'resume']);
        Route::put('subscriptions/change-plan', [SubscriptionController::class, 'changePlan']);

        // Payment Methods
        Route::get('payment-methods', [PaymentMethodController::class, 'index']);
        Route::post('payment-methods', [PaymentMethodController::class, 'store']);
        Route::delete('payment-methods/{id}', [PaymentMethodController::class, 'destroy']);
        Route::post('setup-intent', [PaymentMethodController::class, 'setupIntent']);

        // Invoices
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('invoices/{id}', [InvoiceController::class, 'show']);
    });

    // Plans (public)
    Route::get('plans', [PlanController::class, 'index']);
    Route::get('plans/{plan}', [PlanController::class, 'show']);

    // Webhook (no auth, Stripe signature verification)
    Route::post('webhook/stripe', [WebhookController::class, 'handle']);
});
```

### Web Routes (`routes/web.php`)
```php
// Admin routes (login via Breeze, then admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Plans
    Route::resource('plans', Admin\PlanController::class);
    Route::post('plans/{plan}/toggle-active', [Admin\PlanController::class, 'toggleActive'])->name('plans.toggle-active');

    // Subscriptions
    Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('subscriptions/{subscription}/cancel', [Admin\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('subscriptions/{subscription}/refund', [Admin\SubscriptionController::class, 'refund'])->name('subscriptions.refund');

    // Users
    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [Admin\UserController::class, 'show'])->name('users.show');

    // Logs
    Route::get('logs/webhooks', [Admin\LogController::class, 'webhooks'])->name('logs.webhooks');
    Route::get('logs/payments', [Admin\LogController::class, 'payments'])->name('logs.payments');
});

// Redirect authenticated admin to dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'admin']);
```

---

## Seeders

### AdminUserSeeder
**File:** `database/seeders/AdminUserSeeder.php`
```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true,
    'email_verified_at' => now(),
]);
```

### PlanSeeder
**File:** `database/seeders/PlanSeeder.php`
| Plan | Monthly | Yearly | Features |
|------|---------|--------|----------|
| Pro | $9 | $90 | 10 projects, Priority support, 10GB storage, API access |
| Business | $29 | $290 | Unlimited projects, 24/7 support, 100GB storage, API access, Team features |

---

## React Pages (Admin CMS)

```
resources/js/
├── Layouts/
│   └── AdminLayout.jsx
├── Components/
│   ├── Admin/
│   │   ├── Sidebar.jsx
│   │   ├── StatsCard.jsx
│   │   └── DataTable.jsx
│   └── Common/
│       ├── Modal.jsx
│       ├── Badge.jsx
│       └── Pagination.jsx
└── Pages/
    └── Admin/
        ├── Dashboard.jsx
        ├── Plans/
        │   ├── Index.jsx
        │   ├── Create.jsx
        │   └── Edit.jsx
        ├── Subscriptions/
        │   ├── Index.jsx
        │   └── Show.jsx
        ├── Users/
        │   ├── Index.jsx
        │   └── Show.jsx
        └── Logs/
            ├── Webhooks.jsx
            └── Payments.jsx
```

---

## Key Design Decisions

| Aspect | Decision |
|--------|----------|
| Payment Gateway | Stripe only (via Cashier) |
| Payment Methods | Factory pattern (Card + Bank Transfer) |
| Bank Transfer | Stripe Invoice (`createAndSendInvoice`) with webhook confirmation |
| Invoices | Fetch from Stripe directly via Cashier (no local table) |
| Subscriptions | Use Cashier's table + add `plan_id`, `payment_method_type` |
| Admin Auth | `is_admin` column, seeded admin, login via Breeze |
| User Auth | API only registration/login via Sanctum |
| API Versioning | `/api/v1/` prefix |
| Stripe Keys | `.env` file (standard approach) |
| Logging | Two tables: `webhook_logs` + `payment_logs` |
| Refunds | Full support via Stripe API |

---

## Flow Summary

```
1. Admin creates plans in CMS
   └── Plans saved in DB → synced with Stripe

2. User registers via API
   └── POST /api/v1/auth/register → Creates user + Stripe customer

3. User subscribes via API
   └── POST /api/v1/subscriptions → PaymentMethodFactory → Handler → Stripe

4. Webhooks hit API
   └── POST /api/v1/webhook/stripe → Log event → Update subscription status

5. Admin monitors via CMS
   └── Dashboard, Subscriptions, Users, Logs
```
