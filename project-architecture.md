# 🧺 e-Laundry API - Project Architecture

## Database Schema & Relationships

```mermaid
erDiagram
    users {
        id bigint PK
        name varchar
        email varchar
        password varchar
        is_admin boolean
        created_at timestamp
        updated_at timestamp
    }
    
    services {
        id bigint PK
        name varchar
        category varchar
        price decimal
        pricing_method enum
        price_per_unit decimal
        created_at timestamp
        updated_at timestamp
    }
    
    laundry_orders {
        id bigint PK
        user_id bigint FK
        service_id bigint FK
        quantity int
        total_price decimal
        status enum
        note text
        payment_status enum
        coupon_code varchar
        created_at timestamp
        updated_at timestamp
    }
    
    order_logs {
        id bigint PK
        order_id bigint FK
        admin_id bigint FK
        old_status varchar
        new_status varchar
        created_at timestamp
        updated_at timestamp
    }
    
    coupons {
        id bigint PK
        code varchar
        discount_percent decimal
        expires_at timestamp
        created_at timestamp
        updated_at timestamp
    }
    
    notifications {
        id uuid PK
        type varchar
        notifiable_type varchar
        notifiable_id bigint
        data json
        read_at timestamp
        created_at timestamp
        updated_at timestamp
    }

    users ||--o{ laundry_orders : "places"
    services ||--o{ laundry_orders : "provides"
    laundry_orders ||--o{ order_logs : "tracks"
    users ||--o{ order_logs : "admin_updates"
    users ||--o{ notifications : "receives"
```

## API Architecture Flow

```mermaid
graph TB
    subgraph "Client Layer"
        A[Mobile App] 
        B[Web App]
        C[Admin Panel]
    end
    
    subgraph "API Gateway"
        D[Laravel Routes]
        E[JWT Auth Middleware]
        F[Admin Middleware]
    end
    
    subgraph "Controllers"
        G[AuthController]
        H[LaundryOrderController]
        I[ServiceController]
        J[AdminDashboardController]
        K[NotificationController]
        L[CouponController]
    end
    
    subgraph "Services Layer"
        M[OrderService]
        N[PriceCalculationService]
        O[CouponService]
        P[NotificationService]
    end
    
    subgraph "Repositories"
        Q[OrderRepository]
        R[ServiceRepository]
        S[CouponRepository]
    end
    
    subgraph "Models & Database"
        T[User Model]
        U[LaundryOrder Model]
        V[Service Model]
        W[OrderLog Model]
        X[Coupon Model]
        Y[MySQL Database]
    end
    
    subgraph "Events & Notifications"
        Z[OrderStatusUpdated Event]
        AA[Email Notifications]
        BB[Push Notifications]
    end

    A --> D
    B --> D
    C --> D
    
    D --> E
    E --> F
    E --> G
    E --> H
    E --> I
    F --> J
    E --> K
    E --> L
    
    G --> T
    H --> M
    I --> R
    J --> Q
    K --> P
    L --> O
    
    M --> Q
    M --> N
    N --> S
    O --> S
    P --> AA
    P --> BB
    
    Q --> U
    R --> V
    S --> X
    
    T --> Y
    U --> Y
    V --> Y
    W --> Y
    X --> Y
    
    U --> Z
    Z --> P
```

## Order Status Flow

```mermaid
stateDiagram-v2
    [*] --> Pending : Customer places order
    Pending --> Processing : Admin starts processing
    Pending --> Cancelled : Customer/Admin cancels
    Processing --> Completed : Admin completes order
    Processing --> Cancelled : Admin cancels
    Completed --> [*]
    Cancelled --> [*]
    
    note right of Processing : Notifications sent on each status change
    note right of Completed : Payment processed
```

## Authentication & Authorization Flow

```mermaid
sequenceDiagram
    participant C as Client
    participant A as Auth Controller
    participant M as Middleware
    participant D as Database
    
    C->>A: POST /register or /login
    A->>D: Validate credentials
    D-->>A: User data
    A->>A: Generate JWT token
    A-->>C: Return token + user data
    
    Note over C,D: Subsequent API calls
    
    C->>M: API request with Bearer token
    M->>M: Validate JWT token
    M->>D: Check user exists & is_admin
    D-->>M: User validation
    M-->>C: Allow/Deny access
```

## Service Architecture Components

```mermaid
mindmap
  root((e-Laundry API))
    Authentication
      JWT Tokens
      User Registration
      Admin Authorization
    Order Management
      Place Orders
      Track Status
      Order History
      Price Calculation
    Service Management
      Fixed Pricing
      Weight-based Pricing
      Service Categories
    Admin Features
      Status Updates
      Dashboard Stats
      Revenue Reports
    Notifications
      Email Alerts
      Status Changes
      Real-time Updates
    Coupons & Discounts
      Validation
      Percentage Discounts
      Expiry Management
    Testing
      Feature Tests
      Unit Tests
      SQLite Testing DB
```

## Key Features Overview

### 🔐 Authentication System
- JWT-based authentication using Laravel Sanctum
- User registration and login
- Admin role-based access control
- Protected API routes

### 📦 Order Management
- Create orders with quantity and service selection
- Real-time order status tracking (Pending → Processing → Completed/Cancelled)
- Order history and filtering
- Price calculation with coupon support

### 👨‍💼 Admin Controls
- Update order status with logging
- Dashboard with statistics
- Revenue reporting
- Order management interface

### 🔔 Notification System
- Email notifications on status changes
- Real-time notifications
- Notification history and read status

### 💰 Pricing & Coupons
- Fixed and weight-based pricing methods
- Coupon validation and discount application
- Price calculation service

### 🧪 Testing Infrastructure
- Feature tests for API endpoints
- Unit tests for business logic
- SQLite in-memory testing database
- GitHub Actions CI/CD ready